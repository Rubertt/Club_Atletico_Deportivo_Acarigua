<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

final class ReportePruebasFisicasService
{
    /**
     * Genera el reporte de detalle de una sesión de pruebas físicas en PDF/HTML.
     */
    public function reporteDetalle(int $actividadId): ?array
    {
        $db = Database::connection();

        // 1. Obtener detalles de la actividad (tipo_actividad = 2, Pruebas Físicas)
        $stmtAct = $db->prepare("
            SELECT a.*, CONCAT(u.nombre, ' ', u.apellido) AS entrenador,
                   (SELECT c.nombre_categoria FROM asig_categorias ac JOIN categorias c ON ac.categoria_id = c.categoria_id WHERE ac.asignacion_id = a.asignacion_id LIMIT 1) AS nombre_categoria
            FROM actividades a
            LEFT JOIN usuarios u ON a.usuario_id = u.usuario_id
            WHERE a.actividad_id = ? AND a.tipo_actividad = 2
        ");
        $stmtAct->execute([$actividadId]);
        $actividad = $stmtAct->fetch();

        if (!$actividad) {
            return null;
        }

        // 2. Obtener lista de atletas con sus resultados de pruebas
        $stmtResultados = $db->prepare("
            SELECT rp.*, atl.nombre, atl.apellido, atl.cedula, atl.fecha_nac
            FROM resultados_pruebas rp
            JOIN atletas atl ON rp.atleta_id = atl.atleta_id
            WHERE rp.actividad_id = ?
            ORDER BY atl.apellido, atl.nombre
        ");
        $stmtResultados->execute([$actividadId]);
        $detalles = $stmtResultados->fetchAll();

        $esc = fn($v) => htmlspecialchars((string) ($v ?? '—'), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $terrenoMap = [
            1 => 'Grama Natural',
            2 => 'Grama Sintética',
            3 => 'Grama Alta',
            4 => 'Tierra',
            5 => 'Húmedo',
            6 => 'Alt'
        ];
        $climaMap = [
            0 => 'Soleado',
            1 => 'Nublado',
            2 => 'Lluvioso',
            3 => 'Viento',
            4 => 'Tormenta'
        ];

        // Construir filas
        $rows = '';
        $modeloPrueba = new \App\Models\ResultadoPrueba();
        foreach ($detalles as $d) {
            $fuerza = $d['test_de_fuerza'] !== null ? number_format((float)$d['test_de_fuerza'], 1) . ' cm' : '—';
            $resistencia = $d['test_resistencia'] !== null ? (int)$d['test_resistencia'] . ' m' : '—';
            $velocidad = $d['test_velocidad'] !== null ? number_format((float)$d['test_velocidad'], 2) . ' seg' : '—';
            $coordinacion = $d['test_coordinacion'] !== null ? number_format((float)$d['test_coordinacion'], 1) . ' seg' : '—';
            $reaccion = $d['test_de_reaccion'] !== null ? (int)$d['test_de_reaccion'] . ' ms' : '—';

            $promedioVal = null;
            if (!empty($d['fecha_nac'])) {
                $modeloPrueba->calcularPromedioNacional($d, (string)$d['fecha_nac'], (string)$actividad['fecha']);
                $promedioVal = $modeloPrueba->promedio;
            }
            $promedio = $promedioVal !== null ? number_format((float)$promedioVal, 1) . ' pts' : '—';

            $rows .= sprintf(
                '<tr>
                    <td width="40%%" style="text-align: left; vertical-align: middle; font-weight: bold; font-size: 10px;">%s</td>
                    <td width="10%%" style="text-align: center; vertical-align: middle;">%s</td>
                    <td width="10%%" style="text-align: center; vertical-align: middle;">%s</td>
                    <td width="10%%" style="text-align: center; vertical-align: middle;">%s</td>
                    <td width="10%%" style="text-align: center; vertical-align: middle;">%s</td>
                    <td width="10%%" style="text-align: center; vertical-align: middle;">%s</td>
                    <td width="10%%" style="text-align: center; vertical-align: middle; font-weight: bold;">%s</td>
                </tr>',
                $esc($d['nombre'] . ' ' . $d['apellido'] . (!empty($d['cedula']) ? ' (' . \App\Models\Atleta::formatCedula($d['cedula']) . ')' : '')),
                $esc($fuerza),
                $esc($resistencia),
                $esc($velocidad),
                $esc($coordinacion),
                $esc($reaccion),
                $esc($promedio)
            );
        }

        // Bloque de información en 2 columnas
        $infoItems = [
            ['label' => 'Categoría Deportiva:', 'val' => $actividad['nombre_categoria'] ?? 'Sin Categoría'],
            ['label' => 'Fecha de Evaluación:', 'val' => date('d/m/Y', strtotime($actividad['fecha']))],
            ['label' => 'Evaluador / Entrenador:', 'val' => $actividad['entrenador'] ?? 'No definido'],
        ];

        if (!empty($actividad['hora_inicio']) && !empty($actividad['hora_fin'])) {
            $horario = date('h:i A', strtotime($actividad['hora_inicio'])) . ' - ' . date('h:i A', strtotime($actividad['hora_fin']));
            $infoItems[] = ['label' => 'Horario:', 'val' => $horario];
        }
        if (!empty($actividad['ubicacion'])) {
            $infoItems[] = ['label' => 'Ubicación:', 'val' => $actividad['ubicacion']];
        }
        if (isset($actividad['terreno']) && isset($terrenoMap[(int)$actividad['terreno']])) {
            $infoItems[] = ['label' => 'Terreno de Juego:', 'val' => $terrenoMap[(int)$actividad['terreno']]];
        }
        if (isset($actividad['clima']) && isset($climaMap[(int)$actividad['clima']])) {
            $infoItems[] = ['label' => 'Clima:', 'val' => $climaMap[(int)$actividad['clima']]];
        }

        $infoHtml = '<table class="info-grid" cellpadding="4">';
        $count = count($infoItems);
        for ($i = 0; $i < $count; $i += 2) {
            $infoHtml .= '<tr>';
            $infoHtml .= '<td class="info-label" width="22%">' . $esc($infoItems[$i]['label']) . '</td>';
            $infoHtml .= '<td class="info-value" width="28%">' . $esc($infoItems[$i]['val']) . '</td>';
            
            if (isset($infoItems[$i + 1])) {
                $infoHtml .= '<td class="info-label" width="22%">' . $esc($infoItems[$i + 1]['label']) . '</td>';
                $infoHtml .= '<td class="info-value" width="28%">' . $esc($infoItems[$i + 1]['val']) . '</td>';
            } else {
                $infoHtml .= '<td class="info-label" width="22%"></td><td class="info-value" width="28%"></td>';
            }
            $infoHtml .= '</tr>';
        }
        $infoHtml .= '</table>';

        $html = <<<HTML
<style>
    body { font-family: helvetica, sans-serif; color: #333; line-height: 1.5; }
    .section { margin-bottom: 25px; }
    .section-header { 
        background-color: #f8f9fa; 
        border-bottom: 2px solid #800020; 
        padding: 8px 10px; 
        margin-bottom: 12px; 
        font-size: 13px; 
        font-weight: bold; 
        color: #800020; 
        text-transform: uppercase; 
    }
    table.info-grid { width: 100%; margin-bottom: 10px; }
    table.info-grid td { padding: 5px 6px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; line-height: 1.4; }
    .info-label { font-weight: bold; color: #800020; font-size: 10.5px; }
    .info-value { color: #333; font-size: 10.5px; }
    table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.data-table th { background-color: #800020; color: #ffffff; font-size: 10px; font-weight: bold; padding: 9px 5px; text-align: center; border: 1px solid #800020; }
    table.data-table td { font-size: 10px; padding: 6px 5px; text-align: center; border: 1px solid #e9ecef; }
    table.data-table tr:nth-child(even) { background-color: #fcfcfc; }
</style>

<div class="section">
    <h2 style="margin:0; color:#1a1a1a; font-size: 18px; text-align: center;">Reporte de Resultados de Pruebas Físicas</h2>
    <p style="margin:4px 0 16px 0; font-size: 10px; color: #666; text-align: center;">Sistema de Gestión Club Atlético Deportivo Acarigua (CADA)</p>
    
    <div class="section-header">Información General de la Sesión</div>
    {$infoHtml}
    
    <br>
    <div class="section-header">Resultados por Atleta</div>
    <table class="data-table" cellpadding="4">
        <thead>
            <tr>
                <th width="40%" style="text-align: left;">Atleta</th>
                <th width="10%">Fuerza (CMJ)</th>
                <th width="10%">Resist. (Yo-Yo)</th>
                <th width="10%">Veloc. (30m)</th>
                <th width="10%">Coord. (Conos)</th>
                <th width="10%">Reacc.</th>
                <th width="10%">Promedio</th>
            </tr>
        </thead>
        <tbody>
            {$rows}
        </tbody>
    </table>
</div>
HTML;

        $filename = 'pruebas_fisicas_' . preg_replace('/[^a-z0-9]+/i', '_', $actividad['nombre_categoria'] ?? 'sesion') . '_' . date('Ymd', strtotime($actividad['fecha']));

        if (class_exists(PdfGenerator::class)) {
            return (new PdfGenerator())->render(
                'Reporte Pruebas Fisicas - ' . ($actividad['nombre_categoria'] ?? 'Sesion'),
                $html,
                strtolower($filename)
            );
        }

        return ['mime' => 'text/html', 'filename' => $filename . '.html', 'content' => $html];
    }

    /**
     * Genera el reporte consolidado de pruebas físicas para una categoría deportiva y sesión específica.
     */
    public function reporteCategoria(int $categoriaId, int $actividadId): ?array
    {
        $db = Database::connection();

        // 1. Obtener detalles de la categoría
        $stmtCat = $db->prepare("SELECT nombre_categoria FROM categorias WHERE categoria_id = ?");
        $stmtCat->execute([$categoriaId]);
        $categoria = $stmtCat->fetch();

        if (!$categoria) {
            return null;
        }

        // 2. Obtener detalles de la sesión/actividad física seleccionada
        $stmtAct = $db->prepare("
            SELECT a.*, CONCAT(u.nombre, ' ', u.apellido) AS entrenador
            FROM actividades a
            LEFT JOIN usuarios u ON a.usuario_id = u.usuario_id
            WHERE a.actividad_id = ? AND a.tipo_actividad = 2
        ");
        $stmtAct->execute([$actividadId]);
        $actividad = $stmtAct->fetch();

        if (!$actividad) {
            return null;
        }

        // 3. Obtener lista de atletas activos en esa categoría que tienen resultados en esta sesión
        $stmtAtletas = $db->prepare("
            SELECT a.atleta_id, a.nombre, a.apellido, a.cedula, a.fecha_nac, rp.*
            FROM atletas a
            JOIN asig_categorias ac ON ac.atleta_id = a.atleta_id
            JOIN resultados_pruebas rp ON rp.atleta_id = a.atleta_id
            WHERE ac.categoria_id = ? AND rp.actividad_id = ? AND a.estatus = 1 AND ac.estatus = 1
            ORDER BY a.apellido, a.nombre
        ");
        $stmtAtletas->execute([$categoriaId, $actividadId]);
        $atletas = $stmtAtletas->fetchAll();

        $esc = fn($v) => htmlspecialchars((string) ($v ?? '—'), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $terrenoMap = [
            1 => 'Grama Natural',
            2 => 'Grama Sintética',
            3 => 'Grama Alta',
            4 => 'Tierra',
            5 => 'Húmedo',
            6 => 'Alt'
        ];
        $climaMap = [
            0 => 'Soleado',
            1 => 'Nublado',
            2 => 'Lluvioso',
            3 => 'Viento',
            4 => 'Tormenta'
        ];

        // Construir filas
        $rows = '';
        $modeloPrueba = new \App\Models\ResultadoPrueba();
        
        foreach ($atletas as $a) {
            $fuerza = $a['test_de_fuerza'] !== null ? number_format((float)$a['test_de_fuerza'], 1) . ' cm' : '—';
            $resistencia = $a['test_resistencia'] !== null ? (int)$a['test_resistencia'] . ' m' : '—';
            $velocidad = $a['test_velocidad'] !== null ? number_format((float)$a['test_velocidad'], 2) . ' seg' : '—';
            $coordinacion = $a['test_coordinacion'] !== null ? number_format((float)$a['test_coordinacion'], 1) . ' seg' : '—';
            $reaccion = $a['test_de_reaccion'] !== null ? (int)$a['test_de_reaccion'] . ' ms' : '—';

            $promedioVal = null;
            if (!empty($a['fecha_nac'])) {
                $modeloPrueba->calcularPromedioNacional($a, (string)$a['fecha_nac'], (string)$actividad['fecha']);
                $promedioVal = $modeloPrueba->promedio;
            }
            $promedio = $promedioVal !== null ? number_format((float)$promedioVal, 1) . ' pts' : '—';

            $nombreAtleta = $a['nombre'] . ' ' . $a['apellido'];
            if (!empty($a['cedula'])) {
                $nombreAtleta .= ' (' . \App\Models\Atleta::formatCedula($a['cedula']) . ')';
            }

            $rows .= sprintf(
                '<tr>
                    <td width="40%%" style="text-align: left; vertical-align: middle; font-weight: bold; font-size: 10px;">%s</td>
                    <td width="10%%" style="text-align: center; vertical-align: middle;">%s</td>
                    <td width="10%%" style="text-align: center; vertical-align: middle;">%s</td>
                    <td width="10%%" style="text-align: center; vertical-align: middle;">%s</td>
                    <td width="10%%" style="text-align: center; vertical-align: middle;">%s</td>
                    <td width="10%%" style="text-align: center; vertical-align: middle;">%s</td>
                    <td width="10%%" style="text-align: center; vertical-align: middle; font-weight: bold;">%s</td>
                </tr>',
                $esc($nombreAtleta),
                $esc($fuerza),
                $esc($resistencia),
                $esc($velocidad),
                $esc($coordinacion),
                $esc($reaccion),
                $esc($promedio)
            );
        }

        // Bloque de información en 2 columnas
        $infoItems = [
            ['label' => 'Categoría Deportiva:', 'val' => $categoria['nombre_categoria']],
            ['label' => 'Fecha de Evaluación:', 'val' => date('d/m/Y', strtotime($actividad['fecha']))],
            ['label' => 'Evaluador / Entrenador:', 'val' => $actividad['entrenador'] ?? 'No definido'],
        ];

        if (!empty($actividad['hora_inicio']) && !empty($actividad['hora_fin'])) {
            $horario = date('h:i A', strtotime($actividad['hora_inicio'])) . ' - ' . date('h:i A', strtotime($actividad['hora_fin']));
            $infoItems[] = ['label' => 'Horario:', 'val' => $horario];
        }
        if (!empty($actividad['ubicacion'])) {
            $infoItems[] = ['label' => 'Ubicación:', 'val' => $actividad['ubicacion']];
        }
        if (isset($actividad['terreno']) && isset($terrenoMap[(int)$actividad['terreno']])) {
            $infoItems[] = ['label' => 'Terreno de Juego:', 'val' => $terrenoMap[(int)$actividad['terreno']]];
        }
        if (isset($actividad['clima']) && isset($climaMap[(int)$actividad['clima']])) {
            $infoItems[] = ['label' => 'Clima:', 'val' => $climaMap[(int)$actividad['clima']]];
        }

        $infoHtml = '<table class="info-grid" cellpadding="4">';
        $count = count($infoItems);
        for ($i = 0; $i < $count; $i += 2) {
            $infoHtml .= '<tr>';
            $infoHtml .= '<td class="info-label" width="22%">' . $esc($infoItems[$i]['label']) . '</td>';
            $infoHtml .= '<td class="info-value" width="28%">' . $esc($infoItems[$i]['val']) . '</td>';
            
            if (isset($infoItems[$i + 1])) {
                $infoHtml .= '<td class="info-label" width="22%">' . $esc($infoItems[$i + 1]['label']) . '</td>';
                $infoHtml .= '<td class="info-value" width="28%">' . $esc($infoItems[$i + 1]['val']) . '</td>';
            } else {
                $infoHtml .= '<td class="info-label" width="22%"></td><td class="info-value" width="28%"></td>';
            }
            $infoHtml .= '</tr>';
        }
        $infoHtml .= '</table>';

        $html = <<<HTML
<style>
    body { font-family: helvetica, sans-serif; color: #333; line-height: 1.5; }
    .section { margin-bottom: 25px; }
    .section-header { 
        background-color: #f8f9fa; 
        border-bottom: 2px solid #800020; 
        padding: 8px 10px; 
        margin-bottom: 12px; 
        font-size: 13px; 
        font-weight: bold; 
        color: #800020; 
        text-transform: uppercase; 
    }
    table.info-grid { width: 100%; margin-bottom: 10px; }
    table.info-grid td { padding: 5px 6px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; line-height: 1.4; }
    .info-label { font-weight: bold; color: #800020; font-size: 10.5px; }
    .info-value { color: #333; font-size: 10.5px; }
    table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.data-table th { background-color: #800020; color: #ffffff; font-size: 10px; font-weight: bold; padding: 9px 5px; text-align: center; border: 1px solid #800020; }
    table.data-table td { font-size: 10px; padding: 6px 5px; text-align: center; border: 1px solid #e9ecef; }
    table.data-table tr:nth-child(even) { background-color: #fcfcfc; }
</style>

<div class="section">
    <h2 style="margin:0; color:#1a1a1a; font-size: 18px; text-align: center;">Reporte de Pruebas Físicas por Categoría</h2>
    <p style="margin:4px 0 16px 0; font-size: 10px; color: #666; text-align: center;">Sistema de Gestión Club Atlético Deportivo Acarigua (CADA)</p>
    
    <div class="section-header">Información General de la Sesión</div>
    {$infoHtml}
    
    <br>
    <div class="section-header">Resultados Consolidados por Categoría</div>
    <table class="data-table" cellpadding="4">
        <thead>
            <tr>
                <th width="40%" style="text-align: left;">Atleta</th>
                <th width="10%">Fuerza (CMJ)</th>
                <th width="10%">Resist. (Yo-Yo)</th>
                <th width="10%">Veloc. (30m)</th>
                <th width="10%">Coord. (Conos)</th>
                <th width="10%">Reacc.</th>
                <th width="10%">Promedio</th>
            </tr>
        </thead>
        <tbody>
            {$rows}
        </tbody>
    </table>
</div>
HTML;

        $filename = 'pruebas_fisicas_categoria_' . preg_replace('/[^a-z0-9]+/i', '_', $categoria['nombre_categoria']) . '_' . date('Ymd', strtotime($actividad['fecha']));

        if (class_exists(PdfGenerator::class)) {
            return (new PdfGenerator())->render(
                'Reporte Pruebas Fisicas - ' . $categoria['nombre_categoria'],
                $html,
                strtolower($filename)
            );
        }

        return ['mime' => 'text/html', 'filename' => $filename . '.html', 'content' => $html];
    }
}

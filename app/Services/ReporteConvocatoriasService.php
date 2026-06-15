<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Convocatoria;

final class ReporteConvocatoriasService
{
    /**
     * Genera el reporte de detalle de una convocatoria en PDF/HTML.
     */
    public function reporteDetalle(int $actividadId): ?array
    {
        $db = Database::connection();

        // 1. Obtener detalles del partido/actividad
        $stmtAct = $db->prepare("
            SELECT a.*, CONCAT(u.nombre, ' ', u.apellido) AS entrenador,
                   (SELECT c.nombre_categoria FROM asig_categorias ac JOIN categorias c ON ac.categoria_id = c.categoria_id WHERE ac.asignacion_id = a.asignacion_id LIMIT 1) AS nombre_categoria,
                   (SELECT ac2.categoria_id FROM asig_categorias ac2 WHERE ac2.asignacion_id = a.asignacion_id LIMIT 1) AS categoria_id
            FROM actividades a
            LEFT JOIN usuarios u ON a.usuario_id = u.usuario_id
            WHERE a.actividad_id = ? AND a.tipo_actividad = 0
        ");
        $stmtAct->execute([$actividadId]);
        $actividad = $stmtAct->fetch();

        if (!$actividad) {
            return null;
        }

        // 2. Obtener lista de atletas con su dorsal y estatus
        $stmtAtletas = $db->prepare("
            SELECT conv.*, atl.nombre, atl.apellido, atl.cedula,
                   (SELECT ac.nun_dorsal FROM asig_categorias ac WHERE ac.atleta_id = conv.atleta_id AND ac.categoria_id = :cat_id AND ac.estatus = 1 LIMIT 1) AS nun_dorsal
            FROM convocatorias conv
            JOIN atletas atl ON conv.atleta_id = atl.atleta_id
            WHERE conv.actividad_id = :act_id
            ORDER BY atl.apellido, atl.nombre
        ");
        $stmtAtletas->execute([
            ':act_id' => $actividadId,
            ':cat_id' => (int)$actividad['categoria_id']
        ]);
        $detalles = $stmtAtletas->fetchAll();

        $esc = fn($v) => htmlspecialchars((string) ($v ?? '—'), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Mapeos
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

        // Construir tabla
        $rows = '';
        $convocatoriaModel = new Convocatoria();
        $pdfGen = new PdfGenerator();
        $isPdf = $pdfGen->available();

        foreach ($detalles as $d) {
            $atletaId = (int)$d['atleta_id'];
            $asistenciaPct = $convocatoriaModel->obtenerAsistenciaMensual($atletaId);
            $rendimientoPct = $convocatoriaModel->obtenerPromedioFisico($atletaId);

            // Generar archivo SVG temporal para la barra de progreso
            $barraPath = $this->generarSvgBarra($asistenciaPct, $isPdf);

            // Promedio físico
            $physScore = $rendimientoPct > 0 ? number_format($rendimientoPct, 1) . '%' : 'N/A';

            // Estatus
            $estValue = (int)$d['estatus'];

            if ($estValue === 1) {
                $estTexto = 'Convocado';
                $estColor = '#2ea44f'; // Green
            } else {
                $estTexto = 'No Convocado';
                $estColor = '#cf222e'; // Red
            }

            $dorsalText = $d['nun_dorsal'] !== null ? '#' . $d['nun_dorsal'] : '—';
            $asistenciaText = number_format($asistenciaPct, 1);

            $rows .= sprintf(
                '<tr>
                    <td width="10%%" style="text-align: center; vertical-align: middle;">%s</td>
                    <td width="35%%" style="text-align: left; vertical-align: middle; font-weight: bold; font-size: 10px;">%s</td>
                    <td width="15%%" style="text-align: center; vertical-align: middle;">%s</td>
                    <td width="15%%" style="text-align: center; vertical-align: middle;">
                        <div style="text-align: center; line-height: 1.1;">
                            <img src="%s" width="45" height="6" style="margin-bottom: 2px;" /><br />
                            <span style="font-weight: bold; font-size: 8.5px; color: #1e293b;">%s%%</span>
                        </div>
                    </td>
                    <td width="13%%" style="text-align: center; vertical-align: middle;">%s</td>
                    <td width="12%%" style="text-align: center; vertical-align: middle; color: %s; font-weight: bold;">%s</td>
                </tr>',
                $esc($dorsalText),
                $esc($d['nombre'] . ' ' . $d['apellido']),
                $esc($d['cedula']),
                $barraPath,
                $esc($asistenciaText),
                $esc($physScore),
                $estColor,
                $esc($estTexto)
            );
        }

        // Bloque de información en 2 columnas
        $infoItems = [
            ['label' => 'Categoría:', 'val' => $actividad['nombre_categoria']],
            ['label' => 'Fecha del Partido:', 'val' => date('d/m/Y', strtotime($actividad['fecha']))],
            ['label' => 'Enlistador:', 'val' => $actividad['entrenador']],
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
            $infoHtml .= '<td class="info-label" width="18%">' . $esc($infoItems[$i]['label']) . '</td>';
            $infoHtml .= '<td class="info-value" width="32%">' . $esc($infoItems[$i]['val']) . '</td>';
            
            if (isset($infoItems[$i + 1])) {
                $infoHtml .= '<td class="info-label" width="18%">' . $esc($infoItems[$i + 1]['label']) . '</td>';
                $infoHtml .= '<td class="info-value" width="32%">' . $esc($infoItems[$i + 1]['val']) . '</td>';
            } else {
                $infoHtml .= '<td class="info-label" width="18%"></td><td class="info-value" width="32%"></td>';
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
    <h2 style="margin:0; color:#1a1a1a; font-size: 18px; text-align: center;">Reporte de Convocatoria y Asistencia de Partido</h2>
    <p style="margin:4px 0 16px 0; font-size: 10px; color: #666; text-align: center;">Sistema de Gestión Club Atlético Deportivo Acarigua (CADA)</p>
    
    <div class="section-header">Información de la Convocatoria</div>
    {$infoHtml}
    
    <br>
    <div class="section-header">Listado de Jugadores y Estatus</div>
    <table class="data-table" cellpadding="4">
        <thead>
            <tr>
                <th width="10%">Dorsal</th>
                <th width="35%" style="text-align: left;">Atleta</th>
                <th width="15%">Documento</th>
                <th width="15%">Asistencia (30d)</th>
                <th width="13%">Prom. Físico</th>
                <th width="12%">Estatus</th>
            </tr>
        </thead>
        <tbody>
            {$rows}
        </tbody>
    </table>
</div>
HTML;

        $filename = 'convocatoria_' . preg_replace('/[^a-z0-9]+/i', '_', $actividad['nombre_categoria'] ?? 'partido') . '_' . date('Ymd', strtotime($actividad['fecha']));

        if (class_exists(PdfGenerator::class)) {
            return (new PdfGenerator())->render(
                'Reporte Convocatoria - ' . ($actividad['nombre_categoria'] ?? 'Partido'),
                $html,
                strtolower($filename)
            );
        }

        return ['mime' => 'text/html', 'filename' => $filename . '.html', 'content' => $html];
    }

    /**
     * Genera un gráfico SVG de barra de progreso horizontal temporal y retorna su ruta absoluta o relativa.
     */
    private function generarSvgBarra(float $porcentaje, bool $absolute = true): string
    {
        $w = 60;
        $h = 8;
        $rx = 4;
        
        $progressWidth = ($porcentaje / 100) * $w;
        
        // Determinar color según el porcentaje
        $color = '#cf222e'; // Rojo (0% a 24%)
        if ($porcentaje >= 80) {
            $color = '#2ea44f'; // Verde (>= 80%)
        } elseif ($porcentaje >= 50) {
            $color = '#dbab09'; // Amarillo (50% a 79%)
        } elseif ($porcentaje >= 25) {
            $color = '#f97316'; // Naranja (25% a 49%)
        }
        
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '">';
        // Track
        $svg .= '<rect x="0" y="0" width="' . $w . '" height="' . $h . '" rx="' . $rx . '" ry="' . $rx . '" fill="#e2e8f0" />';
        // Progress
        if ($progressWidth > 0) {
            $svg .= '<rect x="0" y="0" width="' . round($progressWidth, 2) . '" height="' . $h . '" rx="' . $rx . '" ry="' . $rx . '" fill="' . $color . '" />';
        }
        $svg .= '</svg>';
        
        $uploadsDir = dirname(__DIR__, 2) . '/public/assets/uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }
        
        $filename = 'tmp_bar_' . uniqid() . '.svg';
        $tmpPath = $uploadsDir . '/' . $filename;
        file_put_contents($tmpPath, $svg);
        
        return $absolute ? $tmpPath : '/assets/uploads/' . $filename;
    }
}

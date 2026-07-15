<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use DateTime;

final class ReporteAsistenciaService
{
    /**
     * Genera el reporte en PDF/HTML de asistencia individual de un atleta.
     */
    public function asistenciaIndividual(int $atletaId, ?string $desde = null, ?string $hasta = null): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare("
            SELECT a.*, c.nombre_categoria
            FROM atletas a
            LEFT JOIN asig_categorias ac ON ac.atleta_id = a.atleta_id
            LEFT JOIN categorias c ON c.categoria_id = ac.categoria_id
            WHERE a.atleta_id = ?
        ");
        $stmt->execute([$atletaId]);
        $atleta = $stmt->fetch();
        if (!$atleta) return null;

        $stmt = $db->prepare("
            SELECT ast.*, act.fecha, act.tipo_actividad
            FROM asistencias ast
            JOIN actividades act ON ast.actividad_id = act.actividad_id
            WHERE ast.atleta_id = ?
            ORDER BY act.fecha DESC
        ");
        $stmt->execute([$atletaId]);
        $historial = $stmt->fetchAll();

        $esc = fn($v) => htmlspecialchars((string) ($v ?? '—'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $nombreCompleto = $esc($atleta['nombre'] . ' ' . $atleta['apellido']);

        // Filtrar historial para estadísticas
        $historialStats = [];
        foreach ($historial as $row) {
            $fecha = $row['fecha'];
            if ($desde && $fecha < $desde) continue;
            if ($hasta && $fecha > $hasta) continue;
            $historialStats[] = $row;
        }

        // Cálculos de estadísticas sobre el rango
        $totalAsi = count($historialStats);
        $asiMap = [1 => 0, 0 => 0, 2 => 0];
        foreach ($historialStats as $row) {
            $est = (int)$row['estatus'];
            if (isset($asiMap[$est])) {
                $asiMap[$est]++;
            }
        }
        $pctPresente = $totalAsi > 0 ? round(($asiMap[1] / $totalAsi) * 100, 1) : 0;
        $pctAusente = $totalAsi > 0 ? round(($asiMap[0] / $totalAsi) * 100, 1) : 0;
        $pctJustificado = $totalAsi > 0 ? round(($asiMap[2] / $totalAsi) * 100, 1) : 0;

        // Calendario Anual SVG (Siempre completo)
        $calendarioSvg = $this->generarSvgCalendario($historial);

        // Tabla detallada (del mes actual si no hay rango, o del rango si lo hay)
        $asistDetalleRows = '';
        $contadorAsist = 0;
        $filtrarPorMesActual = !$desde && !$hasta;
        $mesActual = date('Y-m');

        foreach ($historial as $ha) {
            $fecha = $ha['fecha'];
            if ($filtrarPorMesActual) {
                if (date('Y-m', strtotime($fecha)) !== $mesActual) {
                    continue;
                }
            } else {
                if ($desde && $fecha < $desde) continue;
                if ($hasta && $fecha > $hasta) continue;
            }
            if ($contadorAsist >= 31) break;

            $tipoEv = match ((int)($ha['tipo_actividad'] ?? 1)) {
                0 => 'Partido',
                1 => 'Entrenamiento',
                2 => 'Prueba Física',
                3 => 'Evento Especial',
                default => 'Otro'
            };
            $estColor = match ((int)($ha['estatus'] ?? 0)) {
                1 => '#2ea44f',
                0 => '#cf222e',
                2 => '#dbab09',
                default => '#888'
            };
            $estTexto = match ((int)($ha['estatus'] ?? 0)) {
                1 => 'Presente',
                0 => 'Ausente',
                2 => 'Justificado',
                default => '—'
            };
            $asistDetalleRows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td style="color: %s; font-weight: bold;">%s</td><td style="font-size: 9px; text-align: left;">%s</td></tr>',
                $esc(date('d/m/Y', strtotime($ha['fecha']))),
                $esc($tipoEv),
                $estColor,
                $esc($estTexto),
                $esc($ha['observaciones'] ?? '—')
            );
            $contadorAsist++;
        }

        $rangoTexto = 'Detalle de Sesiones';
        if ($desde || $hasta) {
            $rangoTexto .= ' (' . ($desde ? date('d/m/Y', strtotime($desde)) : 'Inicio') . ' al ' . ($hasta ? date('d/m/Y', strtotime($hasta)) : date('d/m/Y')) . ')';
        } else {
            $rangoTexto .= ' del Mes en Curso';
        }

        $rangoCabecera = 'Todo el historial';
        if ($desde || $hasta) {
            $rangoCabecera = 'Rango: ' . ($desde ? date('d/m/Y', strtotime($desde)) : 'Inicio') . ' al ' . ($hasta ? date('d/m/Y', strtotime($hasta)) : date('d/m/Y'));
        }

        $asistDetalleTable = $asistDetalleRows ?
            "<table class=\"data-table\" cellpadding=\"4\"><thead><tr><th>Fecha</th><th>Tipo</th><th>Estatus</th><th>Observaciones</th></tr></thead><tbody>$asistDetalleRows</tbody></table>" :
            '<p style="font-size: 10px; color: #666; margin-top: 5px; text-align: center;">Sin registros de asistencia en el periodo seleccionado.</p>';

        $html = <<<HTML
<style>
    body { font-family: helvetica, sans-serif; color: #333; line-height: 1.6; }
    .section { margin-bottom: 25px; }
    .section-header { 
        background-color: #f8f9fa; 
        border-bottom: 2px solid #800020; 
        padding: 8px 10px; 
        margin-bottom: 15px; 
        font-size: 14px; 
        font-weight: bold; 
        color: #800020; 
        text-transform: uppercase; 
    }
    table.info-grid { width: 100%; margin-bottom: 12px; }
    table.info-grid td { padding: 6px; vertical-align: top; border-bottom: 1px solid #f0f0f0; line-height: 1.5; }
    .info-label { font-weight: bold; color: #800020; width: 35%; font-size: 11px; }
    .info-value { color: #333; width: 65%; font-size: 11px; }
    table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.data-table th { background-color: #800020; color: #ffffff; font-size: 10px; font-weight: bold; padding: 8px 5px; text-align: center; border: 1px solid #800020; }
    table.data-table td { font-size: 10px; padding: 7px 5px; text-align: center; border: 1px solid #e9ecef; }
    table.data-table tr:nth-child(even) { background-color: #f8f9fa; }
    .stat-box { text-align: center; border: 1px solid #e0e0e0; background-color: #fcfcfc; padding: 10px 5px; }
    .stat-number { font-size: 20px; font-weight: bold; display: block; margin-top: 5px; }
    .stat-label { font-size: 9px; color: #555; text-transform: uppercase; font-weight: bold; display: block; margin-bottom: 3px; }
    .stat-pct { font-size: 9px; color: #777; display: block; margin-top: 3px; }
</style>

<div class="section">
    <h2 style="margin:0; color:#1a1a1a; font-size: 20px;">Reporte de Asistencia: {$nombreCompleto}</h2>
    <p style="margin:5px 0 15px 0; font-size: 12px; color: #555;">Categoría: <strong>{$esc($atleta['nombre_categoria'])}</strong> | Documento: <strong>{$esc($atleta['cedula'])}</strong> | <strong>{$esc($rangoCabecera)}</strong></p>
    
    <div class="section-header">Resumen de Asistencias</div>
    <table width="100%" cellpadding="0" cellspacing="0" style="border: none; margin-bottom: 10px;">
        <tr>
            <td width="25%" class="stat-box" style="border-right: 1px solid #e0e0e0;">
                <span class="stat-label">Total Sesiones</span>
                <span class="stat-number" style="color: #333;">{$totalAsi}</span>
            </td>
            <td width="25%" class="stat-box" style="border-right: 1px solid #e0e0e0;">
                <span class="stat-label">Presentes</span>
                <span class="stat-number" style="color: #2ea44f;">{$asiMap[1]}</span>
                <span class="stat-pct">{$pctPresente}%</span>
            </td>
            <td width="25%" class="stat-box" style="border-right: 1px solid #e0e0e0;">
                <span class="stat-label">Inasistencias</span>
                <span class="stat-number" style="color: #cf222e;">{$asiMap[0]}</span>
                <span class="stat-pct">{$pctAusente}%</span>
            </td>
            <td width="25%" class="stat-box">
                <span class="stat-label">Justificadas</span>
                <span class="stat-number" style="color: #dbab09;">{$asiMap[2]}</span>
                <span class="stat-pct">{$pctJustificado}%</span>
            </td>
        </tr>
    </table>

    <br>
    <div style="font-weight: bold; font-size: 11px; color: #800020; margin-top: 20px; margin-bottom: 8px; text-transform: uppercase;">Calendario Anual de Asistencias (Últimos 12 Meses)</div>
    {$calendarioSvg}
    
    <br><br>
    <div style="font-weight: bold; font-size: 11px; color: #800020; margin-top: 15px; margin-bottom: 8px; text-transform: uppercase;">{$rangoTexto}</div>
    {$asistDetalleTable}
</div>
HTML;

        $filename = 'asistencia_' . preg_replace('/[^a-z0-9]+/i', '_', $atleta['nombre'] . '_' . $atleta['apellido']) . '_' . date('Ymd');

        if (class_exists(PdfGenerator::class)) {
            return (new PdfGenerator())->render(
                'Reporte de Asistencia - ' . $atleta['nombre'] . ' ' . $atleta['apellido'],
                $html,
                strtolower($filename)
            );
        }

        return ['mime' => 'text/html', 'filename' => $filename . '.html', 'content' => $html];
    }

    /**
     * Genera el reporte en PDF/HTML de asistencia por categoría en un rango de fechas.
     */
    public function asistenciaCategoria(int $categoriaId, ?string $desde = null, ?string $hasta = null): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare("
            SELECT c.*, CONCAT(u.nombre, ' ', u.apellido) AS entrenador
            FROM categorias c
            LEFT JOIN usuarios u ON c.usuario_id = u.usuario_id
            WHERE c.categoria_id = ?
        ");
        $stmt->execute([$categoriaId]);
        $categoria = $stmt->fetch();
        if (!$categoria) return null;

        $stmt = $db->prepare("
            SELECT a.atleta_id, a.nombre, a.apellido, a.cedula
            FROM atletas a
            JOIN asig_categorias ac ON ac.atleta_id = a.atleta_id
            WHERE ac.categoria_id = ? AND a.estatus IN (1, 2)
            ORDER BY a.apellido, a.nombre
        ");
        $stmt->execute([$categoriaId]);
        $atletas = $stmt->fetchAll();

        // Buscar todas las asistencias de esta categoría en el rango
        $sql = "
            SELECT ast.atleta_id, ast.estatus, act.fecha, act.tipo_actividad
            FROM asistencias ast
            JOIN actividades act ON ast.actividad_id = act.actividad_id
            JOIN asig_categorias ac ON ast.atleta_id = ac.atleta_id
            WHERE ac.categoria_id = :catId AND act.tipo_actividad IN (0, 1)
        ";
        $params = [':catId' => $categoriaId];
        if ($desde) {
            $sql .= " AND act.fecha >= :desde";
            $params[':desde'] = $desde;
        }
        if ($hasta) {
            $sql .= " AND act.fecha <= :hasta";
            $params[':hasta'] = $hasta;
        }
        $sql .= " ORDER BY act.fecha DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $asistencias = $stmt->fetchAll();

        $esc = fn($v) => htmlspecialchars((string) ($v ?? '—'), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Consolidar estadísticas
        $stats = [];
        foreach ($atletas as $a) {
            $stats[$a['atleta_id']] = [
                'atleta' => $a,
                'total' => 0,
                'presente' => 0,
                'ausente' => 0,
                'justificado' => 0
            ];
        }

        foreach ($asistencias as $asig) {
            $aId = (int)$asig['atleta_id'];
            if (!isset($stats[$aId])) continue;

            $est = (int)$asig['estatus'];
            $stats[$aId]['total']++;
            if ($est === 1) {
                $stats[$aId]['presente']++;
            } elseif ($est === 0) {
                $stats[$aId]['ausente']++;
            } elseif ($est === 2) {
                $stats[$aId]['justificado']++;
            }
        }

        $rows = '';
        $index = 1;
        $totalPresentesGlobal = 0;
        $totalSesionesGlobal = 0;

        foreach ($stats as $aId => $s) {
            $atl = $s['atleta'];
            $pct = $s['total'] > 0 ? round(($s['presente'] / $s['total']) * 100, 1) : 0;

            $totalPresentesGlobal += $s['presente'];
            $totalSesionesGlobal += $s['total'];

            $pctColor = '#cf222e'; // red
            if ($pct >= 80) $pctColor = '#2ea44f'; // green
            elseif ($pct >= 50) $pctColor = '#dbab09'; // yellow

            $rows .= sprintf(
                '<tr>
                    <td width="5%%" style="color:#999;text-align:center;">%d</td>
                    <td width="30%%" style="text-align:left;font-weight:bold;color:#1a1a1a;">%s</td>
                    <td width="15%%" style="text-align:center;">%s</td>
                    <td width="12%%" style="text-align:center;color:#2ea44f;font-weight:bold;">%d</td>
                    <td width="12%%" style="text-align:center;color:#cf222e;">%d</td>
                    <td width="12%%" style="text-align:center;color:#dbab09;">%d</td>
                    <td width="14%%" style="text-align:center;font-weight:bold;color:%s;">%.1f%%</td>
                </tr>',
                $index++,
                $esc($atl['nombre'] . ' ' . $atl['apellido']),
                $esc($atl['cedula'] ?? '—'),
                $s['presente'],
                $s['ausente'],
                $s['justificado'],
                $pctColor,
                $pct
            );
        }

        $avgPct = $totalSesionesGlobal > 0 ? round(($totalPresentesGlobal / $totalSesionesGlobal) * 100, 1) : 0;
        $rangoFechas = 'Desde ' . ($desde ? date('d/m/Y', strtotime($desde)) : 'Inicio') . ' hasta ' . ($hasta ? date('d/m/Y', strtotime($hasta)) : date('d/m/Y'));

        $html = <<<HTML
<style>
    body { font-family: helvetica, sans-serif; color: #333; line-height: 1.4; }
    .document-header { text-align: center; padding-bottom: 10px; margin-bottom: 15px; border-bottom: 2px solid #800020; }
    .report-title { background-color: #800020; color: #ffffff; padding: 8px 25px; font-size: 14px; display: inline-block; font-weight: bold; text-transform: uppercase; }
    .category-summary { margin: 25px 0; padding: 0; background-color: #ffffff; border: 1px solid #e0e0e0; border-top: 5px solid #800020; }
    .summary-header { background-color: #fcfcfc; padding: 15px; text-align: center; border-bottom: 1px solid #f0f0f0; }
    .summary-title { font-size: 24px; font-weight: bold; color: #1a1a1a; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
    .summary-body { padding: 20px; }
    .info-table { width: 100%; border: none; }
    .info-label { font-weight: bold; color: #800020; width: 35%; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
    .info-value { color: #333; width: 65%; font-size: 12px; font-weight: bold; }
    .section-label { font-size: 14px; font-weight: bold; color: #800020; text-transform: uppercase; margin-top: 35px; margin-bottom: 15px; border-bottom: 2px solid #800020; display: inline-block; padding-bottom: 3px; }
    table.member-list { width: 100%; border-collapse: collapse; margin-top: 5px; }
    table.member-list th { background-color: #800020; color: #ffffff; font-size: 9px; font-weight: bold; padding: 10px 5px; text-align: center; border: 1px solid #800020; text-transform: uppercase; }
    table.member-list td { font-size: 9px; padding: 8px 5px; text-align: center; border: 1px solid #dee2e6; vertical-align: middle; }
    table.member-list tr:nth-child(even) { background-color: #fafafa; }
</style>
<div class="document-header">
    <div class="report-title">Reporte de Asistencia por Categoría</div>
    <p style="font-size:10px; color:#555; margin-top:6px;">Rango: {$esc($rangoFechas)}</p>
</div>
<div class="category-summary">
    <div class="summary-header"><div class="summary-title">{$esc($categoria['nombre_categoria'])}</div></div>
    <div class="summary-body">
        <table class="info-table" cellpadding="2">
            <tr>
                <td width="50%">
                    <table width="100%">
                        <tr><td class="info-label" width="40%">Entrenador:</td><td class="info-value" width="60%">{$esc($categoria['entrenador'])}</td></tr>
                        <tr><td class="info-label" width="40%">Rango Edad:</td><td class="info-value" width="60%">{$esc($categoria['edad_min'])} a {$esc($categoria['edad_max'])} años</td></tr>
                    </table>
                </td>
                <td width="50%">
                    <table width="100%">
                        <tr><td class="info-label" width="40%">Asistencia Prom.:</td><td class="info-value" width="60%" style="color:#800020; font-size: 14px;">{$esc($avgPct)}%</td></tr>
                        <tr><td class="info-label" width="40%">Atletas:</td><td class="info-value" width="60%">{$esc(count($atletas))} activos</td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>
<div class="section-label">Listado de Rendimiento de Asistencia</div>
<table class="member-list" cellpadding="4">
    <thead>
        <tr>
            <th width="5%">#</th>
            <th width="30%" style="text-align:left;">Atleta (Nombres, Apellidos)</th>
            <th width="15%">Documento</th>
            <th width="12%">Presentes</th>
            <th width="12%">Ausentes</th>
            <th width="12%">Justificados</th>
            <th width="14%">% Asistencia</th>
        </tr>
    </thead>
    <tbody>
        {$rows}
    </tbody>
</table>
HTML;

        $filename = 'asistencia_categoria_' . preg_replace('/[^a-z0-9]+/i', '_', $categoria['nombre_categoria']) . '_' . date('Ymd');

        if (class_exists(PdfGenerator::class)) {
            return (new PdfGenerator())->render(
                'Reporte Asistencia Categoría: ' . $categoria['nombre_categoria'],
                $html,
                strtolower($filename)
            );
        }

        return ['mime' => 'text/html', 'filename' => $filename . '.html', 'content' => $html];
    }

    /**
     * Genera un calendario anual en formato SVG (Para evitar timeouts de TCPDF).
     * Reutilizado del monolith ReporteService.
     */
    private function generarSvgCalendario(array $historial): string
    {
        $mapaAsistRaw = [];
        foreach ($historial as $row) {
            $fecha = $row['fecha'] ?? null;
            if (!$fecha) continue;
            $key = date('Y-m-d', strtotime($fecha));
            $est = (int)($row['estatus'] ?? -1);
            if (!isset($mapaAsistRaw[$key])) {
                $mapaAsistRaw[$key] = [];
            }
            $mapaAsistRaw[$key][] = $est;
        }

        $mapaAsist = [];
        foreach ($mapaAsistRaw as $key => $estatuses) {
            $unique = array_unique($estatuses);
            if (count($unique) === 1) {
                $mapaAsist[$key] = ['tipo' => 'simple', 'est' => $unique[0]];
            } else {
                sort($unique);
                $mejor = max($unique);
                $peor = min($unique);
                $mapaAsist[$key] = ['tipo' => 'mixto', 'est1' => $mejor, 'est2' => $peor];
            }
        }

        $mesesNombres = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $diasStr = ['L','M','X','J','V','S','D'];
        
        $hoy = new DateTime('today');
        $mesesData = [];
        for ($i = 11; $i >= 0; $i--) {
            $dt = clone $hoy;
            $dt->modify("-$i months");
            $mesesData[] = [
                'anio' => (int)$dt->format('Y'),
                'mes' => (int)$dt->format('n'),
                'nombre' => $mesesNombres[(int)$dt->format('n') - 1] . ' ' . $dt->format('Y')
            ];
        }

        $w = 520;
        $h = 350;
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '" style="font-family: helvetica, sans-serif;">';
        $svg .= '<rect width="100%" height="100%" fill="#ffffff" />';
        
        $cols = 4;
        $monthW = 115;
        $monthH = 90;
        $padX = ($w - ($cols * $monthW)) / ($cols + 1);
        $padY = 15;
        
        foreach ($mesesData as $index => $mData) {
            $col = $index % $cols;
            $row = floor($index / $cols);
            
            $x = $padX + $col * ($monthW + $padX);
            $y = $padY + $row * ($monthH + $padY);
            
            $svg .= '<text x="' . ($x + $monthW/2) . '" y="' . ($y + 10) . '" text-anchor="middle" font-size="10" font-weight="bold" fill="#800020">' . $mData['nombre'] . '</text>';
            
            $cellW = $monthW / 7;
            $cellH = 11;
            $dy = $y + 15;
            for ($d=0; $d<7; $d++) {
                $svg .= '<text x="' . ($x + $d*$cellW + $cellW/2) . '" y="' . ($dy + 8) . '" text-anchor="middle" font-size="7" fill="#888">' . $diasStr[$d] . '</text>';
            }
            
            $primerDia = new DateTime(sprintf('%04d-%02d-01', $mData['anio'], $mData['mes']));
            $diasEnMes = (int)$primerDia->format('t');
            $diaSemanaInicio = ((int)$primerDia->format('N')) - 1;
            
            $dy += 12;
            $currentCol = $diaSemanaInicio;
            $currentRow = 0;
            
            for ($dia = 1; $dia <= $diasEnMes; $dia++) {
                $fechaKey = sprintf('%04d-%02d-%02d', $mData['anio'], $mData['mes'], $dia);
                
                $rx = $x + $currentCol * $cellW;
                $ry = $dy + $currentRow * $cellH;
                $cw = $cellW - 2;
                $ch = $cellH - 2;
                $cx1 = $rx + 1;
                $cy1 = $ry + 1;

                $colorMap = fn($e) => match($e) {
                    1 => '#2ea44f', 0 => '#cf222e', 2 => '#dbab09', default => '#f1f3f5'
                };

                if (isset($mapaAsist[$fechaKey])) {
                    $info = $mapaAsist[$fechaKey];
                    if ($info['tipo'] === 'mixto') {
                        $color1 = $colorMap($info['est1']);
                        $color2 = $colorMap($info['est2']);
                        $svg .= '<polygon points="' . $cx1 . ',' . $cy1 . ' ' . ($cx1+$cw) . ',' . $cy1 . ' ' . $cx1 . ',' . ($cy1+$ch) . '" fill="' . $color1 . '" />';
                        $svg .= '<polygon points="' . ($cx1+$cw) . ',' . $cy1 . ' ' . ($cx1+$cw) . ',' . ($cy1+$ch) . ' ' . $cx1 . ',' . ($cy1+$ch) . '" fill="' . $color2 . '" />';
                        $colorText = '#fff';
                    } else {
                        $colorBg = $colorMap($info['est']);
                        $colorText = ($info['est'] >= 0 && $info['est'] <= 2) ? '#fff' : '#555';
                        $svg .= '<rect x="' . $cx1 . '" y="' . $cy1 . '" width="' . $cw . '" height="' . $ch . '" fill="' . $colorBg . '" rx="2" ry="2" />';
                    }
                } else {
                    $svg .= '<rect x="' . $cx1 . '" y="' . $cy1 . '" width="' . $cw . '" height="' . $ch . '" fill="#f1f3f5" rx="2" ry="2" />';
                    $colorText = '#555';
                }
                $svg .= '<text x="' . ($rx + $cellW/2) . '" y="' . ($ry + 8) . '" text-anchor="middle" font-size="6" fill="' . $colorText . '">' . $dia . '</text>';
                
                $currentCol++;
                if ($currentCol > 6) {
                    $currentCol = 0;
                    $currentRow++;
                }
            }
        }
        
        $ly = $h - 10;
        $svg .= '<rect x="' . ($w/2 - 170) . '" y="' . ($ly-8) . '" width="10" height="10" fill="#2ea44f" rx="2" ry="2" /><text x="' . ($w/2 - 155) . '" y="' . $ly . '" font-size="8" fill="#555">Presente</text>';
        $svg .= '<rect x="' . ($w/2 - 85) . '" y="' . ($ly-8) . '" width="10" height="10" fill="#cf222e" rx="2" ry="2" /><text x="' . ($w/2 - 70) . '" y="' . $ly . '" font-size="8" fill="#555">Ausente</text>';
        $svg .= '<rect x="' . ($w/2) . '" y="' . ($ly-8) . '" width="10" height="10" fill="#dbab09" rx="2" ry="2" /><text x="' . ($w/2 + 15) . '" y="' . $ly . '" font-size="8" fill="#555">Justificado</text>';
        $svg .= '<rect x="' . ($w/2 + 95) . '" y="' . ($ly-8) . '" width="10" height="10" fill="#f1f3f5" stroke="#ccc" stroke-width="0.5" rx="2" ry="2" /><text x="' . ($w/2 + 110) . '" y="' . $ly . '" font-size="8" fill="#555">Sin sesión</text>';
        
        $svg .= '</svg>';
        
        $tmpPath = dirname(__DIR__, 2) . '/public/assets/uploads/tmp_calendario_' . uniqid() . '.svg';
        if (!is_dir(dirname(__DIR__, 2) . '/public/assets/uploads')) {
            mkdir(dirname(__DIR__, 2) . '/public/assets/uploads', 0777, true);
        }
        file_put_contents($tmpPath, $svg);
        return '<div style="text-align: center; margin-top: 15px;"><img src="' . $tmpPath . '" width="' . $w . '" height="' . $h . '" /></div>';
    }

    /**
     * Genera el reporte de una sesión de asistencia en PDF/HTML.
     */
    public function reporteSesion(int $actividadId): ?array
    {
        $db = Database::connection();

        // 1. Obtener detalles de la actividad (asistencia)
        $stmtAct = $db->prepare("
            SELECT a.*, CONCAT(u.nombre, ' ', u.apellido) AS entrenador,
                   (SELECT c.nombre_categoria FROM asig_categorias ac JOIN categorias c ON ac.categoria_id = c.categoria_id WHERE ac.asignacion_id = a.asignacion_id LIMIT 1) AS nombre_categoria
            FROM actividades a
            LEFT JOIN usuarios u ON a.usuario_id = u.usuario_id
            WHERE a.actividad_id = ?
        ");
        $stmtAct->execute([$actividadId]);
        $actividad = $stmtAct->fetch();

        if (!$actividad) {
            return null;
        }

        // 2. Obtener lista de atletas con sus asistencias en esta sesión
        $stmtResultados = $db->prepare("
            SELECT ast.*, atl.nombre, atl.apellido, atl.cedula
            FROM asistencias ast
            JOIN atletas atl ON ast.atleta_id = atl.atleta_id
            WHERE ast.actividad_id = ?
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

        // Estadísticas de la sesión
        $total = count($detalles);
        $presentes = 0;
        $ausentes = 0;
        $justificados = 0;

        // Construir filas
        $rows = '';
        foreach ($detalles as $d) {
            $est = (int)$d['estatus'];
            if ($est === 1) {
                $presentes++;
                $estTexto = 'Presente';
                $estColor = '#2ea44f';
            } elseif ($est === 2) {
                $justificados++;
                $estTexto = 'Justificado';
                $estColor = '#dbab09';
            } else {
                $ausentes++;
                $estTexto = 'Ausente';
                $estColor = '#cf222e';
            }

            $rows .= sprintf(
                '<tr>
                    <td width="35%%" style="text-align: left; vertical-align: middle; font-weight: bold; font-size: 10px;">%s</td>
                    <td width="20%%" style="text-align: center; vertical-align: middle;">%s</td>
                    <td width="20%%" style="text-align: center; vertical-align: middle; font-weight: bold; color: %s;">%s</td>
                    <td width="25%%" style="text-align: left; vertical-align: middle; font-size: 9px;">%s</td>
                </tr>',
                $esc($d['nombre'] . ' ' . $d['apellido']),
                $esc($d['cedula']),
                $estColor,
                $esc($estTexto),
                $esc($d['observaciones'] ?? '—')
            );
        }

        $porcentaje = $total > 0 ? round(($presentes / $total) * 100, 1) : 0;

        // Bloque de información en 2 columnas
        $tipoLabel = match ((int)($actividad['tipo_actividad'] ?? 1)) {
            0 => 'Partido',
            1 => 'Entrenamiento',
            2 => 'Prueba Física',
            3 => 'Evento Especial',
            default => 'General'
        };

        $infoItems = [
            ['label' => 'Categoría Deportiva:', 'val' => $actividad['nombre_categoria'] ?? 'Sin Categoría'],
            ['label' => 'Fecha de Evaluación:', 'val' => date('d/m/Y', strtotime($actividad['fecha']))],
            ['label' => 'Evaluador / Entrenador:', 'val' => $actividad['entrenador'] ?? 'No definido'],
            ['label' => 'Tipo de Actividad:', 'val' => $tipoLabel]
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
    .stat-box { text-align: center; border: 1px solid #e0e0e0; background-color: #fcfcfc; padding: 8px 4px; }
    .stat-number { font-size: 18px; font-weight: bold; display: block; margin-top: 4px; }
    .stat-label { font-size: 9px; color: #555; text-transform: uppercase; font-weight: bold; display: block; margin-bottom: 2px; }
    .stat-pct { font-size: 9px; color: #777; display: block; margin-top: 2px; }
</style>

<div class="section">
    <h2 style="margin:0; color:#1a1a1a; font-size: 18px; text-align: center;">Reporte de Detalle de Asistencia</h2>
    <p style="margin:4px 0 16px 0; font-size: 10px; color: #666; text-align: center;">Sistema de Gestión Club Atlético Deportivo Acarigua (CADA)</p>
    
    <div class="section-header">Información General de la Sesión</div>
    {$infoHtml}
    
    <br>
    <div class="section-header">Resumen Estadístico</div>
    <table width="100%" cellpadding="0" cellspacing="0" style="border: none; margin-bottom: 10px;">
        <tr>
            <td width="20%" class="stat-box" style="border-right: 1px solid #e0e0e0;">
                <span class="stat-label">Total Atletas</span>
                <span class="stat-number" style="color: #333;">{$total}</span>
            </td>
            <td width="20%" class="stat-box" style="border-right: 1px solid #e0e0e0;">
                <span class="stat-label">Presentes</span>
                <span class="stat-number" style="color: #2ea44f;">{$presentes}</span>
            </td>
            <td width="20%" class="stat-box" style="border-right: 1px solid #e0e0e0;">
                <span class="stat-label">Ausentes</span>
                <span class="stat-number" style="color: #cf222e;">{$ausentes}</span>
            </td>
            <td width="20%" class="stat-box" style="border-right: 1px solid #e0e0e0;">
                <span class="stat-label">Justificados</span>
                <span class="stat-number" style="color: #dbab09;">{$justificados}</span>
            </td>
            <td width="20%" class="stat-box">
                <span class="stat-label">% Asistencia</span>
                <span class="stat-number" style="color: #800020;">{$porcentaje}%</span>
            </td>
        </tr>
    </table>

    <br>
    <div class="section-header">Listado de Asistencia</div>
    <table class="data-table" cellpadding="4">
        <thead>
            <tr>
                <th width="35%" style="text-align: left;">Atleta</th>
                <th width="20%">Documento ID</th>
                <th width="20%">Estado</th>
                <th width="25%" style="text-align: left;">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            {$rows}
        </tbody>
    </table>
</div>
HTML;

        $filename = 'asistencia_sesion_' . preg_replace('/[^a-z0-9]+/i', '_', $actividad['nombre_categoria'] ?? 'sesion') . '_' . date('Ymd', strtotime($actividad['fecha']));

        if (class_exists(PdfGenerator::class)) {
            return (new PdfGenerator())->render(
                'Reporte Asistencia - ' . ($actividad['nombre_categoria'] ?? 'Sesion'),
                $html,
                strtolower($filename)
            );
        }

        return ['mime' => 'text/html', 'filename' => $filename . '.html', 'content' => $html];
    }
}

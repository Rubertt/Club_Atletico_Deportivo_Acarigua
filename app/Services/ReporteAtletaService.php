<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Atleta;
use App\Models\MedidaAntropometrica;
use App\Models\ResultadoPrueba;
use App\Models\Asistencia;
use App\Models\ConsultaMedica;
use App\Core\Database;
use DateTime;

final class ReporteAtletaService
{
    /**
     * Arma la ficha técnica individual de un atleta y la entrega como PDF/HTML.
     *
     * @return array{mime:string, filename:string, content:string}|null
     */
    public function fichaAtleta(int $atletaId): ?array
    {
        $atleta = (new Atleta())->findCompleto($atletaId);
        if (!$atleta) return null;

        // Obtener asignaciones de categorías de forma dinámica a través de asig_categorias
        $asigModel = new \App\Models\AsigCategoria();
        $assignments = $asigModel->athleteAssignments($atletaId);
        
        $activeAsig = array_filter($assignments, function($asig) {
            return (int)$asig['estatus'] === 1;
        });

        if (!empty($activeAsig)) {
            $catNames = array_map(fn($asig) => $asig['nombre_categoria'], $activeAsig);
            
            $posNames = [];
            $posPrincipalNames = [];
            $posSecundariaNames = [];
            $dorsales = [];
            foreach ($activeAsig as $asig) {
                $p1 = $asig['posicion_principal'] ?: 'Sin definir';
                $p2 = $asig['posicion_secundaria'] ?: 'Ninguna';
                $posPrincipalNames[] = $p1;
                $posSecundariaNames[] = $p2;
                if (!empty($asig['nun_dorsal'])) {
                    $dorsales[] = $asig['nun_dorsal'];
                }
                
                $pString = $p1;
                if (!empty($asig['posicion_secundaria'])) {
                    $pString .= ' / ' . $asig['posicion_secundaria'];
                }
                $posNames[] = $pString;
            }
            
            $atleta['nombre_categoria'] = implode(', ', $catNames);
            $atleta['nombre_posicion'] = implode(', ', array_unique($posNames));
            $atleta['posicion_principal'] = implode(', ', array_unique($posPrincipalNames));
            $atleta['posicion_secundaria'] = implode(', ', array_unique($posSecundariaNames));
            $atleta['nun_dorsal'] = !empty($dorsales) ? implode(', ', array_unique($dorsales)) : '—';
        } else {
            $atleta['nombre_categoria'] = '—';
            $atleta['nombre_posicion'] = 'Sin definir';
            $atleta['posicion_principal'] = 'Sin definir';
            $atleta['posicion_secundaria'] = 'Ninguna';
            $atleta['nun_dorsal'] = '—';
        }

        $antropometria    = (new MedidaAntropometrica())->historial($atletaId);
        $pruebas          = (new ResultadoPrueba())->historial($atletaId);
        $asistencia       = (new Asistencia())->resumenAtleta($atletaId);
        $historialAsist   = (new Asistencia())->historialAtleta($atletaId);
        $consultas        = (new ConsultaMedica())->byAtleta($atletaId);

        $html = $this->construirHtml($atleta, $antropometria, $pruebas, $asistencia, $historialAsist, $consultas);

        $filename = 'ficha_' . preg_replace('/[^a-z0-9]+/i', '_', $atleta['nombre'] . '_' . $atleta['apellido']) . '_' . date('Ymd');
        
        if (class_exists('TCPDF')) {
            return (new PdfGenerator())->render(
                'Ficha Técnica - ' . $atleta['nombre'] . ' ' . $atleta['apellido'],
                $html,
                strtolower($filename)
            );
        }
        
        return [
            'mime' => 'text/html',
            'filename' => $filename . '.html',
            'content' => $html
        ];
    }

    /**
     * Genera un listado general de todos los atletas registrados.
     */
    public function listaAtletas(): array
    {
        $db = Database::connection();
        $sql = "
            SELECT a.atleta_id, a.nombre, a.apellido, a.cedula, a.telefono, a.estatus,
                   (
                       SELECT GROUP_CONCAT(c2.nombre_categoria SEPARATOR ', ')
                       FROM asig_categorias ac2
                       JOIN categorias c2 ON c2.categoria_id = ac2.categoria_id
                       WHERE ac2.atleta_id = a.atleta_id
                   ) AS nombre_categoria
            FROM atletas a
            ORDER BY a.apellido, a.nombre
        ";
        $atletas = $db->query($sql)->fetchAll();

        $html = $this->construirHtmlLista($atletas);

        $filename = 'listado_atletas_' . date('Ymd');
        
        if (class_exists('TCPDF')) {
            return (new PdfGenerator())->render(
                'Listado General de Atletas',
                $html,
                strtolower($filename)
            );
        }
        
        return [
            'mime' => 'text/html',
            'filename' => $filename . '.html',
            'content' => $html
        ];
    }

    /**
     * Genera un reporte detallado de los atletas de una categoría específica.
     */
    public function reportePorCategoria(int $categoriaId): ?array
    {
        $db = Database::connection();
        
        $stmt = $db->prepare("
            SELECT c.*, u.nombre as entrenador 
            FROM categorias c 
            LEFT JOIN usuarios u ON u.usuario_id = c.usuario_id 
            WHERE c.categoria_id = :id
        ");
        $stmt->execute([':id' => $categoriaId]);
        $categoria = $stmt->fetch();
        
        if (!$categoria) return null;

        $stmt = $db->prepare("
            SELECT a.atleta_id, a.nombre, a.apellido, a.cedula, a.telefono, a.estatus, a.fecha_nac,
                   r.telefono AS representante_telefono
            FROM asig_categorias ac
            JOIN atletas a ON a.atleta_id = ac.atleta_id
            LEFT JOIN representantes r ON r.representante_id = a.representante_id
            WHERE ac.categoria_id = :id AND a.estatus IN (1, 2) AND ac.estatus = 1
            ORDER BY a.apellido, a.nombre
        ");
        $stmt->execute([':id' => $categoriaId]);
        $atletas = $stmt->fetchAll();

        $html = $this->construirHtmlCategoria($categoria, $atletas);

        $filename = 'reporte_categoria_' . preg_replace('/[^a-z0-9]+/i', '_', $categoria['nombre_categoria']) . '_' . date('Ymd');
        
        if (class_exists('TCPDF')) {
            return (new PdfGenerator())->render(
                'Reporte Categoría: ' . $categoria['nombre_categoria'],
                $html,
                strtolower($filename)
            );
        }
        
        return [
            'mime' => 'text/html',
            'filename' => $filename . '.html',
            'content' => $html
        ];
    }

    /* ======================================================================
     *  MÉTODOS AUXILIARES PARA GRÁFICOS VECTORIALES
     * ====================================================================== */

    /**
     * Genera un calendario anual en formato SVG (Para evitar timeouts de TCPDF)
     */
    private function generarSvgCalendario(array $historial): string
    {
        // Almacenar TODOS los estatus de cada día para poder representar días mixtos
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

        // Consolidar: si hay estatus mixtos, guardar ambos; si son iguales, guardar uno
        $mapaAsist = [];
        foreach ($mapaAsistRaw as $key => $estatuses) {
            $unique = array_unique($estatuses);
            if (count($unique) === 1) {
                $mapaAsist[$key] = ['tipo' => 'simple', 'est' => $unique[0]];
            } else {
                // Priorizar: mostrar el "mejor" arriba-izquierda y el "peor" abajo-derecha
                sort($unique); // 0(ausente), 1(presente), 2(justificado)
                $mejor = max($unique);
                $peor = min($unique);
                // Si tiene presente(1) y ausente(0) => verde/rojo
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
            
            // Month title
            $svg .= '<text x="' . ($x + $monthW/2) . '" y="' . ($y + 10) . '" text-anchor="middle" font-size="10" font-weight="bold" fill="#800020">' . $mData['nombre'] . '</text>';
            
            // Days header
            $cellW = $monthW / 7;
            $cellH = 11;
            $dy = $y + 15;
            for ($d=0; $d<7; $d++) {
                $svg .= '<text x="' . ($x + $d*$cellW + $cellW/2) . '" y="' . ($dy + 8) . '" text-anchor="middle" font-size="7" fill="#888">' . $diasStr[$d] . '</text>';
            }
            
            // Days logic
            $primerDia = new DateTime(sprintf('%04d-%02d-01', $mData['anio'], $mData['mes']));
            $diasEnMes = (int)$primerDia->format('t');
            $diaSemanaInicio = ((int)$primerDia->format('N')) - 1; // 0=Lun, 6=Dom
            
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
                        // Día con estatus mixtos: dos triángulos diagonales
                        $color1 = $colorMap($info['est1']);
                        $color2 = $colorMap($info['est2']);
                        // Triángulo superior-izquierdo (mejor estatus)
                        $svg .= '<polygon points="' . $cx1 . ',' . $cy1 . ' ' . ($cx1+$cw) . ',' . $cy1 . ' ' . $cx1 . ',' . ($cy1+$ch) . '" fill="' . $color1 . '" />';
                        // Triángulo inferior-derecho (peor estatus)
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
        
        // Legend
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
     * Genera un gráfico SVG de líneas y columnas para evolución del peso, altura e IMC.
     */
    private function generarSvgGrafica(array $antropo): string
    {
        if (count($antropo) < 2) {
            return '<p style="font-size: 10px; color: #888; text-align: center; margin-top: 10px;">Se necesitan al menos 2 mediciones para generar la gráfica de evolución.</p>';
        }

        $w = 520;
        $h = 240;
        $padL = 50;
        $padR = 50;
        $padT = 30;
        $padB = 50;
        $chartW = $w - $padL - $padR;
        $chartH = $h - $padT - $padB;

        $pesos = array_map(fn($m) => (float)($m['peso'] ?? 0), $antropo);
        $alturas = array_map(fn($m) => (float)($m['altura'] ?? 0), $antropo);
        $fechas = array_map(fn($m) => date('d/m/y', strtotime($m['fecha_medicion'])), $antropo);
        
        $imcs = [];
        foreach ($antropo as $m) {
            $p = (float)($m['peso'] ?? 0);
            $a = (float)($m['altura'] ?? 0) / 100;
            $imcs[] = ($p > 0 && $a > 0) ? ($p / ($a * $a)) : 0;
        }

        $n = count($antropo);

        // Escalas fijas de la gráfica real
        $maxKgCm = 180;
        $stepKgCm = 30;
        $numLines = $maxKgCm / $stepKgCm; // 6 líneas

        $maxIMC = 30; // De 0 a 30, step 5 (también 6 líneas)
        $stepIMC = 5;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '" style="font-family: helvetica, sans-serif;">';

        // Fondo
        $svg .= '<rect x="' . $padL . '" y="' . $padT . '" width="' . $chartW . '" height="' . $chartH . '" fill="#ffffff" stroke="#e0e0e0" stroke-width="0.5" />';

        // Líneas horizontales de referencia
        for ($i = 0; $i <= $numLines; $i++) {
            $y = $padT + $chartH - ($chartH * $i / $numLines);
            $valKgCm = $i * $stepKgCm;
            $valIMC = $i * $stepIMC;
            $svg .= '<line x1="' . $padL . '" y1="' . $y . '" x2="' . ($padL + $chartW) . '" y2="' . $y . '" stroke="#e8e8e8" stroke-width="0.5" />';
            $svg .= '<text x="' . ($padL - 6) . '" y="' . ($y + 3) . '" text-anchor="end" font-size="8" fill="#555">' . $valKgCm . '</text>';
            $svg .= '<text x="' . ($padL + $chartW + 6) . '" y="' . ($y + 3) . '" text-anchor="start" font-size="8" fill="#555">' . $valIMC . '</text>';
        }

        // Etiquetas de ejes (Títulos)
        $svg .= '<text x="' . $padL . '" y="' . ($padT - 10) . '" text-anchor="middle" font-size="9" fill="#555">Kg/Cm</text>';
        $svg .= '<text x="' . ($padL + $chartW) . '" y="' . ($padT - 10) . '" text-anchor="middle" font-size="9" fill="#555">IMC</text>';

        // Calcular puntos X (Centrados)
        $xStep = $chartW / $n;
        $barWidth = min(35, $xStep * 0.45); // Ancho de las columnas

        $puntosAltura = [];
        $puntosIMC = [];

        for ($i = 0; $i < $n; $i++) {
            $xCenter = $padL + ($i * $xStep) + ($xStep / 2);
            
            // Columna de Peso (Naranja/Amarillo)
            $hPeso = ($pesos[$i] / $maxKgCm) * $chartH;
            $yPeso = $padT + $chartH - $hPeso;
            $svg .= '<rect x="' . ($xCenter - $barWidth/2) . '" y="' . $yPeso . '" width="' . $barWidth . '" height="' . $hPeso . '" fill="#ffa600" />';

            // Puntos para líneas
            $yAltura = $padT + $chartH - (($alturas[$i] / $maxKgCm) * $chartH);
            $puntosAltura[] = $xCenter . ',' . $yAltura;
            
            $yIMC = $padT + $chartH - (($imcs[$i] / $maxIMC) * $chartH);
            $puntosIMC[] = $xCenter . ',' . $yIMC;
            
            // Etiqueta X (Fecha)
            $svg .= '<text x="' . $xCenter . '" y="' . ($padT + $chartH + 15) . '" text-anchor="middle" font-size="8" fill="#555">' . $fechas[$i] . '</text>';
        }

        // Línea de Altura (Morado, dashed)
        $polyAltura = implode(' ', $puntosAltura);
        $svg .= '<polyline points="' . $polyAltura . '" fill="none" stroke="#7a5195" stroke-width="2" stroke-dasharray="5,5" />';
        foreach ($puntosAltura as $pt) {
            [$cx, $cy] = explode(',', $pt);
            $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="3" fill="#7a5195" stroke="#fff" stroke-width="1.5" />';
        }

        // Línea de IMC (Verde, solid)
        $polyIMC = implode(' ', $puntosIMC);
        $svg .= '<polyline points="' . $polyIMC . '" fill="none" stroke="#00C851" stroke-width="2" />';
        foreach ($puntosIMC as $pt) {
            [$cx, $cy] = explode(',', $pt);
            $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="3" fill="#00C851" stroke="#fff" stroke-width="1.5" />';
        }

        // Leyenda Inferior
        $ly = $h - 15;
        // Legend Peso
        $svg .= '<rect x="' . ($w / 2 - 100) . '" y="' . ($ly - 5) . '" width="15" height="7" fill="#ffa600" />';
        $svg .= '<text x="' . ($w / 2 - 80) . '" y="' . ($ly + 2) . '" font-size="8" fill="#555">Peso (kg)</text>';
        // Legend Altura
        $svg .= '<line x1="' . ($w / 2 - 30) . '" y1="' . ($ly - 1) . '" x2="' . ($w / 2 - 10) . '" y2="' . ($ly - 1) . '" stroke="#7a5195" stroke-width="2" stroke-dasharray="3,3" />';
        $svg .= '<circle cx="' . ($w / 2 - 20) . '" cy="' . ($ly - 1) . '" r="2.5" fill="#7a5195" stroke="#fff" stroke-width="1" />';
        $svg .= '<text x="' . ($w / 2 - 5) . '" y="' . ($ly + 2) . '" font-size="8" fill="#555">Altura (cm)</text>';
        // Legend IMC
        $svg .= '<line x1="' . ($w / 2 + 50) . '" y1="' . ($ly - 1) . '" x2="' . ($w / 2 + 70) . '" y2="' . ($ly - 1) . '" stroke="#00C851" stroke-width="2" />';
        $svg .= '<circle cx="' . ($w / 2 + 60) . '" cy="' . ($ly - 1) . '" r="2.5" fill="#00C851" stroke="#fff" stroke-width="1" />';
        $svg .= '<text x="' . ($w / 2 + 75) . '" y="' . ($ly + 2) . '" font-size="8" fill="#555">IMC</text>';

        $svg .= '</svg>';

        // Guardar SVG en archivo temporal para que TCPDF lo lea como imagen
        $tmpPath = dirname(__DIR__, 2) . '/public/assets/uploads/tmp_grafica_' . uniqid() . '.svg';
        file_put_contents($tmpPath, $svg);

        return '<div style="text-align: center; margin-top: 10px;"><img src="' . $tmpPath . '" width="' . $w . '" height="' . $h . '" /></div>';
    }

    /**
     * Genera un gráfico de radar SVG pentagonal (5 ejes) para los tests físicos.
     */
    private function generarSvgRadar(array $pruebas, string $tipo = 'internacional', int $size = 240): string
    {
        if (empty($pruebas)) {
            return '<p style="font-size: 10px; color: #888; text-align: center; margin-top: 10px;">Sin pruebas físicas registradas para generar el gráfico de radar.</p>';
        }

        // Tomar la prueba más reciente (índice 0)
        $ultima = $pruebas[0];
        $labels = ['Fuerza', 'Resistencia', 'Velocidad', 'Coordinación', 'Reacción'];
        
        if ($tipo === 'nacional') {
            $keys = ['test_de_fuerza_nac', 'test_resistencia_nac', 'test_velocidad_nac', 'test_coordinacion_nac', 'test_de_reaccion_nac'];
            $fillColor = 'rgba(14, 165, 233, 0.15)'; // color azul cielo (nacional)
            $strokeColor = '#0EA5E9';
            $titleLabel = 'Última Nac: ';
        } else {
            $keys = ['test_de_fuerza', 'test_resistencia', 'test_velocidad', 'test_coordinacion', 'test_de_reaccion'];
            $fillColor = 'rgba(128,0,32,0.15)'; // color vinotinto (internacional)
            $strokeColor = '#800020';
            $titleLabel = 'Última Int: ';
        }

        $valores = [];
        foreach ($keys as $k) {
            $valores[] = min(100, max(0, (float)($ultima[$k] ?? 0)));
        }

        // Tomar la penúltima prueba (índice 1) si existe
        $valoresPenultima = [];
        if (count($pruebas) > 1) {
            $penultima = $pruebas[1];
            foreach ($keys as $k) {
                $valoresPenultima[] = min(100, max(0, (float)($penultima[$k] ?? 0)));
            }
        }

        $cx = $size / 2;
        $cy = $size / 2;
        $maxR = round($size * 0.28, 1); // Reduced relative radius to leave room for text
        $n = 5;
        // Sentido ANTI-HORARIO desde arriba para coincidir con ECharts
        $angulos = [];
        for ($i = 0; $i < $n; $i++) {
            $angulos[] = -(2 * M_PI * $i / $n) - (M_PI / 2);
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . ($size + 15) . '" viewBox="0 0 ' . $size . ' ' . ($size + 15) . '" style="font-family: helvetica, sans-serif;">';

        // Anillos concéntricos de referencia (20, 40, 60, 80, 100)
        $niveles = [20, 40, 60, 80, 100];
        foreach ($niveles as $nivel) {
            $r = $maxR * ($nivel / 100);
            $puntos = [];
            for ($i = 0; $i < $n; $i++) {
                $px = $cx + $r * cos($angulos[$i]);
                $py = $cy + $r * sin($angulos[$i]);
                $puntos[] = round($px, 1) . ',' . round($py, 1);
            }
            $svg .= '<polygon points="' . implode(' ', $puntos) . '" fill="none" stroke="#ddd" stroke-width="0.5" />';
            // Etiqueta del nivel al lado del primer eje (movemos ligeramente a la derecha para no solapar el 100 del top)
            $labelX = $cx + 5;
            $labelY = $cy + $r * sin($angulos[0]) - 3;
            $svg .= '<text x="' . round($labelX, 1) . '" y="' . round($labelY, 1) . '" font-size="7" fill="#bbb">' . $nivel . '</text>';
        }

        // Ejes radiales
        for ($i = 0; $i < $n; $i++) {
            $px = $cx + $maxR * cos($angulos[$i]);
            $py = $cy + $maxR * sin($angulos[$i]);
            $svg .= '<line x1="' . round($cx, 1) . '" y1="' . round($cy, 1) . '" x2="' . round($px, 1) . '" y2="' . round($py, 1) . '" stroke="#ccc" stroke-width="0.5" />';
        }

        // 1. Polígono de datos de la penúltima prueba (si existe, para dibujar primero y que quede detrás)
        if (!empty($valoresPenultima)) {
            $puntosPenultima = [];
            for ($i = 0; $i < $n; $i++) {
                $r = $maxR * ($valoresPenultima[$i] / 100);
                $px = $cx + $r * cos($angulos[$i]);
                $py = $cy + $r * sin($angulos[$i]);
                $puntosPenultima[] = round($px, 1) . ',' . round($py, 1);
            }
            $svg .= '<polygon points="' . implode(' ', $puntosPenultima) . '" fill="rgba(16,185,129,0.08)" stroke="#10B981" stroke-width="1.2" stroke-dasharray="3,3" />';
        }

        // 2. Polígono de datos de la última prueba
        $puntosDatos = [];
        for ($i = 0; $i < $n; $i++) {
            $r = $maxR * ($valores[$i] / 100);
            $px = $cx + $r * cos($angulos[$i]);
            $py = $cy + $r * sin($angulos[$i]);
            $puntosDatos[] = round($px, 1) . ',' . round($py, 1);
        }
        $svg .= '<polygon points="' . implode(' ', $puntosDatos) . '" fill="' . $fillColor . '" stroke="' . $strokeColor . '" stroke-width="1.8" />';

        // 3. Puntos pequeños de la penúltima prueba
        if (!empty($valoresPenultima)) {
            for ($i = 0; $i < $n; $i++) {
                $r = $maxR * ($valoresPenultima[$i] / 100);
                $px = $cx + $r * cos($angulos[$i]);
                $py = $cy + $r * sin($angulos[$i]);
                $svg .= '<circle cx="' . round($px, 1) . '" cy="' . round($py, 1) . '" r="2" fill="#10B981" stroke="#fff" stroke-width="0.8" />';
            }
        }

        // 4. Puntos grandes de la última prueba
        for ($i = 0; $i < $n; $i++) {
            $r = $maxR * ($valores[$i] / 100);
            $px = $cx + $r * cos($angulos[$i]);
            $py = $cy + $r * sin($angulos[$i]);
            $svg .= '<circle cx="' . round($px, 1) . '" cy="' . round($py, 1) . '" r="2.8" fill="' . $strokeColor . '" stroke="#fff" stroke-width="1.2" />';

            // Etiquetas de los ejes (alejadas)
            $lr = $maxR + 18;
            $lx = $cx + $lr * cos($angulos[$i]);
            $ly = $cy + $lr * sin($angulos[$i]);
            $anchor = 'middle';
            if ($lx < $cx - 15) $anchor = 'end';
            elseif ($lx > $cx + 15) $anchor = 'start';
            $svg .= '<text x="' . round($lx, 1) . '" y="' . round($ly, 1) . '" text-anchor="' . $anchor . '" font-size="7.5" font-weight="bold" fill="#333">' . $labels[$i] . '</text>';
            
            // Valor numérico debajo de la etiqueta (con formato de color sin usar tspan para evitar bugs de TCPDF)
            if (!empty($valoresPenultima)) {
                // Barra en el centro
                $svg .= '<text x="' . round($lx, 1) . '" y="' . round($ly + 8, 1) . '" text-anchor="middle" font-size="7" fill="#666">/</text>';
                // Última a la izquierda
                $svg .= '<text x="' . round($lx - 4, 1) . '" y="' . round($ly + 8, 1) . '" text-anchor="end" font-size="7" font-weight="bold" fill="' . $strokeColor . '">' . round($valores[$i]) . '</text>';
                // Penúltima (Verde) a la derecha
                $svg .= '<text x="' . round($lx + 4, 1) . '" y="' . round($ly + 8, 1) . '" text-anchor="start" font-size="7" fill="#10B981">' . round($valoresPenultima[$i]) . '</text>';
            } else {
                $svg .= '<text x="' . round($lx, 1) . '" y="' . round($ly + 8, 1) . '" text-anchor="' . $anchor . '" font-size="7" fill="' . $strokeColor . '">' . round($valores[$i]) . '/100</text>';
            }
        }

        // Fecha del test / Legend (Usamos textos posicionados para colorear en TCPDF de forma segura)
        $fechaTest = !empty($ultima['fecha_evento']) ? date('d/m/Y', strtotime($ultima['fecha_evento'])) : '—';
        if (!empty($valoresPenultima)) {
            $fechaAnt = !empty($pruebas[1]['fecha_evento']) ? date('d/m/Y', strtotime($pruebas[1]['fecha_evento'])) : '—';
            // Última a la izquierda
            $svg .= '<text x="' . ($cx - 12) . '" y="' . ($size + 5) . '" text-anchor="end" font-size="7" font-weight="bold" fill="' . $strokeColor . '">' . $titleLabel . $fechaTest . '</text>';
            // Separador en el centro
            $svg .= '<text x="' . $cx . '" y="' . ($size + 5) . '" text-anchor="middle" font-size="7" fill="#666">|</text>';
            // Anterior a la derecha
            $svg .= '<text x="' . ($cx + 12) . '" y="' . ($size + 5) . '" text-anchor="start" font-size="7" font-weight="bold" fill="#10B981">Ant: ' . $fechaAnt . '</text>';
        } else {
            $svg .= '<text x="' . $cx . '" y="' . ($size + 5) . '" text-anchor="middle" font-size="7" fill="' . $strokeColor . '" font-weight="bold">' . $titleLabel . $fechaTest . '</text>';
        }

        $svg .= '</svg>';

        // Guardar SVG en archivo temporal
        $tmpPath = dirname(__DIR__, 2) . '/public/assets/uploads/tmp_radar_' . $tipo . '_' . uniqid() . '.svg';
        file_put_contents($tmpPath, $svg);

        return '<div style="text-align: center; margin-top: 5px;"><img src="' . $tmpPath . '" width="' . $size . '" height="' . ($size + 15) . '" /></div>';
    }

    /* ======================================================================
     *  HTML DE LA FICHA TÉCNICA - 4 PÁGINAS
     * ====================================================================== */

    private function construirHtml(array $a, array $antropo, array $pruebas, array $asistencia, array $historialAsist, array $consultas): string
    {
        $esc = fn($v) => htmlspecialchars((string) ($v ?? '—'), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $nombreCompleto = $esc($a['nombre'] . ' ' . $a['apellido']);
        $fechaNac = $a['fecha_nac'] ?? null;
        $page = $fechaNac ? (new DateTime($fechaNac)) : null; // Temp fix to keep variables clear
        $edad = $fechaNac ? (new DateTime($fechaNac))->diff(new DateTime('today'))->y : null;

        $sexoVal = strtoupper((string)($a['sexo'] ?? ''));
        $generoTexto = match ($sexoVal) {
            'M' => 'Masculino',
            'F' => 'Femenino',
            default => 'Sin definir'
        };

        // Foto del atleta usando ruta absoluta resuelta con BASE_PATH
        $fotoHtml = '';
        if (!empty($a['foto'])) {
            $fotoPath = realpath(BASE_PATH . '/public' . $a['foto']);
            if ($fotoPath && file_exists($fotoPath)) {
                // Redimensionar la imagen a 180x180 para reducir el peso del base64
                // y acelerar la generación del PDF (de 12s a ~2s).
                $mime = mime_content_type($fotoPath) ?: 'image/jpeg';
                $imgOriginal = null;
                if (extension_loaded('gd')) {
                    if ((str_contains($mime, 'jpeg') || str_contains($mime, 'jpg')) && function_exists('imagecreatefromjpeg')) {
                        $imgOriginal = @imagecreatefromjpeg($fotoPath);
                    } elseif (str_contains($mime, 'png') && function_exists('imagecreatefrompng')) {
                        $imgOriginal = @imagecreatefrompng($fotoPath);
                    } elseif (str_contains($mime, 'webp') && function_exists('imagecreatefromwebp')) {
                        $imgOriginal = @imagecreatefromwebp($fotoPath);
                    }
                }

                if ($imgOriginal) {
                    $thumb = imagecreatetruecolor(180, 180);
                    imagecopyresampled($thumb, $imgOriginal, 0, 0, 0, 0, 180, 180, imagesx($imgOriginal), imagesy($imgOriginal));
                    ob_start();
                    imagejpeg($thumb, null, 80);
                    $imgData = ob_get_clean();
                    imagedestroy($imgOriginal);
                    imagedestroy($thumb);
                    $b64 = base64_encode($imgData);
                } else {
                    // Fallback: usar la imagen original si GD no puede procesarla
                    $b64 = base64_encode(file_get_contents($fotoPath));
                }
                // TCPDF writeHTML() requiere data URI estándar, NO el prefijo @
                $fotoHtml = '<img src="data:image/jpeg;base64,' . $b64 . '" width="90" height="90" style="border-radius: 6px; border: 2px solid #800020;" />';
            }
        }

        // Estatus
        $estatusVal = (int) ($a['estatus'] ?? 1);
        $estatusTexto = match ($estatusVal) {
            1 => 'Activo',
            2 => 'Lesionado',
            0 => 'Suspendido',
            3 => 'Inactivo',
            default => 'Desconocido'
        };

        // Dirección
        $direccionParts = array_filter([$a['localidad'] ?? null, $a['parroquia'] ?? null, $a['municipio'] ?? null, $a['estado'] ?? null]);
        $direccion = $direccionParts ? $esc(implode(', ', $direccionParts)) : '—';

        // Ficha Médica
        $gsVal = trim($a['grupo_sanguineo'] ?? '');
        $gs = ($gsVal !== '' && $gsVal !== '—') ? $esc($gsVal) : 'Sin Asignar';
        
        $alergiasVal = trim($a['alergias'] ?? '');
        $alergias = $alergiasVal !== '' ? $esc($alergiasVal) : 'Ninguna';
        
        $cronicaVal = trim($a['condicion_cronica'] ?? '');
        $cronica = $cronicaVal !== '' ? $esc($cronicaVal) : 'Ninguna';
        
        $medicacionVal = trim($a['medicacion_actual'] ?? '');
        $medicacion = $medicacionVal !== '' ? $esc($medicacionVal) : 'Ninguna';
        
        $antecedentesFam = !empty($a['antecedentes_familiares']) ? nl2br($esc($a['antecedentes_familiares'])) : 'Sin antecedentes registrados';
        $antecedentesQuir = !empty($a['antecedentes_quirurgicos']) ? nl2br($esc($a['antecedentes_quirurgicos'])) : 'Sin antecedentes registrados';

        // Discapacidades
        $discapacidadesRows = '';
        if (!empty($a['discapacidades'])) {
            foreach ($a['discapacidades'] as $disc) {
                $discapacidadesRows .= sprintf(
                    '<tr><td>%s</td><td>%s</td><td>%s%%</td><td>%s</td></tr>',
                    $esc($disc['nombre_tipo'] ?? '—'),
                    $esc($disc['nro_carnet'] ?? '—'),
                    $esc($disc['porcentaje_discapacidad'] ?? '—'),
                    $esc(date('d/m/Y', strtotime($disc['fecha_registro'])))
                );
            }
        }
        $discapacidadesTable = $discapacidadesRows ? 
            "<table class=\"data-table\" cellpadding=\"4\"><thead><tr><th>Tipo de Discapacidad</th><th>Nro. Carnet</th><th>Porcentaje</th><th>Fecha Registro</th></tr></thead><tbody>$discapacidadesRows</tbody></table>" : 
            '<p style="font-size: 10px; color: #666; margin-top: 5px;">No se registran discapacidades para este atleta.</p>';

        // ============ ASISTENCIAS ============
        $asiMap = [1 => 0, 0 => 0, 2 => 0];
        foreach ($asistencia as $row) { $asiMap[(int)$row['estatus']] = (int) $row['total']; }
        $totalAsi = array_sum($asiMap);
        $pctPresente = $totalAsi > 0 ? round(($asiMap[1] / $totalAsi) * 100, 1) : 0;
        $pctAusente = $totalAsi > 0 ? round(($asiMap[0] / $totalAsi) * 100, 1) : 0;
        $pctJustificado = $totalAsi > 0 ? round(($asiMap[2] / $totalAsi) * 100, 1) : 0;

        // Calendario Anual (SVG 12 meses)
        $calendarioSvg = $this->generarSvgCalendario($historialAsist);

        // Tabla detallada de asistencias (máximo 31 del mes actual)
        $asistDetalleRows = '';
        $contadorAsist = 0;
        $mesActual = date('Y-m'); // Año y mes actual: "YYYY-MM"
        
        foreach ($historialAsist as $ha) {
            $fechaAsist = date('Y-m', strtotime($ha['fecha']));
            if ($fechaAsist !== $mesActual) {
                continue; // Saltar si no es del mes actual
            }
            
            if ($contadorAsist >= 31) break; // Limitar a un máximo de 31
            
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
                '<tr><td>%s</td><td>%s</td><td style="color: %s; font-weight: bold;">%s</td><td style="font-size: 9px;">%s</td></tr>',
                $esc(date('d/m/Y', strtotime($ha['fecha']))),
                $esc($tipoEv),
                $estColor,
                $esc($estTexto),
                $esc($ha['observaciones'] ?? '—')
            );
            $contadorAsist++;
        }
        $asistDetalleTable = $asistDetalleRows ?
            "<table class=\"data-table\" cellpadding=\"4\"><thead><tr><th>Fecha</th><th>Tipo</th><th>Estatus</th><th>Observaciones</th></tr></thead><tbody>$asistDetalleRows</tbody></table>" :
            '<p style="font-size: 10px; color: #666; margin-top: 5px;">Sin registros de asistencia.</p>';

        // ============ ANTROPOMETRÍA ============
        $antropoRows = '';
        foreach ($antropo as $m) {
            $peso = (float)($m['peso'] ?? 0);
            $altura = (float)($m['altura'] ?? 0);
            $imcText = '—';
            if ($peso > 0 && $altura > 0) {
                $alturaM = $altura / 100;
                $imc = $peso / ($alturaM * $alturaM);
                $imcLabel = 'Normal';
                if ($imc < 18.5) $imcLabel = 'Bajo peso';
                elseif ($imc >= 25 && $imc < 30) $imcLabel = 'Sobrepeso';
                elseif ($imc >= 30) $imcLabel = 'Obesidad';
                $imcText = sprintf('%.1f (%s)', $imc, $imcLabel);
            }
            $antropoRows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                $esc(date('d/m/Y', strtotime($m['fecha_medicion']))),
                $esc($m['peso']),
                $esc($m['altura']),
                $esc(!empty($m['porcentaje_grasa']) ? $m['porcentaje_grasa'] . '%' : '—'),
                $esc(!empty($m['porcentaje_musculatura']) ? $m['porcentaje_musculatura'] . '%' : '—'),
                $esc($m['envergadura']),
                $esc($m['largo_de_pierna']),
                $esc($m['largo_de_torso']),
                $esc($imcText)
            );
        }
        $antropoTable = $antropoRows ? 
            "<table class=\"data-table\" cellpadding=\"4\"><thead><tr><th>Fecha</th><th>Peso (kg)</th><th>Altura (cm)</th><th>% Grasa</th><th>% Musc.</th><th>Env. (cm)</th><th>Pierna (cm)</th><th>Torso (cm)</th><th>IMC</th></tr></thead><tbody>$antropoRows</tbody></table>" : 
            '<p style="font-size: 10px; color: #666; margin-top: 5px;">Sin mediciones antropométricas registradas.</p>';

        // SVG Gráfica
        $svgGrafica = $this->generarSvgGrafica($antropo);

        // ============ PRUEBAS FÍSICAS ============
        $pruebasRows = '';
        foreach ($pruebas as $p) {
            $fuerzaStr = $p['test_de_fuerza_raw'] !== null ? $esc($p['test_de_fuerza_raw']) . ' cm<br><font size="7" color="#64748b">I: ' . (int)$p['test_de_fuerza'] . ' | N: ' . (int)$p['test_de_fuerza_nac'] . '</font>' : '—';
            $resistenciaStr = $p['test_resistencia_raw'] !== null ? $esc($p['test_resistencia_raw']) . ' m<br><font size="7" color="#64748b">I: ' . (int)$p['test_resistencia'] . ' | N: ' . (int)$p['test_resistencia_nac'] . '</font>' : '—';
            $velocidadStr = $p['test_velocidad_raw'] !== null ? $esc($p['test_velocidad_raw']) . ' s<br><font size="7" color="#64748b">I: ' . (int)$p['test_velocidad'] . ' | N: ' . (int)$p['test_velocidad_nac'] . '</font>' : '—';
            $coordinacionStr = $p['test_coordinacion_raw'] !== null ? $esc($p['test_coordinacion_raw']) . ' s<br><font size="7" color="#64748b">I: ' . (int)$p['test_coordinacion'] . ' | N: ' . (int)$p['test_coordinacion_nac'] . '</font>' : '—';
            $reaccionStr = $p['test_de_reaccion_raw'] !== null ? $esc($p['test_de_reaccion_raw']) . ' ms<br><font size="7" color="#64748b">I: ' . (int)$p['test_de_reaccion'] . ' | N: ' . (int)$p['test_de_reaccion_nac'] . '</font>' : '—';

            $pruebasRows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                $esc(date('d/m/Y', strtotime($p['fecha_evento']))),
                $fuerzaStr,
                $resistenciaStr,
                $velocidadStr,
                $coordinacionStr,
                $reaccionStr
            );
        }
        $pruebasTable = $pruebasRows ? 
            "<table class=\"data-table\" cellpadding=\"4\"><thead><tr><th>Fecha Evaluación</th><th>Fuerza (CMJ)</th><th>Resistencia (Yo-Yo)</th><th>Velocidad (30m)</th><th>Coordinación (Conos)</th><th>Reacción (Cognitiva)</th></tr></thead><tbody>$pruebasRows</tbody></table>" : 
            '<p style="font-size: 10px; color: #666; margin-top: 5px;">Sin pruebas físicas registradas.</p>';

        // SVG Radars (Internacional y Nacional)
        $svgRadarInternacional = $this->generarSvgRadar($pruebas, 'internacional', 240);
        $svgRadarNacional      = $this->generarSvgRadar($pruebas, 'nacional', 240);

        // Última Evaluación con barras de puntaje
        $ultimaPrueba = !empty($pruebas) ? $pruebas[0] : null;
        $ultimaPruebaHtml = '';
        if ($ultimaPrueba) {
            $f_int = min(100, max(0, (int)($ultimaPrueba['test_de_fuerza'] ?? 0)));
            $f_nac = min(100, max(0, (int)($ultimaPrueba['test_de_fuerza_nac'] ?? 0)));
            $r_int = min(100, max(0, (int)($ultimaPrueba['test_resistencia'] ?? 0)));
            $r_nac = min(100, max(0, (int)($ultimaPrueba['test_resistencia_nac'] ?? 0)));
            $v_int = min(100, max(0, (int)($ultimaPrueba['test_velocidad'] ?? 0)));
            $v_nac = min(100, max(0, (int)($ultimaPrueba['test_velocidad_nac'] ?? 0)));
            $c_int = min(100, max(0, (int)($ultimaPrueba['test_coordinacion'] ?? 0)));
            $c_nac = min(100, max(0, (int)($ultimaPrueba['test_coordinacion_nac'] ?? 0)));
            $re_int = min(100, max(0, (int)($ultimaPrueba['test_de_reaccion'] ?? 0)));
            $re_nac = min(100, max(0, (int)($ultimaPrueba['test_de_reaccion_nac'] ?? 0)));

            $f_int_rem = 100 - $f_int;
            $f_nac_rem = 100 - $f_nac;
            $r_int_rem = 100 - $r_int;
            $r_nac_rem = 100 - $r_nac;
            $v_int_rem = 100 - $v_int;
            $v_nac_rem = 100 - $v_nac;
            $c_int_rem = 100 - $c_int;
            $c_nac_rem = 100 - $c_nac;
            $re_int_rem = 100 - $re_int;
            $re_nac_rem = 100 - $re_nac;

            $f_raw = $ultimaPrueba['test_de_fuerza_raw'] !== null ? $ultimaPrueba['test_de_fuerza_raw'] : '—';
            $r_raw = $ultimaPrueba['test_resistencia_raw'] !== null ? $ultimaPrueba['test_resistencia_raw'] : '—';
            $v_raw = $ultimaPrueba['test_velocidad_raw'] !== null ? $ultimaPrueba['test_velocidad_raw'] : '—';
            $c_raw = $ultimaPrueba['test_coordinacion_raw'] !== null ? $ultimaPrueba['test_coordinacion_raw'] : '—';
            $re_raw = $ultimaPrueba['test_de_reaccion_raw'] !== null ? $ultimaPrueba['test_de_reaccion_raw'] : '—';

            $fechaUltima = date('d/m/Y', strtotime($ultimaPrueba['fecha_evento']));

            $ultimaPruebaHtml = "
            <table width=\"100%\" cellpadding=\"4\" style=\"border: 1px solid #cbd5e1; background-color: #f8fafc; margin-bottom: 20px; font-family: helvetica, sans-serif;\">
                <tr style=\"background-color: #800020; color: #ffffff;\">
                    <td colspan=\"2\" style=\"font-weight: bold; font-size: 11px; padding: 6px 10px;\">
                        ÚLTIMA EVALUACIÓN FÍSICA ({$fechaUltima})
                    </td>
                </tr>
                <tr>
                    <td width=\"50%\" style=\"border-bottom: 1px solid #cbd5e1; border-right: 1px solid #cbd5e1;\">
                        <font size=\"9\"><strong>Fuerza (CMJ):</strong> {$f_raw} cm</font><br>
                        <font size=\"7\" color=\"#64748b\">Int: {$f_int}/100</font>
                        <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color: #e2e8f0;\">
                            <tr><td width=\"{$f_int}%\" style=\"background-color: #BE123C; font-size: 3px; line-height: 3px; height: 3px;\"></td><td width=\"{$f_int_rem}%\" style=\"font-size: 3px; line-height: 3px; height: 3px;\"></td></tr>
                        </table>
                        <font size=\"7\" color=\"#64748b\">Nac: {$f_nac}/100</font>
                        <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color: #e2e8f0;\">
                            <tr><td width=\"{$f_nac}%\" style=\"background-color: #BE123C; opacity: 0.7; font-size: 3px; line-height: 3px; height: 3px;\"></td><td width=\"{$f_nac_rem}%\" style=\"font-size: 3px; line-height: 3px; height: 3px;\"></td></tr>
                        </table>
                    </td>
                    <td width=\"50%\" style=\"border-bottom: 1px solid #cbd5e1;\">
                        <font size=\"9\"><strong>Resistencia (Yo-Yo):</strong> {$r_raw} m</font><br>
                        <font size=\"7\" color=\"#64748b\">Int: {$r_int}/100</font>
                        <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color: #e2e8f0;\">
                            <tr><td width=\"{$r_int}%\" style=\"background-color: #10B981; font-size: 3px; line-height: 3px; height: 3px;\"></td><td width=\"{$r_int_rem}%\" style=\"font-size: 3px; line-height: 3px; height: 3px;\"></td></tr>
                        </table>
                        <font size=\"7\" color=\"#64748b\">Nac: {$r_nac}/100</font>
                        <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color: #e2e8f0;\">
                            <tr><td width=\"{$r_nac}%\" style=\"background-color: #10B981; opacity: 0.7; font-size: 3px; line-height: 3px; height: 3px;\"></td><td width=\"{$r_nac_rem}%\" style=\"font-size: 3px; line-height: 3px; height: 3px;\"></td></tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td width=\"50%\" style=\"border-bottom: 1px solid #cbd5e1; border-right: 1px solid #cbd5e1;\">
                        <font size=\"9\"><strong>Velocidad (30m):</strong> {$v_raw} s</font><br>
                        <font size=\"7\" color=\"#64748b\">Int: {$v_int}/100</font>
                        <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color: #e2e8f0;\">
                            <tr><td width=\"{$v_int}%\" style=\"background-color: #F59E0B; font-size: 3px; line-height: 3px; height: 3px;\"></td><td width=\"{$v_int_rem}%\" style=\"font-size: 3px; line-height: 3px; height: 3px;\"></td></tr>
                        </table>
                        <font size=\"7\" color=\"#64748b\">Nac: {$v_nac}/100</font>
                        <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color: #e2e8f0;\">
                            <tr><td width=\"{$v_nac}%\" style=\"background-color: #F59E0B; opacity: 0.7; font-size: 3px; line-height: 3px; height: 3px;\"></td><td width=\"{$v_nac_rem}%\" style=\"font-size: 3px; line-height: 3px; height: 3px;\"></td></tr>
                        </table>
                    </td>
                    <td width=\"50%\" style=\"border-bottom: 1px solid #cbd5e1;\">
                        <font size=\"9\"><strong>Coordinación (Conos):</strong> {$c_raw} s</font><br>
                        <font size=\"7\" color=\"#64748b\">Int: {$c_int}/100</font>
                        <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color: #e2e8f0;\">
                            <tr><td width=\"{$c_int}%\" style=\"background-color: #8B5CF6; font-size: 3px; line-height: 3px; height: 3px;\"></td><td width=\"{$c_int_rem}%\" style=\"font-size: 3px; line-height: 3px; height: 3px;\"></td></tr>
                        </table>
                        <font size=\"7\" color=\"#64748b\">Nac: {$c_nac}/100</font>
                        <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color: #e2e8f0;\">
                            <tr><td width=\"{$c_nac}%\" style=\"background-color: #8B5CF6; opacity: 0.7; font-size: 3px; line-height: 3px; height: 3px;\"></td><td width=\"{$c_nac_rem}%\" style=\"font-size: 3px; line-height: 3px; height: 3px;\"></td></tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td width=\"50%\" style=\"border-right: 1px solid #cbd5e1;\">
                        <font size=\"9\"><strong>Reacción (Cognitiva):</strong> {$re_raw} ms</font><br>
                        <font size=\"7\" color=\"#64748b\">Int: {$re_int}/100</font>
                        <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color: #e2e8f0;\">
                            <tr><td width=\"{$re_int}%\" style=\"background-color: #EC4899; font-size: 3px; line-height: 3px; height: 3px;\"></td><td width=\"{$re_int_rem}%\" style=\"font-size: 3px; line-height: 3px; height: 3px;\"></td></tr>
                        </table>
                        <font size=\"7\" color=\"#64748b\">Nac: {$re_nac}/100</font>
                        <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color: #e2e8f0;\">
                            <tr><td width=\"{$re_nac}%\" style=\"background-color: #EC4899; opacity: 0.7; font-size: 3px; line-height: 3px; height: 3px;\"></td><td width=\"{$re_nac_rem}%\" style=\"font-size: 3px; line-height: 3px; height: 3px;\"></td></tr>
                        </table>
                    </td>
                    <td width=\"50%\"></td>
                </tr>
            </table>";
        } else {
            $ultimaPruebaHtml = '<p style="font-size: 10px; color: #666; text-align: center; margin-top: 10px; margin-bottom: 20px;">No hay evaluaciones registradas aún.</p>';
        }

        // ============ CONSULTAS MÉDICAS ============
        $tiposMedicos = [
            1 => 'Enfermedad',
            2 => 'Lesión',
            3 => 'Control',
            4 => 'Evaluación',
            5 => 'Asesoría',
            6 => 'Terapia o Rehabilitación',
            7 => 'Intervención o Emergencia'
        ];

        $estatusesMedicos = [
            0 => ['label' => 'No Apto', 'color' => '#cf222e'],
            1 => ['label' => 'Apto', 'color' => '#2ea44f']
        ];

        $consultasBlocks = '';
        if (empty($consultas)) {
            $consultasBlocks = '<p style="font-size: 11px; color: #666; margin-top: 10px;">Sin consultas médicas registradas para este atleta.</p>';
        } else {
            foreach ($consultas as $index => $c) {
                $tipoStr = $tiposMedicos[(int)($c['tipo_consulta'] ?? 0)] ?? 'Otro';
                $diagnosticoStr = !empty($c['diagnostico']) ? $c['diagnostico'] : '—';
                $fechaSucesoStr = !empty($c['fecha_suceso']) ? date('d/m/Y', strtotime($c['fecha_suceso'])) : '—';
                
                $fechaAltaStr = '';
                if (!empty($c['fecha_alta_estimada'])) {
                    $fechaAltaStr = ' · Alta: ' . date('d/m/Y', strtotime($c['fecha_alta_estimada']));
                }
                
                $estInfo = $estatusesMedicos[(int)($c['estatus_disponibilidad'] ?? 0)] ?? ['label' => 'Desconocido', 'color' => '#888'];
                $estLabel = $estInfo['label'];
                $estColor = $estInfo['color'];

                $registradoPor = !empty($c['usuario_nombre']) ? ($c['usuario_nombre'] . ' ' . ($c['usuario_apellido'] ?? '')) : 'Sistema';

                $descVal = trim($c['descripcion'] ?? '');
                $descripcionStr = ($descVal !== '' && $descVal !== '—') ? nl2br($descVal) : '<span style="color: #94a3b8; font-style: italic;">Sin observaciones ni síntomas registrados.</span>';
                
                $tratVal = trim($c['tratamiento_indicado'] ?? '');
                $tratamientoStr = ($tratVal !== '' && $tratVal !== '—') ? nl2br($tratVal) : '<span style="color: #94a3b8; font-style: italic;">Sin tratamiento específico indicado.</span>';

                $consultasBlocks .= sprintf('
                    <table style="width: 100%%; border: 1px solid #cbd5e1; margin-bottom: 15px; font-family: helvetica, sans-serif;" cellpadding="6">
                        <tr style="background-color: #800020; color: #ffffff;">
                            <td style="width: 50%%; font-weight: bold; font-size: 11px;">
                                REGISTRO #%d: %s
                            </td>
                            <td style="width: 50%%; text-align: right; font-size: 10px; font-weight: bold;">
                                Suceso: %s%s
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%%; font-size: 10px; border-bottom: 1px solid #f0f0f0;">
                                <strong>Disponibilidad:</strong> <span style="color: %s; font-weight: bold;">%s</span>
                            </td>
                            <td style="width: 50%%; text-align: right; font-size: 10px; color: #64748b; border-bottom: 1px solid #f0f0f0;">
                                <strong>Registrado por:</strong> %s
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="font-size: 11px; border-bottom: 1px solid #f0f0f0;">
                                <strong>Diagnóstico:</strong> <strong style="color: #800020;">%s</strong>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="font-size: 10px; background-color: #fafafa; border-bottom: 1px solid #f0f0f0;">
                                <strong>Descripción / Síntomas:</strong><br/>
                                %s
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="font-size: 10px; background-color: #f8fafc;">
                                <strong>Tratamiento Indicado:</strong><br/>
                                %s
                            </td>
                        </tr>
                    </table>
                    <br/>
                ',
                count($consultas) - $index,
                $esc($tipoStr),
                $esc($fechaSucesoStr),
                $esc($fechaAltaStr),
                $estColor,
                $esc($estLabel),
                $esc($registradoPor),
                $esc($diagnosticoStr),
                $descripcionStr,
                $tratamientoStr
                );
            }
        }

        $fechaGeneracion = $esc(date('d/m/Y h:i A'));

        // ============ HTML FINAL - 5 PÁGINAS ============
        return <<<HTML
<style>
    body { font-family: helvetica, sans-serif; color: #333; line-height: 1.8; }
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
        letter-spacing: 1px;
    }
    table.info-grid { width: 100%; margin-bottom: 12px; }
    table.info-grid td { padding: 6px; vertical-align: top; border-bottom: 1px solid #f0f0f0; line-height: 1.5; }
    .info-label { font-weight: bold; color: #800020; width: 35%; font-size: 11px; }
    .info-value { color: #333; width: 65%; font-size: 11px; }
    table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.data-table th { background-color: #800020; color: #ffffff; font-size: 10px; font-weight: bold; padding: 8px 5px; text-align: center; border: 1px solid #800020; }
    table.data-table td { font-size: 10px; padding: 7px 5px; text-align: center; border: 1px solid #e9ecef; }
    table.data-table tr:nth-child(even) { background-color: #f8f9fa; }
    .status-badge { color: #800020; border: 1px solid #800020; padding: 3px 12px; font-size: 12px; font-weight: bold; }
    .stat-box { text-align: center; border: 1px solid #e0e0e0; background-color: #fcfcfc; padding: 10px 5px; }
    .stat-number { font-size: 20px; font-weight: bold; display: block; margin-top: 5px; }
    .stat-label { font-size: 9px; color: #555; text-transform: uppercase; font-weight: bold; display: block; margin-bottom: 3px; }
    .stat-pct { font-size: 9px; color: #777; display: block; margin-top: 3px; }
</style>

<!-- ===================== PÁGINA 1: DATOS PERSONALES, REPRESENTANTE Y MÉDICOS ===================== -->

<div class="section">
    <table width="100%">
        <tr>
            <td width="75%">
                <h2 style="margin:0; color:#1a1a1a; font-size: 20px;">{$nombreCompleto}</h2>
                <p style="margin:5px 0 12px 0;"><span class="status-badge">Estatus: {$esc($estatusTexto)}</span></p>
            </td>
            <td width="25%" style="text-align: right; vertical-align: top;">
                {$fotoHtml}
            </td>
        </tr>
    </table>
    <div style="line-height: 18px; height: 18px;">&nbsp;</div>
    <table class="info-grid">
        <tr>
            <td width="48%">
                <table width="100%">
                    <tr><td class="info-label">Documento:</td><td class="info-value">{$esc($a['cedula'])}</td></tr>
                    <tr><td class="info-label">Nacimiento:</td><td class="info-value">{$esc($fechaNac)} ({$esc($edad)} años)</td></tr>
                    <tr><td class="info-label">Género:</td><td class="info-value">{$esc($generoTexto)}</td></tr>
                    <tr><td class="info-label">Teléfono:</td><td class="info-value">{$esc($a['telefono'])}</td></tr>
                </table>
            </td>
            <td width="4%"></td>
            <td width="48%">
                <table width="100%">
                    <tr><td class="info-label">Representante:</td><td class="info-value">{$esc(($a['tutor_nombres'] ?? '') . ' ' . ($a['tutor_apellidos'] ?? ''))}</td></tr>
                    <tr><td class="info-label">Documento Rep:</td><td class="info-value">{$esc($a['tutor_cedula'])}</td></tr>
                    <tr><td class="info-label">Tlf. Rep:</td><td class="info-value">{$esc($a['tutor_telefono'])}</td></tr>
                    <tr><td class="info-label">Relación:</td><td class="info-value">{$esc(ucfirst($a['tutor_relacion'] ?? '—'))}</td></tr>
                    <tr><td class="info-label">Dirección:</td><td class="info-value">{$direccion}</td></tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-header">Ficha Deportiva</div>
    <table class="info-grid">
        <tr>
            <td width="48%">
                <table width="100%">
                    <tr><td class="info-label">Categoría:</td><td class="info-value">{$esc($a['nombre_categoria'])}</td></tr>
                    <tr><td class="info-label">Dorsal:</td><td class="info-value">{$esc($a['nun_dorsal'])}</td></tr>
                    <tr><td class="info-label">Pierna Dom.:</td><td class="info-value">{$esc(ucfirst($a['pierna_dominante'] ?? 'Sin definir'))}</td></tr>
                </table>
            </td>
            <td width="4%"></td>
            <td width="48%">
                <table width="100%">
                    <tr><td class="info-label">Posición Principal:</td><td class="info-value">{$esc($a['posicion_principal'])}</td></tr>
                    <tr><td class="info-label">Posición Secundaria:</td><td class="info-value">{$esc($a['posicion_secundaria'])}</td></tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-header">Información Médica</div>
    <table class="info-grid">
        <tr>
            <td width="48%">
                <table width="100%">
                    <tr><td class="info-label">Grupo Sanguíneo:</td><td class="info-value">{$gs}</td></tr>
                    <tr><td class="info-label">Alergias:</td><td class="info-value">{$alergias}</td></tr>
                    <tr><td class="info-label">Cond. Crónica:</td><td class="info-value">{$cronica}</td></tr>
                    <tr><td class="info-label">Medicación:</td><td class="info-value">{$medicacion}</td></tr>
                </table>
            </td>
            <td width="4%"></td>
            <td width="48%">
                <table width="100%">
                    <tr><td class="info-label">Antecedentes<br>Familiares:</td><td class="info-value">{$antecedentesFam}</td></tr>
                    <tr><td class="info-label">Antecedentes<br>Quirúrgicos:</td><td class="info-value">{$antecedentesQuir}</td></tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<div style="line-height: 25px; height: 25px;">&nbsp;</div>
<div style="font-weight: bold; font-size: 12px; color: #800020; margin-bottom: 8px; text-transform: uppercase; border-bottom: 1px solid #800020; display: inline-block; padding-bottom: 3px;">Discapacidades Registradas</div>
{$discapacidadesTable}

<!-- ===================== SALTO DE PÁGINA ===================== -->
<br pagebreak="true" />

<!-- ===================== PÁGINA 2: HISTORIAL DE CONSULTAS MÉDICAS ===================== -->
<div class="section">
    <div class="section-header">Historial de Consultas Médicas</div>
    <div style="font-weight: bold; font-size: 12px; color: #800020; margin-top: 15px; margin-bottom: 8px; text-transform: uppercase; border-bottom: 1px solid #800020; display: inline-block; padding-bottom: 3px;">Consultas Registradas</div>
    {$consultasBlocks}
</div>

<!-- ===================== SALTO DE PÁGINA ===================== -->
<br pagebreak="true" />

<!-- ===================== PÁGINA 3: CONTROL DE ASISTENCIAS ===================== -->

<div class="section">
    <div class="section-header">Control de Asistencias</div>
    
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
    
    <br>
    <div style="font-weight: bold; font-size: 11px; color: #800020; margin-top: 15px; margin-bottom: 8px; text-transform: uppercase;">Registro Detallado de Sesiones (Mes Actual)</div>
    {$asistDetalleTable}
</div>

<!-- ===================== SALTO DE PÁGINA ===================== -->
<br pagebreak="true" />

<!-- ===================== PÁGINA 4: HISTORIAL ANTROPOMÉTRICO Y GRÁFICA ===================== -->

<div class="section">
    <div class="section-header">Evolución de Peso y Altura</div>
    {$svgGrafica}
    
    <div style="font-weight: bold; font-size: 12px; color: #800020; margin-top: 15px; margin-bottom: 8px; text-transform: uppercase; border-bottom: 1px solid #800020; display: inline-block; padding-bottom: 3px;">Historial Antropométrico</div>
    {$antropoTable}
</div>

<!-- ===================== SALTO DE PÁGINA ===================== -->
<br pagebreak="true" />

<!-- ===================== PÁGINA 5: EVALUACIONES FÍSICAS Y RADAR ===================== -->

<div class="section">
    <div class="section-header">Perfil de Rendimiento Físico</div>
    
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 10px;">
        <tr>
            <td width="50%" align="center">
                <div style="text-align: center; font-size: 8px; font-weight: bold; margin-bottom: 4px; color: #800020;">COMPARACIÓN INTERNACIONAL (ÉLITE)</div>
                {$svgRadarInternacional}
            </td>
            <td width="50%" align="center">
                <div style="text-align: center; font-size: 8px; font-weight: bold; margin-bottom: 4px; color: #0EA5E9;">COMPARACIÓN NACIONAL (FUTVE)</div>
                {$svgRadarNacional}
            </td>
        </tr>
    </table>

    {$ultimaPruebaHtml}

    <div style="font-weight: bold; font-size: 12px; color: #800020; margin-top: 15px; margin-bottom: 8px; text-transform: uppercase; border-bottom: 1px solid #800020; display: inline-block; padding-bottom: 3px;">Historial de Evaluaciones Físicas</div>
    {$pruebasTable}
</div>
HTML;
    }

    private function construirHtmlCategoria(array $cat, array $atletas): string
    {
        $esc = fn($v) => htmlspecialchars((string) ($v ?? '—'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $rows = '';
        foreach ($atletas as $index => $a) {
            $edad = $a['fecha_nac'] ? (new DateTime($a['fecha_nac']))->diff(new DateTime('today'))->y : '—';
            $rows .= sprintf(
                '<tr>
                    <td width="5%%" style="color:#999;">%d</td>
                    <td width="30%%" style="text-align:left; font-weight:bold; color:#1a1a1a;">%s</td>
                    <td width="15%%">%s</td>
                    <td width="10%%">%s años</td>
                    <td width="20%%">%s</td>
                    <td width="20%%">%s</td>
                </tr>',
                $index + 1,
                $esc($a['nombre'] . ' ' . $a['apellido']),
                $esc($a['cedula']),
                $esc($edad),
                $esc($a['telefono']),
                $esc($a['representante_telefono'] ?? '—')
            );
        }

        return <<<HTML
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
    table.member-list th { background-color: #f8f9fa; color: #800020; font-size: 8px; font-weight: bold; padding: 8px 4px; text-align: center; border: 1px solid #dee2e6; text-transform: uppercase; }
    table.member-list td { font-size: 8px; padding: 8px 4px; text-align: center; border: 1px solid #f1f1f1; vertical-align: middle; }
    table.member-list tr:nth-child(even) { background-color: #fafafa; }
</style>
<div class="document-header">
    <div class="report-title">Reporte de Categoría Deportiva</div>
</div>
<div class="category-summary">
    <div class="summary-header"><div class="summary-title">{$esc($cat['nombre_categoria'])}</div></div>
    <div class="summary-body">
        <table class="info-table" cellpadding="2">
            <tr>
                <td width="50%">
                    <table width="100%">
                        <tr><td class="info-label" width="40%">Entrenador:</td><td class="info-value" width="60%">{$esc($cat['entrenador'])}</td></tr>
                        <tr><td class="info-label" width="40%">Rango Edad:</td><td class="info-value" width="60%">{$esc($cat['edad_min'])} a {$esc($cat['edad_max'])} años</td></tr>
                    </table>
                </td>
                <td width="50%">
                    <table width="100%">
                        <tr><td class="info-label" width="40%">Género:</td><td class="info-value" width="60%">{$esc($cat['sexo_categoria'] === 'F' ? 'Femenino' : ($cat['sexo_categoria'] === 'M' ? 'Masculino' : 'Mixto'))}</td></tr>
                        <tr><td class="info-label" width="40%">Integrantes:</td><td class="info-value" width="60%">{$esc(count($atletas))} atletas registrados</td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>
<div class="section-label">Listado de Integrantes</div>
<table class="member-list" cellpadding="4">
    <thead>
        <tr>
            <th width="5%">#</th>
            <th width="30%" style="text-align:left;">Atleta (Nombres, Apellidos)</th>
            <th width="15%">Documento</th>
            <th width="10%">Edad</th>
            <th width="20%">Teléfono Atleta</th>
            <th width="20%">Tlf. Representante</th>
        </tr>
    </thead>
    <tbody>
        {$rows}
    </tbody>
</table>
HTML;
    }

    private function construirHtmlLista(array $atletas): string
    {
        $esc = fn($v) => htmlspecialchars((string) ($v ?? '—'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $fechaGeneracion = $esc(date('d/m/Y h:i A'));
        $rows = '';
        foreach ($atletas as $index => $a) {
            $estatusTexto = ((int)$a['estatus'] === 1) ? 'Activo' : (((int)$a['estatus'] === 2) ? 'Lesionado' : 'Suspendido');
            $rows .= sprintf(
                '<tr>' .
                '<td width="5%%" style="color:#999;text-align:center;">%d</td>' .
                '<td width="30%%" style="text-align:left;font-weight:bold;color:#1a1a1a;">%s</td>' .
                '<td width="15%%" style="text-align:center;">%s</td>' .
                '<td width="20%%" style="text-align:center;">%s</td>' .
                '<td width="15%%" style="text-align:center;">%s</td>' .
                '<td width="15%%" style="text-align:center;"><span style="font-weight:bold;color:#800020;">%s</span></td>' .
                '</tr>',
                $index + 1,
                $esc($a['apellido'] . ', ' . $a['nombre']),
                $esc($a['cedula']),
                $esc($a['nombre_categoria']),
                $esc(!empty($a['telefono']) ? $a['telefono'] : '—'),
                $esc($estatusTexto)
            );
        }

        return <<<HTML
<style>
    body { font-family: helvetica, sans-serif; color: #333; }
    .document-header { text-align: center; padding-bottom: 15px; margin-bottom: 25px; border-bottom: 2px solid #800020; }
    .club-brand { font-size: 28px; font-weight: bold; color: #800020; letter-spacing: 3px; }
    .report-title { background-color: #800020; color: #ffffff; padding: 6px 20px; font-size: 14px; display: inline-block; font-weight: bold; text-transform: uppercase; }
    .report-meta { font-size: 10px; color: #777; margin-top: 10px; }
    table.main-list { width: 100%; border-collapse: collapse; margin-top: 20px; }
    table.main-list th { background-color: #f8f9fa; color: #800020; font-size: 9px; font-weight: bold; padding: 10px 5px; text-align: center; border: 1px solid #dee2e6; text-transform: uppercase; }
    table.main-list td { font-size: 9px; padding: 8px 5px; vertical-align: middle; border: 1px solid #dee2e6; }
    table.main-list tr:nth-child(even) { background-color: #fafafa; }
</style>
<div class="document-header">
    <div class="club-brand">CADA</div>
    <div class="report-title">Listado General de Atletas</div>
    <div class="report-meta">Generado el {$fechaGeneracion} · Total: {$esc(count($atletas))}</div>
</div>
<table class="main-list">
    <thead>
        <tr>
            <th width="5%">#</th>
            <th width="30%" style="text-align:left;">Atleta</th>
            <th width="15%">Documento</th>
            <th width="20%">Categoría</th>
            <th width="15%">Teléfono</th>
            <th width="15%">Estatus</th>
        </tr>
    </thead>
    <tbody>{$rows}</tbody>
</table>
HTML;
    }
}

<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Atleta;
use App\Models\MedidaAntropometrica;
use App\Models\ResultadoPrueba;
use App\Models\Asistencia;

final class ReporteService
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

        $antropometria = (new MedidaAntropometrica())->historial($atletaId);
        $pruebas       = (new ResultadoPrueba())->historial($atletaId);
        $asistencia    = (new Asistencia())->resumenAtleta($atletaId);

        $html = $this->construirHtml($atleta, $antropometria, $pruebas, $asistencia);

        $filename = 'ficha_' . preg_replace('/[^a-z0-9]+/i', '_', $atleta['nombre'] . '_' . $atleta['apellido']) . '_' . date('Ymd');
        return (new PdfGenerator())->render(
            'Ficha Técnica - ' . $atleta['nombre'] . ' ' . $atleta['apellido'],
            $html,
            strtolower($filename)
        );
    }

    private function construirHtml(array $a, array $antropo, array $pruebas, array $asistencia): string
    {
        $esc = fn($v) => htmlspecialchars((string) ($v ?? '—'), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $nombreCompleto = $esc($a['nombre'] . ' ' . $a['apellido']);
        $edad = $a['fecha_nacimiento']
            ? (new \DateTime($a['fecha_nacimiento']))->diff(new \DateTime('today'))->y
            : null;

        // Antropometría — tabla + última medición
        $antropoRows = '';
        foreach ($antropo as $m) {
            $antropoRows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                $esc($m['fecha_medicion']),
                $esc($m['peso']),
                $esc($m['altura']),
                $esc($m['envergadura']),
                $esc($m['porcentaje_grasa']),
                $esc($m['porcentaje_musculatura'])
            );
        }
        $antropoTable = $antropoRows
            ? "<table><thead><tr><th>Fecha</th><th>Peso (kg)</th><th>Altura (cm)</th><th>Envergadura</th><th>% Grasa</th><th>% Musc.</th></tr></thead><tbody>$antropoRows</tbody></table>"
            : '<p>Sin mediciones antropométricas registradas.</p>';

        // Pruebas físicas
        $pruebasRows = '';
        foreach ($pruebas as $p) {
            $pruebasRows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                $esc($p['fecha_evento']),
                $esc($p['test_de_fuerza']),
                $esc($p['test_resistencia']),
                $esc($p['test_velocidad']),
                $esc($p['test_coordinacion']),
                $esc($p['test_de_reaccion'])
            );
        }
        $pruebasTable = $pruebasRows
            ? "<table><thead><tr><th>Fecha</th><th>Fuerza</th><th>Resistencia</th><th>Velocidad</th><th>Coordinación</th><th>Reacción</th></tr></thead><tbody>$pruebasRows</tbody></table>"
            : '<p>Sin pruebas físicas registradas.</p>';

        // Asistencia
        $asiMap = ['Presente' => 0, 'Ausente' => 0, 'Justificado' => 0];
        foreach ($asistencia as $row) {
            $asiMap[$row['estatus']] = (int) $row['total'];
        }
        $totalAsi = array_sum($asiMap) ?: 1;
        $pctPresente = round(($asiMap['Presente'] / $totalAsi) * 100, 1);

        $direccionParts = array_filter([
            $a['calle_avenida'] ?? null, $a['casa_edificio'] ?? null,
            $a['parroquia'] ?? null, $a['municipio'] ?? null, $a['estado'] ?? null, $a['pais'] ?? null,
        ]);
        $direccion = $direccionParts ? $esc(implode(', ', $direccionParts)) : '—';

        return <<<HTML
<div class="header">
    <h1 class="header-title">Ficha Técnica Individual</h1>
    <p style="margin:4px 0 0; color: #6B7280;">
        Club Atlético Deportivo Acarigua · "La Armadura de Dios" · Generado el {$esc(date('Y-m-d H:i'))}
    </p>
</div>

<h2>{$nombreCompleto}</h2>
<span class="badge">{$esc($a['estatus'])}</span>

<div class="grid-2" style="margin-top:16px;">
    <div>
        <p><strong>Cédula:</strong> {$esc($a['cedula'])}</p>
        <p><strong>Fecha de nacimiento:</strong> {$esc($a['fecha_nacimiento'])} ({$esc($edad)} años)</p>
        <p><strong>Teléfono:</strong> {$esc($a['telefono'])}</p>
        <p><strong>Categoría:</strong> {$esc($a['nombre_categoria'])}</p>
        <p><strong>Posición:</strong> {$esc($a['nombre_posicion'])}</p>
        <p><strong>Pierna dominante:</strong> {$esc($a['pierna_dominante'])}</p>
    </div>
    <div>
        <p><strong>Dirección:</strong> {$direccion}</p>
        <p><strong>Representante:</strong> {$esc(($a['tutor_nombres'] ?? '') . ' ' . ($a['tutor_apellidos'] ?? ''))}</p>
        <p><strong>Relación:</strong> {$esc($a['tutor_relacion'])}</p>
        <p><strong>Teléfono representante:</strong> {$esc($a['tutor_telefono'])}</p>
        <p><strong>Tipo sanguíneo:</strong> {$esc($a['tipo_sanguineo'])}</p>
        <p><strong>Alergias:</strong> {$esc($a['alergias'])}</p>
    </div>
</div>

<h3>📏 Antropometría</h3>
{$antropoTable}

<h3>⚡ Pruebas físicas</h3>
{$pruebasTable}

<h3>📋 Resumen de asistencia</h3>
<p><strong>Presentes:</strong> {$asiMap['Presente']} · <strong>Ausentes:</strong> {$asiMap['Ausente']} · <strong>Justificados:</strong> {$asiMap['Justificado']}</p>
<p><strong>Porcentaje de asistencia:</strong> {$pctPresente}%</p>

<h3>🏥 Ficha médica</h3>
<p><strong>Lesiones:</strong> {$esc($a['lesion'])}</p>
<p><strong>Condiciones:</strong> {$esc($a['condicion_medica'])}</p>
<p><strong>Observaciones:</strong> {$esc($a['observacion'])}</p>

HTML;
    }
}

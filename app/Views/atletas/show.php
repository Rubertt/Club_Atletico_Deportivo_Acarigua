<?php /** @var array $atleta */ ?>
<div class="page-header">
    <div>
        <h1><?= e($atleta['nombre'] . ' ' . $atleta['apellido']) ?></h1>
        <div class="subtitle">Cédula: <?= e($atleta['cedula'] ?? '—') ?></div>
    </div>
    <div class="flex gap">
        <a href="<?= e(url('/admin/atletas')) ?>" class="btn btn-ghost">← Volver</a>
        <a href="<?= e(url("/admin/reportes/atleta/{$atleta['atleta_id']}")) ?>" class="btn btn-outline" target="_blank">📄 Descargar PDF</a>
        <?php if (can('admin')): ?>
            <a href="<?= e(url("/admin/atletas/{$atleta['atleta_id']}/editar")) ?>" class="btn btn-primary">Editar</a>
        <?php endif; ?>
    </div>
</div>

<div style="display:grid; grid-template-columns:280px 1fr; gap:24px;" class="show-layout">
    <div class="card" style="text-align:center;">
        <?php if (!empty($atleta['foto'])): ?>
            <img src="<?= e(url($atleta['foto'])) ?>" style="width:180px; height:180px; border-radius:50%; object-fit:cover; margin:0 auto 16px;">
        <?php else: ?>
            <div class="avatar-placeholder" style="width:180px; height:180px; font-size:42px; margin:0 auto 16px;">
                <?= e(mb_substr($atleta['nombre'], 0, 1) . mb_substr($atleta['apellido'], 0, 1)) ?>
            </div>
        <?php endif; ?>
        <h2 style="margin:0"><?= e($atleta['nombre'] . ' ' . $atleta['apellido']) ?></h2>
        <?php $badge = match ($atleta['estatus']) {
            'Activo' => 'success', 'Lesionado' => 'warning', 'Suspendido' => 'danger', default => 'primary'
        }; ?>
        <span class="badge badge-<?= $badge ?>" style="margin-top:8px;"><?= e($atleta['estatus']) ?></span>
        <div class="text-muted mt" style="font-size:14px;">
            <?= e($atleta['nombre_categoria'] ?? 'Sin categoría') ?> ·
            <?= e($atleta['nombre_posicion'] ?? 'Sin posición') ?>
        </div>
    </div>

    <div style="display:flex; flex-direction:column; gap:16px;">
        <div class="card">
            <h3 style="margin-top:0;">Datos personales</h3>
            <div class="form-row-3">
                <div><strong>Fecha de nacimiento</strong><div class="text-muted"><?= e($atleta['fecha_nacimiento']) ?></div></div>
                <div><strong>Teléfono</strong><div class="text-muted"><?= e($atleta['telefono'] ?? '—') ?></div></div>
                <div><strong>Pierna dominante</strong><div class="text-muted"><?= e($atleta['pierna_dominante'] ?? '—') ?></div></div>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-top:0;">Dirección</h3>
            <p class="text-muted" style="margin:0;">
                <?php
                $parts = array_filter([
                    $atleta['calle_avenida'] ?? null,
                    $atleta['casa_edificio'] ?? null,
                    $atleta['parroquia'] ?? null,
                    $atleta['municipio'] ?? null,
                    $atleta['estado'] ?? null,
                    $atleta['pais'] ?? null,
                ]);
                echo $parts ? e(implode(', ', $parts)) : '—';
                ?>
            </p>
            <?php if (!empty($atleta['punto_referencia'])): ?>
                <p class="text-muted" style="margin:4px 0 0; font-size:13px;">
                    <em>Ref:</em> <?= e($atleta['punto_referencia']) ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3 style="margin-top:0;">Representante</h3>
            <?php if (!empty($atleta['tutor_nombres'])): ?>
                <div class="form-row">
                    <div><strong><?= e($atleta['tutor_nombres'] . ' ' . $atleta['tutor_apellidos']) ?></strong><br>
                        <span class="text-muted"><?= e($atleta['tutor_relacion']) ?> · C.I. <?= e($atleta['tutor_cedula']) ?></span>
                    </div>
                    <div>
                        <span class="text-muted"><?= e($atleta['tutor_telefono']) ?></span><br>
                        <span class="text-muted"><?= e($atleta['tutor_correo'] ?? '—') ?></span>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-muted">Sin representante registrado.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3 style="margin-top:0;">Ficha médica</h3>
            <div class="form-row">
                <div><strong>Tipo sanguíneo</strong><div class="text-muted"><?= e($atleta['tipo_sanguineo'] ?? '—') ?></div></div>
                <div><strong>Alergias</strong><div class="text-muted"><?= e($atleta['alergias'] ?? '—') ?></div></div>
            </div>
            <?php foreach (['lesion' => 'Lesiones', 'condicion_medica' => 'Condiciones', 'observacion' => 'Observaciones'] as $k => $label): ?>
                <?php if (!empty($atleta[$k])): ?>
                    <p style="margin:8px 0 0;"><strong><?= e($label) ?>:</strong> <span class="text-muted"><?= e($atleta[$k]) ?></span></p>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h3 style="margin-top:0;">Accesos rápidos</h3>
            <div class="flex gap">
                <a href="<?= e(url("/admin/antropometria/atleta/{$atleta['atleta_id']}")) ?>" class="btn btn-outline btn-sm">📏 Antropometría</a>
                <a href="<?= e(url("/admin/pruebas/atleta/{$atleta['atleta_id']}")) ?>" class="btn btn-outline btn-sm">⚡ Pruebas físicas</a>
                <a href="<?= e(url("/admin/ficha-medica/{$atleta['atleta_id']}")) ?>" class="btn btn-outline btn-sm">🏥 Ficha médica</a>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 900px) {
    .show-layout { grid-template-columns: 1fr !important; }
}
</style>

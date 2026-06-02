<?php /** @var array $atleta */ ?>
<style>
    /* Estilos para pestañas con scroll elegante */
    .profile-tabs {
        display: flex;
        flex-wrap: nowrap;
        /* Mantener en una sola línea */
        overflow-x: auto;
        /* Habilitar scroll horizontal */
        width: 100%;
        padding: 0 24px !important;
        background: var(--color-bg-alt);
        border-bottom: 1px solid var(--color-border);
        -webkit-overflow-scrolling: touch;
        /* Desplazamiento suave en móviles */
    }

    @supports not selector(::-webkit-scrollbar) {
        .profile-tabs {
            scrollbar-width: thin;
            scrollbar-color: #BE123C transparent;
        }
    }


    .profile-tabs .tab-btn {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 14px 16px;
        font-size: 14px;
        font-weight: 600;
        white-space: nowrap;
        border-radius: 0;
        /* Se quita la línea de abajo completamente para todos los estados */
        border-bottom: none !important;
        transition: all 0.2s ease;
        color: var(--color-text-muted);
    }

    .profile-tabs .tab-btn.active {
        color: var(--color-primary) !important;
    }

    .profile-tabs .tab-btn i {
        font-size: 18px;
    }
</style>
<div class="page-header">
    <div>
        <h1>Perfil del Atleta</h1>
        <div class="subtitle">Expediente integral y seguimiento deportivo</div>
    </div>
    <div class="flex gap">
        <a href="<?= e(url('/admin/atletas')) ?>" class="btn btn-ghost"><i class="ph ph-arrow-left"></i> Directorio</a>
        <a href="<?= e(url("/admin/reportes/atleta/{$atleta['atleta_id']}")) ?>" class="btn btn-outline"
            target="_blank"><i class="ph ph-file-pdf"></i> Imprimir PDF</a>
    </div>
</div>

<div style="display:grid; grid-template-columns:300px 1fr; gap:24px;" class="show-layout">
    <!-- Panel Izquierdo (Resumen) -->
    <div style="display:flex; flex-direction:column; gap:24px;">
        <div class="card" style="text-align:center; padding-top: 32px; position: relative;">
            <?php if (can('admin')): ?>
                <button type="button" class="btn-icon-premium" id="btn-abrir-editar-basico" title="Editar Datos Básicos">
                    <i class="ph ph-pencil-simple"></i>
                </button>
            <?php endif; ?>
            <div style="position: relative; width: 180px; height: 180px; margin: 0 auto 20px; cursor: pointer; group"
                id="btn-abrir-editar-foto" title="Cambiar Foto">
                <div
                    style="position: absolute; inset: -5px; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary) 0%, #ff4d4d 100%); opacity: 0.15; filter: blur(8px);">
                </div>
                <?php if (!empty($atleta['foto'])): ?>
                    <div style="position: relative; width: 100%; height: 100%; border-radius: 50%; padding: 4px; background: var(--color-bg); border: 2px solid var(--color-border); box-shadow: var(--shadow-lg); transition: transform 0.2s;"
                        class="hover-scale">
                        <img src="<?= e(url($atleta['foto'])) ?>"
                            style="width:100%; height:100%; border-radius:50%; object-fit:cover; display: block;">
                        <div style="position: absolute; inset: 4px; border-radius: 50%; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; color: white; opacity: 0; transition: opacity 0.2s;"
                            class="photo-overlay">
                            <i class="ph ph-camera" style="font-size: 32px;"></i>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="avatar-placeholder"
                        style="width:100%; height:100%; font-size:48px; background: var(--color-primary-light); color: var(--color-primary); border: 4px solid var(--color-bg); box-shadow: var(--shadow-md); position: relative;">
                        <?= e(mb_substr($atleta['nombre'], 0, 1) . mb_substr($atleta['apellido'], 0, 1)) ?>
                        <div style="position: absolute; inset: 0; border-radius: 50%; background: rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; color: var(--color-primary); opacity: 0; transition: opacity 0.2s;"
                            class="photo-overlay">
                            <i class="ph ph-camera" style="font-size: 32px;"></i>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <h2 style="margin:0 0 4px; font-family: var(--font-display);">
                <?= e($atleta['nombre'] . ' ' . $atleta['apellido']) ?></h2>
            <div style="color: var(--color-text-muted); font-size: 14px; margin-bottom: 16px;">C.I: <?= !empty($atleta['cedula_formateada']) ? e($atleta['cedula_formateada']) : 'Sin Cédula' ?></div>

            <?php
            $estatusVal = (int) ($atleta['estatus'] ?? 1);
            $badge = match ($estatusVal) {
                1 => 'success', // Activo
                2 => 'warning', // Lesionado
                0 => 'danger',  // Suspendido
                3 => 'outline', // Inactivo
                default => 'primary'
            };
            $label = match ($estatusVal) {
                1 => 'Activo',
                2 => 'Lesionado',
                0 => 'Suspendido',
                3 => 'Inactivo',
                default => 'Desconocido'
            };
            ?>
            <span class="badge badge-<?= $badge ?>" style="padding: 6px 16px; border-radius: 20px; font-weight: 600;">
                <span
                    style="display:inline-block; width:8px; height:8px; border-radius:50%; background:currentColor; margin-right:6px;"></span>
                <?= e($label) ?>
            </span>

            <hr style="border:none; border-top:1px solid var(--color-border); margin: 24px 0;">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; text-align: left;">
                <div>
                    <div
                        style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600;">
                        Edad</div>
                    <div style="font-weight: 500; display:flex; align-items:center; gap:4px; margin-top:4px;">
                        <i class="ph ph-calendar-blank text-muted"></i>
                        <?php
                        $nac = new DateTime($atleta['fecha_nac'] ?? 'today');
                        $hoy = new DateTime();
                        echo $hoy->diff($nac)->y . ' años';
                        ?>
                    </div>
                </div>
                <div>
                    <div
                        style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600;">
                        Pierna Dominante</div>
                    <div style="font-weight: 500; display:flex; align-items:center; gap:4px; margin-top:4px;">
                        <i class="ph ph-sneaker text-muted"></i>
                        <?= !empty($atleta['pierna_dominante']) ? e(ucfirst($atleta['pierna_dominante'])) : 'Sin definir' ?>
                    </div>
                </div>
                <div>
                    <div
                        style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600;">
                        Género</div>
                    <div style="font-weight: 500; display:flex; align-items:center; gap:4px; margin-top:4px;">
                        <?php
                        $sexoIcon = match($atleta['sexo'] ?? '') {
                            'M' => 'ph-gender-male',
                            'F' => 'ph-gender-female',
                            default => 'ph-gender-neuter'
                        };
                        $sexoLabel = match($atleta['sexo'] ?? '') {
                            'M' => 'Masculino',
                            'F' => 'Femenino',
                            default => 'Sin definir'
                        };
                        ?>
                        <i class="ph <?= $sexoIcon ?> text-muted"></i>
                        <?= $sexoLabel ?>
                    </div>
                </div>
                <div>
                    <div
                        style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600;">
                        Teléfono</div>
                    <div style="font-weight: 500; display:flex; align-items:center; gap:4px; margin-top:4px;">
                        <i class="ph ph-phone text-muted"></i>
                        <?= !empty($atleta['telefono']) ? e($atleta['telefono']) : 'No registrado' ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div
                style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--color-border); padding-bottom: 12px; margin-bottom: 16px;">
                <h3 style="margin:0; font-size: 16px;">Categor&iacute;as</h3>
                <a href="<?= !empty($asignaciones) ? e(url('/admin/categorias/' . $asignaciones[0]['categoria_id'] . '/detalles')) : e(url('/admin/categorias')) ?>" style="color: var(--color-primary); font-weight: 700; text-decoration: none; font-size: 11px; display: inline-flex; align-items: center; gap: 2px; margin-left: 6px;" 
                title="<?= !empty($asignaciones) ? 'Ver detalles de la categoría asignada' : 'Ver todas las categorías' ?>">Ver categoría <i class="ph ph-arrow-right" style="font-size: 10px;"></i>
                </a>
            </div>
            <div style="margin-top: 0; display: flex; flex-direction: column; gap: 16px;">
                <?php if (empty($asignaciones)): ?>
                    <div style="text-align: center; padding: 12px 0; color: var(--color-text-muted); font-size: 13px;">
                        Sin categor&iacute;as asignadas
                    </div>
                <?php else: ?>
                    <?php foreach ($asignaciones as $asig): ?>
                        <div style="padding-bottom: 16px; border-bottom: 1px dashed var(--color-border); margin-bottom: 8px;">
                            
                            <!-- Fila Superior: Nombre de categoría y Estatus -->
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                <div>
                                    <span style="font-size: 11px; color: var(--color-text-muted); font-weight: 600; text-transform: uppercase; display: block; margin-bottom: 2px;">Categoría</span>
                                    <div style="font-weight: 700; color: var(--color-primary); font-size: 16px; white-space: nowrap;">
                                        <?= e($asig['nombre_categoria']) ?>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span style="font-size: 11px; color: var(--color-text-muted); font-weight: 600; text-transform: uppercase; display: block; margin-bottom: 2px;">Estatus</span>
                                    <?php if ((int)($asig['estatus'] ?? 1) === 1): ?>
                                        <span class="badge badge-success" style="font-weight: 600; font-size: 10px; padding: 3px 8px; border-radius: 10px;">Vigente</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger" style="font-weight: 600; font-size: 10px; padding: 3px 8px; border-radius: 10px;">Vencido</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Fila Inferior: Posición Principal, Posición Secundaria y Dorsal en Paralelo -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 16px; align-items: flex-start;">
                                <!-- Posición Principal -->
                                <div>
                                    <div style="font-weight: 700; color: var(--color-primary); font-size: 13px; margin-bottom: 2px;">Posición Principal</div>
                                    <div style="font-size: 12px; color: var(--color-text); font-weight: 500;">
                                        <?= !empty($asig['posicion_principal']) ? e($asig['posicion_principal']) : 'Sin definir' ?>
                                    </div>
                                </div>

                                <!-- Posición Secundaria -->
                                <div>
                                    <div style="font-weight: 700; color: var(--color-primary); font-size: 13px; margin-bottom: 2px;">Posición Secundaria</div>
                                    <div style="font-size: 12px; color: var(--color-text); font-weight: 500;">
                                        <?= !empty($asig['posicion_secundaria']) ? e($asig['posicion_secundaria']) : 'Ninguna' ?>
                                    </div>
                                </div>

                                <!-- Dorsal Asignado -->
                                <div style="text-align: center; display: flex; flex-direction: column; align-items: center; min-width: 80px;">
                                    <span style="font-size: 9px; color: var(--color-text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px;">Dorsal asignado</span>
                                    <span class="dorsal-circle">
                                        <?= $asig['nun_dorsal'] !== null ? (int)$asig['nun_dorsal'] : 'S/D' ?>
                                    </span>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Panel Derecho (Contenido Principal con Tabs) -->
    <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
        <div class="profile-tabs">
            <button class="tab-btn active" data-target="tab-general"><i class="ph ph-user-list"></i> Datos
                Generales</button>
            <button class="tab-btn" data-target="tab-ficha"><i class="ph ph-heartbeat"></i> Ficha Médica</button>
            <button class="tab-btn" data-target="tab-antropometria"><i class="ph ph-ruler"></i> Antropometría</button>
            <button class="tab-btn" data-target="tab-pruebas"><i class="ph ph-chart-line-up"></i> Pruebas
                Físicas</button>
            <button class="tab-btn" data-target="tab-asistencia"><i class="ph ph-calendar-check"></i>
                Asistencia</button>
        </div>

        <div style="padding: 32px; flex: 1;">
            <!-- Tab: General -->
            <?php include __DIR__ . '/partials/perfil/_tab_general.php'; ?>

            <!-- Tab: Ficha Médica -->
            <?php include __DIR__ . '/partials/perfil/_tab_ficha_medica.php'; ?>

            <!-- Tab: Antropometría -->
            <?php include __DIR__ . '/partials/perfil/_tab_antropometria.php'; ?>

            <!-- Tab: Pruebas Físicas -->
            <?php include __DIR__ . '/partials/perfil/_tab_pruebas.php'; ?>

            <!-- Tab: Asistencia -->
            <?php include __DIR__ . '/partials/perfil/_tab_asistencia.php'; ?>
        </div>

        <?php include __DIR__ . '/partials/perfil/_modals.php'; ?>

    </div>
</div>
</div>

<?php include __DIR__ . '/partials/perfil/_styles.php'; ?>

<?php include __DIR__ . '/partials/perfil/_scripts.php'; ?>




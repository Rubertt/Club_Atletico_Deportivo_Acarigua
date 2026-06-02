<?php /** @var array $item @var array $roles */ 
if (!function_exists('formatDocumento')) {
    function formatDocumento($cedula) {
        if (empty($cedula)) {
            return '—';
        }
        $cedula = trim($cedula);
        if (preg_match('/^[VEPvep]-?\d+/', $cedula)) {
            $prefix = strtoupper($cedula[0]);
            $number = ltrim(substr($cedula, 1), '-');
            return $prefix . '-' . $number;
        }
        if (ctype_digit($cedula)) {
            return 'V-' . $cedula;
        }
        return $cedula;
    }
}
?>
<div class="page-header">
    <div>
        <h1>Perfil de Usuario</h1>
        <div class="subtitle">Detalles y datos de contacto</div>
    </div>
    <div class="flex gap">
        <a href="<?= e(url("/admin/reportes/usuario/{$item['usuario_id']}")) ?>" class="btn btn-outline" target="_blank" title="Ver Ficha PDF">
            <i class="ph ph-file-pdf"></i> PDF
        </a>
        <a href="<?= e(url('/admin/usuarios')) ?>" class="btn btn-ghost"><i class="ph ph-arrow-left"></i> Volver</a>
    </div>
</div>

<div style="display:grid; grid-template-columns:300px 1fr; gap:24px;" class="show-layout">
    <!-- Panel Izquierdo (Resumen) -->
    <div style="display:flex; flex-direction:column; gap:24px;">
        <div class="card" style="text-align:center; padding-top: 32px; position: relative;">
            <?php if (can('admin') || auth_user()['usuario_id'] == $item['usuario_id']): ?>
                <button type="button" class="btn-icon-premium" id="btn-abrir-editar-basico" title="Editar Datos Básicos">
                    <i class="ph ph-pencil-simple"></i>
                </button>
            <?php endif; ?>
            <div style="position: relative; width: 180px; height: 180px; margin: 0 auto 20px; cursor: pointer;" id="btn-abrir-editar-foto" title="Cambiar Foto">
                <div style="position: absolute; inset: -5px; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary) 0%, #ff4d4d 100%); opacity: 0.15; filter: blur(8px);"></div>
                <?php if (!empty($item['foto'])): ?>
                    <div style="position: relative; width: 100%; height: 100%; border-radius: 50%; padding: 4px; background: var(--color-bg); border: 2px solid var(--color-border); box-shadow: var(--shadow-lg); transition: transform 0.2s;" class="hover-scale">
                        <img src="<?= e(url($item['foto'])) ?>" style="width:100%; height:100%; border-radius:50%; object-fit:cover; display: block;">
                        <div style="position: absolute; inset: 4px; border-radius: 50%; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; color: white; opacity: 0; transition: opacity 0.2s;" class="photo-overlay">
                            <i class="ph ph-camera" style="font-size: 32px;"></i>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="avatar-placeholder hover-scale" style="width:100%; height:100%; font-size:48px; background: var(--color-primary-light); color: var(--color-primary); border: 4px solid var(--color-bg); box-shadow: var(--shadow-md); position: relative; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: transform 0.2s;">
                        <?= e(mb_substr($item['nombre'], 0, 1) . mb_substr($item['apellido'], 0, 1)) ?>
                        <div style="position: absolute; inset: 0; border-radius: 50%; background: rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; color: var(--color-primary); opacity: 0; transition: opacity 0.2s;" class="photo-overlay">
                            <i class="ph ph-camera" style="font-size: 32px;"></i>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <h2 style="margin:0 0 4px; font-family: var(--font-display);">
                <?= e($item['nombre'] . ' ' . $item['apellido']) ?></h2>
            <div style="color: var(--color-text-muted); font-size: 14px; margin-bottom: 16px;">
                <?= !empty($item['cedula']) ? e(formatDocumento($item['cedula'])) : 'Sin Documento' ?></div>

            <?php
            $estatusVal = $item['estatus'] ?? 'Activo';
            $badge = match ($estatusVal) {
                'Activo' => 'success',
                'Inactivo' => 'danger',
                default => 'primary'
            };
            ?>
            <span class="badge badge-<?= $badge ?>" style="padding: 6px 16px; border-radius: 20px; font-weight: 600;">
                <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:currentColor; margin-right:6px;"></span>
                <?= e($estatusVal) ?>
            </span>

            <hr style="border:none; border-top:1px solid var(--color-border); margin: 24px 0;">

            <div style="display: grid; grid-template-columns: 1fr; gap: 16px; text-align: left;">
                <div>
                    <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600;">Rol / Cargo</div>
                    <div style="font-weight: 500; display:flex; align-items:center; gap:4px; margin-top:4px;">
                        <i class="ph ph-shield-check text-muted"></i>
                        <?= e($item['nombre_rol'] ?? 'Sin asignar') ?>
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600;">Edad</div>
                    <div style="font-weight: 500; display:flex; align-items:center; gap:4px; margin-top:4px;">
                        <i class="ph ph-calendar-blank text-muted"></i>
                        <?php
                        $nac = new DateTime($item['fecha_nac'] ?? 'today');
                        $hoy = new DateTime();
                        echo $hoy->diff($nac)->y . ' años';
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Derecho (Detalles Completos) -->
    <div style="display:flex; flex-direction:column; gap:24px;">
        <!-- Ficha de Contacto (Arriba de la Dirección) -->
        <div class="card" style="padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--color-border); padding-bottom: 12px; margin-bottom: 16px;">
                <h3 style="margin:0; font-size: 16px; font-family: var(--font-display);"><i class="ph ph-phone-call text-primary" style="margin-right: 8px;"></i>Contacto</h3>
            </div>
            <div class="af-grid af-grid--2" style="margin-top: 0;">
                <div style="display:flex; align-items:center; gap: 12px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:var(--color-bg-alt); display:flex; align-items:center; justify-content:center; color:var(--color-primary);">
                        <i class="ph ph-whatsapp-logo" style="font-size:20px;"></i></div>
                    <div>
                        <div style="font-size: 12px; color: var(--color-text-muted);">Teléfono Personal</div>
                        <div style="font-weight: 500;">
                            <?= !empty($item['telefono']) ? e($item['telefono']) : 'No registrado' ?></div>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap: 12px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:var(--color-bg-alt); display:flex; align-items:center; justify-content:center; color:var(--color-primary);">
                        <i class="ph ph-envelope" style="font-size:20px;"></i></div>
                    <div style="word-break: break-all;">
                        <div style="font-size: 12px; color: var(--color-text-muted);">Correo Electrónico</div>
                        <div style="font-weight: 500;">
                            <?= !empty($item['correo']) ? e($item['correo']) : 'No registrado' ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dirección de Residencia (Efecto y Estilos idénticos al perfil del atleta) -->
        <div class="card" style="padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="margin: 0; font-family: var(--font-display);"><i class="ph ph-map-pin-line text-primary" style="margin-right: 8px;"></i>Dirección Detallada</h3>
                <?php if (can('admin') || auth_user()['usuario_id'] == $item['usuario_id']): ?>
                    <button type="button" class="btn btn-outline btn-sm" id="btn-abrir-editar-direccion" style="border-radius: 20px;">
                        <i class="ph ph-pencil-simple"></i> Editar
                    </button>
                <?php endif; ?>
            </div>
            
            <div style="background: var(--color-bg-alt); border: 1px solid var(--color-border); border-radius: var(--radius); padding: 24px; box-shadow: var(--shadow-sm); transition: transform 0.2s, box-shadow 0.2s;" class="hover-card">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px;">
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(var(--color-primary-rgb, 59, 130, 246), 0.1); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                            <i class="ph ph-map-trifold"></i>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 2px;">Estado</div>
                            <div style="font-weight: 600; font-size: 15px; color: var(--color-text);"><?= e($item['estado_nombre'] ?? '—') ?></div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(var(--color-primary-rgb, 59, 130, 246), 0.1); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                            <i class="ph ph-buildings"></i>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 2px;">Municipio</div>
                            <div style="font-weight: 600; font-size: 15px; color: var(--color-text);"><?= e($item['municipio_nombre'] ?? '—') ?></div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(var(--color-primary-rgb, 59, 130, 246), 0.1); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                            <i class="ph ph-map-pin"></i>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 2px;">Parroquia</div>
                            <div style="font-weight: 600; font-size: 15px; color: var(--color-text);"><?= e($item['parroquia_nombre'] ?? '—') ?></div>
                        </div>
                    </div>
                </div>

                <hr style="border:none; border-top:1px dashed var(--color-border); margin: 24px 0;">

                <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 20px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(var(--color-primary-rgb, 59, 130, 246), 0.1); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                        <i class="ph ph-house-line"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Localidad / Sector</div>
                        <div style="font-weight: 500; font-size: 15px; color: var(--color-text); line-height: 1.5; background: var(--color-bg); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--color-border);">
                            <?= !empty($item['localidad']) ? e($item['localidad']) : '<span style="color:var(--color-text-muted); font-style:italic;">No especificada</span>' ?>
                        </div>
                    </div>
                </div>

                <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center;">
                    <div style="padding: 8px 16px; background: var(--color-bg); border: 1px solid var(--color-border); border-radius: 20px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; color: var(--color-text);">
                        <i class="ph ph-house text-primary" style="font-size: 16px;"></i> Vivienda:
                        <?= e(ucfirst($item['tipo_vivienda'] ?? '—')) ?>
                    </div>
                    <div style="padding: 8px 16px; background: var(--color-bg); border: 1px solid var(--color-border); border-radius: 20px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; color: var(--color-text);">
                        <i class="ph ph-info text-primary" style="font-size: 16px;"></i> Ubicación Exacta:
                        <?= e($item['ubicacion_vivienda'] ?? '—') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edición Básica -->
<div class="modal-overlay" id="modal-editar-basico" style="display:none;">
    <form id="form-editar-basico" action="<?= e(url("/admin/usuarios/{$item['usuario_id']}/update-basico")) ?>" method="POST" class="modal-container" style="max-width: 600px;" novalidate>
        <?= csrf_field() ?>
        <div class="modal-header">
            <h3 class="modal-title"><i class="ph ph-pencil-simple"></i> Editar Datos Básicos</h3>
            <button type="button" class="modal-close" data-close-modal>&times;</button>
        </div>
        <div class="modal-body">
            <div class="af-grid af-grid--2" style="margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label" data-tooltip="Nombres del usuario. Solo letras (mínimo 3, máximo 30 caracteres)." data-tooltip-pos="top"><span class="required">*</span> Nombres</label>
                    <input type="text" name="nombre" class="form-control" value="<?= e($item['nombre']) ?>" required minlength="3" maxlength="30">
                </div>
                <div class="form-group">
                    <label class="form-label" data-tooltip="Apellidos del usuario. Solo letras (mínimo 3, máximo 30 caracteres)." data-tooltip-pos="top"><span class="required">*</span> Apellidos</label>
                    <input type="text" name="apellido" class="form-control" value="<?= e($item['apellido']) ?>" required minlength="3" maxlength="30">
                </div>
            </div>
            <div class="af-grid af-grid--2" style="margin-bottom: 16px;">
                <?php
                    $telVal   = $item['telefono'] ?? '';
                    $telPref  = '';
                    $telNum   = '';
                    foreach (['0412','0414','0416','0422','0424','0426','0255','0256'] as $_p) {
                        if (str_starts_with($telVal, $_p)) { $telPref = $_p; $telNum = substr($telVal, 4); break; }
                    }
                    if (empty($telPref) && !empty($telVal)) {
                        $telPref = '0414';
                        $telNum = $telVal;
                    }
                ?>
                <div class="form-group">
                    <label class="form-label" id="label-telefono" data-tooltip="Número telefónico móvil de contacto (7 dígitos sin incluir el prefijo)." data-tooltip-pos="top"><span class="required">*</span> Teléfono</label>
                    <div class="phone-field" id="phone-wrap-telefono">
                        <select class="phone-prefix" id="telefono_prefix" aria-label="Prefijo">
                            <option value="0412" <?= $telPref==='0412'?'selected':'' ?>>0412</option>
                            <option value="0414" <?= $telPref==='0414'?'selected':'' ?>>0414</option>
                            <option value="0416" <?= $telPref==='0416'?'selected':'' ?>>0416</option>
                            <option value="0422" <?= $telPref==='0422'?'selected':'' ?>>0422</option>
                            <option value="0424" <?= $telPref==='0424'?'selected':'' ?>>0424</option>
                            <option value="0426" <?= $telPref==='0426'?'selected':'' ?>>0426</option>
                            <option value="0255" <?= $telPref==='0255'?'selected':'' ?>>0255</option>
                            <option value="0256" <?= $telPref==='0256'?'selected':'' ?>>0256</option>
                        </select>
                        <span class="phone-sep">-</span>
                        <input type="text" class="phone-number" id="telefono_number" maxlength="7" placeholder="1234567" value="<?= e($telNum) ?>" required>
                        <input type="hidden" name="telefono" id="telefono" value="<?= e($telVal) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" data-tooltip="Dirección de correo electrónico válida que servirá para acceder al sistema." data-tooltip-pos="top"><span class="required">*</span> Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control" value="<?= e($item['correo']) ?>" required maxlength="50">
                </div>
            </div>
            <div class="af-grid af-grid--2" style="margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label" data-tooltip="Rol institucional asignado para definir los permisos del usuario." data-tooltip-pos="top"><span class="required">*</span> Rol / Cargo</label>
                    <select name="rol_id" class="form-control" required>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['rol_id'] ?>" <?= $item['rol_id'] == $r['rol_id'] ? 'selected' : '' ?>><?= e($r['nombre_rol']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" data-tooltip="Estado de la cuenta. Inactivo suspende el acceso al sistema." data-tooltip-pos="top"><span class="required">*</span> Estatus</label>
                    <select name="estatus" class="form-control" required>
                        <option value="Activo" <?= $item['estatus'] == 'Activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= $item['estatus'] == 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="padding: 16px 24px; background: var(--color-bg-alt); display: flex; gap: 12px; justify-content: flex-end; border-top: 1px solid var(--color-border);">
            <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Guardar Cambios</button>
            <button type="button" class="btn-icon-premium js-btn-help-usuario" title="¿Cómo llenar este formulario?" style="width: 38px; height: 38px;">
                <i class="ph ph-question"></i>
            </button>
        </div>
    </form>
</div>

<!-- Modal Editar Foto -->
<div class="modal-overlay" id="modal-editar-foto" style="display:none;">
    <form id="form-editar-foto" action="<?= e(url("/admin/usuarios/{$item['usuario_id']}/foto")) ?>" method="POST" class="modal-container" style="max-width: 400px;" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>
        <div class="modal-header">
            <h3 class="modal-title"><i class="ph ph-camera"></i> Actualizar Foto</h3>
            <button type="button" class="modal-close" data-close-modal>&times;</button>
        </div>
        <div class="modal-body" style="text-align: center;">
            <div style="width: 150px; height: 150px; border-radius: 50%; overflow: hidden; margin: 0 auto 20px; border: 3px solid var(--color-primary-light);">
                <?php if (!empty($item['foto'])): ?>
                    <img src="<?= e(url($item['foto'])) ?>" style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>
                    <div style="width:100%; height:100%; background:var(--color-bg-alt); display:flex; align-items:center; justify-content:center; color:var(--color-text-muted);">
                        <i class="ph ph-user" style="font-size: 64px;"></i>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <div class="upload-zone" id="upload-zone-foto">
                    <input type="file" name="foto" id="input-foto-file" accept="image/*" style="display:none;">
                    <div class="upload-content">
                        <i class="ph ph-cloud-arrow-up"></i>
                        <p>Seleccionar nueva imagen</p>
                        <span id="foto-filename">Formatos: JPG, PNG</span>
                    </div>
                </div>
            </div>

            <?php if (!empty($item['foto'])): ?>
                <div style="margin-bottom: 16px; text-align: left;">
                    <label style="display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="eliminar_foto" value="1">
                        <span style="font-size: 14px; color: var(--color-danger);">Eliminar foto actual</span>
                    </label>
                </div>
            <?php endif; ?>
        </div>
        <div class="modal-footer" style="padding: 16px 24px; background: var(--color-bg-alt); display: flex; gap: 12px; justify-content: flex-end; border-top: 1px solid var(--color-border);">
            <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="ph ph-upload"></i> Subir Foto</button>
        </div>
    </form>
</div>

<!-- Modal: Editar Dirección Detallada (Efecto e Inputs idénticos al del atleta) -->
<div id="modal-editar-direccion" class="modal-overlay" style="display:none;">
    <form id="form-editar-direccion" action="<?= e(url("/admin/usuarios/{$item['usuario_id']}/direccion")) ?>" method="POST" class="modal-container" style="max-width: 600px;" novalidate>
        <?= csrf_field() ?>
        <div class="modal-header">
            <h3 class="modal-title"><i class="ph ph-map-pin"></i> Editar Dirección Detallada</h3>
            <button type="button" class="modal-close" data-close-modal>&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="select-pais" value="1">
            <div style="margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label" data-tooltip="Estado de residencia en el territorio nacional." data-tooltip-pos="top"><span class="required">*</span> Estado</label>
                    <select id="select-estado" class="form-control" required>
                        <option value="">— Seleccionar —</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label" data-tooltip="Municipio correspondiente al estado de residencia." data-tooltip-pos="top"><span class="required">*</span> Municipio</label>
                    <select id="select-municipio" class="form-control" required>
                        <option value="">— Seleccionar —</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" data-tooltip="Parroquia correspondiente al municipio de residencia." data-tooltip-pos="top"><span class="required">*</span> Parroquia</label>
                    <select name="parroquia_id" id="select-parroquia" class="form-control" required>
                        <option value="">— Seleccionar —</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label" data-tooltip="Urbanización, barrio, caserío o sector de residencia." data-tooltip-pos="top"><span class="required">*</span> Localidad / Sector</label>
                    <input type="text" name="localidad" class="form-control" value="<?= e($item['localidad'] ?? '') ?>" required placeholder="Ej: Urb. La Goajira">
                </div>
                <div class="form-group">
                    <label class="form-label" data-tooltip="Clasificación del tipo de inmueble donde reside el usuario." data-tooltip-pos="top"><span class="required">*</span> Tipo de Vivienda</label>
                    <select name="tipo_vivienda" class="form-control" required>
                        <option value="">— Seleccionar —</option>
                        <option value="casa" <?= ($item['tipo_vivienda'] ?? '') === 'casa' ? 'selected' : '' ?>>Casa</option>
                        <option value="apto" <?= ($item['tipo_vivienda'] ?? '') === 'apto' ? 'selected' : '' ?>>Apartamento</option>
                        <option value="edificio" <?= ($item['tipo_vivienda'] ?? '') === 'edificio' ? 'selected' : '' ?>>Edificio</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" data-tooltip="Dirección exacta detallada, incluyendo número de casa/apartamento, calle y puntos de referencia." data-tooltip-pos="top"><span class="required">*</span> Ubicación Específica (Calle, Nro...)</label>
                <textarea name="ubicacion_vivienda" class="form-control" rows="2" required placeholder="Ej: Calle 3, Vereda 5, Casa 12"><?= e($item['ubicacion_vivienda'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="modal-footer" style="padding: 16px 24px; background: var(--color-bg-alt); display: flex; gap: 12px; justify-content: flex-end; border-top: 1px solid var(--color-border);">
            <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> Guardar</button>
            <button type="button" class="btn-icon-premium js-btn-help-usuario" title="¿Cómo llenar este formulario?" style="width: 38px; height: 38px;">
                <i class="ph ph-question"></i>
            </button>
        </div>
    </form>
</div>

<style>
.view-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.view-label {
    font-size: 12px;
    color: var(--color-text-muted);
    font-weight: 600;
    text-transform: uppercase;
}
.view-value {
    font-size: 15px;
    font-weight: 500;
    color: var(--color-text);
}

.btn-icon-premium {
    background: var(--color-bg-alt);
    border: 1px solid var(--color-border);
    color: var(--color-text-muted);
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-icon-premium:hover {
    background: var(--color-primary-light);
    color: var(--color-primary);
    border-color: var(--color-primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
}
#btn-abrir-editar-basico { position: absolute; top: 12px; right: 12px; z-index: 10; }

#btn-abrir-editar-foto:hover .photo-overlay { opacity: 1 !important; }
#btn-abrir-editar-foto:hover .hover-scale { transform: scale(1.02); }

.upload-zone {
    border: 2px dashed var(--color-border);
    border-radius: 12px;
    padding: 24px 16px;
    cursor: pointer;
    transition: all 0.2s;
    background: var(--color-bg-alt);
    position: relative;
    text-align: center;
}
.upload-zone:hover {
    border-color: var(--color-primary);
    background: var(--color-primary-light);
}
.upload-content i { font-size: 32px; color: var(--color-primary); margin-bottom: 8px; display: block; }
.upload-content p { font-weight: 600; margin: 0; color: var(--color-text); }
.upload-content span { font-size: 12px; color: var(--color-text-muted); }

.required {
    color: var(--color-danger, #e53e3e) !important;
    margin-right: 4px;
    font-weight: bold;
}

/* Teléfono Widget */
.phone-field {
    display: flex;
    align-items: stretch;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm, 6px);
    overflow: hidden;
    background: var(--color-surface, #fff);
    transition: all 0.2s;
    height: 44px;
}
.phone-field:focus-within {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 4px rgba(190, 18, 60, 0.08);
}
.phone-field .phone-prefix {
    border: none;
    background: var(--color-surface-2);
    font-weight: 700;
    font-size: 13px;
    padding: 0 12px;
    cursor: pointer;
    border-right: 1px solid var(--color-border);
    color: var(--color-text);
    outline: none;
    height: 100%;
}
.phone-field .phone-prefix option {
    background-color: var(--color-surface, #1e293b) !important;
    color: var(--color-text, #f8fafc) !important;
}
.phone-field .phone-number {
    flex: 1;
    border: none;
    background: transparent;
    padding: 0 12px;
    font-size: 14px;
    outline: none;
    color: var(--color-text);
    height: 100%;
}
.phone-field .phone-sep {
    display: flex;
    align-items: center;
    color: var(--color-text-muted);
    padding: 0 4px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Botones de ayuda
    document.querySelectorAll('.js-btn-help-usuario').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (typeof FormValidator !== 'undefined' && FormValidator.showHelp) {
                FormValidator.showHelp(
                    'Guía: Perfil de Usuario',
                    '<?= e(asset("img/ayuda/formulario_usuario.png")) ?>'
                );
            }
        });
    });

    // (Las validaciones y envíos de estos formularios se gestionan directamente abajo en formsEdit)

    // Inicializar widget de teléfono
    setupPhoneWidget('telefono_prefix', 'telefono_number', 'telefono');

    // 2. Modales Registro / Lista
    const formsEdit = [
        { id: 'basico', modal: 'modal-editar-basico', form: 'form-editar-basico', error: 'error-basico', custom: validarBasicoCustom },
        { id: 'direccion', modal: 'modal-editar-direccion', form: 'form-editar-direccion', error: 'error-direccion', custom: null },
        { id: 'foto', modal: 'modal-editar-foto', form: 'form-editar-foto', error: 'error-foto', custom: null }
    ];

    formsEdit.forEach(item => {
        const modal = document.getElementById(item.modal);
        const form = document.getElementById(item.form);
        const btnAbrir = document.getElementById(`btn-abrir-editar-${item.id}`);

        btnAbrir?.addEventListener('click', () => {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });

        form?.addEventListener('focusin', (e) => {
            if (e.target.matches('input, select, textarea')) {
                FormValidator.clearMark(e.target);
            }
        });

        // Close modals inside form footer / cancel buttons
        form?.querySelectorAll('[data-close-modal]').forEach(btn => {
            btn.addEventListener('click', () => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            });
        });

        form?.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Ejecutar validación de FormValidator estándar
            const validation = FormValidator.validate(form, item.custom || null);
            if (!validation.valid) {
                FormValidator.showErrors(validation.errors);
                if (validation.elements.length > 0) {
                    const first = validation.elements[0];
                    const wrap = first.closest('.phone-field') || first;
                    wrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Guardando...';

            // Validación para foto de perfil
            if (item.form === 'form-editar-foto') {
                const fileInput = form.querySelector('#input-foto-file');
                const eliminarCheckbox = form.querySelector('input[name="eliminar_foto"]');
                const hasSelectedFile = fileInput && fileInput.files.length > 0;
                const isEliminarChecked = eliminarCheckbox && eliminarCheckbox.checked;

                if (!hasSelectedFile && !isEliminarChecked) {
                    CadaModal.alert({
                        title: 'Atención',
                        text: 'Por favor, seleccione una imagen para subir o marque la opción de eliminar la foto actual.',
                        type: 'warning'
                    });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    return;
                }
            }

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });

                const result = await response.json();

                if (result.success) {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';

                    if (typeof CadaToast !== 'undefined') {
                        CadaToast.success(result.message || 'Cambios guardados correctamente.', () => {
                            window.location.reload();
                        });
                    } else {
                        window.location.reload();
                    }
                } else {
                    if (result.errors) {
                        const errorsList = [];
                        Object.entries(result.errors).forEach(([field, msgs]) => {
                            const input = form.querySelector(`[name="${field}"]`) || document.getElementById(field);
                            if (input) {
                                FormValidator.markError(input);
                                input.addEventListener('focus', function clearOnFocus() {
                                    FormValidator.clearMark(input);
                                    input.removeEventListener('focus', clearOnFocus);
                                });
                            }
                            if (Array.isArray(msgs)) {
                                msgs.forEach(m => errorsList.push(m));
                            } else {
                                errorsList.push(msgs);
                            }
                        });
                        CadaModal.alert({
                            title: 'Campos Incompletos',
                            text: `Por favor revisa lo siguiente:<br><br>${errorsList.map(e => `• ${e}`).join('<br>')}`,
                            type: 'warning',
                            confirmText: 'Corregir ahora'
                        });
                    } else {
                        CadaModal.alert({
                            title: 'Error',
                            text: result.message || 'Ocurrió un error al guardar los cambios.',
                            type: 'danger'
                        });
                    }
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            } catch (error) {
                CadaModal.alert({
                    title: 'Error de Conexión',
                    text: 'No se pudo conectar con el servidor. Inténtalo de nuevo.',
                    type: 'danger'
                });
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    });

    // Close modals on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if(e.target === overlay) {
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    });

    // Modal foto drag/drop support
    const uploadZone = document.getElementById('upload-zone-foto');
    const fileInput = document.getElementById('input-foto-file');
    const filenameDisplay = document.getElementById('foto-filename');

    if(uploadZone) {
        uploadZone.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                filenameDisplay.textContent = e.target.files[0].name;
                filenameDisplay.style.color = 'var(--color-primary)';
                filenameDisplay.style.fontWeight = '600';
            }
        });
    }

    // 3. Lógica de Direcciones Dinámicas (Estado -> Municipio -> Parroquia)
    const selectPais = document.getElementById('select-pais');
    const selectEstado = document.getElementById('select-estado');
    const selectMunicipio = document.getElementById('select-municipio');
    const selectParroquia = document.getElementById('select-parroquia');

    const baseUrl = "<?= e(url('/api/direcciones')) ?>";

    async function cargarEstados(paisId, selectedId = null) {
        if (!paisId) return;
        try {
            const res = await fetch(`${baseUrl}/estados/${paisId}`);
            const estados = await res.json();
            selectEstado.innerHTML = '<option value="">— Seleccionar —</option>';
            estados.forEach(e => {
                const opt = document.createElement('option');
                opt.value = e.estado_id;
                opt.textContent = e.estado;
                if (selectedId && e.estado_id == selectedId) opt.selected = true;
                selectEstado.appendChild(opt);
            });
            if (selectedId) cargarMunicipios(selectedId, <?= (int) ($item['municipio_id'] ?? 0) ?>);
        } catch (err) { console.error(err); }
    }

    async function cargarMunicipios(estadoId, selectedId = null) {
        if (!estadoId) return;
        try {
            const res = await fetch(`${baseUrl}/municipios/${estadoId}`);
            const municipios = await res.json();
            selectMunicipio.innerHTML = '<option value="">— Seleccionar —</option>';
            municipios.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m.municipio_id;
                opt.textContent = m.municipio;
                if (selectedId && m.municipio_id == selectedId) opt.selected = true;
                selectMunicipio.appendChild(opt);
            });
            if (selectedId) cargarParroquias(selectedId, <?= (int) ($item['parroquias_id'] ?? 0) ?>);
        } catch (err) { console.error(err); }
    }

    async function cargarParroquias(municipioId, selectedId = null) {
        if (!municipioId) return;
        try {
            const res = await fetch(`${baseUrl}/parroquias/${municipioId}`);
            const parroquias = await res.json();
            selectParroquia.innerHTML = '<option value="">— Seleccionar —</option>';
            parroquias.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.parroquia_id;
                opt.textContent = p.parroquia;
                if (selectedId && p.parroquia_id == selectedId) opt.selected = true;
                selectParroquia.appendChild(opt);
            });
        } catch (err) { console.error(err); }
    }

    selectEstado?.addEventListener('change', (e) => cargarMunicipios(e.target.value));
    selectMunicipio?.addEventListener('change', (e) => cargarParroquias(e.target.value));

    // Carga inicial de dirección si existe
    if (selectEstado && <?= (int) ($item['estado_id'] ?? 0) ?> > 0) {
        cargarEstados(selectPais.value, <?= (int) ($item['estado_id'] ?? 0) ?>);
    } else if (selectEstado) {
        cargarEstados(selectPais.value);
    }
});

function setupPhoneWidget(prefixId, numberId, hiddenId) {
    const prefixEl = document.getElementById(prefixId);
    const numberEl = document.getElementById(numberId);
    const hiddenEl = document.getElementById(hiddenId);
    if (!prefixEl || !numberEl || !hiddenEl) return;

    function sync() {
        const num = numberEl.value.replace(/[^\d]/g, '').substring(0, 7);
        numberEl.value = num;
        hiddenEl.value = num.length ? prefixEl.value + num : '';
    }
    sync();

    numberEl.addEventListener('input', sync);
    prefixEl.addEventListener('change', () => { sync(); numberEl.focus(); });
}

function validarBasicoCustom(form) {
    const errors = [];
    const telefonoInput = form.querySelector('[name="telefono"]');
    const telefonoNumInput = form.querySelector('#telefono_number');
    const phoneWrap = form.querySelector('#phone-wrap-telefono');

    if (!telefonoInput || !telefonoNumInput) return errors;

    const telefonoVal = telefonoInput.value;
    const telefonoNum = telefonoNumInput.value;

    // La obligatoriedad ya es validada por el atributo 'required' en el input visible,
    // de modo que aquí solo validamos la longitud de 7 dígitos si se ingresa un valor.
    if (telefonoVal && telefonoNum.length !== 7) {
        errors.push({
            element: phoneWrap || telefonoNumInput,
            message: 'El Teléfono debe tener exactamente 7 dígitos'
        });
    }
    return errors;
}
</script>

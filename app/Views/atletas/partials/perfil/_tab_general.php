<!-- Tab: General -->
<div id="tab-general" class="tab-content active">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 style="margin: 0; font-family: var(--font-display);"><i class="ph ph-map-pin-line text-primary"
                style="margin-right: 8px;"></i>Dirección Detallada</h3>
        <?php if (can('admin')): ?>
            <button type="button" class="btn btn-outline btn-sm" id="btn-abrir-editar-direccion"
                style="border-radius: 20px;">
                <i class="ph ph-pencil-simple"></i> Editar
            </button>
        <?php endif; ?>
    </div>

    <div style="background: var(--color-bg-alt); border: 1px solid var(--color-border); border-radius: var(--radius); padding: 24px; margin-bottom: 32px; box-shadow: var(--shadow-sm); transition: transform 0.2s, box-shadow 0.2s;"
        class="hover-card">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px;">
            <div style="display: flex; align-items: flex-start; gap: 12px;">
                <div
                    style="width: 40px; height: 40px; border-radius: 10px; background: rgba(var(--color-primary-rgb, 59, 130, 246), 0.1); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    <i class="ph ph-map-trifold"></i>
                </div>
                <div>
                    <div
                        style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 2px;">
                        Estado</div>
                    <div style="font-weight: 600; font-size: 15px; color: var(--color-text);">
                        <?= e($atleta['estado'] ?? '—') ?></div>
                </div>
            </div>

            <div style="display: flex; align-items: flex-start; gap: 12px;">
                <div
                    style="width: 40px; height: 40px; border-radius: 10px; background: rgba(var(--color-primary-rgb, 59, 130, 246), 0.1); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    <i class="ph ph-buildings"></i>
                </div>
                <div>
                    <div
                        style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 2px;">
                        Municipio</div>
                    <div style="font-weight: 600; font-size: 15px; color: var(--color-text);">
                        <?= e($atleta['municipio'] ?? '—') ?></div>
                </div>
            </div>

            <div style="display: flex; align-items: flex-start; gap: 12px;">
                <div
                    style="width: 40px; height: 40px; border-radius: 10px; background: rgba(var(--color-primary-rgb, 59, 130, 246), 0.1); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    <i class="ph ph-map-pin"></i>
                </div>
                <div>
                    <div
                        style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 2px;">
                        Parroquia</div>
                    <div style="font-weight: 600; font-size: 15px; color: var(--color-text);">
                        <?= e($atleta['parroquia'] ?? '—') ?></div>
                </div>
            </div>
        </div>

        <hr style="border:none; border-top:1px dashed var(--color-border); margin: 24px 0;">

        <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 20px;">
            <div
                style="width: 40px; height: 40px; border-radius: 10px; background: rgba(var(--color-primary-rgb, 59, 130, 246), 0.1); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                <i class="ph ph-house-line"></i>
            </div>
            <div style="flex: 1;">
                <div
                    style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">
                    Localidad / Dirección Exacta</div>
                <div
                    style="font-weight: 500; font-size: 15px; color: var(--color-text); line-height: 1.5; background: var(--color-bg); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--color-border);">
                    <?= !empty($atleta['localidad']) ? e($atleta['localidad']) : '<span style="color:var(--color-text-muted); font-style:italic;">No especificada</span>' ?>
                </div>
            </div>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center;">
            <div
                style="padding: 8px 16px; background: var(--color-bg); border: 1px solid var(--color-border); border-radius: 20px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; color: var(--color-text);">
                <i class="ph ph-house text-primary" style="font-size: 16px;"></i> Vivienda:
                <?= e(ucfirst($atleta['tipo_vivienda'] ?? 'N/A')) ?>
            </div>
            <div
                style="padding: 8px 16px; background: var(--color-bg); border: 1px solid var(--color-border); border-radius: 20px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; color: var(--color-text);">
                <i class="ph ph-info text-primary" style="font-size: 16px;"></i> Ubicación:
                <?= e($atleta['ubicacion_vivienda'] ?? 'N/A') ?>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 style="margin: 0; font-family: var(--font-display);"><i class="ph ph-users text-primary"
                style="margin-right: 8px;"></i>Información del Representante Legal</h3>
        <?php if (can('admin')): ?>
            <button type="button" class="btn btn-outline btn-sm" id="btn-abrir-editar-representante"
                style="border-radius: 20px; <?= empty($atleta['tutor_nombres'] ?? $atleta['rep_nombre']) ? 'display: none;' : '' ?>">
                <i class="ph ph-pencil-simple"></i> Editar
            </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($atleta['tutor_nombres'] ?? $atleta['rep_nombre'])): ?>
        <div style="background: linear-gradient(to right, var(--color-bg-alt), var(--color-bg)); border: 1px solid var(--color-border); border-radius: var(--radius); padding: 24px; box-shadow: var(--shadow-sm); transition: transform 0.2s, box-shadow 0.2s;"
            class="hover-card">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px; flex-wrap: wrap;">
                <div
                    style="width: 56px; height: 56px; border-radius: 50%; background: rgba(var(--color-primary-rgb, 59, 130, 246), 0.1); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 28px; border: 2px solid rgba(var(--color-primary-rgb, 59, 130, 246), 0.2);">
                    <i class="ph ph-user"></i>
                </div>
                <div>
                    <div
                        style="font-size: 13px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">
                        Nombre Completo</div>
                    <div
                        style="font-weight: 700; font-size: 20px; color: var(--color-text); font-family: var(--font-display);">
                        <?= e($atleta['rep_nombre'] ?? ($atleta['tutor_nombres'] . ' ' . $atleta['tutor_apellidos'])) ?>
                    </div>
                </div>
                <div style="margin-left: auto;">
                    <div
                        style="font-weight: 600; display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; background: rgba(var(--color-primary-rgb, 59, 130, 246), 0.1); color: var(--color-primary); border-radius: 20px; font-size: 14px; border: 1px solid rgba(var(--color-primary-rgb, 59, 130, 246), 0.2);">
                        <i class="ph ph-users-three"></i>
                        <?= e(ucfirst($atleta['rep_relacion'] ?? $atleta['tutor_relacion'] ?? 'No definido')) ?>
                    </div>
                </div>
            </div>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; background: var(--color-bg); padding: 20px; border-radius: 12px; border: 1px solid var(--color-border);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div
                        style="width: 36px; height: 36px; border-radius: 8px; background: var(--color-bg-alt); display: flex; align-items: center; justify-content: center; color: var(--color-text-muted); font-size: 18px;">
                        <i class="ph ph-identification-card"></i>
                    </div>
                    <div>
                        <div
                            style="font-size: 12px; color: var(--color-text-muted); font-weight: 600; margin-bottom: 2px;">
                            Cédula de Identidad</div>
                        <div style="font-weight: 600; font-size: 15px; color: var(--color-text);">
                            <?= e($atleta['tutor_cedula_formateada'] ?? '—') ?></div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 12px;">
                    <div
                        style="width: 36px; height: 36px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; color: #10B981; font-size: 18px;">
                        <i class="ph ph-whatsapp-logo"></i>
                    </div>
                    <div>
                        <div
                            style="font-size: 12px; color: var(--color-text-muted); font-weight: 600; margin-bottom: 2px;">
                            Teléfono de Contacto</div>
                        <div style="font-weight: 600; font-size: 15px; color: var(--color-text);">
                            <?= e($atleta['rep_telefono'] ?? $atleta['tutor_telefono'] ?? '—') ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div style="background: var(--color-bg-alt); border: 1px dashed var(--color-border); border-radius: var(--radius); padding: 40px 24px; text-align: center; transition: background 0.2s;"
            class="hover-bg-alt-light">
            <div
                style="width: 64px; height: 64px; border-radius: 50%; background: var(--color-bg); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; box-shadow: var(--shadow-sm);">
                <i class="ph ph-user-circle-minus text-muted" style="font-size: 32px; opacity: 0.7;"></i>
            </div>
            <h4 style="margin: 0 0 8px; font-weight: 600; color: var(--color-text);">Sin Representante
                Registrado</h4>
            <p class="text-muted"
                style="margin: 0 0 20px; font-size: 14px; max-width: 400px; margin-left: auto; margin-right: auto;">
                Este atleta no tiene un representante legal asignado en el sistema actualmente.</p>
            <?php if (can('admin')): ?>
                <button type="button" class="btn btn-outline btn-sm"
                    onclick="document.getElementById('btn-abrir-editar-representante').click();"
                    style="border-radius: 20px;">
                    <i class="ph ph-plus"></i> Asignar Representante
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

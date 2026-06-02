<?php /** @var array $pag @var array $categorias @var array $filters */ ?>
<div class="page-header">
    <div>
        <h1>Directorio de Atletas</h1>
        <div class="subtitle">Gestión y control del plantel deportivo</div>
    </div>
    <?php if (can('admin')): ?>
        <a href="<?= e(url('/admin/atletas/crear')) ?>" class="btn btn-primary">
            <i class="ph ph-user-plus"></i> Nuevo Atleta
        </a>
    <?php endif; ?>
</div>

<!-- Tarjetas de Estadísticas (Mock/Dummy Data for UI) -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number" style="color: var(--color-primary);"><?= (int) ($pag['total'] ?? 0) ?></div>
        <div class="stat-label">Total Registrados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #10B981;"><?= (int) ($stats['activo'] ?? 0) ?></div>
        <div class="stat-label">Atletas Activos</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #F59E0B;"><?= (int) ($stats['lesionado'] ?? 0) ?></div>
        <div class="stat-label">Lesionados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #EF4444;"><?= (int) ($stats['suspendido'] ?? 0) ?></div>
        <div class="stat-label">Suspendidos</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #9CA3AF;"><?= (int) ($stats['inactivo'] ?? 0) ?></div>
        <div class="stat-label">Inactivos</div>
    </div>
</div>

<form method="GET" class="table-filters card" style="display: flex; gap: 16px; align-items: flex-end; padding: 16px; margin-bottom: 24px; flex-wrap: wrap;">
    <div class="form-group" style="flex: 1; min-width: 250px; margin-bottom: 0;">
        <label class="form-label" for="q"><i class="ph ph-magnifying-glass"></i> Buscar Atleta</label>
        <input type="search" id="q" name="q" class="form-control" placeholder="Nombre, apellido o cédula..." value="<?= e($filters['q'] ?? '') ?>">
    </div>

    <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
        <label class="form-label" for="categoria_id"><i class="ph ph-tag"></i> Categoría</label>
        <select id="categoria_id" name="categoria_id" class="form-control">
            <option value="">Todas las categorías</option>
            <option value="sin_asignacion" <?= ($filters['categoria_id'] ?? '') === 'sin_asignacion' ? 'selected' : '' ?>>Sin asignación</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= (int) $cat['categoria_id'] ?>" <?= ($filters['categoria_id'] ?? '') == $cat['categoria_id'] ? 'selected' : '' ?>><?= e($cat['nombre_categoria']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
        <label class="form-label" for="estatus"><i class="ph ph-activity"></i> Estatus</label>
        <select id="estatus" name="estatus" class="form-control">
            <option value="">Todos los estatus</option>
            <option value="1" <?= ($filters['estatus'] ?? '') == '1' ? 'selected' : '' ?>>Activo</option>
            <option value="3" <?= ($filters['estatus'] ?? '') == '3' ? 'selected' : '' ?>>Inactivo</option>
            <option value="2" <?= ($filters['estatus'] ?? '') == '2' ? 'selected' : '' ?>>Lesionado</option>
            <option value="0" <?= ($filters['estatus'] ?? '') == '0' ? 'selected' : '' ?>>Suspendido</option>
        </select>
    </div>

    <div style="display: flex; gap: 8px;">
        <button type="submit" class="btn btn-outline"><i class="ph ph-funnel"></i> Filtrar</button>
        <a href="<?= e(url('/admin/atletas')) ?>" class="btn btn-ghost" title="Limpiar filtros"><i class="ph ph-x"></i></a>
    </div>
</form>

<div class="data-table-wrap card" style="padding: 0; overflow: hidden;">
    <table class="data-table" style="margin: 0; border: none;">
        <thead style="background: var(--color-bg-alt);">
            <tr>
                <th style="width:52px; padding-left: 24px;"></th>
                <th>Atleta</th>
                <th>Categoría</th>
                <th>Edad</th>
                <th>Estatus</th>
                <th style="width:160px; text-align: right; padding-right: 24px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($pag['data'])): ?>
            <tr>
                <td colspan="6" style="padding: 64px 24px; text-align: center;">
                    <i class="ph ph-users text-muted" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.5;"></i>
                    <h3 class="text-muted" style="margin: 0 0 8px;">No hay atletas registrados</h3>
                    <p class="text-muted" style="font-size: 14px; max-width: 400px; margin: 0 auto;">No se encontraron atletas con los filtros actuales o no hay datos registrados en el sistema.</p>
                </td>
            </tr>
        <?php else: foreach ($pag['data'] as $a): ?>
            <tr>
                <td style="padding-left: 24px;">
                    <?php if (!empty($a['foto'])): ?>
                        <div style="position: relative; width: 44px; height: 44px; padding: 2px; border: 1px solid var(--color-border); border-radius: 50%; background: var(--color-bg);">
                            <img src="<?= e(url($a['foto'])) ?>" class="avatar-thumb" alt="" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: block;">
                        </div>
                    <?php else: ?>
                        <div class="avatar-placeholder" style="width: 44px; height: 44px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold; border: 1px solid var(--color-primary-light);">
                            <?= e(mb_substr($a['nombre'], 0, 1) . mb_substr($a['apellido'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="font-weight: 600; color: var(--color-text);"><?= e($a['nombre'] . ' ' . $a['apellido']) ?></div>
                    <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 2px;">C.I: <?= !empty($a['cedula_formateada']) ? e($a['cedula_formateada']) : 'Sin Cédula' ?></div>
                </td>
                <td>
                    <?php if (!empty($a['nombre_categoria'])): ?>
                        <div style="font-weight: 600; color: var(--color-text);"><?= e($a['nombre_categoria']) ?></div>
                        <div style="margin-top: 4px;">
                            <?php if ((int)$a['asig_estatus'] === 1): ?>
                                <span class="badge badge-success" style="font-size: 11px; padding: 2px 8px; border-radius: 12px;">Vigente</span>
                            <?php else: ?>
                                <span class="badge badge-danger" style="font-size: 11px; padding: 2px 8px; border-radius: 12px;">Vencido</span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <span class="text-muted" style="font-size: 13px; font-style: italic;">Sin Asignación</span>
                    <?php endif; ?>
                </td>

                <td>
                    <?php 
                        $edad = 0;
                        if (!empty($a['fecha_nac'])) {
                            $edad = (new \App\Models\ResultadoPrueba())->calcularEdad((string)$a['fecha_nac']);
                        }
                    ?>
                    <span style="font-weight: 600; color: var(--color-text);"><?= $edad ?> años</span>
                </td>

                <td>
                    <?php 
                        $val = (int) $a['estatus'];
                        [$label, $badge] = match ($val) {
                            1 => ['Activo', 'success'],
                            2 => ['Lesionado', 'warning'],
                            0 => ['Suspendido', 'danger'],
                            3 => ['Inactivo', 'outline'],
                            default => ['Desconocido', 'primary']
                        }; 
                    ?>
                    <span class="badge badge-<?= $badge ?>" style="padding: 6px 12px; border-radius: 20px;">
                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: currentColor; margin-right: 6px; vertical-align: middle;"></span>
                        <?= e($label) ?>
                    </span>
                </td>
                <td style="text-align: right; padding-right: 24px;">
                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <a href="<?= e(url('/admin/atletas/' . $a['atleta_id'])) ?>" class="btn btn-sm btn-ghost" title="Ver Perfil" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="ph ph-eye"></i>
                        </a>
                        <a href="<?= e(url('/admin/reportes/atleta/' . $a['atleta_id'])) ?>" class="btn btn-sm btn-ghost" title="Reporte Individual" target="_blank" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="ph ph-file-pdf"></i>
                        </a>
                        <?php if (can('admin')): ?>
                            <form method="POST" action="<?= e(url('/admin/atletas/' . $a['atleta_id'] . '/eliminar')) ?>" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="button" class="btn btn-sm btn-ghost text-danger btn-eliminar-atleta" title="Eliminar Atleta" data-nombre="<?= e($a['nombre'] . ' ' . $a['apellido']) ?>" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>



<?php if (($pag['last_page'] ?? 1) > 1): ?>
    <div style="display: flex; justify-content: center; margin-top: 24px;">
        <ul class="pagination">
            <?php for ($p = 1; $p <= $pag['last_page']; $p++):
                $qs = array_filter(array_merge($filters, ['page' => $p]), fn($v) => $v !== null && $v !== ''); ?>
                <li class="<?= $p === (int) $pag['page'] ? 'active' : '' ?>">
                    <?php if ($p === (int) $pag['page']): ?>
                        <span><?= $p ?></span>
                    <?php else: ?>
                        <a href="<?= e(url('/admin/atletas?' . http_build_query($qs))) ?>"><?= $p ?></a>
                    <?php endif; ?>
                </li>
            <?php endfor; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Modal: Nueva Medición -->
<div id="modal-medicion" class="modal-overlay" style="display:none;">
    <form id="form-medicion" action="" method="POST" class="modal-container" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title"><i class="ph ph-ruler"></i> Nueva Medición: <span id="atleta-nombre-modal"></span></h3>
            <button type="button" class="modal-close" data-close-modal>&times;</button>
        </div>
        <?= csrf_field() ?>
        <div class="modal-body">
            <div id="medicion-error" style="display:none; background:rgba(239, 68, 68, 0.1); color:var(--color-danger); padding:12px; border-radius:8px; margin-bottom:16px; font-size:14px;"></div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Fecha de Medición *</label>
                    <input type="date" name="fecha_medicion" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Peso (kg)</label>
                    <input type="number" step="0.1" name="peso" class="form-control" placeholder="Ej: 70.5">
                </div>
                <div class="form-group">
                    <label class="form-label">Altura (m)</label>
                    <input type="number" step="0.01" name="altura" class="form-control" placeholder="Ej: 1.75">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">% Grasa</label>
                    <input type="number" step="0.1" name="porcentaje_grasa" class="form-control" placeholder="Ej: 12.5">
                </div>
                <div class="form-group">
                    <label class="form-label">% Musculatura</label>
                    <input type="number" step="0.1" name="porcentaje_musculatura" class="form-control" placeholder="Ej: 40.2">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Envergadura (m)</label>
                    <input type="number" step="0.01" name="envergadura" class="form-control" placeholder="Ej: 1.80">
                </div>
                <div class="form-group">
                    <label class="form-label">Pierna (cm)</label>
                    <input type="number" step="0.1" name="largo_de_pierna" class="form-control" placeholder="Ej: 90">
                </div>
                <div class="form-group">
                    <label class="form-label">Torso (cm)</label>
                    <input type="number" step="0.1" name="largo_de_torso" class="form-control" placeholder="Ej: 50">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> Guardar Medición</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal-medicion');
    const form = document.getElementById('form-medicion');
    const baseUrl = "<?= e(url('/admin/medidas/atleta')) ?>";

    document.querySelectorAll('.btn-nueva-medicion').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const nombre = btn.getAttribute('data-nombre');
            
            document.getElementById('atleta-nombre-modal').textContent = nombre;
            form.action = `${baseUrl}/${id}`;
            document.getElementById('medicion-error').style.display = 'none';
            form.reset();
            form.querySelector('[name="fecha_medicion"]').value = "<?= date('Y-m-d') ?>";
            modal.style.display = 'flex';
        });
    });

    const cerrarModal = () => { modal.style.display = 'none'; };
    modal.querySelectorAll('[data-close-modal]').forEach(btn => btn.addEventListener('click', cerrarModal));
    modal.addEventListener('click', (e) => { if (e.target === modal) cerrarModal(); });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const errorDiv = document.getElementById('medicion-error');
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;

        errorDiv.style.display = 'none';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Guardando...';

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });

            const result = await response.json();

            if (result.success) {
                // Notificar éxito y recargar
                if (typeof CadaToast !== 'undefined') {
                    CadaToast.success(result.message, () => window.location.reload());
                } else {
                    window.location.reload();
                }
            } else {
                errorDiv.textContent = result.message || 'Error al guardar la medición.';
                if (result.errors) {
                    const firstError = Object.values(result.errors)[0][0];
                    errorDiv.textContent += ' ' + firstError;
                }
                errorDiv.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        } catch (error) {
            errorDiv.textContent = 'Error de conexión.';
            errorDiv.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
});
</script>

<!-- Modal: Registrar Prueba Física -->
<div id="modal-prueba-fisica" class="modal-overlay" style="display:none;">
    <form id="form-prueba-fisica" action="" method="POST" class="modal-container" style="max-width: 550px;">
        <div class="modal-header">
            <h3 class="modal-title"><i class="ph ph-chart-line-up"></i> Registrar Prueba: <span id="atleta-nombre-prueba-modal"></span></h3>
            <button type="button" class="modal-close" data-close-modal>&times;</button>
        </div>
        <?= csrf_field() ?>
        <div class="modal-body">
            <div id="prueba-fisica-error" style="display:none; background:rgba(239, 68, 68, 0.1); color:var(--color-danger); padding:12px; border-radius:8px; margin-bottom:16px; font-size:14px;"></div>
            
            <p style="font-size: 13px; color: var(--color-text-muted); margin-bottom: 20px;">Ingrese los valores físicos reales de rendimiento de las pruebas. Se generará un evento automático para hoy.</p>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Test de Fuerza (cm)</label>
                    <input type="number" step="0.01" name="test_de_fuerza" class="form-control" min="1" max="100" placeholder="Rango Élite (100%): 20 - 45 cm">
                </div>
                <div class="form-group">
                    <label class="form-label">Test de Resistencia (m)</label>
                    <input type="number" step="1" name="test_resistencia" class="form-control" min="1" max="1000" placeholder="Rango Élite (100%): 600 - 2200 m">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Test de Velocidad (s)</label>
                    <input type="number" step="0.01" name="test_velocidad" class="form-control" min="1" max="10" placeholder="Rango Élite (100%): 5.20 - 4.10 s">
                </div>
                <div class="form-group">
                    <label class="form-label">Test de Coordinación (s)</label>
                    <input type="number" step="0.01" name="test_coordinacion" class="form-control" min="1" max="100" placeholder="Rango Élite (100%): 22.50 - 16.50 s">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Test de Reacción (ms)</label>
                    <input type="number" step="1" name="test_de_reaccion" class="form-control" min="100" max="1000" placeholder="Rango Élite (100%): 450 - 220 ms">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> Guardar Resultados</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalPrueba = document.getElementById('modal-prueba-fisica');
    const formPrueba = document.getElementById('form-prueba-fisica');
    const baseUrlPruebas = "<?= e(url('/admin/resultados-pruebas/atleta')) ?>";

    document.querySelectorAll('.btn-nueva-prueba-fisica').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const nombre = btn.getAttribute('data-nombre');
            
            document.getElementById('atleta-nombre-prueba-modal').textContent = nombre;
            formPrueba.action = `${baseUrlPruebas}/${id}`;
            document.getElementById('prueba-fisica-error').style.display = 'none';
            formPrueba.reset();
            modalPrueba.style.display = 'flex';
        });
    });

    const cerrarModalPrueba = () => { modalPrueba.style.display = 'none'; };
    modalPrueba.querySelectorAll('[data-close-modal]').forEach(btn => btn.addEventListener('click', cerrarModalPrueba));
    modalPrueba.addEventListener('click', (e) => { if (e.target === modalPrueba) cerrarModalPrueba(); });

    formPrueba.addEventListener('submit', async (e) => {
        e.preventDefault();
        const errorDiv = document.getElementById('prueba-fisica-error');
        const submitBtn = formPrueba.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;

        errorDiv.style.display = 'none';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Guardando...';

        try {
            const formData = new FormData(formPrueba);
            const response = await fetch(formPrueba.action, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });

            const text = await response.text();
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                console.error("Invalid JSON:", text);
                throw new Error("El servidor no devolvió una respuesta válida.");
            }

            if (result.success) {
                if (typeof CadaToast !== 'undefined') {
                    CadaToast.success(result.message, () => window.location.reload());
                } else {
                    window.location.reload();
                }
            } else {
                errorDiv.textContent = result.message || 'Error al guardar los resultados.';
                errorDiv.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        } catch (error) {
            errorDiv.textContent = error.message || 'Error de conexión.';
            errorDiv.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-eliminar-atleta').forEach(btn => {
        btn.addEventListener('click', () => {
            const form = btn.closest('form');
            const nombre = btn.getAttribute('data-nombre');

            CadaModal.confirm({
                title: '¿Eliminar Atleta?',
                text: `¿Estás seguro de que deseas eliminar permanentemente a <strong>${nombre}</strong>?<br><br><small style="color:var(--color-text-muted);">Nota: Si el atleta ya tiene registros de asistencia, pruebas físicas o historial antropométrico, la base de datos no permitirá borrarlo por integridad de datos, y se sugerirá desactivarlo en su lugar.</small>`,
                type: 'danger',
                confirmText: 'Sí, Eliminar',
                cancelText: 'Cancelar'
            }).then(confirmed => {
                if (confirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>

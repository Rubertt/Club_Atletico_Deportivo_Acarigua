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
        <a href="<?= e(url('/admin/atletas')) ?>" class="btn btn-outline" title="Limpiar filtros" style="height: 44px; display: inline-flex; align-items: center; justify-content: center;"><i class="ph ph-trash"></i> Limpiar</a>
    </div>
</form>

<div class="data-table-wrap card" style="padding: 0; border: none; border-radius: 0; border-top: 1px solid var(--color-border);">
    <!-- Cabeceras en PC -->
    <div class="asig-headers-desktop" style="display: flex; align-items: center; gap: 16px; padding: 12px 24px; background: var(--color-bg-alt); border-bottom: 1px solid var(--color-border); position: sticky; top: 0; z-index: 10; font-size: 13px; font-weight: 600; color: var(--color-text-muted);">
        <div style="width: 320px; flex-shrink: 0; display: flex; align-items: center; gap: 12px;">
            <div style="width: 44px;"></div>
            <div>Atleta</div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr 120px; gap: 16px; flex: 1;">
            <div>Categoría</div>
            <div>Edad</div>
            <div>Estatus</div>
        </div>
        <div style="width: 140px; text-align: right; flex-shrink: 0; padding-right: 12px;">Acciones</div>
    </div>

    <!-- Listado de atletas registrados -->
    <div class="atletas-list-container">
        <?php if (empty($pag['data'])): ?>
            <div style="padding: 64px 24px; text-align: center; background: var(--color-surface);">
                <i class="ph ph-users text-muted" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.5;"></i>
                <h3 class="text-muted" style="margin: 0 0 8px;">No hay atletas registrados</h3>
                <p class="text-muted" style="font-size: 14px; max-width: 400px; margin: 0 auto;">No se encontraron atletas con los filtros actuales o no hay datos registrados en el sistema.</p>
            </div>
        <?php else: foreach ($pag['data'] as $a): ?>
            <div class="asig-atleta-row">
                <div class="asig-atleta-row__athlete">
                    <?php if (!empty($a['foto'])): ?>
                        <div style="position: relative; width: 44px; height: 44px; padding: 2px; border: 1px solid var(--color-border); border-radius: 50%; background: var(--color-bg); flex-shrink: 0;">
                            <img src="<?= e(url($a['foto'])) ?>" class="avatar-thumb" alt="" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: block;">
                        </div>
                    <?php else: ?>
                        <div class="avatar-placeholder" style="width: 44px; height: 44px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold; border: 1px solid var(--color-primary-light); flex-shrink: 0;">
                            <?= e(mb_substr($a['nombre'], 0, 1) . mb_substr($a['apellido'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="asig-atleta-row__name-wrap">
                        <div class="asig-atleta-row__name">
                            <?= e($a['nombre'] . ' ' . $a['apellido']) ?>
                        </div>
                        <div class="asig-atleta-row__meta">
                            <?= !empty($a['cedula_formateada']) ? e($a['cedula_formateada']) : 'Sin Cédula' ?>
                        </div>
                    </div>
                </div>

                <div class="asig-atleta-row__inputs">
                    <div class="asig-input-group">
                        <span class="asig-input-label">Categoría</span>
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
                    </div>

                    <div class="asig-input-group">
                        <span class="asig-input-label">Edad</span>
                        <?php 
                            $edad = 0;
                            if (!empty($a['fecha_nac'])) {
                                $edad = (new \App\Models\ResultadoPrueba())->calcularEdad((string)$a['fecha_nac']);
                            }
                        ?>
                        <span style="font-weight: 600; color: var(--color-text); font-size: 14px;"><?= $edad ?> años</span>
                    </div>

                    <div class="asig-input-group">
                        <span class="asig-input-label">Estatus</span>
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
                        <span class="badge badge-<?= $badge ?>" style="padding: 6px 12px; border-radius: 20px; font-size: 12px; align-self: flex-start;">
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: currentColor; margin-right: 6px; vertical-align: middle;"></span>
                            <?= e($label) ?>
                        </span>
                    </div>
                </div>

                <div class="asig-atleta-row__actions">
                    <a href="<?= e(url('/admin/atletas/' . $a['atleta_id'])) ?>" class="btn-view-premium" title="Ver Perfil">
                        <i class="ph ph-eye"></i>
                    </a>
                    <a href="<?= e(url('/admin/reportes/atleta/' . $a['atleta_id'])) ?>" class="btn-report-premium" title="Reporte Individual" target="_blank">
                        <i class="ph ph-file-pdf"></i>
                    </a>
                    <?php if (can('admin')): ?>
                        <form method="POST" action="<?= e(url('/admin/atletas/' . $a['atleta_id'] . '/eliminar')) ?>" style="display:inline;">
                            <?= csrf_field() ?>
                            <button type="button" class="btn-delete-premium btn-eliminar-atleta" title="Eliminar Atleta" data-nombre="<?= e($a['nombre'] . ' ' . $a['apellido']) ?>">
                                <i class="ph ph-trash"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>



<div class="pagination-wrap">
<?php
$currentPage = (int) ($pag['page'] ?? 1);
$totalPages = (int) ($pag['last_page'] ?? 1);
if ($totalPages > 1):
    $range = 1;
    $pagesToShow = [];
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i === 1 || $i === $totalPages || ($i >= $currentPage - $range && $i <= $currentPage + $range)) {
            $pagesToShow[] = $i;
        } else if (empty($pagesToShow) || end($pagesToShow) !== '...') {
            $pagesToShow[] = '...';
        }
    }
?>
    <div style="display: flex; justify-content: center; margin-top: 24px;">
        <ul class="pagination">
            <!-- Botón << (Primero) -->
            <?php if ($currentPage === 1): ?>
                <li class="disabled"><span><i class="ph ph-caret-double-left"></i></span></li>
            <?php else: 
                $qsFirst = array_filter(array_merge($filters, ['page' => 1]), fn($v) => $v !== null && $v !== ''); ?>
                <li><a href="<?= e(url('/admin/atletas?' . http_build_query($qsFirst))) ?>"><i class="ph ph-caret-double-left"></i></a></li>
            <?php endif; ?>

            <!-- Botón < (Anterior) -->
            <?php if ($currentPage === 1): ?>
                <li class="disabled"><span><i class="ph ph-caret-left"></i></span></li>
            <?php else: 
                $qsPrev = array_filter(array_merge($filters, ['page' => $currentPage - 1]), fn($v) => $v !== null && $v !== ''); ?>
                <li><a href="<?= e(url('/admin/atletas?' . http_build_query($qsPrev))) ?>"><i class="ph ph-caret-left"></i></a></li>
            <?php endif; ?>

            <!-- Números de Página -->
            <?php foreach ($pagesToShow as $p): ?>
                <?php if ($p === '...'): ?>
                    <li class="disabled"><span>...</span></li>
                <?php elseif ($p === $currentPage): ?>
                    <li class="active"><span><?= $p ?></span></li>
                <?php else: 
                    $qsPage = array_filter(array_merge($filters, ['page' => $p]), fn($v) => $v !== null && $v !== ''); ?>
                    <li><a href="<?= e(url('/admin/atletas?' . http_build_query($qsPage))) ?>"><?= $p ?></a></li>
                <?php endif; ?>
            <?php endforeach; ?>

            <!-- Botón > (Siguiente) -->
            <?php if ($currentPage === $totalPages): ?>
                <li class="disabled"><span><i class="ph ph-caret-right"></i></span></li>
            <?php else: 
                $qsNext = array_filter(array_merge($filters, ['page' => $currentPage + 1]), fn($v) => $v !== null && $v !== ''); ?>
                <li><a href="<?= e(url('/admin/atletas?' . http_build_query($qsNext))) ?>"><i class="ph ph-caret-right"></i></a></li>
            <?php endif; ?>

            <!-- Botón >> (Último) -->
            <?php if ($currentPage === $totalPages): ?>
                <li class="disabled"><span><i class="ph ph-caret-double-right"></i></span></li>
            <?php else: 
                $qsLast = array_filter(array_merge($filters, ['page' => $totalPages]), fn($v) => $v !== null && $v !== ''); ?>
                <li><a href="<?= e(url('/admin/atletas?' . http_build_query($qsLast))) ?>"><i class="ph ph-caret-double-right"></i></a></li>
            <?php endif; ?>
        </ul>
    </div>
<?php endif; ?>
</div>

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
    const form = document.querySelector('.table-filters');
    const paginationWrap = document.querySelector('.pagination-wrap');
    if (form) {
        const qInput = form.querySelector('#q');
        const categoriaSelect = form.querySelector('#categoria_id');
        const estatusSelect = form.querySelector('#estatus');
        let debounceTimer;

        const performFilter = (page = 1) => {
            const formData = new FormData(form);
            formData.append('ajax', '1');
            formData.append('page', page);
            const queryString = new URLSearchParams(formData).toString();
            
            const navParams = new URLSearchParams(new FormData(form));
            if (page > 1) {
                navParams.append('page', page);
            }
            const newUrl = `${window.location.pathname}?${navParams.toString()}`;
            window.history.replaceState({ path: newUrl }, '', newUrl);

            fetch(`${window.location.pathname}?${queryString}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Actualizar listado
                const oldList = document.querySelector('.atletas-list-container');
                const newList = doc.querySelector('.atletas-list-container');
                if (oldList && newList) {
                    oldList.innerHTML = newList.innerHTML;
                }
                
                // Actualizar stats-grid
                const oldStats = document.querySelector('.stats-grid');
                const newStats = doc.querySelector('.stats-grid');
                if (oldStats && newStats) {
                    oldStats.innerHTML = newStats.innerHTML;
                }

                // Actualizar pagination-wrap
                const oldPagWrap = document.querySelector('.pagination-wrap');
                const newPagWrap = doc.querySelector('.pagination-wrap');
                if (oldPagWrap && newPagWrap) {
                    oldPagWrap.innerHTML = newPagWrap.innerHTML;
                }

                // Re-vincular botones de eliminar
                bindDeleteButtons();
            })
            .catch(err => console.error('Error al filtrar:', err));
        };

        if (categoriaSelect) categoriaSelect.addEventListener('change', () => performFilter(1));
        if (estatusSelect) estatusSelect.addEventListener('change', () => performFilter(1));
        if (qInput) {
            qInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => performFilter(1), 300);
            });
            qInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') e.preventDefault();
            });
        }
        
        form.addEventListener('submit', (e) => e.preventDefault());

        // Interceptar clicks de paginación
        if (paginationWrap) {
            paginationWrap.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (link) {
                    e.preventDefault();
                    const urlObj = new URL(link.href);
                    const page = urlObj.searchParams.get('page') || 1;
                    performFilter(page);
                }
            });
        }
    }

    function bindDeleteButtons() {
        document.querySelectorAll('.btn-eliminar-atleta').forEach(btn => {
            btn.onclick = () => {
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
            };
        });
    }

    bindDeleteButtons();
});
</script>

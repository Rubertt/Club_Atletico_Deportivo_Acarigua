<?php /** @var array $stats @var array $atletas @var array $usuarios @var array $categorias */ 
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
        <h1>Centro de Reportes y Estadísticas</h1>
        <div class="subtitle">Generación de fichas, exportación de datos y analíticas de asistencia</div>
    </div>
</div>

<div class="reportes-grid">
    <!-- Main Content: Buscadores y Pestañas -->
    <div class="card" style="padding: 24px; min-height: 500px; min-width: 0;">
        
        <!-- Pestañas (Tabs) -->
        <div style="display: flex; gap: 16px; border-bottom: 2px solid var(--color-border); margin-bottom: 24px;">
            <button type="button" class="tab-btn active" data-target="tab-atletas" style="padding: 12px 24px; font-weight: 600; border: 0; background: transparent; border-bottom: 3px solid var(--color-primary); color: var(--color-primary); cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px;">
                <i class="ph ph-users-three" style="font-size: 18px;"></i> Atletas
            </button>
            <?php if (can('admin')): ?>
            <button type="button" class="tab-btn" data-target="tab-usuarios" style="padding: 12px 24px; font-weight: 600; border: 0; background: transparent; border-bottom: 3px solid transparent; color: var(--color-text-muted); cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px;">
                <i class="ph ph-shield-chevron" style="font-size: 18px;"></i> Usuarios
            </button>
            <?php endif; ?>
        </div>

        <!-- Panel de Atletas -->
        <div id="tab-atletas" class="tab-content-pane">
            <!-- Buscador y Filtros de Atletas -->
            <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px; position: relative;">
                    <input type="text" id="search-atleta" class="form-control" placeholder="Buscar atleta por nombre o documento..." style="padding-left: 36px;">
                    <i class="ph ph-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); pointer-events: none;"></i>
                </div>
                <div style="width: 180px;">
                    <select id="filter-cat" class="form-control">
                        <option value="">Todas las Categorías</option>
                        <?php foreach (($categorias ?? []) as $c): ?>
                            <option value="<?= (int) $c['categoria_id'] ?>"><?= e($c['nombre_categoria']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="width: 150px;">
                    <select id="filter-estatus-atleta" class="form-control">
                        <option value="">Todos los Estatus</option>
                        <?php foreach (ESTATUS_ATLETA as $k => $v): ?>
                            <option value="<?= $k ?>"><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Tabla de Atletas (Refactorizada) -->
            <div class="responsive-table-wrap">
                <div class="responsive-table-header" style="grid-template-columns: 2fr 1.2fr 1.2fr 1fr 1.5fr;">
                    <div style="padding-left: 12px;">Atleta</div>
                    <div>Documento</div>
                    <div>Categoría</div>
                    <div>Estatus</div>
                    <div style="text-align: right; padding-right: 12px;">Acciones</div>
                </div>
                <div class="responsive-table-body">
                <?php foreach ($atletas as $a): ?>
                    <div class="responsive-table-row atleta-row" data-name="<?= e($a['nombre'] . ' ' . $a['apellido']) ?>" data-cedula="<?= e($a['cedula'] ?? '') ?>" data-categoria="<?= (int)$a['categoria_id'] ?>" data-estatus="<?= (int)$a['estatus'] ?>" style="grid-template-columns: 2fr 1.2fr 1.2fr 1fr 1.5fr;">
                        <div class="responsive-row-col" style="padding-left: 12px;">
                            <span class="responsive-col-label">Atleta</span>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <?php if (!empty($a['foto'])): ?>
                                    <img src="<?= e(url($a['foto'])) ?>" class="avatar-thumb" alt="" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                                <?php else: ?>
                                    <div class="avatar-placeholder" style="width: 32px; height: 32px; font-size: 12px; background: var(--color-primary-light); color: var(--color-primary); flex-shrink: 0;">
                                        <?= e(mb_substr($a['nombre'], 0, 1) . mb_substr($a['apellido'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <strong style="color: var(--color-text);"><?= e($a['nombre'] . ' ' . $a['apellido']) ?></strong>
                            </div>
                        </div>
                        <div class="responsive-row-col">
                            <span class="responsive-col-label">Documento</span>
                            <span style="color: var(--color-text-muted); font-size: 13px;"><i class="ph ph-identification-card"></i> <?= e(formatDocumento($a['cedula'] ?? '')) ?></span>
                        </div>
                        <div class="responsive-row-col">
                            <span class="responsive-col-label">Categoría</span>
                            <span style="font-weight: 500; font-size: 13px;"><?= e($a['nombre_categoria'] ?? 'Sin Categoría') ?></span>
                        </div>
                        <div class="responsive-row-col">
                            <span class="responsive-col-label">Estatus</span>
                            <div>
                                <?php
                                $estText = ESTATUS_ATLETA[(int)$a['estatus']] ?? 'Desconocido';
                                $estBadge = match((int)$a['estatus']) {
                                    1 => 'badge-success',
                                    2 => 'badge-warning',
                                    0 => 'badge-danger',
                                    default => 'badge-secondary'
                                };
                                ?>
                                <span class="badge <?= $estBadge ?>"><?= e($estText) ?></span>
                            </div>
                        </div>
                        <div class="responsive-row-col" style="text-align: right; padding-right: 12px;">
                            <span class="responsive-col-label" style="text-align: right;">Acciones</span>
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <button type="button" class="btn btn-sm btn-ghost" onclick="openModalAsistAtleta(<?= (int)$a['atleta_id'] ?>, '<?= e(addslashes($a['nombre'] . ' ' . $a['apellido'])) ?>')" title="Imprimir Asistencia">
                                    <i class="ph ph-calendar-check" style="font-size: 16px;"></i>
                                </button>
                                <a href="<?= e(url("/admin/reportes/atleta/{$a['atleta_id']}")) ?>" class="btn btn-sm btn-outline" target="_blank" title="Imprimir Ficha Técnica">
                                    <i class="ph ph-file-pdf"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline btn-share-report" data-url="<?= e(url("/admin/reportes/atleta/{$a['atleta_id']}")) ?>" data-filename="ficha_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $a['nombre'] . '_' . $a['apellido']) ?>.pdf" title="Compartir Ficha Técnica">
                                    <i class="ph ph-share-network"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($atletas)): ?>
                    <div class="responsive-table-row no-results-row" style="grid-template-columns: 1fr; justify-content: center; text-align: center; padding: 48px;">
                        <div class="text-center text-muted">
                            <i class="ph ph-user-list text-muted" style="font-size:32px; display:block; margin-bottom:8px; opacity:0.5;"></i>
                            No hay atletas registrados.
                        </div>
                    </div>
                <?php endif; ?>
                <div id="no-atletas-search" class="responsive-table-row" style="display: none; grid-template-columns: 1fr; justify-content: center; text-align: center; padding: 48px;">
                    <div class="text-center text-muted">
                        <i class="ph ph-magnifying-glass text-muted" style="font-size:32px; display:block; margin-bottom:8px; opacity:0.5;"></i>
                        No se encontraron atletas con esos filtros.
                    </div>
                </div>
                </div>
            </div>
            <div id="atletas-pagination" style="display: flex; justify-content: center; margin-top: 24px;"></div>
        </div>

        <?php if (can('admin')): ?>
        <!-- Panel de Usuarios -->
        <div id="tab-usuarios" class="tab-content-pane" style="display: none;">
            <!-- Buscador y Filtros de Usuarios -->
            <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px; position: relative;">
                    <input type="text" id="search-usuario" class="form-control" placeholder="Buscar usuario por nombre o documento..." style="padding-left: 36px;">
                    <i class="ph ph-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); pointer-events: none;"></i>
                </div>
                <div style="width: 180px;">
                    <select id="filter-rol" class="form-control">
                        <option value="">Todos los Roles</option>
                        <option value="1">Superusuario</option>
                        <option value="2">Administrador</option>
                        <option value="3">Entrenador</option>
                        <option value="4">Directivo</option>
                        <option value="5">Médico</option>
                    </select>
                </div>
            </div>

            <!-- Tabla de Usuarios (Refactorizada) -->
            <div class="responsive-table-wrap">
                <div class="responsive-table-header" style="grid-template-columns: 2fr 1.2fr 1.2fr 1fr 1fr;">
                    <div style="padding-left: 12px;">Usuario</div>
                    <div>Documento</div>
                    <div>Rol</div>
                    <div>Estatus</div>
                    <div style="text-align: right; padding-right: 12px;">Acciones</div>
                </div>
                <div class="responsive-table-body">
                <?php foreach ($usuarios as $u): ?>
                    <div class="responsive-table-row usuario-row" data-name="<?= e($u['nombre'] . ' ' . $u['apellido']) ?>" data-cedula="<?= e($u['cedula'] ?? '') ?>" data-rol="<?= (int)$u['rol_id'] ?>" style="grid-template-columns: 2fr 1.2fr 1.2fr 1fr 1fr;">
                        <div class="responsive-row-col" style="padding-left: 12px;">
                            <span class="responsive-col-label">Usuario</span>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <?php if (!empty($u['foto'])): ?>
                                    <img src="<?= e(url($u['foto'])) ?>" class="avatar-thumb" alt="" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                                <?php else: ?>
                                    <div class="avatar-placeholder" style="width: 32px; height: 32px; font-size: 12px; background: var(--color-primary-light); color: var(--color-primary); flex-shrink: 0;">
                                        <?= e(mb_substr($u['nombre'], 0, 1) . mb_substr($u['apellido'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <strong style="color: var(--color-text);"><?= e($u['nombre'] . ' ' . $u['apellido']) ?></strong>
                            </div>
                        </div>
                        <div class="responsive-row-col">
                            <span class="responsive-col-label">Documento</span>
                            <span style="color: var(--color-text-muted); font-size: 13px;"><i class="ph ph-identification-card"></i> <?= e(formatDocumento($u['cedula'] ?? '')) ?></span>
                        </div>
                        <div class="responsive-row-col">
                            <span class="responsive-col-label">Rol</span>
                            <div>
                                <?php
                                $rolText = match((int)$u['rol_id']) {
                                    1 => 'Superusuario',
                                    2 => 'Administrador',
                                    3 => 'Entrenador',
                                    4 => 'Directivo',
                                    5 => 'Médico',
                                    default => 'Desconocido'
                                };
                                ?>
                                <span style="font-weight: 500; font-size: 13px;"><?= e($rolText) ?></span>
                            </div>
                        </div>
                        <div class="responsive-row-col">
                            <span class="responsive-col-label">Estatus</span>
                            <div>
                                <?php
                                $uEst = $u['estatus'] ?? 'Activo';
                                $isActive = (strcasecmp((string)$uEst, 'activo') === 0 || $uEst === '1' || $uEst === 1);
                                $uEstText = $isActive ? 'Activo' : 'Inactivo';
                                $uEstBadge = $isActive ? 'badge-success' : 'badge-secondary';
                                ?>
                                <span class="badge <?= $uEstBadge ?>"><?= e($uEstText) ?></span>
                            </div>
                        </div>
                        <div class="responsive-row-col" style="text-align: right; padding-right: 12px;">
                            <span class="responsive-col-label" style="text-align: right;">Acciones</span>
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <a href="<?= e(url("/admin/reportes/usuario/{$u['usuario_id']}")) ?>" class="btn btn-sm btn-outline" target="_blank" title="Imprimir Ficha de Usuario">
                                    <i class="ph ph-file-pdf"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline btn-share-report" data-url="<?= e(url("/admin/reportes/usuario/{$u['usuario_id']}")) ?>" data-filename="ficha_usuario_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $u['nombre'] . '_' . $u['apellido']) ?>.pdf" title="Compartir Ficha de Usuario">
                                    <i class="ph ph-share-network"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($usuarios)): ?>
                    <div class="responsive-table-row no-results-row" style="grid-template-columns: 1fr; justify-content: center; text-align: center; padding: 48px;">
                        <div class="text-center text-muted">
                            <i class="ph ph-user-list text-muted" style="font-size:32px; display:block; margin-bottom:8px; opacity:0.5;"></i>
                            No hay usuarios registrados.
                        </div>
                    </div>
                <?php endif; ?>
                <div id="no-usuarios-search" class="responsive-table-row" style="display: none; grid-template-columns: 1fr; justify-content: center; text-align: center; padding: 48px;">
                    <div class="text-center text-muted">
                        <i class="ph ph-magnifying-glass text-muted" style="font-size:32px; display:block; margin-bottom:8px; opacity:0.5;"></i>
                        No se encontraron usuarios con esos filtros.
                    </div>
                </div>
                </div>
            </div>
            <div id="usuarios-pagination" style="display: flex; justify-content: center; margin-top: 24px;"></div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Sidebar: Otros Reportes Globales -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div class="card" style="padding: 24px;">
            <h3 style="margin-top: 0; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="ph ph-files" style="color: var(--color-primary); font-size: 20px;"></i> Reportes por Categoría
            </h3>
            <p class="text-muted" style="font-size: 13px; margin-bottom: 20px;">Genera reportes específicos consolidados por categoría deportiva.</p>
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <!-- Atletas por Categoría -->
                <button type="button" class="btn btn-outline" style="justify-content: flex-start; padding: 12px 16px; border-width: 2px; text-align: left; width: 100%;" onclick="openModalCat('atletas')">
                    <i class="ph ph-file-pdf" style="color: var(--color-info); font-size: 24px; margin-right: 8px;"></i> 
                    <div>
                        <div style="font-weight: 700; font-size: 14px;">Atletas por Categoría</div>
                        <div style="font-size: 11px; opacity: 0.7;">Fichas y listado de la categoría</div>
                    </div>
                </button>

                <!-- Asistencia por Categoría -->
                <button type="button" class="btn btn-outline" style="justify-content: flex-start; padding: 12px 16px; border-width: 2px; text-align: left; width: 100%;" onclick="openModalCat('asistencia')">
                    <i class="ph ph-file-pdf" style="color: var(--color-success); font-size: 24px; margin-right: 8px;"></i> 
                    <div>
                        <div style="font-weight: 700; font-size: 14px;">Asistencia por Categoría</div>
                        <div style="font-size: 11px; opacity: 0.7;">Porcentaje de asistencia en rango de fechas</div>
                    </div>
                </button>

                <!-- Pruebas Físicas por Categoría -->
                <button type="button" class="btn btn-outline" style="justify-content: flex-start; padding: 12px 16px; border-width: 2px; text-align: left; width: 100%;" onclick="openModalCat('pruebas')">
                    <i class="ph ph-file-pdf" style="color: var(--color-primary); font-size: 24px; margin-right: 8px;"></i> 
                    <div>
                        <div style="font-weight: 700; font-size: 14px;">Pruebas Físicas por Categoría</div>
                        <div style="font-size: 11px; opacity: 0.7;">Reporte de pruebas físicas consolidadas</div>
                    </div>
                </button>

                <?php if (can('admin')): ?>
                <!-- Listado de Usuarios (Solo Admin) -->
                <a href="<?= e(url('/admin/reportes/usuarios/listado')) ?>" class="btn btn-outline" style="justify-content: flex-start; padding: 12px 16px; border-width: 2px; text-align: left; width: 100%; text-decoration: none;" target="_blank">
                    <i class="ph ph-file-pdf" style="color: var(--color-warning); font-size: 24px; margin-right: 8px;"></i> 
                    <div>
                        <div style="font-weight: 700; font-size: 14px; color: var(--color-text);">Listado de Usuarios</div>
                        <div style="font-size: 11px; opacity: 0.7; color: var(--color-text-muted);">Personal administrativo y entrenadores</div>
                    </div>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Reportes por Categoría -->
<div class="modal-overlay" id="modal-reporte-cat" style="display: none;">
    <div class="modal-container" style="max-width: 420px; width: 90%;">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title-cat">Generar Reporte</h3>
            <button type="button" class="modal-close" onclick="closeModalCat()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form-reporte-cat" target="_blank" method="GET" novalidate>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Categoría Deportiva</label>
                    <select name="categoria" id="cat-select" class="form-control">
                        <option value="">— Seleccione —</option>
                        <?php foreach (($categorias ?? []) as $c): ?>
                            <option value="<?= (int) $c['categoria_id'] ?>"><?= e($c['nombre_categoria']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group" id="activity-select-group" style="display: none; margin-top: 16px;">
                    <label class="form-label"><span class="required">*</span> Sesión de Pruebas Físicas</label>
                    <select name="actividad_id" id="activity-select" class="form-control">
                        <option value="">— Seleccione una Categoría primero —</option>
                    </select>
                </div>
                
                <div id="date-range-fields" class="form-responsive-row" style="display:none; margin-top:16px;">
                    <div class="form-group" style="flex:1">
                        <label class="form-label"><span class="required">*</span> Desde</label>
                        <input type="date" name="desde" id="r-desde" class="form-control" min="2019-01-01" max="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group" style="flex:1">
                        <label class="form-label"><span class="required">*</span> Hasta</label>
                        <input type="date" name="hasta" id="r-hasta" class="form-control" value="<?= date('Y-m-d') ?>" min="2019-01-01" max="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="form-actions-btn-group" style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
                    <button type="button" class="btn btn-ghost" onclick="closeModalCat()">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="ph ph-file-pdf"></i> Generar PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Reporte de Asistencia Individual (Atleta) -->
<div class="modal-overlay" id="modal-asistencia-atleta" style="display: none;">
    <div class="modal-container" style="max-width: 420px; width: 90%;">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title-asist-atleta"><i class="ph ph-calendar-check"></i> Reporte de Asistencia</h3>
            <button type="button" class="modal-close" onclick="closeModalAsistAtleta()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form-asistencia-atleta" target="_blank" method="GET" novalidate>
                <p style="font-size: 13px; color: var(--color-text-muted); margin-bottom: 16px;">
                    Seleccione el rango de fechas para el reporte de <strong id="asist-atleta-nombre"></strong>.
                </p>
                <div class="form-responsive-row">
                    <div class="form-group" style="flex:1">
                        <label class="form-label">Desde</label>
                        <input type="date" name="desde" id="asist-desde" class="form-control" min="2019-01-01" max="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group" style="flex:1">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="hasta" id="asist-hasta" class="form-control" min="2019-01-01" max="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <p style="font-size: 11px; color: var(--color-text-muted); margin-top: 8px;">
                    * Si se dejan en blanco, el reporte detallará el mes actual y resumirá el año.
                </p>

                <div class="form-actions-btn-group" style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
                    <button type="button" class="btn btn-ghost" onclick="closeModalAsistAtleta()">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="ph ph-file-pdf"></i> Generar PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Pestañas (Tabs)
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('active');
            b.style.borderBottomColor = 'transparent';
            b.style.color = 'var(--color-text-muted)';
        });
        document.querySelectorAll('.tab-content-pane').forEach(p => p.style.display = 'none');

        this.classList.add('active');
        this.style.borderBottomColor = 'var(--color-primary)';
        this.style.color = 'var(--color-primary)';
        document.getElementById(this.dataset.target).style.display = 'block';
    });
});

// Buscador y filtros de Atletas con Paginación
const $searchAtleta = document.getElementById('search-atleta');
const $filterCat = document.getElementById('filter-cat');
const $filterEstAtleta = document.getElementById('filter-estatus-atleta');
const $rowsAtletas = document.querySelectorAll('.atleta-row');
const $noAtletasSearch = document.getElementById('no-atletas-search');

const rowsPerPage = window.ROWS_PER_PAGE || 15;
let currentAtletasPage = 1;

function filterAtletas() {
    const q = $searchAtleta.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    const cat = $filterCat.value;
    const est = $filterEstAtleta.value;

    $rowsAtletas.forEach(row => {
        const name = row.dataset.name.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        const cedula = row.dataset.cedula.toLowerCase();
        const rowCat = row.dataset.categoria;
        const rowEst = row.dataset.estatus;

        const matchQ = !q || name.includes(q) || cedula.includes(q);
        const matchCat = !cat || rowCat === cat;
        const matchEst = !est || rowEst === est;

        row.dataset.matched = (matchQ && matchCat && matchEst) ? '1' : '0';
    });

    paginateAtletas(1);
}

function paginateAtletas(page) {
    currentAtletasPage = page;
    const matchedRows = Array.from($rowsAtletas).filter(row => row.dataset.matched === '1');
    const totalCount = matchedRows.length;
    const totalPages = Math.ceil(totalCount / rowsPerPage);

    if (totalCount === 0 && $rowsAtletas.length > 0) {
        $noAtletasSearch.style.display = '';
    } else {
        $noAtletasSearch.style.display = 'none';
    }

    $rowsAtletas.forEach(row => row.style.display = 'none');

    const startIndex = (page - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;

    matchedRows.forEach((row, index) => {
        if (index >= startIndex && index < endIndex) {
            row.style.display = '';
        }
    });

    renderPagination('atletas-pagination', page, totalPages, paginateAtletas);
}

$searchAtleta.addEventListener('input', filterAtletas);
$filterCat.addEventListener('change', filterAtletas);
$filterEstAtleta.addEventListener('change', filterAtletas);

// Buscador y filtros de Usuarios con Paginación
const $searchUsuario = document.getElementById('search-usuario');
const $filterRol = document.getElementById('filter-rol');
const $rowsUsuarios = document.querySelectorAll('.usuario-row');
const $noUsuariosSearch = document.getElementById('no-usuarios-search');

let currentUsuariosPage = 1;

function filterUsuarios() {
    if (!$rowsUsuarios.length) return;
    const q = $searchUsuario.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    const rol = $filterRol.value;

    $rowsUsuarios.forEach(row => {
        const name = row.dataset.name.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        const cedula = row.dataset.cedula.toLowerCase();
        const rowRol = row.dataset.rol;

        const matchQ = !q || name.includes(q) || cedula.includes(q);
        const matchRol = !rol || rowRol === rol;

        row.dataset.matched = (matchQ && matchRol) ? '1' : '0';
    });

    paginateUsuarios(1);
}

function paginateUsuarios(page) {
    currentUsuariosPage = page;
    const matchedRows = Array.from($rowsUsuarios).filter(row => row.dataset.matched === '1');
    const totalCount = matchedRows.length;
    const totalPages = Math.ceil(totalCount / rowsPerPage);

    if (totalCount === 0 && $rowsUsuarios.length > 0) {
        if ($noUsuariosSearch) $noUsuariosSearch.style.display = '';
    } else {
        if ($noUsuariosSearch) $noUsuariosSearch.style.display = 'none';
    }

    $rowsUsuarios.forEach(row => row.style.display = 'none');

    const startIndex = (page - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;

    matchedRows.forEach((row, index) => {
        if (index >= startIndex && index < endIndex) {
            row.style.display = '';
        }
    });

    renderPagination('usuarios-pagination', page, totalPages, paginateUsuarios);
}

if ($searchUsuario) {
    $searchUsuario.addEventListener('input', filterUsuarios);
    $filterRol.addEventListener('change', filterUsuarios);
}

// Función genérica para renderizar paginación idéntica al directorio de atletas (solo números de página)
function renderPagination(containerId, currentPage, totalPages, onPageChange) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '';

    if (totalPages <= 1) return;

    const ul = document.createElement('ul');
    ul.className = 'pagination';

    // << First page
    const liFirst = document.createElement('li');
    if (currentPage === 1) {
        liFirst.className = 'disabled';
        liFirst.innerHTML = '<span><i class="ph ph-caret-double-left"></i></span>';
    } else {
        const a = document.createElement('a');
        a.href = '#';
        a.innerHTML = '<i class="ph ph-caret-double-left"></i>';
        a.onclick = (e) => { e.preventDefault(); onPageChange(1); };
        liFirst.appendChild(a);
    }
    ul.appendChild(liFirst);

    // < Prev page
    const liPrev = document.createElement('li');
    if (currentPage === 1) {
        liPrev.className = 'disabled';
        liPrev.innerHTML = '<span><i class="ph ph-caret-left"></i></span>';
    } else {
        const a = document.createElement('a');
        a.href = '#';
        a.innerHTML = '<i class="ph ph-caret-left"></i>';
        a.onclick = (e) => { e.preventDefault(); onPageChange(currentPage - 1); };
        liPrev.appendChild(a);
    }
    ul.appendChild(liPrev);

    // Sliding window pages
    const range = 1;
    const pages = [];
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - range && i <= currentPage + range)) {
            pages.push(i);
        } else if (pages[pages.length - 1] !== '...') {
            pages.push('...');
        }
    }

    pages.forEach(p => {
        const li = document.createElement('li');
        if (p === '...') {
            li.className = 'disabled';
            li.innerHTML = '<span>...</span>';
        } else if (p === currentPage) {
            li.className = 'active';
            li.innerHTML = `<span>${p}</span>`;
        } else {
            const a = document.createElement('a');
            a.href = '#';
            a.textContent = p;
            a.onclick = (e) => { e.preventDefault(); onPageChange(p); };
            li.appendChild(a);
        }
        ul.appendChild(li);
    });

    // > Next page
    const liNext = document.createElement('li');
    if (currentPage === totalPages) {
        liNext.className = 'disabled';
        liNext.innerHTML = '<span><i class="ph ph-caret-right"></i></span>';
    } else {
        const a = document.createElement('a');
        a.href = '#';
        a.innerHTML = '<i class="ph ph-caret-right"></i>';
        a.onclick = (e) => { e.preventDefault(); onPageChange(currentPage + 1); };
        liNext.appendChild(a);
    }
    ul.appendChild(liNext);

    // >> Last page
    const liLast = document.createElement('li');
    if (currentPage === totalPages) {
        liLast.className = 'disabled';
        liLast.innerHTML = '<span><i class="ph ph-caret-double-right"></i></span>';
    } else {
        const a = document.createElement('a');
        a.href = '#';
        a.innerHTML = '<i class="ph ph-caret-double-right"></i>';
        a.onclick = (e) => { e.preventDefault(); onPageChange(totalPages); };
        liLast.appendChild(a);
    }
    ul.appendChild(liLast);

    container.appendChild(ul);
}

// Modales
function openModalCat(type) {
    const modal = document.getElementById('modal-reporte-cat');
    const title = document.getElementById('modal-title-cat');
    const dateRange = document.getElementById('date-range-fields');
    const activityGroup = document.getElementById('activity-select-group');
    const activitySelect = document.getElementById('activity-select');
    const form = document.getElementById('form-reporte-cat');

    // Limpiar campos y marcas
    const catSelect = document.getElementById('cat-select');
    catSelect.value = '';
    activitySelect.value = '';
    activitySelect.innerHTML = '<option value="">— Seleccione una Categoría primero —</option>';
    FormValidator.clearMark(catSelect);
    FormValidator.clearMark(activitySelect);
    FormValidator.clearMark(document.getElementById('r-desde'));
    FormValidator.clearMark(document.getElementById('r-hasta'));

    form.setAttribute('data-report-type', type);

    if (type === 'atletas') {
        title.innerHTML = '<i class="ph ph-users-three"></i> Fichas por Categoría';
        dateRange.style.display = 'none';
        activityGroup.style.display = 'none';
    } else if (type === 'asistencia') {
        title.innerHTML = '<i class="ph ph-calendar-check"></i> Asistencia por Categoría';
        dateRange.style.display = 'flex';
        activityGroup.style.display = 'none';
    } else if (type === 'pruebas') {
        title.innerHTML = '<i class="ph ph-chart-line-up"></i> Pruebas Físicas por Categoría';
        dateRange.style.display = 'none';
        activityGroup.style.display = 'block';
    }

    modal.style.display = 'flex';
}

function closeModalCat() {
    document.getElementById('modal-reporte-cat').style.display = 'none';
}

// Cierra modal al hacer click fuera del contenedor
document.getElementById('modal-reporte-cat').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModalCat();
    }
});

// Validación del formulario de reportes por categoría para evitar tooltips nativos del navegador
document.getElementById('form-reporte-cat').addEventListener('submit', function(e) {
    const catSelect = document.getElementById('cat-select');
    const dateRange = document.getElementById('date-range-fields');
    const desde = document.getElementById('r-desde');
    const hasta = document.getElementById('r-hasta');

    // Validar categoría
    if (!catSelect.value) {
        e.preventDefault();
        FormValidator.markError(catSelect);
        CadaModal.alert({
            title: 'Campo Requerido',
            text: 'Por favor, seleccione una categoría deportiva.',
            type: 'warning'
        });
        return;
    }

    // Configurar acción del formulario dinámicamente según la categoría seleccionada
    const reportType = this.getAttribute('data-report-type');
    if (reportType === 'atletas') {
        this.action = '<?= e(url('/admin/reportes/categoria/')) ?>' + catSelect.value;
    } else if (reportType === 'pruebas') {
        const activitySelect = document.getElementById('activity-select');
        if (!activitySelect.value) {
            e.preventDefault();
            FormValidator.markError(activitySelect);
            CadaModal.alert({
                title: 'Campo Requerido',
                text: 'Por favor, seleccione una sesión de pruebas físicas.',
                type: 'warning'
            });
            return;
        }
        this.action = '<?= e(url('/admin/reportes/pruebas/categoria/')) ?>' + catSelect.value + '/' + activitySelect.value;
    } else {
        this.action = '<?= e(url('/admin/reportes/asistencia/categoria')) ?>';
    }

    // Validar 'desde' si el rango de fechas está visible
    if (dateRange.style.display !== 'none') {
        if (!desde.value) {
            e.preventDefault();
            FormValidator.markError(desde);
            CadaModal.alert({
                title: 'Campo Requerido',
                text: 'Por favor, indique la fecha de inicio (Desde).',
                type: 'warning'
            });
            return;
        }

        const today = new Date().toISOString().split('T')[0];
        if (desde.value < '2019-01-01' || desde.value > today) {
            e.preventDefault();
            FormValidator.markError(desde);
            CadaModal.alert({
                title: 'Fecha Inválida',
                text: 'La fecha "Desde" debe estar entre el 01/01/2019 y el día de hoy.',
                type: 'warning'
            });
            return;
        }

        if (hasta.value && (hasta.value < '2019-01-01' || hasta.value > today)) {
            e.preventDefault();
            FormValidator.markError(hasta);
            CadaModal.alert({
                title: 'Fecha Inválida',
                text: 'La fecha "Hasta" debe estar entre el 01/01/2019 y el día de hoy.',
                type: 'warning'
            });
            return;
        }

        if (hasta.value && desde.value > hasta.value) {
            e.preventDefault();
            FormValidator.markError(desde);
            FormValidator.markError(hasta);
            CadaModal.alert({
                title: 'Rango Inválido',
                text: 'La fecha "Desde" no puede ser posterior a la fecha "Hasta".',
                type: 'warning'
            });
            return;
        }
    }
});

// Limpiar marcas de error en el modal de categorías y cargar actividades si es necesario
document.getElementById('cat-select').addEventListener('change', async function() {
    FormValidator.clearMark(this);
    
    const form = document.getElementById('form-reporte-cat');
    const reportType = form.getAttribute('data-report-type');
    const activitySelect = document.getElementById('activity-select');
    
    if (reportType !== 'pruebas') {
        return;
    }
    
    if (!this.value) {
        activitySelect.value = '';
        activitySelect.innerHTML = '<option value="">— Seleccione una Categoría primero —</option>';
        return;
    }
    
    activitySelect.innerHTML = '<option value="">Cargando actividades...</option>';
    
    try {
        const response = await fetch('<?= url("/admin/reportes/pruebas/actividades") ?>?categoria_id=' + this.value);
        if (!response.ok) throw new Error('Error al cargar actividades');
        const data = await response.json();
        
        activitySelect.innerHTML = '<option value="">— Seleccione —</option>';
        if (data.length === 0) {
            activitySelect.innerHTML = '<option value="">No hay sesiones registradas</option>';
        } else {
            data.forEach(act => {
                const opt = document.createElement('option');
                opt.value = act.actividad_id;
                
                // Formatear fecha
                let dateStr = act.fecha;
                if (act.fecha) {
                    const parts = act.fecha.split('-');
                    if (parts.length === 3) {
                        dateStr = `${parts[2]}/${parts[1]}/${parts[0]}`;
                    }
                }
                opt.textContent = `${dateStr} - ${act.nombre_categoria || 'Pruebas Físicas'} (${act.total} atletas)`;
                activitySelect.appendChild(opt);
            });
        }
    } catch (err) {
        console.error(err);
        activitySelect.innerHTML = '<option value="">Error al cargar actividades</option>';
    }
});

document.getElementById('activity-select')?.addEventListener('change', function() {
    FormValidator.clearMark(this);
});
document.getElementById('r-desde').addEventListener('input', function() {
    FormValidator.clearMark(this);
});

// --- Lógica del Modal de Reporte de Asistencia Individual (Atleta) ---
function openModalAsistAtleta(atletaId, nombreCompleto) {
    const modal = document.getElementById('modal-asistencia-atleta');
    const form = document.getElementById('form-asistencia-atleta');
    document.getElementById('asist-atleta-nombre').textContent = nombreCompleto;
    
    // Configurar acción del formulario dinámicamente
    form.action = '<?= e(url('/admin/reportes/asistencia/atleta/')) ?>' + atletaId;
    
    // Limpiar campos y errores anteriores
    document.getElementById('asist-desde').value = '';
    document.getElementById('asist-hasta').value = '';
    FormValidator.clearMark(document.getElementById('asist-desde'));
    FormValidator.clearMark(document.getElementById('asist-hasta'));

    modal.style.display = 'flex';
}

function closeModalAsistAtleta() {
    document.getElementById('modal-asistencia-atleta').style.display = 'none';
}

// Cierra modal al hacer click fuera del contenedor
document.getElementById('modal-asistencia-atleta').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModalAsistAtleta();
    }
});

// Validación del formulario de reporte de asistencia de atleta para asegurar rango lógico e interdependencia de fechas
document.getElementById('form-asistencia-atleta').addEventListener('submit', function(e) {
    const desde = document.getElementById('asist-desde');
    const hasta = document.getElementById('asist-hasta');

    // Validación de interdependencia: si se selecciona una, la otra es obligatoria
    if ((desde.value && !hasta.value) || (!desde.value && hasta.value)) {
        e.preventDefault();
        if (!desde.value) FormValidator.markError(desde);
        if (!hasta.value) FormValidator.markError(hasta);
        CadaModal.alert({
            title: 'Fechas Incompletas',
            text: 'Si selecciona una fecha de inicio o fin, debe especificar ambas fechas para definir el rango.',
            type: 'warning'
        });
        return;
    }

    if (desde.value || hasta.value) {
        const today = new Date().toISOString().split('T')[0];
        if (desde.value && (desde.value < '2019-01-01' || desde.value > today)) {
            e.preventDefault();
            FormValidator.markError(desde);
            CadaModal.alert({
                title: 'Fecha Inválida',
                text: 'La fecha "Desde" debe estar entre el 01/01/2019 y el día de hoy.',
                type: 'warning'
            });
            return;
        }
        if (hasta.value && (hasta.value < '2019-01-01' || hasta.value > today)) {
            e.preventDefault();
            FormValidator.markError(hasta);
            CadaModal.alert({
                title: 'Fecha Inválida',
                text: 'La fecha "Hasta" debe estar entre el 01/01/2019 y el día de hoy.',
                type: 'warning'
            });
            return;
        }
        if (desde.value && hasta.value && desde.value > hasta.value) {
            e.preventDefault();
            FormValidator.markError(desde);
            FormValidator.markError(hasta);
            CadaModal.alert({
                title: 'Rango Inválido',
                text: 'La fecha "Desde" no puede ser posterior a la fecha "Hasta".',
                type: 'warning'
            });
            return;
        }
    }
});

// Limpiar marcas de error al interactuar en el modal del atleta
document.getElementById('asist-desde').addEventListener('input', function() {
    FormValidator.clearMark(this);
    FormValidator.clearMark(document.getElementById('asist-hasta'));
});
document.getElementById('asist-hasta').addEventListener('input', function() {
    FormValidator.clearMark(this);
    FormValidator.clearMark(document.getElementById('asist-desde'));
});

// Inicializar paginación al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    filterAtletas();
    if ($searchUsuario) {
        filterUsuarios();
    }
});

// Lógica para compartir reportes por WhatsApp en Centro de Reportes
document.addEventListener('click', async function (e) {
    const shareBtn = e.target.closest('.btn-share-report');
    if (!shareBtn) return;
    
    const pdfUrl = shareBtn.getAttribute('data-url');
    const filename = shareBtn.getAttribute('data-filename');
    const originalText = shareBtn.innerHTML;
    
    shareBtn.disabled = true;
    shareBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i>...';
    
    try {
        const response = await fetch(pdfUrl);
        if (!response.ok) throw new Error('No se pudo descargar el reporte PDF.');
        
        const blob = await response.blob();
        const file = new File([blob], filename, { type: 'application/pdf' });
        
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({
                files: [file],
                title: 'Ficha CADA',
                text: 'Te comparto el reporte del Club Atlético Deportivo Acarigua (CADA)'
            });
        } else {
            const shareText = "Hola, te comparto el reporte de CADA: " + encodeURIComponent(window.location.origin + pdfUrl);
            const whatsappUrl = "https://api.whatsapp.com/send?text=" + shareText;
            window.open(whatsappUrl, '_blank');
        }
    } catch (error) {
        console.error('Error al compartir:', error);
        const shareText = "Hola, te comparto el reporte de CADA: " + encodeURIComponent(window.location.origin + pdfUrl);
        const whatsappUrl = "https://api.whatsapp.com/send?text=" + shareText;
        window.open(whatsappUrl, '_blank');
    } finally {
        shareBtn.disabled = false;
        shareBtn.innerHTML = originalText;
    }
});
</script>

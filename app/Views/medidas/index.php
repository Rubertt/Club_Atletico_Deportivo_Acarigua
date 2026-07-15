<?php /** @var array $pag */ ?>
<div class="page-header">
    <div>
        <h1>Antropometría y Composición</h1>
        <div class="subtitle">Seguimiento físico y mediciones de rendimiento de los atletas</div>
    </div>
</div>

<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th style="padding-left: 24px; width: 60px;">Atleta</th>
                <th>Nombre Completo</th>
                <th>Documento ID</th>
                <th>Categoría</th>
                <th>Última Medición</th>
                <th style="text-align: right; padding-right: 24px;">Acción</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($pag['data'] as $a): ?>
            <tr>
                <td style="padding-left: 24px;">
                    <?php if (!empty($a['foto'])): ?>
                        <div style="width: 44px; height: 44px; padding: 2px; border: 1px solid var(--color-border); border-radius: 50%;">
                            <img src="<?= e(url($a['foto'])) ?>" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                        </div>
                    <?php else: ?>
                        <div class="avatar-placeholder" style="width: 44px; height: 44px; font-size: 14px;">
                            <?= e(mb_substr($a['nombre'], 0, 1) . mb_substr($a['apellido'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="font-weight: 600; color: var(--color-text);"><?= e($a['nombre'] . ' ' . $a['apellido']) ?></div>
                </td>
                <td><span style="font-size: 13px; color: var(--color-text-muted);"><?= e($a['cedula'] ?? '—') ?></span></td>
                <td>
                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: var(--color-bg-alt); border-radius: 12px; font-size: 13px; font-weight: 500;">
                        <i class="ph ph-shield-chevron text-muted"></i> <?= e($a['nombre_categoria'] ?? 'Sin Categoría') ?>
                    </span>
                </td>
                <td>
                    <span style="font-size: 13px; color: var(--color-text-muted);">
                        <i class="ph ph-calendar"></i> <?= !empty($a['ultima_medicion']) ? e(date('d/m/Y', strtotime($a['ultima_medicion']))) : 'Sin registros' ?>
                    </span>
                </td>
                <td style="text-align: right; padding-right: 24px;">
                    <a href="<?= e(url("/admin/medidas/atleta/{$a['atleta_id']}")) ?>" class="btn btn-sm btn-outline">
                        <i class="ph ph-chart-line-up"></i> Ver / Registrar
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($pag['data'])): ?>
            <tr>
                <td colspan="6" class="text-center text-muted" style="padding:48px">
                    <i class="ph ph-user-focus" style="font-size: 48px; opacity: 0.2; display: block; margin-bottom: 12px;"></i>
                    No se encontraron atletas activos.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

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
            <?php else: ?>
                <li><a href="<?= e(url('/admin/medidas?page=1')) ?>"><i class="ph ph-caret-double-left"></i></a></li>
            <?php endif; ?>

            <!-- Botón < (Anterior) -->
            <?php if ($currentPage === 1): ?>
                <li class="disabled"><span><i class="ph ph-caret-left"></i></span></li>
            <?php else: ?>
                <li><a href="<?= e(url('/admin/medidas?page=' . ($currentPage - 1))) ?>"><i class="ph ph-caret-left"></i></a></li>
            <?php endif; ?>

            <!-- Números de Página -->
            <?php foreach ($pagesToShow as $p): ?>
                <?php if ($p === '...'): ?>
                    <li class="disabled"><span>...</span></li>
                <?php elseif ($p === $currentPage): ?>
                    <li class="active"><span><?= $p ?></span></li>
                <?php else: ?>
                    <li><a href="<?= e(url('/admin/medidas?page=' . $p)) ?>"><?= $p ?></a></li>
                <?php endif; ?>
            <?php endforeach; ?>

            <!-- Botón > (Siguiente) -->
            <?php if ($currentPage === $totalPages): ?>
                <li class="disabled"><span><i class="ph ph-caret-right"></i></span></li>
            <?php else: ?>
                <li><a href="<?= e(url('/admin/medidas?page=' . ($currentPage + 1))) ?>"><i class="ph ph-caret-right"></i></a></li>
            <?php endif; ?>

            <!-- Botón >> (Último) -->
            <?php if ($currentPage === $totalPages): ?>
                <li class="disabled"><span><i class="ph ph-caret-double-right"></i></span></li>
            <?php else: ?>
                <li><a href="<?= e(url('/admin/medidas?page=' . $totalPages)) ?>"><i class="ph ph-caret-double-right"></i></a></li>
            <?php endif; ?>
        </ul>
    </div>
<?php endif; ?>

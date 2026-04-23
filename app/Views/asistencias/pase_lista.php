<?php /** @var array $categorias @var array $entrenadores */ ?>
<div class="page-header">
    <div><h1>Pase de lista</h1><div class="subtitle">Registra la asistencia del día</div></div>
    <a href="<?= e(url('/admin/asistencia')) ?>" class="btn btn-ghost">← Volver</a>
</div>

<form method="POST" action="<?= e(url('/admin/asistencia/pase')) ?>" class="card">
    <?= csrf_field() ?>

    <div class="form-row-3">
        <div class="form-group">
            <label class="form-label"><span class="required">*</span> Categoría</label>
            <select id="sel-cat" class="form-control" required>
                <option value="">Selecciona...</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= (int) $c['categoria_id'] ?>"><?= e($c['nombre_categoria']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label"><span class="required">*</span> Fecha</label>
            <input type="date" name="fecha_evento" class="form-control" required value="<?= e(date('Y-m-d')) ?>">
        </div>
        <div class="form-group">
            <label class="form-label"><span class="required">*</span> Tipo de evento</label>
            <select name="tipo_evento" class="form-control" required>
                <?php foreach (TIPO_EVENTO as $op): ?>
                    <option value="<?= e($op) ?>"><?= e($op) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label"><span class="required">*</span> Entrenador responsable</label>
        <select name="entrenador_id" class="form-control" required>
            <option value="">Selecciona...</option>
            <?php foreach ($entrenadores as $e): ?>
                <option value="<?= (int) $e['plantel_id'] ?>"><?= e($e['nombre'] . ' ' . $e['apellido']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="atletas-wrap" style="margin-top:16px;"></div>

    <div class="flex gap mt" style="justify-content:flex-end;">
        <button type="submit" class="btn btn-primary" id="btn-guardar" disabled>Guardar asistencia</button>
    </div>
</form>

<script>
(function () {
    const $cat = document.getElementById('sel-cat');
    const $wrap = document.getElementById('atletas-wrap');
    const $btn = document.getElementById('btn-guardar');

    $cat.addEventListener('change', async () => {
        const id = $cat.value;
        $wrap.innerHTML = '';
        $btn.disabled = true;
        if (!id) return;
        try {
            const atletas = await API.get(`<?= e(url('/api/asistencia/categoria')) ?>/${id}`);
            if (!atletas.length) {
                $wrap.innerHTML = '<div class="alert alert-warning">No hay atletas activos en esta categoría.</div>';
                return;
            }
            const table = document.createElement('div');
            table.className = 'data-table-wrap';
            table.innerHTML = `
                <table class="data-table">
                    <thead><tr><th></th><th>Atleta</th><th>Cédula</th><th>Estatus</th><th>Observación</th></tr></thead>
                    <tbody>
                        ${atletas.map(a => `
                            <tr>
                                <td><input type="checkbox" name="atletas[]" value="${a.atleta_id}" checked></td>
                                <td>${a.nombre} ${a.apellido}</td>
                                <td>${a.cedula ?? '—'}</td>
                                <td>
                                    <select name="estatus[${a.atleta_id}]" class="form-control form-control-sm">
                                        <option value="Presente" selected>Presente</option>
                                        <option value="Ausente">Ausente</option>
                                        <option value="Justificado">Justificado</option>
                                    </select>
                                </td>
                                <td><input type="text" name="observaciones[${a.atleta_id}]" class="form-control" maxlength="200"></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>`;
            $wrap.appendChild(table);
            $btn.disabled = false;
        } catch (e) {
            Toast.show('Error cargando atletas', 'danger');
        }
    });
})();
</script>

<?php /** @var array $item @var array $categoria @var array $atleta @var array $posiciones @var string $action */ 
$get = fn(string $k, $d = '') => old($k, $item[$k] ?? $d);
?>
<div class="page-header">
    <div>
        <h1>Editar Asignación</h1>
        <div class="subtitle">Modifica el dorsal y posición del atleta en el grupo</div>
    </div>
    <div style="display: flex; gap: 12px; align-items: center;">
        <a href="<?= e(url('/admin/categorias/' . $categoria['categoria_id'] . '/detalles')) ?>" class="btn btn-ghost">
            <i class="ph ph-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto; padding: 0; overflow: hidden; margin-bottom: 32px;">
    <div style="padding: 24px; background: var(--color-surface); border-bottom: 1px solid var(--color-border);">
        <h3 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
            <i class="ph ph-user-gear" style="color: var(--color-primary)"></i> Parámetros de Asignación
        </h3>
    </div>

    <form id="form-editar-asig" method="POST" action="<?= e($action) ?>" style="padding: 32px;" novalidate>
        <?= csrf_field() ?>

        <?php if (has_errors() && isset(errors()['nun_dorsal'])): ?>
            <div class="alert alert-danger" style="margin-bottom: 24px;">
                <?= errors()['nun_dorsal'] ?>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label class="form-label">Atleta</label>
            <input type="text" class="form-control" style="background: var(--color-bg-alt);" value="<?= e($atleta['nombre'] . ' ' . $atleta['apellido']) ?>" readonly>
        </div>

        <div class="form-group">
            <label class="form-label">Categoría Destino</label>
            <input type="text" class="form-control" style="background: var(--color-bg-alt);" value="<?= e($categoria['nombre_categoria']) ?>" readonly>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Posición principal en la cancha en la que se desempeña el atleta." data-tooltip-pos="top">Posición de Juego Principal</label>
                <div style="position: relative;">
                    <i class="ph ph-t-shirt" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); z-index: 10;"></i>
                    <select name="posicion_principal_id" class="form-control select-posicion-principal" style="padding-left: 40px;">
                        <option value="">Sin definir</option>
                        <?php foreach ($posiciones as $pos): ?>
                            <option value="<?= (int)$pos['posicion_id'] ?>" <?= (int)$get('posicion_principal_id', '') === (int)$pos['posicion_id'] ? 'selected' : '' ?>>
                                <?= e($pos['nombre_posicion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Posición táctica alternativa. Debe ser distinta a la posición principal seleccionada." data-tooltip-pos="top">Posición de Juego Secundaria</label>
                <div style="position: relative;">
                    <i class="ph ph-t-shirt" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); z-index: 10;"></i>
                    <select name="posicion_secundaria_id" class="form-control select-posicion-secundaria" style="padding-left: 40px;">
                        <option value="">Ninguna</option>
                        <?php foreach ($posiciones as $pos): ?>
                            <option value="<?= (int)$pos['posicion_id'] ?>" <?= (int)$get('posicion_secundaria_id', '') === (int)$pos['posicion_id'] ? 'selected' : '' ?>>
                                <?= e($pos['nombre_posicion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <label class="form-label" data-tooltip="Número de camiseta del atleta. Debe ser un número del 1 al 999 y no puede repetirse dentro de esta categoría." data-tooltip-pos="top">Dorsal / Jersey Nº</label>
            <div style="position: relative;">
                <i class="ph ph-hash" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);"></i>
                <input type="number" name="nun_dorsal" class="form-control" style="padding-left: 40px;" min="1" max="999" placeholder="Ej: 10" value="<?= e($get('nun_dorsal', '')) ?>">
            </div>
        </div>

        <div style="background: var(--color-surface); margin: 32px -32px -32px; padding: 24px 32px; border-top: 1px solid var(--color-border); display: flex; justify-content: flex-end; align-items: center; gap: 16px;">
            <a href="<?= e(url('/admin/categorias/' . $categoria['categoria_id'] . '/detalles')) ?>" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary btn-lg" style="padding-left: 40px; padding-right: 40px;">
                <i class="ph ph-floppy-disk"></i> Guardar Cambios
            </button>
        </div>
    </form>
</div>

<style>
#form-editar-asig .form-control {
    height: 44px;
    background: var(--color-surface);
    border-color: var(--color-border);
    transition: all 0.2s;
}

#form-editar-asig .form-control:focus {
    background: var(--color-bg);
    box-shadow: 0 0 0 4px rgba(190, 18, 60, 0.08);
}

#form-editar-asig select.form-control {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%236b7280' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    padding-right: 40px;
    cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-editar-asig');
    const submitBtn = form.querySelector('button[type="submit"]');
    const selectPrincipal = form.querySelector('.select-posicion-principal');
    const selectSecundaria = form.querySelector('.select-posicion-secundaria');

    function updateSecondaryOptions() {
        const valPrincipal = selectPrincipal.value;

        if (valPrincipal === '') {
            selectSecundaria.disabled = true;
            selectSecundaria.value = '';
        } else {
            selectSecundaria.disabled = false;
        }

        const options = selectSecundaria.querySelectorAll('option');
        options.forEach(opt => {
            if (opt.value !== '' && opt.value === valPrincipal) {
                opt.style.display = 'none';
                opt.disabled = true;
                if (selectSecundaria.value === opt.value) {
                    selectSecundaria.value = '';
                }
            } else {
                opt.style.display = '';
                opt.disabled = false;
            }
        });
    }

    // Initialize state
    updateSecondaryOptions();

    // Listen for changes
    selectPrincipal.addEventListener('change', updateSecondaryOptions);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Guardando...';

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                if (typeof CadaToast !== 'undefined') {
                    CadaToast.success(result.message, () => {
                        window.location.href = result.redirect;
                    });
                } else {
                    window.location.href = result.redirect;
                }
            } else {
                CadaModal.alert({
                    title: 'Error de validación',
                    text: result.message || 'Ocurrió un error al actualizar la asignación.',
                    type: 'error'
                });
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        } catch (error) {
            CadaModal.alert({
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor. Inténtelo nuevamente.',
                type: 'error'
            });
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
});
</script>

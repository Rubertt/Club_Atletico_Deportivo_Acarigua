<?php /** @var array $categoria @var array $atletas @var array $posiciones @var string $action */ ?>
<div class="page-header">
    <div>
        <h1>Asignar Atletas</h1>
        <div class="subtitle">Categoría: <?= e($categoria['nombre_categoria']) ?></div>
    </div>
    <div style="display: flex; gap: 12px; align-items: center;">
        <a href="<?= e(url('/admin/categorias/' . $categoria['categoria_id'] . '/detalles')) ?>" class="btn btn-ghost">
            <i class="ph ph-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="card" style="max-width: 900px; margin: 0 auto; padding: 0; overflow: hidden; margin-bottom: 32px;">
    <div style="padding: 24px; background: var(--color-surface); border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
            <i class="ph ph-users" style="color: var(--color-primary)"></i> Atletas Aptos Disponibles
        </h3>
        <div style="font-size: 13px; color: var(--color-text-muted); font-weight: 500;">
            Requisitos: <span class="badge badge-outline"><?= $categoria['sexo_categoria'] === 'F' ? 'Femenino' : ($categoria['sexo_categoria'] === 'M' ? 'Masculino' : 'Mixto') ?></span>
            <span class="badge badge-outline"><?= (int)$categoria['edad_min'] ?> a <?= (int)$categoria['edad_max'] ?> años</span>
        </div>
    </div>

    <form id="form-asignar" method="POST" action="<?= e($action) ?>" style="padding: 0;">
        <?= csrf_field() ?>

        <?php if (has_errors() && isset(errors()['dorsales'])): ?>
            <div style="margin: 24px 24px 0; background:rgba(239, 68, 68, 0.08); border:1px solid var(--color-danger); color:var(--color-danger); padding:16px; border-radius:8px; font-size:14px; line-height: 1.5;">
                <i class="ph ph-warning-circle" style="vertical-align: middle; margin-right: 6px;"></i>
                <?= errors()['dorsales'] ?>
            </div>
        <?php endif; ?>

        <div style="padding: 24px 24px 0;">
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Categoría Destino</label>
                <input type="text" class="form-control" style="background: var(--color-bg-alt);" value="<?= e($categoria['nombre_categoria']) ?>" readonly>
            </div>
        </div>

        <div class="data-table-wrap" style="border: none; border-radius: 0; border-top: 1px solid var(--color-border); max-height: 500px; overflow-y: auto;">
            <table class="data-table" style="margin: 0; border: none;">
                <thead style="background: var(--color-bg-alt); position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th style="width: 48px; text-align: center; padding-left: 24px;">
                            <input type="checkbox" id="check-all" style="transform: scale(1.2); cursor: pointer;" title="Seleccionar todos">
                        </th>
                        <th style="width: 52px;"></th>
                        <th>Atleta</th>
                        <th>Fecha Nac. (Edad)</th>
                        <th data-tooltip="Posición principal en la cancha en la que se desempeña el atleta." data-tooltip-pos="top">Posición Principal</th>
                        <th data-tooltip="Posición táctica alternativa. Debe ser distinta a la posición principal seleccionada." data-tooltip-pos="top">Posición Secundaria</th>
                        <th style="width: 120px; padding-right: 24px;" data-tooltip="Número de camiseta del atleta. Debe ser un número del 1 al 999 y no puede repetirse dentro de esta categoría." data-tooltip-pos="top">Dorsal</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($atletas)): ?>
                    <tr>
                        <td colspan="7" style="padding: 64px 24px; text-align: center;">
                            <i class="ph ph-user-circle-minus text-muted" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.5;"></i>
                            <h3 class="text-muted" style="margin: 0 0 8px;">No hay atletas candidatos</h3>
                            <p class="text-muted" style="font-size: 14px; max-width: 450px; margin: 0 auto;">No se encontraron atletas activos sin categoría que cumplan con las condiciones de edad y sexo de este grupo.</p>
                        </td>
                    </tr>
                <?php else: foreach ($atletas as $a): 
                    $isDisabled = in_array((int)$a['estatus'], [0, 3], true);
                ?>
                    <tr class="atleta-row" style="<?= $isDisabled ? 'opacity: 0.65; background: var(--color-bg-alt);' : '' ?>">
                        <td style="text-align: center; padding-left: 24px;">
                            <input type="checkbox" name="selected_atletas[]" value="<?= (int)$a['atleta_id'] ?>" class="atleta-checkbox" style="transform: scale(1.2); cursor: <?= $isDisabled ? 'not-allowed' : 'pointer' ?>;" <?= $isDisabled ? 'disabled' : '' ?>>
                        </td>
                        <td>
                            <?php if (!empty($a['foto'])): ?>
                                <div style="width: 38px; height: 38px; padding: 2px; border: 1px solid var(--color-border); border-radius: 50%; background: var(--color-bg);">
                                    <img src="<?= e(url($a['foto'])) ?>" class="avatar-thumb" alt="" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: block;">
                                </div>
                            <?php else: ?>
                                <div class="avatar-placeholder" style="width: 38px; height: 38px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px; border: 1px solid var(--color-primary-light);">
                                    <?= e(mb_substr($a['nombre'], 0, 1) . mb_substr($a['apellido'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--color-text); display: flex; align-items: center; gap: 6px;">
                                <?= e($a['nombre'] . ' ' . $a['apellido']) ?>
                                <?php if ((int)$a['estatus'] === 0): ?>
                                    <span class="badge badge-danger" style="font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 600;">Suspendido</span>
                                <?php elseif ((int)$a['estatus'] === 3): ?>
                                    <span class="badge badge-outline" style="font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 600; border-color: var(--color-text-muted); color: var(--color-text-muted);">Inactivo</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 13px; color: var(--color-text-muted);"><?= e(date('d/m/Y', strtotime($a['fecha_nac']))) ?> (<?= (int)$a['edad'] ?> años)</div>
                        </td>
                        <td>
                            <select name="posicion_principal_id[<?= (int)$a['atleta_id'] ?>]" class="form-control select-posicion-principal" style="height: 36px; padding: 4px 8px; font-size: 13px;" disabled>
                                <option value="">Sin definir</option>
                                <?php foreach ($posiciones as $pos): ?>
                                    <option value="<?= (int)$pos['posicion_id'] ?>"><?= e($pos['nombre_posicion']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="posicion_secundaria_id[<?= (int)$a['atleta_id'] ?>]" class="form-control select-posicion-secundaria" style="height: 36px; padding: 4px 8px; font-size: 13px;" disabled>
                                <option value="">Ninguna</option>
                                <?php foreach ($posiciones as $pos): ?>
                                    <option value="<?= (int)$pos['posicion_id'] ?>"><?= e($pos['nombre_posicion']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td style="padding-right: 24px;">
                            <input type="number" name="nun_dorsal[<?= (int)$a['atleta_id'] ?>]" class="form-control input-dorsal" min="1" max="999" style="height: 36px; padding: 4px 8px; font-size: 13px;" placeholder="Nº" disabled>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <div style="background: var(--color-surface); padding: 24px 32px; border-top: 1px solid var(--color-border); display: flex; justify-content: flex-end; align-items: center; gap: 16px;">
            <a href="<?= e(url('/admin/categorias/' . $categoria['categoria_id'] . '/detalles')) ?>" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary btn-lg" id="btn-submit-asignacion" style="padding-left: 40px; padding-right: 40px;" disabled>
                <i class="ph ph-check-circle"></i> Asignar Atletas
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkAll = document.getElementById('check-all');
    const checkboxes = document.querySelectorAll('.atleta-checkbox');
    const submitBtn = document.getElementById('btn-submit-asignacion');
    const form = document.getElementById('form-asignar');

    function updateSecondaryOptions(row) {
        const selectPrincipal = row.querySelector('.select-posicion-principal');
        const selectSecundaria = row.querySelector('.select-posicion-secundaria');
        const cb = row.querySelector('.atleta-checkbox');

        const isRowEnabled = cb ? cb.checked : true;
        const valPrincipal = selectPrincipal.value;

        if (!isRowEnabled || valPrincipal === '') {
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

    function updateRowStates() {
        let checkedCount = 0;

        checkboxes.forEach(cb => {
            const row = cb.closest('.atleta-row');
            const selectPosPrincipal = row.querySelector('.select-posicion-principal');
            const selectPosSecundaria = row.querySelector('.select-posicion-secundaria');
            const inputDorsal = row.querySelector('.input-dorsal');

            if (cb.checked) {
                checkedCount++;
                row.style.background = 'rgba(var(--color-primary-rgb, 190, 18, 60), 0.02)';
                selectPosPrincipal.disabled = false;
                inputDorsal.disabled = false;
                updateSecondaryOptions(row);
            } else {
                row.style.background = '';
                selectPosPrincipal.disabled = true;
                selectPosSecundaria.disabled = true;
                inputDorsal.disabled = true;
            }
        });

        submitBtn.disabled = checkedCount === 0;
    }

    if (checkAll) {
        checkAll.addEventListener('change', () => {
            checkboxes.forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = checkAll.checked;
                }
            });
            updateRowStates();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            if (!cb.checked && checkAll) {
                checkAll.checked = false;
            }
            updateRowStates();
        });
    });

    document.querySelectorAll('.select-posicion-principal').forEach(select => {
        select.addEventListener('change', (e) => {
            const row = e.target.closest('.atleta-row');
            updateSecondaryOptions(row);
        });
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const checkedDorsales = [];
        let hasDuplicate = false;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                const row = cb.closest('.atleta-row');
                const dorsalInput = row.querySelector('.input-dorsal');
                const val = dorsalInput.value.trim();

                if (val !== '') {
                    const numVal = parseInt(val, 10);
                    if (checkedDorsales.includes(numVal)) {
                        hasDuplicate = true;
                        dorsalInput.style.borderColor = 'var(--color-danger)';
                    } else {
                        checkedDorsales.push(numVal);
                        dorsalInput.style.borderColor = '';
                    }
                }
            }
        });

        if (hasDuplicate) {
            CadaModal.alert({
                title: 'Error de validación',
                text: 'Has ingresado dorsales duplicados para los atletas seleccionados en el formulario. Por favor, asegúrate de que cada dorsal sea único.',
                type: 'error',
                confirmText: 'Corregir'
            });
            return;
        }

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
                    text: result.message || 'Ocurrió un error al procesar la asignación.',
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

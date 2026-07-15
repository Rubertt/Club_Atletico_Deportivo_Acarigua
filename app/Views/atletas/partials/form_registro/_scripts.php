<script>
// —— Tabs & Navegación ————————————————————————————————————————————————————————
const tabs = document.querySelectorAll('.ft-tab');
const panels = document.querySelectorAll('.form-tab-panel');
const btnNext = document.getElementById('btn-next');
const btnPrev = document.getElementById('btn-prev');
const btnSubmit = document.getElementById('btn-submit');
const stepNumEl = document.getElementById('current-step-num');

let currentIdx = 0;
const ATLETA_ID = <?= json_encode($a['atleta_id'] ?? null) ?>;

function updateUI() {
    const isLast = currentIdx === tabs.length - 1;
    const isFirst = currentIdx === 0;

    // Actualizar Tabs y Paneles
    tabs.forEach((tab, i) => {
        tab.classList.toggle('active', i === currentIdx);
        panels[i].classList.toggle('active', i === currentIdx);
    });

    // Actualizar Botones
    btnPrev.style.display = isFirst ? 'none' : 'inline-flex';
    btnNext.style.display = isLast ? 'none' : 'inline-flex';
    btnSubmit.style.display = isLast ? 'inline-flex' : 'none';
    
    // Actualizar Contador
    if (stepNumEl) stepNumEl.textContent = currentIdx + 1;
}

// validarCedula definida más abajo con soporte para Partida (P)

function showError(id, msg) {
    const wrap = document.getElementById('phone-wrap-' + id);
    if (wrap) wrap.style.borderColor = msg ? 'var(--color-danger,#e53e3e)' : '';
    const inp = document.getElementById(id);
    if (inp && !wrap) inp.style.borderColor = msg ? 'var(--color-danger,#e53e3e)' : '';
}

function validateStep(idx) {
    try {
        const panel = panels[idx];
        const requiredInputs = panel.querySelectorAll('[required]');
        let isValid = true;
        let missingFields = [];
        
        requiredInputs.forEach(input => {
            if (input.style.display === 'none' || input.offsetParent === null) return;
            const fg = input.closest('.form-group');
            const labelEl = fg ? fg.querySelector('.form-label') : null;
            const label = labelEl ? labelEl.textContent.replace('*', '').trim() : (input.name || 'Campo');
            
            const wrap = input.closest('.phone-field');
            if (!input.value.trim()) {
                if (wrap) wrap.style.borderColor = 'var(--color-danger)';
                else input.style.borderColor = 'var(--color-danger)';
                missingFields.push(label);
                isValid = false;
            } else {
                if (wrap) wrap.style.borderColor = '';
                else input.style.borderColor = '';
            }
        });

        // Validaciones especiales por step
        if (idx === 0) { // Personal
            const ced = document.getElementById('cedula');
            const cedPref = document.getElementById('cedula_prefix');
            const cedInput = document.getElementById('cedula_number');
            
            if (cedPref && cedPref.value === 'N') {
                const y = document.getElementById('folio_year').value;
                const a = document.getElementById('folio_acta').value;
                if (!y || !a) {
                    showError('cedula', 'Completa el Código de Acta de Nacimiento (Año y Acta)');
                    missingFields.push('Código de Acta de Nacimiento');
                    isValid = false;
                } else {
                    if (ced && ced.value && !validarCedula(ced.value)) {
                        showError('cedula', 'Formato de Código de Acta de Nacimiento inválido (Año-Acta)');
                        missingFields.push('Código de Acta de Nacimiento (Formato)');
                        isValid = false;
                    }
                    const birthVal = panel.querySelector('[name="fecha_nacimiento"]').value;
                    if (birthVal) {
                        const birthYear = new Date(birthVal).getFullYear();
                        const certYear = parseInt(y, 10);
                        if (certYear < birthYear) {
                            showError('cedula', 'El año del acta de nacimiento no puede ser menor al año de nacimiento.');
                            missingFields.push('Año del acta de nacimiento menor al año de nacimiento');
                            isValid = false;
                        }
                    }
                }
            } else {
                if (ced && ced.value && !validarCedula(ced.value)) {
                    const docName = (cedPref && cedPref.value === 'P') ? 'Pasaporte' : 'Documento de Identidad';
                    showError('cedula', 'Formato de ' + docName.toLowerCase() + ' inválido');
                    missingFields.push(docName + ' (Formato)');
                    isValid = false;
                }
            }

            const telEl = document.getElementById('telefono_number');
            if (telEl && telEl.value && telEl.value.length !== 7) {
                showError('telefono', 'Teléfono debe tener 7 dígitos');
                missingFields.push('Teléfono Personal (Formato)');
                isValid = false;
            }
        }
        
        if (idx === 2) { // Representante
            const tced = document.getElementById('tutor_cedula');
            if (tced && tced.value && !validarCedula(tced.value)) {
                showError('tutor_cedula', 'Formato inválido');
                missingFields.push('Documento de Identidad del Representante (Formato)');
                isValid = false;
            }
            
            const ttel = document.getElementById('tutor_telefono_number');
            if (ttel && ttel.value && ttel.value.length !== 7) {
                showError('tutor_telefono', 'Teléfono debe tener 7 dígitos');
                missingFields.push('Teléfono del Representante (Formato)');
                isValid = false;
            }
        }

        if (!isValid) {
            if (typeof CadaModal !== 'undefined' && CadaModal.alert) {
                CadaModal.alert({
                    title: 'Campos Requeridos',
                    text: 'Debes completar los siguientes campos: <br><br><strong>' + missingFields.join(', ') + '</strong>',
                    type: 'warning'
                });
            }
        }

        return isValid;
    } catch (err) {
        console.error("Error en validateStep:", err);
        CadaModal.alert({ title: 'Error Interno', text: 'Ocurrió un error al validar: ' + err.message, type: 'danger' });
        return false;
    }
}

// —— Validación Asíncrona (Fetch API) ————————————————————————————————————————
async function validateStepAsync(idx) {
    // 1. Validar localmente primero
    if (!validateStep(idx)) {
        return false;
    }

    // 2. Preparar FormData para Fetch
    const formData = new FormData(mainForm);
    formData.append('step', idx);
    formData.append('atleta_id', ATLETA_ID || '');

    // 3. Determinar qué botón deshabilitar y mostrar spinner
    const isLast = idx === tabs.length - 1;
    const btnToDisable = isLast ? btnSubmit : btnNext;
    const originalText = btnToDisable.innerHTML;

    btnToDisable.disabled = true;
    btnToDisable.innerHTML = '<i class="ph ph-spinner spinner"></i> Validando...';

    try {
        const response = await fetch('<?= e(url('/admin/atletas/validar-paso')) ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();
        btnToDisable.disabled = false;
        btnToDisable.innerHTML = originalText;

        if (!response.ok || !result.success) {
            const errors = result.errors || {};
            const errorMsgs = [];
            for (const key in errors) {
                errorMsgs.push(errors[key]);
                // Mostrar error visualmente
                showError(key, errors[key]);
                
                // Pintar borde rojo
                const input = document.getElementsByName(key)[0] || document.getElementById(key);
                if (input) {
                    const wrap = input.closest('.phone-field');
                    if (wrap) wrap.style.borderColor = 'var(--color-danger)';
                    else input.style.borderColor = 'var(--color-danger)';
                }
            }

            if (typeof CadaModal !== 'undefined' && CadaModal.alert) {
                CadaModal.alert({
                    title: 'Alerta de Validación',
                    text: errorMsgs.join('<br>'),
                    type: 'error'
                });
            } else {
                alert(errorMsgs.join('\n'));
            }
            return false;
        }

        return true;
    } catch (err) {
        console.error(err);
        btnToDisable.disabled = false;
        btnToDisable.innerHTML = originalText;
        if (typeof CadaModal !== 'undefined' && CadaModal.alert) {
            CadaModal.alert({
                title: 'Error de Conexión',
                text: 'Ocurrió un error al conectarse con el servidor para validar los datos.',
                type: 'danger'
            });
        }
        return false;
    }
}

// Click en botones con soporte asíncrono
btnNext.addEventListener('click', async () => {
    if (await validateStepAsync(currentIdx)) {
        if (currentIdx < tabs.length - 1) {
            currentIdx++;
            updateUI();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
});

btnPrev.addEventListener('click', () => {
    if (currentIdx > 0) {
        currentIdx--;
        updateUI();
    }
});

// Manejador del submit con validación asíncrona del último paso
const mainForm = document.querySelector('.af-card');
if (mainForm) {
    mainForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validar el paso actual asíncronamente
        const isValid = await validateStepAsync(currentIdx);
        if (isValid) {
            // Si es válido, enviamos el formulario programáticamente
            this.submit();
        }
    });
}


// --- Lógica de cascada para direcciones ---
const selEstado = document.getElementById('sel-estado');
const selMunicipio = document.getElementById('sel-municipio');
const selParroquia = document.getElementById('sel-parroquia');

function loadEstados() {
    // Asumimos 232 = Venezuela
    fetch('<?= e(url('/api/direcciones/estados/232')) ?>')
        .then(res => res.json())
        .then(data => {
            selEstado.innerHTML = '<option value="">— Seleccione Estado —</option>';
            data.forEach(est => {
                let selected = (parseInt(selEstado.dataset.current) === est.estado_id) ? 'selected' : '';
                selEstado.innerHTML += `<option value="${est.estado_id}" ${selected}>${est.estado}</option>`;
            });
            if (selEstado.value) loadMunicipios(selEstado.value);
        })
        .catch(console.error);
}

function loadMunicipios(estadoId) {
    if (!estadoId) {
        selMunicipio.innerHTML = '<option value="">— Seleccione Municipio —</option>';
        selMunicipio.disabled = true;
        selParroquia.innerHTML = '<option value="">— Seleccione Parroquia —</option>';
        selParroquia.disabled = true;
        return;
    }
    selMunicipio.disabled = false;
    fetch('<?= e(url('/api/direcciones/municipios')) ?>/' + estadoId)
        .then(res => res.json())
        .then(data => {
            selMunicipio.innerHTML = '<option value="">— Seleccione Municipio —</option>';
            data.forEach(mun => {
                let selected = (parseInt(selMunicipio.dataset.current) === parseInt(mun.municipio_id)) ? 'selected' : '';
                selMunicipio.innerHTML += `<option value="${mun.municipio_id}" ${selected}>${mun.municipio}</option>`;
            });
            if (selMunicipio.value) loadParroquias(selMunicipio.value);
        })
        .catch(console.error);
}

function loadParroquias(municipioId) {
    if (!municipioId) {
        selParroquia.innerHTML = '<option value="">— Seleccione Parroquia —</option>';
        selParroquia.disabled = true;
        return;
    }
    selParroquia.disabled = false;
    fetch('<?= e(url('/api/direcciones/parroquias')) ?>/' + municipioId)
        .then(res => res.json())
        .then(data => {
            selParroquia.innerHTML = '<option value="">— Seleccione Parroquia —</option>';
            data.forEach(par => {
                let selected = (parseInt(selParroquia.dataset.current) === parseInt(par.parroquia_id)) ? 'selected' : '';
                selParroquia.innerHTML += `<option value="${par.parroquia_id}" ${selected}>${par.parroquia}</option>`;
            });
        })
        .catch(console.error);
}

selEstado.addEventListener('change', (e) => {
    selMunicipio.dataset.current = '0';
    selParroquia.dataset.current = '0';
    loadMunicipios(e.target.value);
});
selMunicipio.addEventListener('change', (e) => {
    selParroquia.dataset.current = '0';
    loadParroquias(e.target.value);
});

// Inicializar cascada de direcciones
loadEstados();


// Botón Limpiar
const btnReset = document.getElementById('btn-reset');
const form = document.querySelector('.af-card');

if (btnReset) {
    btnReset.addEventListener('click', () => {
        if (typeof CadaModal !== 'undefined') {
            CadaModal.confirm({
                title: 'Limpiar Formulario',
                text: '¿Estás seguro de que deseas limpiar todo el formulario y restablecer todos los campos?',
                type: 'warning',
                confirmText: 'Sí, limpiar'
            }).then(confirmed => {
                if (confirmed) {
                    // Reset campos nativos
                    form.reset();
                    
                    // Reset widgets custom
                    document.querySelectorAll('.field-error').forEach(el => el.style.display = 'none');
                    document.querySelectorAll('.phone-field').forEach(el => el.style.borderColor = '');
                    
                    // Reset Foto
                    if (fotoPreviewCont) {
                        fotoPreviewCont.style.display = 'none';
                        fotoPreviewImg.src = '';
                        fotoLabel.classList.remove('has-file');
                        fotoLabel.querySelector('span').textContent = 'Subir foto';
                    }
                    
                    // Volver al inicio
                    currentIdx = 0;
                    updateUI();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        } else {
            CadaModal.confirm({
                title: '¿Limpiar Formulario?',
                text: '¿Estás seguro de que deseas limpiar todo el formulario? Se perderán los datos ingresados.',
                type: 'warning',
                confirmText: 'Sí, Limpiar',
                cancelText: 'Cancelar'
            }).then(confirmed => {
                if (confirmed) {
                    // Reset campos nativos
                    form.reset();
                    
                    // Reset widgets custom
                    document.querySelectorAll('.field-error').forEach(el => el.style.display = 'none');
                    document.querySelectorAll('.phone-field').forEach(el => el.style.borderColor = '');
                    
                    // Reset Foto
                    if (fotoPreviewCont) {
                        fotoPreviewCont.style.display = 'none';
                        fotoPreviewImg.src = '';
                        fotoLabel.classList.remove('has-file');
                        fotoLabel.querySelector('span').textContent = 'Subir foto';
                    }
                    
                    // Volver al inicio
                    currentIdx = 0;
                    updateUI();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        }
    });
}

// Inicializar
updateUI();

// —— Documento de Identidad, Pasaporte y Acta de Nacimiento ——————————————————————————————————————————————
const CEDULA_REGEX = /^[VE]-\d{6,8}$/i;
const PASAPORTE_REGEX = /^P-[A-Z0-9]{5,15}$/i;
const PARTIDA_REGEX = /^N-\d{4}-[A-Z0-9]{1,6}-[A-Z0-9]{1,3}$/i;

function formatCedulaNumber(digits) {
    return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function validarCedula(val) {
    if (!val) return true;
    if (val.startsWith('N-') || val.startsWith('n-')) {
        return PARTIDA_REGEX.test(val);
    }
    if (val.startsWith('P-') || val.startsWith('p-')) {
        // Si el pasaporte es puramente numérico, validar longitud
        const digitsOnly = val.substring(2).replace(/\./g, '');
        if (/^\d+$/.test(digitsOnly)) {
            return digitsOnly.length >= 6 && digitsOnly.length <= 8;
        }
        return PASAPORTE_REGEX.test(val);
    }
    const cleanVal = val.replace(/\./g, '');
    const digitsOnly = cleanVal.replace(/\D/g, '');
    if (digitsOnly.length < 6 || digitsOnly.length > 8) {
        return false;
    }
    return CEDULA_REGEX.test(cleanVal);
}

function showError(id, msg) {
    const wrap = document.getElementById('phone-wrap-' + id);
    if (wrap) wrap.style.borderColor = msg ? 'var(--color-danger,#e53e3e)' : '';
    const inp = document.getElementById(id);
    if (inp && !wrap) inp.style.borderColor = msg ? 'var(--color-danger,#e53e3e)' : '';
}
function clearError(id) { showError(id, ''); }

function setupCedulaWidget(prefixId, numberId, hiddenId, errorKey) {
    const prefixEl = document.getElementById(prefixId);
    const numberEl = document.getElementById(numberId);
    const hiddenEl = document.getElementById(hiddenId);
    if (!prefixEl || !numberEl || !hiddenEl) return;

    // Folio elements (solo para el widget del atleta, si existen)
    const isAthlete = (prefixId === 'cedula_prefix');
    const folioInputs = isAthlete ? document.getElementById('folio_inputs') : null;
    const fYear = isAthlete ? document.getElementById('folio_year') : null;
    const fActa = isAthlete ? document.getElementById('folio_acta') : null;
    const fFolio = isAthlete ? document.getElementById('folio_folio') : null;

    function sync() {
        let val = '';
        if (prefixEl.value === 'N' && folioInputs) {
            let y = fYear.value.replace(/\D/g, '').substring(0, 4);
            let a = fActa.value.replace(/[^a-zA-Z0-9]/g, '').substring(0, 6).toUpperCase();
            let f = fFolio.value.replace(/[^a-zA-Z0-9]/g, '').substring(0, 3).toUpperCase();
            fYear.value = y; fActa.value = a; fFolio.value = f;
            val = (y||a||f) ? `${y}-${a}-${f}` : '';
        } else if (prefixEl.value === 'P') {
            let raw = numberEl.value.replace(/[^A-Z0-9]/gi, '').toUpperCase();
            let digitsOnly = raw.replace(/\./g, '');
            if (/^\d+$/.test(digitsOnly)) {
                numberEl.value = formatCedulaNumber(digitsOnly);
                val = digitsOnly;
            } else {
                numberEl.value = raw;
                val = raw;
            }
        } else {
            let digits = numberEl.value.replace(/\D/g, '').substring(0, 8);
            numberEl.value = formatCedulaNumber(digits);
            val = digits;
        }
        hiddenEl.value = val.length ? prefixEl.value + '-' + val : '';
    }
    
    // Si ya viene un valor cargado
    if (hiddenEl.value) {
        let raw = hiddenEl.value;
        let prefix = 'V', num = raw;
        if (raw.includes('-')) {
            let parts = raw.split('-');
            prefix = parts[0].toUpperCase();
            num = parts.slice(1).join('-') || '';
        } else {
            let firstChar = raw.charAt(0).toUpperCase();
            if (['V', 'E', 'P', 'N'].includes(firstChar)) {
                prefix = firstChar;
                num = raw.substring(1);
            }
        }
        prefixEl.value = prefix;
        if (prefix === 'N') {
            if (folioInputs) {
                let parts = num.split('-');
                if (fYear) fYear.value = parts[0] || '';
                if (fActa) fActa.value = parts[1] || '';
                if (fFolio) fFolio.value = parts[2] || '';
            }
        } else {
            let cleanNum = num.replace(/[^A-Z0-9]/gi, '').toUpperCase();
            if (prefix === 'V' || prefix === 'E' || (prefix === 'P' && /^\d+$/.test(cleanNum.replace(/\./g, '')))) {
                numberEl.value = formatCedulaNumber(cleanNum.replace(/\D/g, '').substring(0, 8));
            } else {
                numberEl.value = cleanNum;
            }
        }
    }
    sync();

    numberEl.addEventListener('input', () => { sync(); clearError(errorKey); });
    if (folioInputs) {
        fYear.addEventListener('input', () => { sync(); clearError(errorKey); });
        fActa.addEventListener('input', () => { sync(); clearError(errorKey); });
        fFolio?.addEventListener('input', () => { sync(); clearError(errorKey); });
    }

    prefixEl.addEventListener('change', () => {
        numberEl.value = '';
        hiddenEl.value = '';
        if (prefixEl.value === 'N') {
            numberEl.style.display = 'none';
            if (folioInputs) {
                folioInputs.style.display = 'flex';
                fYear.value = ''; fActa.value = ''; fFolio.value = '';
                fYear.focus();
            } else {
                numberEl.style.display = 'block';
                numberEl.placeholder = "Cód. Partida";
                numberEl.maxLength = 15;
                numberEl.focus();
            }
        } else {
            if (folioInputs) folioInputs.style.display = 'none';
            numberEl.style.display = 'block';
            if (prefixEl.value === 'P') {
                numberEl.placeholder = "ABC123456";
                numberEl.maxLength = 15;
            } else {
                numberEl.placeholder = "12.345.678";
                numberEl.maxLength = 10; // Acomodar puntos ej: 12.345.678 (10 chars)
            }
            numberEl.focus();
        }
        sync();
        clearError(errorKey);
    });
    
    const blurHandler = () => {
        const val = hiddenEl.value;
        if (val && !validarCedula(val)) {
            if (prefixEl.value === 'N') {
                showError(errorKey, 'Completa Año, Acta y Folio (Formato Año-Acta-Folio)');
            } else if (prefixEl.value === 'P') {
                showError(errorKey, 'Formato de Pasaporte inválido.');
            } else {
                showError(errorKey, 'Formato inválido. Ej: ' + prefixEl.value + '-12.345.678');
            }
        } else {
            clearError(errorKey);
        }
    };

    numberEl.addEventListener('blur', blurHandler);
    if (folioInputs) {
        fYear.addEventListener('blur', blurHandler);
        fActa.addEventListener('blur', blurHandler);
        fFolio?.addEventListener('blur', blurHandler);
    }
    
    numberEl.addEventListener('focus', () => clearError(errorKey));
    if (folioInputs) {
        fYear.addEventListener('focus', () => clearError(errorKey));
        fActa.addEventListener('focus', () => clearError(errorKey));
        fFolio?.addEventListener('focus', () => clearError(errorKey));
    }
}

// Inicializar widgets de Documento de Identidad
setupCedulaWidget('cedula_prefix', 'cedula_number', 'cedula', 'cedula');
setupCedulaWidget('tutor_cedula_prefix', 'tutor_cedula_number', 'tutor_cedula', 'tutor_cedula');

// —— Preview de Foto ——————————————————————————————————————————————————————————
const fotoInput = document.getElementById('foto-input');
const fotoLabel = document.getElementById('foto-label');
const fotoPreviewCont = document.getElementById('foto-preview-container');
const fotoPreviewImg = document.getElementById('foto-preview-img');
const btnRemoveFoto = document.getElementById('btn-remove-foto');

if (fotoInput) {
    fotoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                fotoPreviewImg.src = e.target.result;
                fotoPreviewCont.style.display = 'block';
                fotoLabel.classList.add('has-file');
                fotoLabel.querySelector('span').textContent = 'Cambiar foto';
            }
            reader.readAsDataURL(file);

            // Eliminar flag de borrado si se sube una nueva
            let deleteInput = document.getElementById('delete-foto-flag');
            if (deleteInput) deleteInput.remove();
        }
    });

    btnRemoveFoto.addEventListener('click', (e) => {
        e.preventDefault();
        fotoInput.value = '';
        fotoPreviewCont.style.display = 'none';
        fotoLabel.classList.remove('has-file');
        fotoLabel.querySelector('span').textContent = 'Subir foto';

        // Notificar al servidor que se borre la foto si es edición
        let deleteInput = document.getElementById('delete-foto-flag');
        if (!deleteInput) {
            deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'eliminar_foto';
            deleteInput.id = 'delete-foto-flag';
            deleteInput.value = '1';
            fotoInput.parentNode.appendChild(deleteInput);
        }
    });
}

// —— Widget teléfono ——————————————————————————————————————————————————————————
function setupPhoneWidget(prefixId, numberId, hiddenId, errorKey) {
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

    numberEl.addEventListener('input', () => { sync(); clearError(errorKey); });
    prefixEl.addEventListener('change', () => { sync(); clearError(errorKey); numberEl.focus(); });
    numberEl.addEventListener('blur', () => {
        const num = numberEl.value;
        if (num && num.length !== 7) showError(errorKey, 'Ingresa 7 dígitos');
        else clearError(errorKey);
    });
    numberEl.addEventListener('focus', () => clearError(errorKey));
}

setupPhoneWidget('telefono_prefix',     'telefono_number',     'telefono',     'telefono');
setupPhoneWidget('tutor_telefono_prefix', 'tutor_telefono_number', 'tutor_telefono', 'tutor_telefono');

// --- Lógica Dinámica de Documento de Identidad y Representante por Edad ---
function updateDynamicRequirements() {
    const dobInput = document.querySelector('input[name="fecha_nacimiento"]');
    if (!dobInput || !dobInput.value) return;

    const dob = new Date(dobInput.value);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;

    // 1. Documento de Identidad obligatorio si es mayor de 9 años
    const cedInput = document.getElementById('cedula_number');
    const cedLabel = document.getElementById('label-cedula');
    if (age > 9) {
        if (cedInput) cedInput.setAttribute('required', 'true');
        if (cedLabel && !cedLabel.querySelector('.required')) {
            const wrapper = cedLabel.querySelector('.label-text-wrapper') || cedLabel;
            wrapper.insertAdjacentHTML('afterbegin', '<span class="required">*</span> ');
        }
    } else {
        if (cedInput) cedInput.removeAttribute('required');
        if (cedLabel) {
            const reqSpan = cedLabel.querySelector('.required');
            if (reqSpan) reqSpan.remove();
        }
    }

    const isAdult = age >= 18;

    // 2. Teléfono personal obligatorio si es mayor de edad (Adulto)
    const telInput = document.getElementById('telefono_number');
    const telLabel = document.getElementById('label-telefono');
    if (isAdult) {
        if (telInput) telInput.setAttribute('required', 'true');
        if (telLabel && !telLabel.querySelector('.required')) {
            const wrapper = telLabel.querySelector('.label-text-wrapper') || telLabel;
            wrapper.insertAdjacentHTML('afterbegin', '<span class="required">*</span> ');
        }
    } else {
        if (telInput) telInput.removeAttribute('required');
        if (telLabel) {
            const reqSpan = telLabel.querySelector('.required');
            if (reqSpan) reqSpan.remove();
        }
    }

    // 3. Datos del representante no obligatorios si es mayor de edad
    const tutorFields = [
        'tutor_nombres',
        'tutor_apellidos',
        'tutor_cedula_number',
        'tutor_telefono_number',
        'tutor_relacion'
    ];

    tutorFields.forEach(id => {
        const inputEl = document.getElementById(id) || document.querySelector(`[name="${id}"]`);
        if (inputEl) {
            const fg = inputEl.closest('.form-group');
            const label = fg ? fg.querySelector('.form-label') : null;
            if (isAdult) {
                inputEl.removeAttribute('required');
                if (label) {
                    const reqSpan = label.querySelector('.required');
                    if (reqSpan) reqSpan.remove();
                }
            } else {
                inputEl.setAttribute('required', 'true');
                if (label && !label.querySelector('.required')) {
                    const wrapper = label.querySelector('.label-text-wrapper') || label;
                    wrapper.insertAdjacentHTML('afterbegin', '<span class="required">*</span> ');
                }
            }
        }
    });
}

const dobEl = document.querySelector('input[name="fecha_nacimiento"]');
if (dobEl) {
    dobEl.addEventListener('change', updateDynamicRequirements);
    dobEl.addEventListener('input', updateDynamicRequirements);
    // Ejecutar al cargar para atletas existentes o reediciones
    updateDynamicRequirements();
}

// —— Lógica de Representante Existente ————————————————————————————————————————
const selRepresentante = document.getElementById('sel-representante');
if (selRepresentante) {
    selRepresentante.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const isSelected = this.value !== '';

        const tutorNombres = document.getElementById('tutor_nombres');
        const tutorApellidos = document.getElementById('tutor_apellidos');
        const tutorCedulaPref = document.getElementById('tutor_cedula_prefix');
        const tutorCedulaNum = document.getElementById('tutor_cedula_number');
        const tutorCedula = document.getElementById('tutor_cedula');
        const tutorTelPref = document.getElementById('tutor_telefono_prefix');
        const tutorTelNum = document.getElementById('tutor_telefono_number');
        const tutorTel = document.getElementById('tutor_telefono');
        const tutorRelacion = document.querySelector('select[name="tutor_relacion"]');

        const fieldsToToggle = [tutorNombres, tutorApellidos, tutorCedulaPref, tutorCedulaNum, tutorTelPref, tutorTelNum, tutorRelacion];

        if (isSelected) {
            // Llenar campos
            tutorNombres.value = opt.getAttribute('data-nombre') || '';
            tutorApellidos.value = opt.getAttribute('data-apellido') || '';
            
            tutorCedulaPref.value = opt.getAttribute('data-cedula-prefix') || 'V';
            tutorCedulaNum.value = formatCedulaNumber(opt.getAttribute('data-cedula-numero') || '');
            tutorCedula.value = opt.getAttribute('data-cedula-completa') || '';
            
            const tel = opt.getAttribute('data-telefono') || '';
            let repTelPref = '';
            let repTelNum = '';
            const prefixes = ['0412','0414','0416','0422','0424','0426','0255','0256'];
            for (let i = 0; i < prefixes.length; i++) {
                if (tel.startsWith(prefixes[i])) {
                    repTelPref = prefixes[i];
                    repTelNum = tel.substring(4);
                    break;
                }
            }
            tutorTelPref.value = repTelPref || '0412';
            tutorTelNum.value = repTelNum;
            tutorTel.value = tel;

            tutorRelacion.value = opt.getAttribute('data-relacion') || '';

            // Deshabilitar campos
            fieldsToToggle.forEach(el => {
                if (el) {
                    el.disabled = true;
                    el.style.backgroundColor = 'var(--color-bg-alt)';
                    el.style.cursor = 'not-allowed';
                    el.removeAttribute('required');
                }
            });
        } else {
            // Habilitar campos
            fieldsToToggle.forEach(el => {
                if (el) {
                    el.disabled = false;
                    el.value = '';
                    el.style.backgroundColor = '';
                    el.style.cursor = '';
                }
            });
            tutorCedula.value = '';
            tutorTel.value = '';
            
            // Re-aplicar requeridos dinámicos si es menor de edad
            updateDynamicRequirements();
        }
    });

    // Ejecutar después de un breve delay al cargar la página para mantener estado si ya está seleccionado
    setTimeout(() => {
        if (selRepresentante.value !== '') {
            selRepresentante.dispatchEvent(new Event('change'));
        }
    }, 150);
}

// Helper para cascada asíncrona de dirección al elegir dirección existente
function setCascadingDireccion(estadoId, municipioId, parroquiaId) {
    const selEstado = document.getElementById('sel-estado');
    const selMunicipio = document.getElementById('sel-municipio');
    const selParroquia = document.getElementById('sel-parroquia');

    selEstado.value = estadoId;
    selMunicipio.dataset.current = municipioId;
    selParroquia.dataset.current = parroquiaId;
    
    selMunicipio.disabled = false;
    fetch('<?= e(url('/api/direcciones/municipios')) ?>/' + estadoId)
        .then(res => res.json())
        .then(data => {
            selMunicipio.innerHTML = '<option value="">— Seleccione Municipio —</option>';
            data.forEach(mun => {
                let selected = (parseInt(selMunicipio.dataset.current) === parseInt(mun.municipio_id)) ? 'selected' : '';
                selMunicipio.innerHTML += `<option value="${mun.municipio_id}" ${selected}>${mun.municipio}</option>`;
            });
            if (selMunicipio.value) {
                selParroquia.disabled = false;
                fetch('<?= e(url('/api/direcciones/parroquias')) ?>/' + selMunicipio.value)
                    .then(res => res.json())
                    .then(data2 => {
                        selParroquia.innerHTML = '<option value="">— Seleccione Parroquia —</option>';
                        data2.forEach(par => {
                            let selected = (parseInt(selParroquia.dataset.current) === parseInt(par.parroquia_id)) ? 'selected' : '';
                            selParroquia.innerHTML += `<option value="${par.parroquia_id}" ${selected}>${par.parroquia}</option>`;
                        });
                    });
            }
        })
        .catch(console.error);
}

// —— Lógica de Dirección Existente ———————————————————————————————————————————
const selDireccion = document.getElementById('sel-direccion');
if (selDireccion) {
    selDireccion.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const isSelected = this.value !== '';

        const selEstado = document.getElementById('sel-estado');
        const selMunicipio = document.getElementById('sel-municipio');
        const selParroquia = document.getElementById('sel-parroquia');
        const tipoVivienda = document.querySelector('select[name="tipo_vivienda"]');
        const localidad = document.querySelector('input[name="localidad"]');
        const ubicacionVivienda = document.querySelector('input[name="ubicacion_vivienda"]');

        const fieldsToToggle = [selEstado, selMunicipio, selParroquia, tipoVivienda, localidad, ubicacionVivienda];

        if (isSelected) {
            // Llenar campos
            const estadoId = opt.getAttribute('data-estado-id');
            const municipioId = opt.getAttribute('data-municipio-id');
            const parroquiaId = opt.getAttribute('data-parroquia-id');
            
            setCascadingDireccion(estadoId, municipioId, parroquiaId);

            tipoVivienda.value = opt.getAttribute('data-tipo-vivienda') || '';
            localidad.value = opt.getAttribute('data-localidad') || '';
            ubicacionVivienda.value = opt.getAttribute('data-ubicacion-vivienda') || '';

            // Deshabilitar campos y quitar required
            fieldsToToggle.forEach(el => {
                if (el) {
                    el.disabled = true;
                    el.style.backgroundColor = 'var(--color-bg-alt)';
                    el.style.cursor = 'not-allowed';
                    el.removeAttribute('required');
                }
            });
        } else {
            // Habilitar campos
            fieldsToToggle.forEach(el => {
                if (el) {
                    el.disabled = false;
                    el.style.backgroundColor = '';
                    el.style.cursor = '';
                    el.setAttribute('required', 'true');
                }
            });
            // Limpiar campos
            selEstado.value = '';
            selMunicipio.innerHTML = '<option value="">— Seleccione Municipio —</option>';
            selMunicipio.disabled = true;
            selParroquia.innerHTML = '<option value="">— Seleccione Parroquia —</option>';
            selParroquia.disabled = true;
            tipoVivienda.value = '';
            localidad.value = '';
            ubicacionVivienda.value = '';
        }
    });

    // Ejecutar después de un breve delay al cargar la página para mantener estado si ya está seleccionado
    setTimeout(() => {
        if (selDireccion.value !== '') {
            selDireccion.dispatchEvent(new Event('change'));
        }
    }, 150);
}
</script>

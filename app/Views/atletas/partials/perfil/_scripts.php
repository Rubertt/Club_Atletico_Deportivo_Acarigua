<!-- Inclusión de ECharts para gráficos -->
<script src="<?= e(url('/assets/js/lib/echarts.min.js')) ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Manejo de Pestañas y Lazy Loading
        const tabs = document.querySelectorAll('.tab-btn');
        const contents = document.querySelectorAll('.tab-content');

        // Referencias globales a gráficos
        let chartAntro = null;
        let chartRadar = null;
        let chartDona = null;

        // Resolución de colores CSS para ECharts (no soporta var())
        const rootStyles = getComputedStyle(document.documentElement);
        const chartTextColor = rootStyles.getPropertyValue('--color-text').trim() || '#1E293B';
        const chartTextMuted = rootStyles.getPropertyValue('--color-text-muted').trim() || '#64748B';
        const chartBorderColor = rootStyles.getPropertyValue('--color-border').trim() || '#E2E8F0';

        // Delegación de Cierre de Modales
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-close-modal]');
            if (btn) {
                const modal = btn.closest('.modal-overlay');
                if (modal) modal.style.display = 'none';
            }

            // Cerrar al hacer clic fuera del contenido del modal
            if (e.target.classList.contains('modal-overlay')) {
                const closableModalIds = [
                    'modal-ficha-medica', 'modal-discapacidad', 'modal-editar-basico', 
                    'modal-editar-contacto', 'modal-editar-representante', 
                    'modal-editar-direccion', 'modal-editar-foto', 
                    'modal-consulta-medica', 'modal-ver-consulta', 'modal-historial-asistencia',
                    'modal-medicion', 'modal-medicion-editar', 'modal-prueba', 'modal-prueba-editar'
                ];
                if (closableModalIds.includes(e.target.id)) {
                    e.target.style.display = 'none';
                }
            }
        });

        // Abrir Modal de Asistencia (si existe botón e historial)
        document.addEventListener('click', (e) => {
            if (e.target.id === 'btn-historial-asistencia' || e.target.closest('#btn-historial-asistencia')) {
                const modalHistAsist = document.getElementById('modal-historial-asistencia');
                if (modalHistAsist) modalHistAsist.style.display = 'flex';
            }
        });

        // Carga dinámica de contenido de pestaña
        async function loadTabContent(tab, container) {
            const url = container.getAttribute('data-url');
            if (!url) return;

            try {
                const response = await fetch(url);
                if (!response.ok) throw new Error('Error al cargar la pestaña');
                const html = await response.text();
                container.innerHTML = html;
                container.setAttribute('data-loaded', 'true');

                // Inicializar lógica específica de la pestaña cargada
                const targetId = tab.getAttribute('data-target');
                if (targetId === 'tab-ficha') initFichaTab();
                else if (targetId === 'tab-consulta') initConsultaTab();
                else if (targetId === 'tab-antropometria') initAntropometriaTab();
                else if (targetId === 'tab-pruebas') initPruebasTab();
                else if (targetId === 'tab-asistencia') initAsistenciaTab();
            } catch (error) {
                console.error(error);
                container.innerHTML = `
                    <div class="alert alert-danger" style="margin: 20px; text-align: center;">
                        <i class="ph ph-warning-circle" style="font-size: 24px; display: block; margin: 0 auto 8px;"></i>
                        No se pudo cargar el contenido. <a href="#" onclick="window.location.reload(); return false;" style="text-decoration: underline; font-weight: 600;">Reintentar</a>
                    </div>
                `;
            }
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', async () => {
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.style.display = 'none');

                tab.classList.add('active');
                const targetId = tab.getAttribute('data-target');
                const container = document.getElementById(targetId);
                if (container) {
                    container.style.display = 'block';

                    if (container.getAttribute('data-loaded') === 'false') {
                        await loadTabContent(tab, container);
                    }
                }

                // Redimensionar gráficos si están en la pestaña activa
                if (targetId === 'tab-antropometria' && chartAntro) {
                    setTimeout(() => chartAntro.resize(), 50);
                }
                if (targetId === 'tab-pruebas' && chartRadar) {
                    setTimeout(() => chartRadar.resize(), 50);
                }
                if (targetId === 'tab-asistencia' && chartDona) {
                    setTimeout(() => chartDona.resize(), 50);
                }
            });
        });

        // Activar pestaña desde URL si existe (ej: ?tab=tab-ficha)
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if (tabParam) {
            const targetTabBtn = document.querySelector(`.tab-btn[data-target="${tabParam}"]`);
            if (targetTabBtn) {
                targetTabBtn.click();
            }
        }

        // Filtro estricto de números positivos con delegación
        document.addEventListener('keydown', (e) => {
            if (e.target.matches('input[type="number"]')) {
                if (['-', '+', 'e', 'E'].includes(e.key)) {
                    e.preventDefault();
                }
            }
        });
        document.addEventListener('input', (e) => {
            if (e.target.matches('input[type="number"]')) {
                if (parseFloat(e.target.value) < 0) {
                    e.target.value = '';
                }
            }
        });
        document.addEventListener('paste', (e) => {
            if (e.target.matches('input[type="number"]')) {
                const pasteData = e.clipboardData.getData('text');
                if (pasteData.includes('-') || pasteData.includes('+') || pasteData.toLowerCase().includes('e')) {
                    e.preventDefault();
                }
            }
        });

        // Limpiar marcas de error del FormValidator al enfocar un campo
        document.addEventListener('focusin', (e) => {
            if (e.target.matches('input, select, textarea')) {
                FormValidator.clearMark(e.target);
            }
        });

        // —— Lógica específica de la pestaña Ficha Médica —————————————————————
        function initFichaTab() {
            const modalFicha = document.getElementById('modal-ficha-medica');
            const formFicha = document.getElementById('form-ficha-medica');
            const modalDisc = document.getElementById('modal-discapacidad');
            const formDisc = document.getElementById('form-discapacidad');
            const baseActionDisc = "<?= e(url("/admin/ficha-medica/{$atleta['atleta_id']}/discapacidad")) ?>";

            function abrirModalFicha() {
                if (modalFicha) modalFicha.style.display = 'flex';
            }

            document.getElementById('btn-editar-ficha')?.addEventListener('click', abrirModalFicha);
            document.getElementById('btn-crear-ficha')?.addEventListener('click', abrirModalFicha);

            function abrirModalDisc(modo = 'agregar', data = {}) {
                if (!modalDisc) return;

                const title = document.getElementById('title-discapacidad');
                const submitText = document.getElementById('submit-text-discapacidad');

                if (modo === 'editar') {
                    title.textContent = 'Editar Discapacidad';
                    submitText.textContent = 'Guardar';
                    formDisc.action = baseActionDisc + '/' + data.id + '/editar';
                    document.getElementById('input-tipo-disc').value = data.tipo;
                    document.getElementById('input-carnet-disc').value = data.carnet;
                    document.getElementById('input-porcentaje-disc').value = data.porcentaje;
                } else {
                    title.textContent = 'Agregar Discapacidad';
                    submitText.textContent = 'Agregar';
                    formDisc.action = baseActionDisc;
                    formDisc.reset();
                }

                const discErrorEl = document.getElementById('discapacidad-error');
                if (discErrorEl) discErrorEl.style.display = 'none';
                modalDisc.style.display = 'flex';
            }

            formDisc?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const validation = FormValidator.validate(formDisc);
                if (!validation.valid) {
                    FormValidator.showErrors(validation.errors);
                    return;
                }

                const submitBtn = formDisc.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Guardando...';

                try {
                    const formData = new FormData(formDisc);
                    const response = await fetch(formDisc.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json' }
                    });
                    const result = await response.json();

                    if (result.success) {
                        window.location.href = window.location.pathname + '?tab=tab-ficha';
                    } else {
                        CadaModal.alert({
                            title: 'Error',
                            text: result.message || 'Ocurrió un error al guardar la discapacidad.',
                            type: 'danger',
                            confirmText: 'Cerrar'
                        });
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                } catch (error) {
                    CadaModal.alert({
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor. Intente nuevamente.',
                        type: 'danger',
                        confirmText: 'Cerrar'
                    });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });

            formFicha?.addEventListener('submit', (e) => {
                // Verificar que al menos un campo tenga valor
                const inputs = formFicha.querySelectorAll('input:not([type="hidden"]), select, textarea');
                let hasValue = false;
                inputs.forEach(input => {
                    if (input.value.trim() !== '') {
                        hasValue = true;
                    }
                });

                if (!hasValue) {
                    e.preventDefault();
                    CadaModal.alert({
                        title: 'Ficha Vacía',
                        text: 'Debe ingresar al menos un dato en la ficha médica antes de guardar.',
                        type: 'warning',
                        confirmText: 'Entendido'
                    });
                    return;
                }

                const validation = FormValidator.validate(formFicha);
                if (!validation.valid) {
                    e.preventDefault();
                    FormValidator.showErrors(validation.errors);
                }
            });

            document.getElementById('btn-agregar-discapacidad')?.addEventListener('click', () => abrirModalDisc('agregar'));

            document.querySelectorAll('.btn-editar-discapacidad').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const btnEl = e.currentTarget;
                    abrirModalDisc('editar', {
                        id: btnEl.getAttribute('data-id'),
                        tipo: btnEl.getAttribute('data-tipo'),
                        carnet: btnEl.getAttribute('data-carnet'),
                        porcentaje: btnEl.getAttribute('data-porcentaje')
                    });
                });
            });

            document.querySelectorAll('.btn-delete-disc').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const form = e.currentTarget.closest('form');
                    CadaModal.confirm({
                        title: 'Eliminar Discapacidad',
                        text: '¿Estás seguro de que deseas eliminar esta discapacidad?',
                        type: 'danger',
                        confirmText: 'Sí, eliminar'
                    }).then(confirmed => {
                        if (confirmed) form.submit();
                    });
                });
            });

            document.getElementById('btn-help-ficha-medica')?.addEventListener('click', () => {
                FormValidator.showHelp(
                    'Guía: Ficha Médica',
                    '<?= e(asset("img/ayuda/formulario_atleta.png")) ?>'
                );
            });

            document.getElementById('btn-help-discapacidad')?.addEventListener('click', () => {
                FormValidator.showHelp(
                    'Guía: Agregar Discapacidad',
                    '<?= e(asset("img/ayuda/formulario_atleta.png")) ?>'
                );
            });

            paginateTable('tabla-discapacidades', 5);
        }

        // —— Lógica específica de la pestaña Consulta Médica ——————————————————
        function initConsultaTab() {
            const modalConsulta = document.getElementById('modal-consulta-medica');
            const formConsulta = document.getElementById('form-consulta-medica');
            const baseActionConsulta = "<?= e(url("/admin/atletas/{$atleta['atleta_id']}/consultas-medicas")) ?>";
            const modalVer = document.getElementById('modal-ver-consulta');
            let estadoOriginal = null;

            function getMinAltaDate(sucesoStr) {
                if (!sucesoStr) return '';
                const parts = sucesoStr.split('-');
                if (parts.length !== 3) return '';
                const date = new Date(parts[0], parts[1] - 1, parts[2]);
                date.setDate(date.getDate() + 1);
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }

            function abrirModalVer(data) {
                if (!modalVer) return;
                document.getElementById('detail-id').textContent = data.id;
                document.getElementById('detail-tipo').textContent = data.tipo;
                const estatusEl = document.getElementById('detail-estatus');
                estatusEl.textContent = data.estatus;
                estatusEl.className = 'badge badge-' + data.estatusClass;
                document.getElementById('detail-fecha-suceso').textContent = data.fechaSuceso;
                document.getElementById('detail-fecha-alta').textContent = data.fechaAlta;
                document.getElementById('detail-diagnostico').textContent = data.diagnostico;
                document.getElementById('detail-registrado').textContent = data.registrado;

                const desc = data.descripcion && data.descripcion !== '—' && data.descripcion.trim() !== '' ? data.descripcion : '';
                const descEl = document.getElementById('detail-descripcion');
                if (desc) {
                    descEl.textContent = desc;
                    descEl.style.fontStyle = 'normal';
                    descEl.style.color = 'var(--color-text)';
                } else {
                    descEl.textContent = 'Sin descripción ni síntomas adicionales registrados.';
                    descEl.style.fontStyle = 'italic';
                    descEl.style.color = 'var(--color-text-muted)';
                }

                const trat = data.tratamiento && data.tratamiento !== '—' && data.tratamiento.trim() !== '' ? data.tratamiento : '';
                const tratEl = document.getElementById('detail-tratamiento');
                if (trat) {
                    tratEl.textContent = trat;
                    tratEl.style.fontStyle = 'normal';
                    tratEl.style.color = 'var(--color-text)';
                } else {
                    tratEl.textContent = 'Sin tratamiento indicado registrado.';
                    tratEl.style.fontStyle = 'italic';
                    tratEl.style.color = 'var(--color-text-muted)';
                }

                modalVer.style.display = 'flex';
            }

            function abrirModalConsulta(modo = 'agregar', data = {}) {
                if (!modalConsulta) return;
                const title = document.getElementById('title-consulta');
                const submitText = document.getElementById('submit-text-consulta');

                if (modo === 'editar') {
                    title.textContent = 'Editar Consulta Médica';
                    submitText.innerHTML = '<i class="ph ph-floppy-disk"></i> Guardar Cambios';
                    formConsulta.action = baseActionConsulta + '/' + data.id + '/editar';
                    document.getElementById('input-tipo-consulta').value = data.tipo;
                    document.getElementById('input-fecha-suceso').value = data.fecha_suceso;
                    document.getElementById('input-fecha-alta').value = data.fecha_alta;
                    document.getElementById('input-estatus-disp').value = data.estatus;
                    document.getElementById('input-diagnostico').value = data.diagnostico;
                    document.getElementById('input-descripcion').value = data.descripcion;
                    document.getElementById('input-tratamiento').value = data.tratamiento;

                    const inputCreadoEn = document.getElementById('input-creado-en');
                    if (inputCreadoEn) {
                        inputCreadoEn.value = data.creado_en || '';
                    }

                    estadoOriginal = String(data.estatus);
                } else {
                    title.textContent = 'Registrar Consulta Médica';
                    submitText.innerHTML = '<i class="ph ph-plus"></i> Registrar';
                    formConsulta.action = baseActionConsulta;
                    formConsulta.reset();

                    const hoy = new Date().toISOString().split('T')[0];
                    document.getElementById('input-fecha-suceso').value = hoy;

                    const inputCreadoEn = document.getElementById('input-creado-en');
                    if (inputCreadoEn) {
                        const now = new Date();
                        const year = now.getFullYear();
                        const month = String(now.getMonth() + 1).padStart(2, '0');
                        const day = String(now.getDate()).padStart(2, '0');
                        const hours = String(now.getHours()).padStart(2, '0');
                        const minutes = String(now.getMinutes()).padStart(2, '0');
                        const seconds = String(now.getSeconds()).padStart(2, '0');
                        inputCreadoEn.value = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
                    }

                    estadoOriginal = null;
                }

                const sucesoVal = document.getElementById('input-fecha-suceso').value;
                if (sucesoVal) {
                    const minAlta = getMinAltaDate(sucesoVal);
                    if (minAlta) {
                        document.getElementById('input-fecha-alta').setAttribute('min', minAlta);
                    }
                } else {
                    document.getElementById('input-fecha-alta').removeAttribute('min');
                }

                const errorEl = document.getElementById('consulta-error');
                if (errorEl) errorEl.style.display = 'none';
                modalConsulta.style.display = 'flex';
            }

            document.getElementById('btn-agregar-consulta')?.addEventListener('click', () => abrirModalConsulta('agregar'));

            document.querySelectorAll('.btn-editar-consulta').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const target = e.currentTarget;
                    abrirModalConsulta('editar', {
                        id: target.getAttribute('data-id'),
                        tipo: target.getAttribute('data-tipo'),
                        fecha_suceso: target.getAttribute('data-fecha-suceso'),
                        fecha_alta: target.getAttribute('data-fecha-alta'),
                        estatus: target.getAttribute('data-estatus'),
                        creado_en: target.getAttribute('data-creado-en'),
                        diagnostico: target.getAttribute('data-diagnostico'),
                        descripcion: target.getAttribute('data-descripcion'),
                        tratamiento: target.getAttribute('data-tratamiento')
                    });
                });
            });

            document.querySelectorAll('.btn-ver-consulta').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const target = e.currentTarget;
                    abrirModalVer({
                        id: target.getAttribute('data-id'),
                        tipo: target.getAttribute('data-tipo-lbl'),
                        estatus: target.getAttribute('data-estatus-lbl'),
                        estatusClass: target.getAttribute('data-estatus-class'),
                        fechaSuceso: target.getAttribute('data-fecha-suceso'),
                        fechaAlta: target.getAttribute('data-fecha-alta'),
                        diagnostico: target.getAttribute('data-diagnostico'),
                        descripcion: target.getAttribute('data-descripcion'),
                        tratamiento: target.getAttribute('data-tratamiento'),
                        registrado: target.getAttribute('data-registrado')
                    });
                });
            });

            const inputSuceso = document.getElementById('input-fecha-suceso');
            const inputAlta = document.getElementById('input-fecha-alta');

            function actualizarMinAlta() {
                if (inputSuceso && inputAlta) {
                    const sucesoVal = inputSuceso.value;
                    if (sucesoVal) {
                        const minAlta = getMinAltaDate(sucesoVal);
                        if (minAlta) {
                            inputAlta.setAttribute('min', minAlta);
                            if (inputAlta.value && inputAlta.value < minAlta) {
                                inputAlta.value = minAlta;
                            }
                        }
                    } else {
                        inputAlta.removeAttribute('min');
                    }
                }
            }

            inputSuceso?.addEventListener('change', actualizarMinAlta);
            inputSuceso?.addEventListener('input', actualizarMinAlta);

            function restrictDateInput(input) {
                if (!input) return;
                input.addEventListener('blur', (e) => {
                    const min = e.target.getAttribute('min');
                    const max = e.target.getAttribute('max');
                    let val = e.target.value;
                    if (val) {
                        const parts = val.split('-');
                        if (parts[0] && parts[0].length > 4) {
                            parts[0] = parts[0].substring(0, 4);
                            val = parts.join('-');
                            e.target.value = val;
                        }
                        if (min && val < min) e.target.value = min;
                        else if (max && val > max) e.target.value = max;
                    }
                });
            }
            restrictDateInput(inputSuceso);
            restrictDateInput(inputAlta);

            formConsulta?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const validation = FormValidator.validate(formConsulta);
                if (!validation.valid) {
                    FormValidator.showErrors(validation.errors);
                    return;
                }

                const submitBtn = formConsulta.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Guardando...';

                try {
                    const response = await fetch(formConsulta.action, {
                        method: 'POST',
                        body: new FormData(formConsulta),
                        headers: { 'Accept': 'application/json' }
                    });
                    const result = await response.json();
                    if (result.success) {
                        modalConsulta.style.display = 'none';
                        if (typeof CadaToast !== 'undefined') {
                            CadaToast.success(result.message || 'Consulta médica guardada.');
                        }
                        
                        const estatusNuevo = document.getElementById('input-estatus-disp').value;
                        if (estadoOriginal === '0' && estatusNuevo === '1') {
                            CadaModal.alert({
                                title: 'Recomendación',
                                text: 'Se recomienda hacer un chequeo antropométrico en este atleta, y realizarle pruebas físicas, para estimar los posibles efectos adversos luego de su inactividad.',
                                type: 'warning',
                                confirmText: 'Entendido'
                            }).then(() => {
                                window.location.href = window.location.pathname + '?tab=tab-consulta';
                            });
                        } else {
                            window.location.href = window.location.pathname + '?tab=tab-consulta';
                        }
                    } else {
                        if (result.errors) {
                            const errorsList = [];
                            let limiteAlcanzado = false;
                            Object.entries(result.errors).forEach(([field, msgs]) => {
                                const msgText = Array.isArray(msgs) ? msgs.join(' ') : String(msgs);
                                if (msgText.includes('Límite de registro por fecha de suceso alcanzado') || field === 'limite') {
                                    limiteAlcanzado = true;
                                }
                                const input = formConsulta.querySelector(`[name="${field}"]`) || document.getElementById('input-' + field);
                                if (input) {
                                    FormValidator.markError(input);
                                }
                                if (Array.isArray(msgs)) msgs.forEach(m => errorsList.push(m));
                                else errorsList.push(msgs);
                            });

                            if (limiteAlcanzado) {
                                CadaModal.alert({
                                    title: 'Límite de registro por fecha de suceso alcanzado',
                                    text: 'Se ha alcanzado el límite máximo de 3 consultas médicas registradas para el mismo atleta en la misma fecha de suceso.',
                                    type: 'warning',
                                    confirmText: 'Entendido'
                                });
                            } else {
                                CadaModal.alert({
                                    title: 'Campos Incompletos',
                                    text: `Por favor revisa lo siguiente:<br><br>${errorsList.map(err => `• ${err}`).join('<br>')}`,
                                    type: 'warning',
                                    confirmText: 'Corregir ahora'
                                });
                            }
                        } else {
                            CadaModal.alert({ title: 'Error', text: result.message || 'Ocurrió un error.', type: 'danger' });
                        }
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                } catch (err) {
                    console.error(err);
                    CadaModal.alert({ title: 'Error de conexión', text: 'Intente nuevamente.', type: 'danger' });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });

            document.querySelectorAll('.btn-delete-consulta').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    const form = e.currentTarget.closest('form');
                    if (!form) return;
                    const confirmed = await CadaModal.confirm({
                        title: 'Eliminar Consulta Médica',
                        text: '¿Estás seguro de que deseas eliminar esta consulta médica?',
                        type: 'danger',
                        confirmText: 'Sí, eliminar'
                    });
                    if (confirmed) {
                        try {
                            const response = await fetch(form.action, {
                                method: 'POST',
                                body: new FormData(form),
                                headers: { 'Accept': 'application/json' }
                            });
                            const result = await response.json();
                            if (result.success) {
                                if (typeof CadaToast !== 'undefined') {
                                    CadaToast.success(result.message || 'Consulta médica eliminada.');
                                }
                                window.location.href = window.location.pathname + '?tab=tab-consulta';
                            } else {
                                CadaModal.alert({ title: 'Error', text: result.message || 'No se pudo eliminar.', type: 'danger' });
                            }
                        } catch (err) {
                            console.error(err);
                            form.submit();
                        }
                    }
                });
            });
            paginateTable('tabla-consultas', 5);
        }

        // —— Lógica específica de la pestaña Antropometría ———————————————————
        function initAntropometriaTab() {
            const modalMedicion = document.getElementById('modal-medicion');
            const formMedicion = document.getElementById('form-medicion');
            const modalMedicionEditar = document.getElementById('modal-medicion-editar');
            const formMedicionEditar = document.getElementById('form-medicion-editar');

            function abrirModalMedicion() {
                if (modalMedicion) modalMedicion.style.display = 'flex';
            }

            document.getElementById('btn-nueva-medicion')?.addEventListener('click', abrirModalMedicion);

            formMedicion?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const validation = FormValidator.validate(formMedicion, validarMedicionCustom);
                if (!validation.valid) {
                    FormValidator.showErrors(validation.errors);
                    return;
                }

                const submitBtn = formMedicion.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Guardando...';

                try {
                    const formData = new FormData(formMedicion);
                    const response = await fetch(formMedicion.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json' }
                    });
                    const result = await response.json();

                    if (result.success) {
                        window.location.href = window.location.pathname + '?tab=tab-antropometria';
                    } else {
                        if (result.errors) {
                            const errorsList = [];
                            Object.entries(result.errors).forEach(([field, msgs]) => {
                                const input = formMedicion.querySelector(`[name="${field}"]`);
                                if (input) {
                                    FormValidator.markError(input);
                                }
                                if (Array.isArray(msgs)) msgs.forEach(m => errorsList.push(m));
                                else errorsList.push(msgs);
                            });
                            FormValidator.showErrors(errorsList);
                        } else {
                            CadaModal.alert({
                                title: 'Error',
                                text: result.message || 'Error al guardar la medición.',
                                type: 'danger'
                            });
                        }
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                } catch (error) {
                    CadaModal.alert({
                        title: 'Error',
                        text: 'Error de conexión con el servidor. Inténtalo de nuevo.',
                        type: 'danger'
                    });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });

            document.querySelectorAll('.btn-editar-medicion').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-id');
                    const fecha = btn.getAttribute('data-fecha');
                    const peso = btn.getAttribute('data-peso');
                    const altura = btn.getAttribute('data-altura');
                    const grasa = btn.getAttribute('data-grasa');
                    const musculo = btn.getAttribute('data-musculo');
                    const envergadura = btn.getAttribute('data-envergadura');
                    const pierna = btn.getAttribute('data-pierna');
                    const torso = btn.getAttribute('data-torso');

                    document.getElementById('edit-fecha_medicion').value = fecha ? fecha.substring(0, 10) : '';
                    document.getElementById('edit-peso').value = peso || '';
                    document.getElementById('edit-altura').value = altura || '';
                    document.getElementById('edit-porcentaje_grasa').value = grasa || '';
                    document.getElementById('edit-porcentaje_musculatura').value = musculo || '';
                    document.getElementById('edit-envergadura').value = envergadura || '';
                    document.getElementById('edit-largo_de_pierna').value = pierna || '';
                    document.getElementById('edit-largo_de_torso').value = torso || '';

                    formMedicionEditar.action = `<?= url("/admin/medidas") ?>/${id}/editar`;
                    if (modalMedicionEditar) modalMedicionEditar.style.display = 'flex';
                });
            });

            formMedicionEditar?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const validation = FormValidator.validate(formMedicionEditar, validarMedicionCustom);
                if (!validation.valid) {
                    FormValidator.showErrors(validation.errors);
                    return;
                }

                const submitBtn = formMedicionEditar.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Guardando...';

                try {
                    const formData = new FormData(formMedicionEditar);
                    const response = await fetch(formMedicionEditar.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json' }
                    });
                    const result = await response.json();

                    if (result.success) {
                        window.location.href = window.location.pathname + '?tab=tab-antropometria';
                    } else {
                        if (result.errors) {
                            const errorsList = [];
                            Object.entries(result.errors).forEach(([field, msgs]) => {
                                const input = formMedicionEditar.querySelector(`[name="${field}"]`);
                                if (input) {
                                    FormValidator.markError(input);
                                }
                                if (Array.isArray(msgs)) msgs.forEach(m => errorsList.push(m));
                                else errorsList.push(msgs);
                            });
                            FormValidator.showErrors(errorsList);
                        } else {
                            CadaModal.alert({
                                title: 'Error',
                                text: result.message || 'Error al actualizar la medición.',
                                type: 'danger'
                            });
                        }
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                } catch (error) {
                    CadaModal.alert({
                        title: 'Error',
                        text: 'Error de conexión con el servidor. Inténtalo de nuevo.',
                        type: 'danger'
                    });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });

            document.querySelectorAll('.btn-eliminar-medicion').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const atletaId = "<?= $atleta['atleta_id'] ?>";

                    CadaModal.confirm({
                        title: '¿Eliminar Medición?',
                        text: '¿Estás seguro de eliminar este registro antropométrico? Esta acción no se puede deshacer.',
                        type: 'danger',
                        confirmText: 'Sí, Eliminar',
                        cancelText: 'Cancelar'
                    }).then((confirmed) => {
                        if (confirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `<?= url('/admin/medidas') ?>/${id}/eliminar?atleta_id=${atletaId}&redirect=${encodeURIComponent(window.location.pathname + '?tab=tab-antropometria')}`;
                            const csrf = document.createElement('input');
                            csrf.type = 'hidden';
                            csrf.name = '_csrf';
                            csrf.value = document.querySelector('meta[name="csrf-token"]').content;
                            form.appendChild(csrf);
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            });

            // Gráfica Real de Antropometría (Peso vs Altura)
            const chartAntroDOM = document.getElementById('chart-antropometria');
            if (chartAntroDOM && typeof echarts !== 'undefined') {
                chartAntro = echarts.init(chartAntroDOM);
                const historialMedidas = JSON.parse(chartAntroDOM.getAttribute('data-historial') || '[]');

                const dates = historialMedidas.map(m => {
                    const d = new Date(m.fecha_medicion);
                    return d.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
                });
                const pesos = historialMedidas.map(m => parseFloat(m.peso) || 0);
                const alturas = historialMedidas.map(m => {
                    let h = parseFloat(m.altura) || 0;
                    if (h > 0 && h < 3) h = h * 100;
                    return h;
                });
                const imcs = historialMedidas.map(m => {
                    const p = parseFloat(m.peso) || 0;
                    const h = parseFloat(m.altura) || 0;
                    if (p > 0 && h > 0) {
                        const a = h > 3 ? h / 100 : h;
                        const imc = p / (a * a);
                        return isFinite(imc) ? parseFloat(imc.toFixed(1)) : 0;
                    }
                    return 0;
                });

                const optionAntro = {
                    tooltip: { trigger: 'axis' },
                    legend: { 
                        data: ['Peso (kg)', 'Altura (cm)', 'IMC'], 
                        bottom: 0,
                        textStyle: { fontSize: 12, color: chartTextMuted }
                    },
                    grid: { left: '10%', right: '10%', bottom: '15%', containLabel: true },
                    xAxis: { type: 'category', boundaryGap: true, data: dates.length ? dates : ['Sin datos'], axisLabel: { color: chartTextMuted }, axisLine: { lineStyle: { color: chartBorderColor } } },
                    yAxis: [
                        { type: 'value', name: 'Kg/Cm', position: 'left', min: 0, nameTextStyle: { color: chartTextMuted }, axisLabel: { color: chartTextMuted }, axisLine: { lineStyle: { color: chartBorderColor } }, splitLine: { lineStyle: { color: chartBorderColor } } },
                        { type: 'value', name: 'IMC', position: 'right', splitLine: { show: false }, nameTextStyle: { color: chartTextMuted }, axisLabel: { color: chartTextMuted }, axisLine: { lineStyle: { color: chartBorderColor } } }
                    ],
                    series: [
                        {
                            name: 'Peso (kg)',
                            type: 'bar',
                            yAxisIndex: 0,
                            barWidth: '35%',
                            itemStyle: {
                                color: '#F59E0B',
                                borderRadius: [4, 4, 0, 0]
                            },
                            data: pesos.length ? pesos : [0]
                        },
                        {
                            name: 'Altura (cm)',
                            type: 'line',
                            smooth: true,
                            yAxisIndex: 0,
                            lineStyle: { color: '#6366F1', width: 2, type: 'dashed' },
                            itemStyle: { color: '#6366F1' },
                            data: alturas.length ? alturas : [0]
                        },
                        {
                            name: 'IMC',
                            type: 'line',
                            smooth: true,
                            yAxisIndex: 1,
                            lineStyle: { color: '#10B981', width: 3 },
                            itemStyle: { color: '#10B981' },
                            data: imcs.length ? imcs : [0]
                        }
                    ]
                };
                chartAntro.setOption(optionAntro);
            }

            document.getElementById('btn-help-medicion')?.addEventListener('click', () => {
                FormValidator.showHelp(
                    'Guía: Nueva Medición',
                    '<?= e(asset("img/ayuda/formulario_atleta.png")) ?>'
                );
            });

            document.getElementById('btn-help-medicion-editar')?.addEventListener('click', () => {
                FormValidator.showHelp(
                    'Guía: Editar Medición',
                    '<?= e(asset("img/ayuda/formulario_atleta.png")) ?>'
                );
            });

            paginateTable('tabla-antropometria', 5);
        }

        // —— Lógica específica de la pestaña Pruebas Físicas —————————————————
        function initPruebasTab() {
            const modalPrueba = document.getElementById('modal-prueba');
            const formPrueba = document.getElementById('form-prueba');
            const modalPruebaEditar = document.getElementById('modal-prueba-editar');
            const formPruebaEditar = document.getElementById('form-prueba-editar');

            function abrirModalPrueba() {
                if (modalPrueba) modalPrueba.style.display = 'flex';
            }

            document.getElementById('btn-nueva-prueba')?.addEventListener('click', abrirModalPrueba);

            formPrueba?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const validation = FormValidator.validate(formPrueba, validarPruebaCustom);
                if (!validation.valid) {
                    FormValidator.showErrors(validation.errors);
                    return;
                }

                const submitBtn = formPrueba.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
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
                        window.location.href = window.location.pathname + '?tab=tab-pruebas';
                    } else {
                        if (result.errors) {
                            const errorsList = [];
                            Object.entries(result.errors).forEach(([field, msgs]) => {
                                const input = formPrueba.querySelector(`[name="${field}"]`);
                                if (input) {
                                    FormValidator.markError(input);
                                }
                                if (Array.isArray(msgs)) msgs.forEach(m => errorsList.push(m));
                                else errorsList.push(msgs);
                            });
                            FormValidator.showErrors(errorsList);
                        } else {
                            CadaModal.alert({
                                title: 'Error',
                                text: result.message || 'Error al guardar los resultados.',
                                type: 'danger'
                            });
                        }
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                } catch (error) {
                    CadaModal.alert({
                        title: 'Error',
                        text: error.message || 'Error de conexión con el servidor. Inténtalo de nuevo.',
                        type: 'danger'
                    });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });

            document.querySelectorAll('.btn-editar-prueba').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-id');
                    const fecha = btn.getAttribute('data-fecha');
                    const entrenadorId = btn.getAttribute('data-entrenador-id');
                    const fuerza = btn.getAttribute('data-fuerza');
                    const resistencia = btn.getAttribute('data-resistencia');
                    const velocidad = btn.getAttribute('data-velocidad');
                    const coordinacion = btn.getAttribute('data-coordinacion');
                    const reaccion = btn.getAttribute('data-reaccion');

                    document.getElementById('edit-prueba-fecha').value = fecha ? fecha.substring(0, 10) : '';
                    document.getElementById('edit-prueba-entrenador').value = entrenadorId || '';
                    document.getElementById('edit-prueba-fuerza').value = fuerza || '';
                    document.getElementById('edit-prueba-resistencia').value = resistencia || '';
                    document.getElementById('edit-prueba-velocidad').value = velocidad || '';
                    document.getElementById('edit-prueba-coordinacion').value = coordinacion || '';
                    document.getElementById('edit-prueba-reaccion').value = reaccion || '';

                    formPruebaEditar.action = `<?= url("/admin/resultados-pruebas") ?>/${id}/editar`;
                    if (modalPruebaEditar) modalPruebaEditar.style.display = 'flex';
                });
            });

            formPruebaEditar?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const validation = FormValidator.validate(formPruebaEditar, validarPruebaCustom);
                if (!validation.valid) {
                    FormValidator.showErrors(validation.errors);
                    return;
                }

                const submitBtn = formPruebaEditar.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Guardando...';

                try {
                    const formData = new FormData(formPruebaEditar);
                    const response = await fetch(formPruebaEditar.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json' }
                    });
                    const result = await response.json();

                    if (result.success) {
                        window.location.href = window.location.pathname + '?tab=tab-pruebas';
                    } else {
                        if (result.errors) {
                            const errorsList = [];
                            Object.entries(result.errors).forEach(([field, msgs]) => {
                                const input = formPruebaEditar.querySelector(`[name="${field}"]`);
                                if (input) {
                                    FormValidator.markError(input);
                                }
                                if (Array.isArray(msgs)) msgs.forEach(m => errorsList.push(m));
                                else errorsList.push(msgs);
                            });
                            FormValidator.showErrors(errorsList);
                        } else {
                            CadaModal.alert({
                                title: 'Error',
                                text: result.message || 'Error al actualizar la prueba.',
                                type: 'danger'
                            });
                        }
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                } catch (error) {
                    CadaModal.alert({
                        title: 'Error',
                        text: 'Error de conexión con el servidor. Inténtalo de nuevo.',
                        type: 'danger'
                    });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });

            document.querySelectorAll('.btn-eliminar-prueba').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const atletaId = "<?= $atleta['atleta_id'] ?>";

                    CadaModal.confirm({
                        title: '¿Eliminar Prueba Física?',
                        text: '¿Estás seguro de eliminar este registro de pruebas físicas? Esta acción no se puede deshacer.',
                        type: 'danger',
                        confirmText: 'Sí, Eliminar',
                        cancelText: 'Cancelar'
                    }).then((confirmed) => {
                        if (confirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `<?= url('/admin/resultados-pruebas') ?>/${id}/eliminar?atleta_id=${atletaId}&redirect=${encodeURIComponent(window.location.pathname + '?tab=tab-pruebas')}`;
                            const csrf = document.createElement('input');
                            csrf.type = 'hidden';
                            csrf.name = '_csrf';
                            csrf.value = document.querySelector('meta[name="csrf-token"]').content;
                            form.appendChild(csrf);
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            });

            // Gráfica Real Radar de Pruebas Físicas
            const chartRadarDOM = document.getElementById('chart-radar-pruebas');
            if (chartRadarDOM && typeof echarts !== 'undefined') {
                chartRadar = echarts.init(chartRadarDOM);
                const historialPruebasRadar = JSON.parse(chartRadarDOM.getAttribute('data-historial') || '[]');

                let radarDataSeries = [];
                const colores = [
                    { line: 'var(--color-primary)', fill: 'rgba(37, 99, 235, 0.4)' },
                    { line: '#10B981', fill: 'rgba(16, 185, 129, 0.3)' }
                ];

                if (historialPruebasRadar.length > 0) {
                    const p1 = historialPruebasRadar[0];
                    let d1 = 'Manual';
                    if (p1.fecha_evento) d1 = new Date(p1.fecha_evento).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
                    radarDataSeries.push({
                        value: [
                            p1.test_de_fuerza || 0,
                            p1.test_resistencia || 0,
                            p1.test_velocidad || 0,
                            p1.test_coordinacion || 0,
                            p1.test_de_reaccion || 0
                        ],
                        name: 'Última: ' + d1,
                        itemStyle: { color: colores[0].line },
                        areaStyle: { color: colores[0].fill },
                        symbol: 'circle',
                        symbolSize: 6
                    });

                    if (historialPruebasRadar.length > 1) {
                        const p2 = historialPruebasRadar[1];
                        let d2 = 'Manual';
                        if (p2.fecha_evento) d2 = new Date(p2.fecha_evento).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
                        radarDataSeries.push({
                            value: [
                                p2.test_de_fuerza || 0,
                                p2.test_resistencia || 0,
                                p2.test_velocidad || 0,
                                p2.test_coordinacion || 0,
                                p2.test_de_reaccion || 0
                            ],
                            name: 'Anterior: ' + d2,
                            itemStyle: { color: colores[1].line },
                            lineStyle: { type: 'dashed' },
                            areaStyle: { color: colores[1].fill },
                            symbol: 'circle',
                            symbolSize: 6
                        });
                    }
                } else {
                    radarDataSeries.push({
                        value: [0, 0, 0, 0, 0],
                        name: 'Sin Evaluaciones',
                        itemStyle: { color: 'var(--color-text-muted)' },
                        areaStyle: { color: 'rgba(150, 150, 150, 0.1)' }
                    });
                }

                const optionRadar = {
                    tooltip: { trigger: 'item' },
                    legend: { 
                        data: radarDataSeries.map(s => s.name), 
                        bottom: 0,
                        textStyle: { fontSize: 11, color: chartTextMuted }
                    },
                    radar: {
                        indicator: [
                            { name: 'Fuerza', max: 100 },
                            { name: 'Resistencia', max: 100 },
                            { name: 'Velocidad', max: 100 },
                            { name: 'Coordinación', max: 100 },
                            { name: 'Reacción', max: 100 }
                        ],
                        radius: '60%',
                        axisName: { color: chartTextMuted, fontWeight: 'bold' },
                        axisLine: { lineStyle: { color: chartBorderColor } },
                        splitLine: { lineStyle: { color: chartBorderColor } },
                        splitArea: {
                            areaStyle: {
                                color: ['rgba(255, 255, 255, 0.05)', 'rgba(200, 200, 200, 0.05)']
                            }
                        }
                    },
                    series: [{
                        name: 'Rendimiento',
                        type: 'radar',
                        data: radarDataSeries
                    }]
                };
                chartRadar.setOption(optionRadar);
            }

            document.getElementById('btn-help-prueba')?.addEventListener('click', () => {
                FormValidator.showHelp(
                    'Guía: Registrar Prueba Física',
                    '<?= e(asset("img/ayuda/formulario_atleta.png")) ?>',
                    'Ingrese los resultados de las pruebas (escala 1-100). Si no tiene un evento creado, se generará uno automáticamente para la fecha indicada.'
                );
            });

            document.getElementById('btn-help-prueba-editar')?.addEventListener('click', () => {
                FormValidator.showHelp(
                    'Guía: Editar Prueba Física',
                    '<?= e(asset("img/ayuda/formulario_atleta.png")) ?>',
                    'Ingrese los resultados de las pruebas (escala 1-100). Si no tiene un evento creado, se generará uno automáticamente para la fecha indicada.'
                );
            });

            paginateTable('tabla-pruebas', 5);
        }

        // —— Lógica específica de la pestaña Asistencia —————————————————————
        function initAsistenciaTab() {
            const chartDonaDOM = document.getElementById('chart-asistencia-dona');
            let historialAsistenciasData = [];
            if (chartDonaDOM) {
                historialAsistenciasData = JSON.parse(chartDonaDOM.getAttribute('data-historial') || '[]');
            }

            if (chartDonaDOM && typeof echarts !== 'undefined') {
                chartDona = echarts.init(chartDonaDOM);

                let countPresente = 0;
                let countAusente = 0;
                let countJustificado = 0;

                historialAsistenciasData.forEach(a => {
                    const estatus = parseInt(a.estatus);
                    if (estatus === 1) countPresente++;
                    else if (estatus === 2) countJustificado++;
                    else if (estatus === 0) countAusente++;
                });

                const total = historialAsistenciasData.length;

                const optionDona = {
                    tooltip: { trigger: 'item' },
                    legend: { bottom: '0%', textStyle: { color: chartTextMuted } },
                    series: [
                        {
                            name: 'Asistencia',
                            type: 'pie',
                            radius: ['45%', '70%'],
                            avoidLabelOverlap: false,
                            itemStyle: {
                                borderRadius: 6,
                                borderColor: 'var(--color-bg-alt)',
                                borderWidth: 2
                            },
                            label: { show: false, position: 'center' },
                            emphasis: {
                                label: { show: true, fontSize: 16, fontWeight: 'bold', color: chartTextColor }
                            },
                            labelLine: { show: false },
                            data: total === 0 ? [{ value: 1, name: 'Sin registros', itemStyle: { color: chartBorderColor }, label: { show: true, position: 'center', fontSize: 14, color: chartTextMuted, fontWeight: 'bold' }, emphasis: { label: { color: chartTextMuted } } }] : [
                                { value: countPresente, name: 'Presente', itemStyle: { color: '#10B981' } },
                                { value: countJustificado, name: 'Justificado', itemStyle: { color: '#F59E0B' } },
                                { value: countAusente, name: 'Ausente', itemStyle: { color: '#EF4444' } }
                            ].filter(d => d.value > 0)
                        }
                    ]
                };
                chartDona.setOption(optionDona);
            }

            // Calendario interactivo mensual
            let currentYear = new Date().getFullYear();
            let currentMonth = new Date().getMonth();

            const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

            function renderCalendar(year, month) {
                const grid = document.getElementById('calendar-grid');
                const monthLabel = document.getElementById('calendar-month-year');
                if (!grid || !monthLabel) return;

                monthLabel.textContent = `${monthNames[month]} ${year}`;
                grid.innerHTML = '';

                let firstDay = new Date(year, month, 1).getDay();
                firstDay = firstDay === 0 ? 6 : firstDay - 1;

                const daysInMonth = new Date(year, month + 1, 0).getDate();
                const today = new Date();
                const isCurrentMonth = today.getFullYear() === year && today.getMonth() === month;

                for (let i = 0; i < firstDay; i++) {
                    const emptyDiv = document.createElement('div');
                    emptyDiv.className = 'calendar-day empty';
                    grid.appendChild(emptyDiv);
                }

                for (let i = 1; i <= daysInMonth; i++) {
                    const dayDiv = document.createElement('div');
                    dayDiv.className = 'calendar-day';
                    if (isCurrentMonth && i === today.getDate()) {
                        dayDiv.classList.add('today');
                    }

                    const spanNum = document.createElement('span');
                    spanNum.className = 'day-num';
                    spanNum.textContent = i;
                    dayDiv.appendChild(spanNum);

                    const mStr = String(month + 1).padStart(2, '0');
                    const dStr = String(i).padStart(2, '0');
                    const dateStr = `${year}-${mStr}-${dStr}`;

                    const dayRecords = historialAsistenciasData.filter(a => {
                        return a.fecha && a.fecha.substring(0, 10) === dateStr;
                    });

                    if (dayRecords.length > 0) {
                        const dotsContainer = document.createElement('div');
                        dotsContainer.className = 'status-dots-container';

                        dayRecords.slice(0, 3).forEach(r => {
                            const dot = document.createElement('div');
                            dot.className = 'status-dot';
                            const estatus = parseInt(r.estatus);
                            const tipo = parseInt(r.tipo_actividad);

                            if (estatus === 1) dot.classList.add('presente');
                            else if (estatus === 2) dot.classList.add('justificado');
                            else if (estatus === 0) dot.classList.add('ausente');

                            const txtEstatus = estatus === 1 ? 'Presente' : (estatus === 2 ? 'Justificado' : 'Ausente');
                            const txtTipo = tipo === 0 ? 'Partido' : (tipo === 1 ? 'Entrenamiento' : 'Otro');
                            dayDiv.title = dayDiv.title ? dayDiv.title + `\n${txtTipo}: ${txtEstatus}` : `${txtTipo}: ${txtEstatus}`;

                            dotsContainer.appendChild(dot);
                        });

                        dayDiv.appendChild(dotsContainer);
                    }
                    grid.appendChild(dayDiv);
                }
            }

            renderCalendar(currentYear, currentMonth);

            document.getElementById('btn-prev-month')?.addEventListener('click', () => {
                currentMonth--;
                if (currentMonth < 0) { currentMonth = 11; currentYear--; }
                renderCalendar(currentYear, currentMonth);
            });

            document.getElementById('btn-next-month')?.addEventListener('click', () => {
                currentMonth++;
                if (currentMonth > 11) { currentMonth = 0; currentYear++; }
                renderCalendar(currentYear, currentMonth);
            });

            paginateTable('tabla-asistencias', 5);
        }

        // —— Lógica de Direcciones Dinámicas (Estado -> Municipio -> Parroquia)
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
                if (selectedId) cargarMunicipios(selectedId, <?= (int) ($atleta['municipio_id'] ?? 0) ?>);
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
                if (selectedId) cargarParroquias(selectedId, <?= (int) ($atleta['parroquias_id'] ?? 0) ?>);
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

        if (selectEstado && <?= (int) ($atleta['estado_id'] ?? 0) ?> > 0) {
            cargarEstados(selectPais.value, <?= (int) ($atleta['estado_id'] ?? 0) ?>);
        } else if (selectEstado) {
            cargarEstados(selectPais.value);
        }

        window.addEventListener('resize', () => {
            if (chartAntro) chartAntro.resize();
            if (chartRadar) chartRadar.resize();
            if (chartDona) chartDona.resize();
        });

        // —— Lógica de Zona de Carga (Foto del Atleta) ———————————————————————
        const uploadZone = document.getElementById('upload-zone-foto');
        const fileInput = document.getElementById('input-foto-file');
        const filenameDisplay = document.getElementById('foto-filename');

        uploadZone?.addEventListener('click', () => fileInput.click());
        fileInput?.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                filenameDisplay.textContent = e.target.files[0].name;
                filenameDisplay.style.color = 'var(--color-primary)';
                filenameDisplay.style.fontWeight = '600';
            }
        });
        uploadZone?.addEventListener('dragover', (e) => { e.preventDefault(); uploadZone.classList.add('dragover'); });
        uploadZone?.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
        uploadZone?.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                filenameDisplay.textContent = e.dataTransfer.files[0].name;
                filenameDisplay.style.color = 'var(--color-primary)';
                filenameDisplay.style.fontWeight = '600';
            }
        });

        // CSS Dinámico para efectos de foto y botones
        const style = document.createElement('style');
        style.innerHTML = `
            #btn-abrir-editar-foto:hover .photo-overlay { opacity: 1 !important; }
            #btn-abrir-editar-foto:hover .hover-scale { transform: scale(1.02); }
            .alert-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.2); font-size: 14px; line-height: 1.4; }
            .btn, .btn-icon, .tab-btn { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; }
            .btn:active, .btn-icon:active { transform: scale(0.95); }
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
            #btn-abrir-editar-basico { position: absolute; top: 12px; right: 12px; }
            .upload-zone {
                border: 2px dashed var(--color-border);
                border-radius: 12px;
                padding: 32px 16px;
                cursor: pointer;
                transition: all 0.2s;
                background: var(--color-bg-alt);
                position: relative;
            }
            .upload-zone:hover {
                border-color: var(--color-primary);
                background: var(--color-primary-light);
            }
            .upload-zone.dragover {
                border-color: var(--color-primary);
                background: rgba(37, 99, 235, 0.1);
                transform: scale(1.02);
            }
            .upload-content i { font-size: 40px; color: var(--color-primary); margin-bottom: 8px; display: block; }
            .upload-content p { font-weight: 600; margin: 0; color: var(--color-text); }
            .upload-content span { font-size: 12px; color: var(--color-text-muted); }
            @keyframes scaleIn {
                from { transform: scale(0.8); opacity: 0; }
                to { transform: scale(1); opacity: 1; }
            }
            .success-animation { animation: scaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
        `;
        document.head.appendChild(style);

        // Generic Pagination Script
        function paginateTable(tableId, rowsPerPage = 5) {
            const table = document.getElementById(tableId);
            if (!table) return;

            let rows = [];
            const tbody = table.querySelector('tbody');
            if (tbody) {
                rows = Array.from(tbody.querySelectorAll('tr'));
            } else {
                rows = Array.from(table.querySelectorAll('.perfil-table-row'));
            }

            if (!rows || rows.length === 0) return;

            if (rows.length === 1 && rows[0] && (rows[0].innerText.includes('No hay') || rows[0].innerText.includes('registradas') || rows[0].innerText.includes('registradas aún'))) return;

            if (rows.length <= rowsPerPage) {
                const existingControls = document.getElementById(tableId + '-pagination');
                if (existingControls) existingControls.remove();
                return;
            }

            let controls = document.getElementById(tableId + '-pagination');
            if (!controls) {
                controls = document.createElement('div');
                controls.id = tableId + '-pagination';
                controls.style.display = 'flex';
                controls.style.justifyContent = 'center';
                controls.style.marginTop = '24px';
                controls.style.paddingBottom = '16px';
                table.parentNode.appendChild(controls);
            }

            const uniqueClassName = `row-to-paginate-${tableId}`;
            rows.forEach(r => r.classList.add(uniqueClassName));

            if (typeof window.CadaPagination === 'function') {
                window.CadaPagination({
                    rowSelector: `.${uniqueClassName}`,
                    containerId: `${tableId}-pagination`,
                    rowsPerPage: rowsPerPage
                });
            }
        }

        // —— Validaciones de Cédula y Widgets ————————————————————————————————
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

        function setupCedulaWidget(prefixId, numberId, hiddenId) {
            const prefixEl = document.getElementById(prefixId);
            const numberEl = document.getElementById(numberId);
            const hiddenEl = document.getElementById(hiddenId);
            if (!prefixEl || !numberEl || !hiddenEl) return;

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
                    val = (y || a || f) ? `${y}-${a}-${f}` : '';
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

            function updateUI(isInit = false) {
                if (!isInit) {
                    numberEl.value = '';
                    hiddenEl.value = '';
                }
                if (prefixEl.value === 'N') {
                    numberEl.style.display = 'none';
                    if (folioInputs) {
                        folioInputs.style.display = 'flex';
                        if (!isInit) {
                            fYear.value = ''; fActa.value = ''; fFolio.value = '';
                            fYear.focus();
                        }
                    } else {
                        numberEl.style.display = 'block';
                        numberEl.placeholder = "Cód. Partida";
                        numberEl.maxLength = 15;
                        if (!isInit) numberEl.focus();
                    }
                } else {
                    if (folioInputs) folioInputs.style.display = 'none';
                    numberEl.style.display = 'block';
                    if (prefixEl.value === 'P') {
                        numberEl.placeholder = "ABC123456";
                        numberEl.maxLength = 15;
                    } else {
                        numberEl.placeholder = "12.345.678";
                        numberEl.maxLength = 10;
                    }
                    if (!isInit) numberEl.focus();
                }
                sync();
            }

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
            updateUI(true);

            numberEl.addEventListener('input', sync);
            if (folioInputs) {
                fYear.addEventListener('input', sync);
                fActa.addEventListener('input', sync);
                fFolio?.addEventListener('input', sync);
            }

            prefixEl.addEventListener('change', () => {
                updateUI(false);
            });
        }

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

        // Inicializar widgets de Datos Generales
        setupCedulaWidget('cedula_prefix', 'cedula_number', 'cedula');
        setupCedulaWidget('tutor_cedula_prefix', 'tutor_cedula_number', 'tutor_cedula');
        setupPhoneWidget('telefono_prefix', 'telefono_number', 'telefono');
        setupPhoneWidget('tutor_telefono_prefix', 'tutor_telefono_number', 'tutor_telefono');

        // —— Lógica de los Modales de Edición (Datos Generales, Representante, Dirección, Foto) ——
        const formsEdit = [
            { id: 'basico', modal: 'modal-editar-basico', form: 'form-editar-basico', error: 'error-basico', tab: 'tab-general' },
            { id: 'representante', modal: 'modal-editar-representante', form: 'form-editar-representante', error: 'error-representante', tab: 'tab-general' },
            { id: 'direccion', modal: 'modal-editar-direccion', form: 'form-editar-direccion', error: 'error-direccion', tab: 'tab-general' },
            { id: 'foto', modal: 'modal-editar-foto', form: 'form-editar-foto', error: 'error-foto', tab: 'tab-general' }
        ];

        formsEdit.forEach(item => {
            const modal = document.getElementById(item.modal);
            const form = document.getElementById(item.form);
            const errorDiv = document.getElementById(item.error);
            const btnAbrir = document.getElementById(`btn-abrir-editar-${item.id}`);

            btnAbrir?.addEventListener('click', () => {
                if (errorDiv) errorDiv.style.display = 'none';
                modal.style.display = 'flex';
            });

            form?.addEventListener('focusin', (e) => {
                if (e.target.matches('input, select, textarea')) {
                    FormValidator.clearMark(e.target);
                }
            });

            form?.addEventListener('submit', async (e) => {
                e.preventDefault();

                // 1. Validaciones extra/custom
                let customVal = null;
                if (item.id === 'basico') {
                    customVal = validarBasicoCustom;
                } else if (item.id === 'representante') {
                    customVal = validarRepresentanteCustom;
                }

                // 2. Ejecutar validación de FormValidator
                const validation = FormValidator.validate(form, customVal);
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

                // Validación inteligente para subida/eliminación de foto de perfil
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
                        headers: { 'Accept': 'application/json' }
                    });

                    const result = await response.json();

                    if (result.success) {
                        modal.style.display = 'none';

                        CadaModal.alert({
                            title: '¡Éxito!',
                            text: result.message || 'Cambios guardados correctamente.',
                            type: 'success',
                            confirmText: 'Aceptar'
                        }).then(() => {
                            const currentTab = new URLSearchParams(window.location.search).get('tab') || 'tab-general';
                            window.location.href = window.location.pathname + '?tab=' + currentTab;
                        });
                    } else {
                        // Si hay errores de validación específicos del backend, marcamos los inputs
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
                            FormValidator.showErrors(errorsList);
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

        // —— Validadores Custom para FormValidator ——————————————————————————
        function validarBasicoCustom(form) {
            const errors = [];
            const birthVal = form.querySelector('[name="fecha_nacimiento"]').value;
            let age = 0;
            if (birthVal) {
                const birthDate = new Date(birthVal);
                const today = new Date();
                age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
            }

            const cedulaVal = document.getElementById('cedula').value;
            const prefixVal = document.getElementById('cedula_prefix').value;
            
            if (prefixVal === 'N') {
                const y = document.getElementById('folio_year').value;
                const a = document.getElementById('folio_acta').value;
                if (age > 9 && (!y || !a)) {
                    errors.push({
                        element: document.getElementById('phone-wrap-cedula'),
                        message: 'El Código de Acta de Nacimiento (Año y Acta) es obligatorio para mayores de 9 años'
                    });
                } else if (y || a) {
                    if (!validarCedula(cedulaVal)) {
                        errors.push({
                            element: document.getElementById('phone-wrap-cedula'),
                            message: 'Formato de Código de Acta de Nacimiento inválido (Año-Acta)'
                        });
                    }
                    if (birthVal) {
                        const birthYear = new Date(birthVal).getFullYear();
                        const certYear = parseInt(y, 10);
                        if (certYear < birthYear) {
                            errors.push({
                                element: document.getElementById('phone-wrap-cedula'),
                                message: 'El año del acta de nacimiento no puede ser menor al año de nacimiento del atleta.'
                            });
                        }
                    }
                }
            } else if (age > 9) {
                const docName = (prefixVal === 'P') ? 'Pasaporte' : 'Cédula';
                if (!cedulaVal) {
                    errors.push({
                        element: document.getElementById('phone-wrap-cedula'),
                        message: 'El ' + docName + ' es obligatorio para mayores de 9 años'
                    });
                } else if (!validarCedula(cedulaVal)) {
                    errors.push({
                        element: document.getElementById('phone-wrap-cedula'),
                        message: 'Formato de ' + docName + ' inválido'
                    });
                }
            } else if (cedulaVal) {
                if (!validarCedula(cedulaVal)) {
                    errors.push({
                        element: document.getElementById('phone-wrap-cedula'),
                        message: 'Formato de documento inválido'
                    });
                }
            }

            const telefonoVal = document.getElementById('telefono').value;
            const telefonoNum = document.getElementById('telefono_number').value;
            if (age >= 18) {
                if (!telefonoVal) {
                    errors.push({
                        element: document.getElementById('phone-wrap-telefono'),
                        message: 'El Teléfono Personal es obligatorio para mayores de edad'
                    });
                } else if (telefonoNum.length !== 7) {
                    errors.push({
                        element: document.getElementById('phone-wrap-telefono'),
                        message: 'El Teléfono Personal debe tener exactamente 7 dígitos'
                    });
                }
            } else if (telefonoVal) {
                if (telefonoNum.length !== 7) {
                    errors.push({
                        element: document.getElementById('phone-wrap-telefono'),
                        message: 'El Teléfono Personal debe tener exactamente 7 dígitos'
                    });
                }
            }

            return errors;
        }

        function validarRepresentanteCustom(form) {
            const errors = [];
            const tutorCedulaVal = document.getElementById('tutor_cedula').value;
            const tutorTelefonoVal = document.getElementById('tutor_telefono').value;
            const tutorTelefonoNum = document.getElementById('tutor_telefono_number').value;

            if (!tutorCedulaVal) {
                errors.push({ element: document.getElementById('phone-wrap-tutor_cedula'), message: 'La Cédula o Pasaporte del Representante es obligatoria' });
            } else if (!validarCedula(tutorCedulaVal)) {
                errors.push({ element: document.getElementById('phone-wrap-tutor_cedula'), message: 'Formato de Cédula o Pasaporte del Representante inválido' });
            }
            if (!tutorTelefonoVal) {
                errors.push({ element: document.getElementById('phone-wrap-tutor_telefono'), message: 'El Teléfono del Representante es obligatorio' });
            } else if (tutorTelefonoNum.length !== 7) {
                errors.push({ element: document.getElementById('phone-wrap-tutor_telefono'), message: 'El Teléfono del Representante debe tener exactamente 7 dígitos' });
            }

            return errors;
        }

        function validarMedicionCustom(form) {
            const errors = [];
            const fechaInput = form.querySelector('[name="fecha_medicion"]');
            if (fechaInput) {
                const fechaVal = fechaInput.value;
                if (fechaVal) {
                    const selectedDate = new Date(fechaVal + 'T00:00:00');
                    const today = new Date();
                    today.setHours(0,0,0,0);
                    if (selectedDate > today) {
                        errors.push({
                            element: fechaInput,
                            message: 'La fecha de medición no puede ser en el futuro'
                        });
                    }
                }
            }

            const campos = ['peso', 'altura', 'porcentaje_grasa', 'porcentaje_musculatura', 'envergadura', 'largo_de_pierna', 'largo_de_torso'];
            let filledCount = 0;
            campos.forEach(campo => {
                const input = form.querySelector(`[name="${campo}"]`);
                if (input && input.value && input.value.trim() !== '') {
                    filledCount++;
                }
            });

            if (filledCount === 0) {
                const firstInput = form.querySelector('[name="peso"]');
                errors.push({
                    element: firstInput,
                    message: 'Debe ingresar al menos una medición (Peso, Altura, % Grasa, % Musculatura, Envergadura, Pierna o Torso)'
                });
                campos.forEach(campo => {
                    const input = form.querySelector(`[name="${campo}"]`);
                    if (input) {
                        FormValidator.markError(input);
                    }
                });
            }

            return errors;
        }

        function validarPruebaCustom(form) {
            const errors = [];
            const fechaInput = form.querySelector('[name="fecha_evaluacion"]');
            if (fechaInput) {
                const fechaVal = fechaInput.value;
                if (fechaVal) {
                    const selectedDate = new Date(fechaVal + 'T00:00:00');
                    const today = new Date();
                    today.setHours(0,0,0,0);
                    if (selectedDate > today) {
                        errors.push({
                            element: fechaInput,
                            message: 'La fecha de evaluación no puede ser en el futuro'
                        });
                    }
                }
            }

            const campos = ['test_de_fuerza', 'test_resistencia', 'test_velocidad', 'test_coordinacion', 'test_de_reaccion'];
            let filledCount = 0;
            campos.forEach(campo => {
                const input = form.querySelector(`[name="${campo}"]`);
                if (input && input.value && input.value.trim() !== '') {
                    filledCount++;
                }
            });

            if (filledCount === 0) {
                const firstInput = form.querySelector('[name="test_de_fuerza"]');
                errors.push({
                    element: firstInput,
                    message: 'Debe ingresar al menos un resultado de test (Fuerza, Resistencia, Velocidad, Coordinación o Reacción)'
                });
                campos.forEach(campo => {
                    const input = form.querySelector(`[name="${campo}"]`);
                    if (input) {
                        FormValidator.markError(input);
                    }
                });
            }

            return errors;
        }

        // —— Actualización Dinámica de Asteriscos de Obligatoriedad ————————
        function updateRequiredLabels() {
            const birthInput = document.querySelector('#form-editar-basico [name="fecha_nacimiento"]');
            if (!birthInput) return;
            const birthVal = birthInput.value;
            if (!birthVal) return;
            const birthDate = new Date(birthVal);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            const labelCedula = document.getElementById('label-cedula');
            if (labelCedula) {
                if (age > 9) {
                    if (!labelCedula.querySelector('.required')) {
                        labelCedula.insertAdjacentHTML('afterbegin', '<span class="required">*</span> ');
                    }
                } else {
                    const reqSpan = labelCedula.querySelector('.required');
                    if (reqSpan) reqSpan.remove();
                }
            }
            
            const labelTelefono = document.getElementById('label-telefono');
            if (labelTelefono) {
                if (age >= 18) {
                    if (!labelTelefono.querySelector('.required')) {
                        labelTelefono.insertAdjacentHTML('afterbegin', '<span class="required">*</span> ');
                    }
                } else {
                    const reqSpan = labelTelefono.querySelector('.required');
                    if (reqSpan) reqSpan.remove();
                }
            }
        }

        const birthInput = document.querySelector('#form-editar-basico [name="fecha_nacimiento"]');
        if (birthInput) {
            birthInput.addEventListener('change', updateRequiredLabels);
            updateRequiredLabels();
        }

        // —— Botones de Ayuda en Modales Generales —————————————————————————
        document.getElementById('btn-help-basico')?.addEventListener('click', () => {
            FormValidator.showHelp(
                'Guía: Datos Básicos',
                '<?= e(asset("img/ayuda/formulario_atleta.png")) ?>'
            );
        });

        document.getElementById('btn-help-representante')?.addEventListener('click', () => {
            FormValidator.showHelp(
                'Guía: Editar Representante',
                '<?= e(asset("img/ayuda/formulario_atleta.png")) ?>'
            );
        });

        document.getElementById('btn-help-direccion')?.addEventListener('click', () => {
            FormValidator.showHelp(
                'Guía: Dirección Detallada',
                '<?= e(asset("img/ayuda/formulario_atleta.png")) ?>'
            );
        });

    });
</script>
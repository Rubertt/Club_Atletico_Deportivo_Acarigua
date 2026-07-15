/**
 * FormValidator — Estándar unificado de validación para todos los formularios del sistema.
 *
 * Comportamiento:
 * - NO valida mientras el usuario escribe.
 * - SOLO valida al hacer clic en el botón de envío.
 * - Pinta bordes rojos en los campos con error.
 * - Muestra UN SOLO CadaModal.alert con la lista de errores.
 * - Al hacer focus en un campo con error, el borde rojo desaparece.
 *
 * Uso:
 *   FormValidator.init('#mi-formulario');                     // Formulario simple
 *   FormValidator.init('#mi-formulario', { custom: fn });     // Con validaciones extra
 *   FormValidator.validate(formElement);                      // Validar manualmente (wizard)
 */
const FormValidator = (() => {

    /**
     * Marca un campo con borde rojo de error.
     */
    function markError(el) {
        if (!el) return;
        // Para widgets compuestos (phone-field, etc.)
        const wrap = el.closest('.phone-field');
        if (wrap) {
            wrap.style.borderColor = 'var(--color-danger, #e53e3e)';
        } else {
            el.style.borderColor = 'var(--color-danger, #e53e3e)';
        }
    }

    /**
     * Quita el borde rojo de un campo.
     */
    function clearMark(el) {
        if (!el) return;
        const wrap = el.closest('.phone-field');
        if (wrap) {
            wrap.style.borderColor = '';
        } else {
            el.style.borderColor = '';
        }
    }

    /**
     * Obtiene el label legible de un campo.
     */
    function getLabel(input) {
        const fg = input.closest('.form-group');
        if (fg) {
            const labelEl = fg.querySelector('.form-label, label');
            if (labelEl) return labelEl.textContent.replace('*', '').trim();
        }
        return input.name || input.id || 'Campo';
    }

    /**
     * Valida un conjunto de campos y retorna la lista de errores.
     * @param {HTMLElement} container - El form o un panel/div que contiene los campos.
     * @param {Function|null} customValidator - Función extra que retorna array de {field, label, element}.
     * @returns {{ valid: boolean, errors: string[], elements: HTMLElement[] }}
     */
    function validate(container, customValidator = null) {
        const errors = [];
        const errorElements = [];

        // 1. Validar todos los campos requeridos vacíos
        const requiredInputs = container.querySelectorAll('[required]');
        requiredInputs.forEach(input => {
            // Ignorar inputs ocultos de widgets (ej: hidden de teléfono)
            if (input.type === 'hidden') return;

            const label = getLabel(input);

            if (!input.value || !input.value.trim()) {
                markError(input);
                errors.push(`El campo "${label}" es obligatorio`);
                errorElements.push(input);
            } else if (!input.checkValidity()) {
                markError(input);
                let msg = `El campo "${label}" tiene un valor inválido`;
                if (input.validity.tooShort) {
                    msg = `El campo "${label}" debe tener al menos ${input.minLength} caracteres`;
                } else if (input.validity.typeMismatch && input.type === 'email') {
                    msg = `El campo "${label}" debe ser un correo válido (ej: usuario@correo.com)`;
                } else if (input.validity.patternMismatch && input.title) {
                    msg = `${label}: ${input.title}`;
                } else if (input.validity.rangeOverflow) {
                    msg = `El campo "${label}" excede el valor máximo permitido`;
                } else if (input.validity.rangeUnderflow) {
                    msg = `El campo "${label}" es menor al valor mínimo permitido`;
                }
                errors.push(msg);
                errorElements.push(input);
            } else {
                clearMark(input);
            }
        });

        // 2. Validaciones custom (Documento de Identidad, teléfono, edades, etc.)
        if (typeof customValidator === 'function') {
            const customErrors = customValidator(container);
            if (Array.isArray(customErrors)) {
                customErrors.forEach(err => {
                    errors.push(err.label || err.message || err);
                    if (err.element) {
                        markError(err.element);
                        errorElements.push(err.element);
                    }
                });
            }
        }

        return {
            valid: errors.length === 0,
            errors,
            elements: errorElements
        };
    }

    /**
     * Muestra el CadaModal con la lista de errores.
     */
    function showErrors(errors) {
        const list = errors.map(e => `• ${e}`).join('<br>');
        if (typeof CadaModal !== 'undefined' && CadaModal.alert) {
            CadaModal.alert({
                title: 'Campos Incompletos',
                text: `Por favor revisa lo siguiente:<br><br>${list}`,
                type: 'warning',
                confirmText: 'Corregir ahora'
            });
        }
    }

    /**
     * Inicializa la validación estándar en un formulario.
     * @param {string|HTMLElement} formSelector - Selector CSS o elemento del formulario.
     * @param {Object} options
     * @param {Function} options.custom - Validador custom que retorna array de errores.
     * @param {Function} options.onSuccess - Callback si la validación pasa (para wizards).
     */
    function init(formSelector, options = {}) {
        const form = typeof formSelector === 'string'
            ? document.querySelector(formSelector)
            : formSelector;

        if (!form) return;

        // Asegurar novalidate
        form.setAttribute('novalidate', '');

        // Limpiar borde rojo al hacer focus en cualquier campo
        form.addEventListener('focusin', (e) => {
            const input = e.target;
            if (input.matches('input, select, textarea')) {
                clearMark(input);
            }
        });

        // Interceptar submit
        form.addEventListener('submit', (e) => {
            const result = validate(form, options.custom || null);

            if (!result.valid) {
                e.preventDefault();
                e.stopImmediatePropagation();
                showErrors(result.errors);

                // Scroll al primer campo con error
                if (result.elements.length > 0) {
                    const first = result.elements[0];
                    const wrap = first.closest('.phone-field') || first;
                    wrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }

    /**
     * Abre un CadaModal con la imagen de ayuda del formulario.
     * @param {string} title - Título del modal.
     * @param {string|null} imageSrc - Ruta de la imagen de ayuda (null para omitir).
     * @param {string} descriptionText - Texto HTML descriptivo.
     */
    function showHelp(title, imageSrc, descriptionText = '') {
        if (typeof CadaModal !== 'undefined' && CadaModal.alert) {
            let textHtml = '';
            if (descriptionText) {
                textHtml += `<p style="font-size: 14px; color: var(--color-text, #e2e8f0); margin-bottom: 16px; text-align: left; line-height: 1.5;">${descriptionText}</p>`;
            }
            if (imageSrc) {
                textHtml += `<img src="${imageSrc}" alt="Guía del formulario" style="width:100%; border-radius:8px;">`;
            }
            CadaModal.alert({
                title: title,
                text: textHtml,
                type: 'info',
                confirmText: 'Cerrar'
            });
        }
    }

    /**
     * Muestra errores de campos específicos vía CadaModal.
     * @param {Object} fieldErrors - Objeto { nombre_campo: 'mensaje error' }
     */
    function showFieldErrors(fieldErrors) {
        const errors = Object.values(fieldErrors);
        showErrors(errors);
    }

    return { init, validate, showErrors, showFieldErrors, showHelp, markError, clearMark, getLabel };
})();

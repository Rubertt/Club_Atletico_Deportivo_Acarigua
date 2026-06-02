<?php
/**
 * @var string $title
 * @var string $active
 * @var array $configs
 */
?>
<div class="page-header">
    <div>
        <h1>Configuración General</h1>
        <div class="subtitle">Ajustes del sistema y datos de la comunidad</div>
    </div>
</div>

<form id="form-configuracion" method="POST" action="<?= e(url('/admin/configuracion')) ?>" novalidate>
    <?= csrf_field() ?>

    <div class="form-tabs">
        <button type="button" class="tab-btn active" data-target="tab-general">
            <i class="ph ph-gear"></i> General y Sesión
        </button>
        <button type="button" class="tab-btn" data-target="tab-identidad">
            <i class="ph ph-flag-banner"></i> Identidad de la Comunidad
        </button>
        <button type="button" class="tab-btn" data-target="tab-contacto">
            <i class="ph ph-phone"></i> Contacto y Redes
        </button>
    </div>

    <!-- Pestaña: General y Sesión -->
    <div id="tab-general" class="form-tab-panel active">
        <div class="form-card" style="background:var(--color-bg); padding:24px; border:1px solid var(--color-border); border-radius:var(--radius); margin-bottom:24px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                <h3 style="margin:0; color:var(--color-primary);">Ajustes de Sesión</h3>
                <button type="button" class="btn-help" id="btn-help-general" title="Ayuda sobre sesión">
                    <i class="ph ph-question"></i>
                </button>
            </div>
            <div class="form-group">
                <label class="form-label" for="tiempo_sesion" data-tooltip="Tiempo máximo de inactividad antes de cerrar la sesión automáticamente. Mínimo 5 minutos, máximo 480 minutos." data-tooltip-pos="top"><span class="required">*</span> Tiempo de expiración de sesión (en minutos)</label>
                <input type="number" name="tiempo_sesion" id="tiempo_sesion" class="form-control" 
                       value="<?= e($configs['tiempo_sesion'] ?? '120') ?>" required min="5" max="480"
                       data-label="Tiempo de sesión">
                <p class="form-help" style="font-size:12px; color:var(--color-text-muted); margin-top:4px;">
                    Mínimo 5 minutos · Máximo 480 minutos (8 horas).
                </p>
            </div>
        </div>
    </div>

    <!-- Pestaña: Identidad -->
    <div id="tab-identidad" class="form-tab-panel">
        <div class="form-card" style="background:var(--color-bg); padding:24px; border:1px solid var(--color-border); border-radius:var(--radius); margin-bottom:24px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                <h3 style="margin:0; color:var(--color-primary);">Identidad de la Comunidad</h3>
                <button type="button" class="btn-help" id="btn-help-identidad" title="Ayuda sobre identidad">
                    <i class="ph ph-question"></i>
                </button>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="mision" data-tooltip="Declaración del propósito del club: qué hace y para quién existe." data-tooltip-pos="top"><span class="required">*</span> Misión</label>
                <textarea name="mision" id="mision" class="form-control" rows="4" required
                          data-label="Misión"><?= e($configs['mision'] ?? '') ?></textarea>
            </div>

            <div class="form-group" style="margin-top:16px;">
                <label class="form-label" for="vision" data-tooltip="Meta a largo plazo del club: qué aspira lograr en el futuro." data-tooltip-pos="top"><span class="required">*</span> Visión</label>
                <textarea name="vision" id="vision" class="form-control" rows="4" required
                          data-label="Visión"><?= e($configs['vision'] ?? '') ?></textarea>
            </div>

            <div class="form-group" style="margin-top:16px;">
                <label class="form-label" for="requisitos_inscripcion" data-tooltip="Listado de documentos y condiciones necesarios para inscribirse en el club. Se muestra al público." data-tooltip-pos="top"><span class="required">*</span> Requisitos para la Inscripción</label>
                <textarea name="requisitos_inscripcion" id="requisitos_inscripcion" class="form-control" rows="4" required
                          data-label="Requisitos de inscripción"><?= e($configs['requisitos_inscripcion'] ?? '') ?></textarea>
                <p class="form-help" style="font-size:12px; color:var(--color-text-muted); margin-top:4px;">
                    Estos requisitos se mostrarán en la página de Nosotros al público.
                </p>
            </div>
        </div>
    </div>

    <!-- Pestaña: Contacto y Redes -->
    <div id="tab-contacto" class="form-tab-panel">
        <div class="form-card" style="background:var(--color-bg); padding:24px; border:1px solid var(--color-border); border-radius:var(--radius); margin-bottom:24px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                <h3 style="margin:0; color:var(--color-primary);">Información de Contacto y Redes</h3>
                <button type="button" class="btn-help" id="btn-help-contacto" title="Ayuda sobre contacto">
                    <i class="ph ph-question"></i>
                </button>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="correo_contacto" data-tooltip="Correo electrónico oficial del club para recibir mensajes del público." data-tooltip-pos="top"><span class="required">*</span> Correo Electrónico de Contacto</label>
                    <input type="email" name="correo_contacto" id="correo_contacto" class="form-control" 
                           value="<?= e($configs['correo_contacto'] ?? '') ?>" required
                           data-label="Correo de contacto">
                </div>
                <div class="form-group">
                    <label class="form-label" for="telefono_whatsapp" data-tooltip="Número de WhatsApp del club con prefijo internacional (ej: +584120000000)." data-tooltip-pos="top"><span class="required">*</span> Teléfono / WhatsApp</label>
                    <input type="text" name="telefono_whatsapp" id="telefono_whatsapp" class="form-control" 
                           value="<?= e($configs['telefono_whatsapp'] ?? '') ?>" 
                           placeholder="Ej: +584120000000" required
                           pattern="[\+0-9]+"
                           data-label="Teléfono WhatsApp">
                </div>
            </div>

            <div class="form-row" style="margin-top:16px;">
                <div class="form-group">
                    <label class="form-label" for="facebook_url" data-tooltip="Enlace completo al perfil o página de Facebook del club (opcional)." data-tooltip-pos="top">Enlace de Facebook</label>
                    <input type="url" name="facebook_url" id="facebook_url" class="form-control" 
                           value="<?= e($configs['facebook_url'] ?? '') ?>"
                           data-label="Enlace de Facebook">
                </div>
                <div class="form-group">
                    <label class="form-label" for="instagram_url" data-tooltip="Enlace completo al perfil de Instagram del club (opcional)." data-tooltip-pos="top">Enlace de Instagram</label>
                    <input type="url" name="instagram_url" id="instagram_url" class="form-control" 
                           value="<?= e($configs['instagram_url'] ?? '') ?>"
                           data-label="Enlace de Instagram">
                </div>
            </div>

            <div class="form-group" style="margin-top:16px;">
                <label class="form-label" for="google_maps_url" data-tooltip="Enlace de Google Maps apuntando a la ubicación física del club." data-tooltip-pos="top"><span class="required">*</span> Enlace de Google Maps (Ubicación)</label>
                <input type="url" name="google_maps_url" id="google_maps_url" class="form-control" 
                       value="<?= e($configs['google_maps_url'] ?? '') ?>" required
                       data-label="Enlace de Google Maps">
            </div>
        </div>
    </div>

    <div style="text-align: right;">
        <button type="submit" class="btn btn-primary btn-lg"><i class="ph ph-floppy-disk"></i> Guardar Cambios</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // ========== Tabs ==========
    const tabs = document.querySelectorAll('.tab-btn');
    const panels = document.querySelectorAll('.form-tab-panel');

    function switchToTab(tabId) {
        tabs.forEach(t => t.classList.remove('active'));
        panels.forEach(p => p.classList.remove('active'));
        const targetTab = document.querySelector('[data-target="' + tabId + '"]');
        if (targetTab) targetTab.classList.add('active');
        document.getElementById(tabId)?.classList.add('active');
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => switchToTab(tab.dataset.target));
    });

    // ========== Restricción solo números en teléfono ==========
    const telInput = document.getElementById('telefono_whatsapp');
    if (telInput) {
        telInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9+]/g, '');
        });
        telInput.addEventListener('keypress', (e) => {
            if (!/[0-9+]/.test(e.key) && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
            }
        });
    }

    // ========== Botones de Ayuda ==========
    document.getElementById('btn-help-general')?.addEventListener('click', () => {
        FormValidator.showHelp(
            'Ayuda: General y Sesión',
            null,
            '<p>El <strong>tiempo de expiración de sesión</strong> determina cuánto tiempo de inactividad ' +
            'puede pasar antes de que el sistema cierre tu sesión automáticamente.</p>' +
            '<p><strong>Mínimo:</strong> 5 minutos<br><strong>Máximo:</strong> 480 minutos (8 horas)</p>' +
            '<p>Si el campo está vacío o tiene un valor fuera de rango, no se guardará.</p>'
        );
    });

    document.getElementById('btn-help-identidad')?.addEventListener('click', () => {
        FormValidator.showHelp(
            'Ayuda: Identidad de la Comunidad',
            null,
            '<p>Aquí puedes editar la <strong>Misión</strong> y <strong>Visión</strong> del club, ' +
            'así como los <strong>Requisitos de Inscripción</strong> que se mostrarán al público en la página de Nosotros.</p>' +
            '<p>Todos los campos marcados con <span style="color:var(--color-danger);">*</span> son obligatorios.</p>'
        );
    });

    document.getElementById('btn-help-contacto')?.addEventListener('click', () => {
        FormValidator.showHelp(
            'Ayuda: Contacto y Redes',
            null,
            '<p>Configura la información de contacto del club que se mostrará públicamente en la página de Contacto y en el pie de página.</p>' +
            '<p><strong>Teléfono WhatsApp:</strong> Debe incluir el código de país sin espacios. Ejemplo: +584121234567</p>' +
            '<p><strong>Redes sociales:</strong> Pega la URL completa del perfil (ejemplo: https://facebook.com/tu_club)</p>'
        );
    });

    // ========== FormValidator ==========
    const form = document.getElementById('form-configuracion');

    // Interceptar submit para activar la pestaña del primer error
    form.addEventListener('submit', (e) => {
        // Buscar el primer campo required vacío o inválido en TODAS las pestañas
        const allRequired = form.querySelectorAll('[required]');
        let firstErrorPanel = null;

        for (const field of allRequired) {
            if (!field.value || !field.value.trim() || !field.checkValidity()) {
                const panel = field.closest('.form-tab-panel');
                if (panel) {
                    firstErrorPanel = panel.id;
                    break;
                }
            }
        }

        // Si hay error en pestaña oculta, activarla ANTES de que FormValidator procese
        if (firstErrorPanel) {
            switchToTab(firstErrorPanel);
        }
    }, true); // Captura para ejecutar ANTES del listener de FormValidator

    FormValidator.init('#form-configuracion', {
        custom: function(container) {
            const errors = [];
            const tiempoInput = document.getElementById('tiempo_sesion');
            if (tiempoInput) {
                const val = parseInt(tiempoInput.value);
                if (isNaN(val) || val < 5 || val > 480) {
                    errors.push({
                        label: 'El tiempo de sesión debe estar entre 5 y 480 minutos.',
                        element: tiempoInput
                    });
                }
            }
            return errors;
        }
    });
});
</script>


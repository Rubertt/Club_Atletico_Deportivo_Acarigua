<style>
/* =========================================================================
   Atletas Form — Premium Design
========================================================================= */

.af-container {
    max-width: 900px;
    margin: 0 auto;
    padding-bottom: 40px;
}

.af-header {
    margin-bottom: 24px;
    align-items: flex-end;
}

.af-header h1 {
    font-size: 28px;
    font-weight: 800;
    color: var(--color-text);
    margin: 0;
    font-family: var(--font-display);
}

.af-header .subtitle {
    margin: 4px 0 0;
}

.af-back-btn {
    padding: 8px 20px;
}

/* ✦ Card Estilizado ✦ */
.af-card {
    border: none;
    padding: 0;
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.08), 
                0 0 1px rgba(0,0,0,0.1);
    overflow: hidden;
    background: var(--color-bg);
    border-radius: var(--radius-lg);
    display: flex;
    flex-direction: column;
}

/* ✦ Tabs Premium ✦ */
.af-tabs-wrapper {
    background: var(--color-surface);
    border-bottom: 1px solid var(--color-border);
    padding: 0 24px;
}

.af-tabs {
    display: flex;
    gap: 0;
    overflow-x: auto;
    scrollbar-width: none;
}
.af-tabs::-webkit-scrollbar { display: none; }

.ft-tab {
    flex: 1;
    min-width: 140px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 16px 10px;
    border: none;
    background: transparent;
    cursor: default;
    position: relative;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    color: var(--color-text-muted);
}

.ft-tab__icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    background: var(--color-surface-2);
    transition: all 0.2s;
}

.ft-tab__text {
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
}



.ft-tab.active { color: var(--color-primary); }
.ft-tab.active .ft-tab__icon {
    background: var(--color-primary);
    color: #fff;
    box-shadow: 0 4px 12px rgba(190, 18, 60, 0.25);
}

.ft-tab::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 20%;
    right: 20%;
    height: 3px;
    background: var(--color-primary);
    border-radius: 3px 3px 0 0;
    transform: scaleX(0);
    transition: transform 0.2s;
}

.ft-tab.active::after { transform: scaleX(1); }

/* ✦ Cuerpo del formulario ✦ */
.af-body {
    padding: 32px 40px;
    min-height: 450px;
}

.form-tab-panel {
    display: none;
    animation: fadeInSlide .3s ease-out;
}
.form-tab-panel.active { display: block; }

@keyframes fadeInSlide {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ✦ Sección Header ✦ */
.af-section-header {
    display: flex;
    gap: 16px;
    margin-bottom: 28px;
    padding-bottom: 16px;
    border-bottom: 1px dashed var(--color-border);
}

.af-section-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: var(--color-primary-light);
    color: var(--color-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}

.af-section-info h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: var(--color-text);
}

.af-section-info p {
    margin: 2px 0 0;
    font-size: 13px;
    color: var(--color-text-muted);
}

/* ✦ Grid Responsivo ✦ */
.af-grid {
    display: grid;
    gap: 20px;
    margin-bottom: 8px;
}

.af-grid--2 { grid-template-columns: repeat(2, 1fr); }
.af-grid--3 { grid-template-columns: repeat(3, 1fr); }

@media (max-width: 768px) {
    .af-grid--2, .af-grid--3 { grid-template-columns: 1fr; }
    .af-body { padding: 24px; }
    .af-header { flex-direction: column; align-items: flex-start; gap: 16px; }
    .af-back-btn span { display: none; }
    .af-back-btn { padding: 8px; width: 40px; height: 40px; border-radius: 50%; }
}

/* —— Mejoras de Input —— */
.form-control {
    height: 44px;
    background: var(--color-surface);
    border-color: var(--color-border);
    transition: all 0.2s;
}

.form-control:focus {
    background: var(--color-bg);
    box-shadow: 0 0 0 4px rgba(190, 18, 60, 0.08);
}

select.form-control {
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

/* ✦ Upload de Foto ✦ */
.af-file-upload {
    display: flex;
    align-items: center;
    gap: 12px;
}

.af-file-input { display: none; }

.af-file-label {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: var(--color-surface-2);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    color: var(--color-text);
    transition: all 0.2s;
}

.af-file-label:hover { background: var(--color-border); }

.af-file-label.has-file {
    background: var(--color-primary);
    color: #fff !important;
    border-color: var(--color-primary);
}
.af-file-label.has-file * {
    color: #fff !important;
}

.af-file-preview {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    overflow: hidden;
    border: 2px solid var(--color-primary-light);
    position: relative;
}
.af-file-preview img { width: 100%; height: 100%; object-fit: cover; }

.af-file-remove {
    position: absolute;
    top: -2px;
    right: -2px;
    width: 18px;
    height: 18px;
    background: var(--color-danger);
    color: #fff;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* ✦ Widget Teléfono ✦ */
.phone-field {
    display: flex;
    align-items: stretch;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    overflow: hidden;
    background: var(--color-surface);
    transition: all 0.2s;
    height: 44px;
}
.phone-field:focus-within {
    border-color: var(--color-primary);
    background: var(--color-bg);
    box-shadow: 0 0 0 4px rgba(190, 18, 60, 0.08);
}
.phone-field .phone-prefix {
    border: none;
    background: var(--color-surface-2);
    font-weight: 700;
    font-size: 13px;
    padding: 0 24px 0 12px;
    cursor: pointer;
    border-right: 1px solid var(--color-border);
    color: var(--color-text) !important;
    outline: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%236b7280' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 8px center;
    background-size: 12px;
}
.phone-field .phone-prefix option {
    background-color: var(--color-surface, #1e293b) !important;
    color: var(--color-text, #f8fafc) !important;
}
.phone-field #cedula_prefix,
.phone-field #tutor_cedula_prefix {
    width: 60px !important;
    padding: 0 16px 0 10px !important;
    background-position: right 4px center !important;
}
.phone-field .phone-number {
    flex: 1;
    border: none;
    background: transparent;
    padding: 0 12px;
    font-size: 14px;
    outline: none;
    color: var(--color-text);
}
.phone-field .phone-sep {
    display: flex;
    align-items: center;
    color: var(--color-text-muted);
}

/* ✦ Footer ✦ */
.af-footer {
    padding: 24px 40px;
    background: var(--color-surface);
    border-top: 1px solid var(--color-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.af-footer-info {
    font-size: 13px;
    color: var(--color-text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
}

.af-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}

.af-actions-sep {
    width: 1px;
    height: 24px;
    background: var(--color-border);
    margin: 0 4px;
}

.af-submit-btn {
    padding: 10px 24px;
    gap: 10px;
}

@media (max-width: 600px) {
    .af-footer { flex-direction: column-reverse; padding: 24px; text-align: center; }
    .af-actions { width: 100%; flex-direction: column; }
    .af-actions .btn { width: 100%; }
}

.field-error {
    display: none;
    color: var(--color-danger);
    font-size: 12px;
    margin-top: 4px;
    font-weight: 500;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.spinner {
    display: inline-block;
    animation: spin 1s linear infinite;
}
</style>

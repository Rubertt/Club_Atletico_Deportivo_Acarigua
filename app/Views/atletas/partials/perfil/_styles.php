<style>
    .profile-tabs .tab-btn {
        padding: 16px 24px;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        color: var(--color-text-muted);
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .profile-tabs .tab-btn:hover {
        color: var(--color-primary);
    }

    .profile-tabs .tab-btn.active {
        color: var(--color-primary);
        border-bottom-color: var(--color-primary);
        font-weight: 600;
    }

    @media (max-width: 900px) {
        .show-layout {
            grid-template-columns: 1fr !important;
        }
    }

    @media print {

        .page-header .btn,
        .profile-tabs {
            display: none !important;
        }

        .card {
            box-shadow: none !important;
            border: 1px solid #ccc !important;
        }

        .tab-content {
            display: block !important;
            page-break-inside: avoid;
            margin-bottom: 30px;
        }
    }

    /* Calendario Interactivo Asistencias */
    .calendar-day {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
        position: relative;
    }
    .calendar-day.empty {
        background: transparent;
        cursor: default;
    }
    .calendar-day:not(.empty) {
        background: rgba(0,0,0,0.02);
    }
    .calendar-day:not(.empty):hover {
        background: var(--color-bg);
        border-color: var(--color-border);
        transform: scale(1.05);
        z-index: 10;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .calendar-day.today {
        border-color: var(--color-primary);
        font-weight: 700;
        color: var(--color-primary);
    }
    .calendar-day .day-num {
        z-index: 1;
    }
    .calendar-day .status-dots-container {
        position: absolute;
        bottom: 4px;
        display: flex;
        gap: 2px;
        justify-content: center;
        width: 100%;
    }
    .calendar-day .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }
    .calendar-day .status-dot.presente { background: #10B981; }
    .calendar-day .status-dot.ausente { background: #EF4444; }
    .calendar-day .status-dot.justificado { background: #F59E0B; }
    .calendar-day .status-dot.partido { background: var(--color-primary); width: 8px; height: 8px; transform: translateY(-1px); }
    .required {
        color: var(--color-danger, #e53e3e) !important;
        margin-right: 4px;
        font-weight: bold;
    }
    .modal-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }
    @media (max-width: 600px) {
        .modal-grid-2 {
            grid-template-columns: 1fr !important;
        }
    }

    /* —— Mejoras de Input y Form (Igual a Form Registro) —— */
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

    /* ✦ Widget Teléfono y Cédula ✦ */
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

    /* Dorsal circular */
    .dorsal-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: #ef4444; /* Rojo */
        font-size: 18px; /* Incrementado en 50% */
        font-weight: 700;
        color: #000000; /* Negro en modo claro */
    }

    html.dark .dorsal-circle {
        color: #ffffff; /* Blanco en modo oscuro */
    }

    /* —— Tablas Responsivas en Perfil (Patrón Flex/Grid) —— */
    .perfil-table-wrap {
        background: var(--color-bg);
        border: 1px solid var(--color-border);
        border-radius: var(--radius);
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: var(--shadow-sm);
    }

    .perfil-table-header {
        display: grid;
        gap: 16px;
        padding: 12px 20px;
        background: var(--color-surface-2);
        font-size: 13px;
        font-weight: 600;
        color: var(--color-text-muted);
        border-bottom: 1px solid var(--color-border);
        align-items: center;
    }

    .perfil-table-row {
        display: grid;
        gap: 16px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--color-border);
        align-items: center;
        transition: background 0.2s;
        background: var(--color-surface);
    }

    .perfil-table-row:hover {
        background: var(--color-bg-alt);
    }

    .perfil-table-row:last-child {
        border-bottom: 0;
    }

    .perfil-row-col {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    .perfil-col-label {
        display: none;
        font-size: 11px;
        font-weight: 600;
        color: var(--color-text-muted);
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    @media (max-width: 768px) {
        .perfil-table-header {
            display: none !important;
        }
        
        .perfil-table-row {
            grid-template-columns: 1fr !important;
            gap: 12px !important;
            padding: 16px !important;
        }
        
        .perfil-col-label {
            display: block;
        }
    }
</style>

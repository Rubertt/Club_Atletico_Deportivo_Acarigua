<?php /** @var array $roles @var array $permisos */ ?>
<div class="page-header">
    <div>
        <h1>Roles y Permisos</h1>
        <div class="subtitle">Definición de matrices de acceso por cargo</div>
    </div>
    <?php if (can('admin')): ?>
        <button class="btn btn-primary"><i class="ph ph-plus"></i> Nuevo Rol</button>
    <?php endif; ?>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 24px; border-bottom: 1px solid var(--color-border); background: var(--color-bg-alt);">
        <h3 style="margin: 0; display: flex; align-items: center; gap: 8px;">
            <i class="ph ph-shield-check" style="color: var(--color-primary); font-size: 24px;"></i> Matriz de Permisos
        </h3>
        <p class="text-muted" style="margin: 8px 0 0; font-size: 14px;">Administra lo que cada rol puede ver y editar dentro de la plataforma.</p>
    </div>

    <div class="data-table-wrap">
        <table class="data-table" style="margin: 0; border: none;">
            <thead style="background: var(--color-bg);">
                <tr>
                    <th style="padding-left: 24px; min-width: 200px;">Nivel de Rol</th>
                    <th style="text-align: center;">Atletas</th>
                    <th style="text-align: center;">Fichas Médicas</th>
                    <th style="text-align: center;">Personal</th>
                    <th style="text-align: center;">Usuarios</th>
                    <th style="text-align: center;">Reportes</th>
                    <th style="text-align: right; padding-right: 24px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Mock de datos para la matriz -->
                <tr>
                    <td style="padding-left: 24px;">
                        <div style="font-weight: 600; font-size: 15px;">Administrador</div>
                        <div style="font-size: 12px; color: var(--color-text-muted);">Acceso total al sistema</div>
                    </td>
                    <td style="text-align: center;"><i class="ph ph-check-circle" style="color: var(--color-success); font-size: 20px;"></i></td>
                    <td style="text-align: center;"><i class="ph ph-check-circle" style="color: var(--color-success); font-size: 20px;"></i></td>
                    <td style="text-align: center;"><i class="ph ph-check-circle" style="color: var(--color-success); font-size: 20px;"></i></td>
                    <td style="text-align: center;"><i class="ph ph-check-circle" style="color: var(--color-success); font-size: 20px;"></i></td>
                    <td style="text-align: center;"><i class="ph ph-check-circle" style="color: var(--color-success); font-size: 20px;"></i></td>
                    <td style="text-align: right; padding-right: 24px;">
                        <button class="btn btn-sm btn-ghost" disabled title="Rol de sistema"><i class="ph ph-lock-key text-muted"></i></button>
                    </td>
                </tr>
                <tr>
                    <td style="padding-left: 24px;">
                        <div style="font-weight: 600; font-size: 15px;">Entrenador</div>
                        <div style="font-size: 12px; color: var(--color-text-muted);">Gestión deportiva</div>
                    </td>
                    <td style="text-align: center;"><i class="ph ph-check-circle" style="color: var(--color-success); font-size: 20px;"></i></td>
                    <td style="text-align: center;"><i class="ph ph-minus-circle" style="color: var(--color-warning); font-size: 20px;" title="Solo Lectura"></i></td>
                    <td style="text-align: center;"><i class="ph ph-x-circle" style="color: var(--color-danger); font-size: 20px;"></i></td>
                    <td style="text-align: center;"><i class="ph ph-x-circle" style="color: var(--color-danger); font-size: 20px;"></i></td>
                    <td style="text-align: center;"><i class="ph ph-check-circle" style="color: var(--color-success); font-size: 20px;"></i></td>
                    <td style="text-align: right; padding-right: 24px;">
                        <button class="btn btn-sm btn-outline"><i class="ph ph-pencil-simple"></i></button>
                    </td>
                </tr>
                <tr>
                    <td style="padding-left: 24px;">
                        <div style="font-weight: 600; font-size: 15px;">Médico</div>
                        <div style="font-size: 12px; color: var(--color-text-muted);">Gestión de salud integral</div>
                    </td>
                    <td style="text-align: center;"><i class="ph ph-minus-circle" style="color: var(--color-warning); font-size: 20px;" title="Solo Lectura"></i></td>
                    <td style="text-align: center;"><i class="ph ph-check-circle" style="color: var(--color-success); font-size: 20px;"></i></td>
                    <td style="text-align: center;"><i class="ph ph-x-circle" style="color: var(--color-danger); font-size: 20px;"></i></td>
                    <td style="text-align: center;"><i class="ph ph-x-circle" style="color: var(--color-danger); font-size: 20px;"></i></td>
                    <td style="text-align: center;"><i class="ph ph-check-circle" style="color: var(--color-success); font-size: 20px;"></i></td>
                    <td style="text-align: right; padding-right: 24px;">
                        <button class="btn btn-sm btn-outline"><i class="ph ph-pencil-simple"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Editar Datos Personales -->
<div id="modal-editar-basico" class="modal-overlay" style="display:none;">
    <form id="form-editar-basico" action="<?= e(url("/admin/atletas/{$atleta['atleta_id']}")) ?>" method="POST"
        enctype="multipart/form-data" class="modal-container" style="max-width: 600px;" novalidate>
        <?= csrf_field() ?>
        <div class="modal-header">
            <h3 class="modal-title"><i class="ph ph-user-circle"></i> Editar Datos Básicos</h3>
            <button type="button" class="modal-close" data-close-modal>&times;</button>
        </div>
        <div class="modal-body">
            <div id="error-basico" class="alert alert-danger" style="display:none; margin-bottom: 16px;"></div>

            <div class="modal-grid-2">
                <div class="form-group">
                    <label class="form-label" data-tooltip="Nombres completos del atleta. Solo letras y espacios (mín. 2 caracteres)." data-tooltip-pos="top"><span class="required">*</span> Nombres</label>
                    <input type="text" name="nombre" class="form-control" value="<?= e($atleta['nombre']) ?>"
                        placeholder="Ej: Juan Carlos" required>
                </div>
                <div class="form-group">
                    <label class="form-label" data-tooltip="Apellidos completos del atleta. Solo letras y espacios (mín. 2 caracteres)." data-tooltip-pos="top"><span class="required">*</span> Apellidos</label>
                    <input type="text" name="apellido" class="form-control"
                        value="<?= e($atleta['apellido']) ?>" placeholder="Ej: Pérez Rodríguez" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" id="label-cedula" data-tooltip="Cédula (V/E-Número), Acta de nacimiento (menores: N-Año-Acta) o Pasaporte. Requerido." data-tooltip-pos="top"><span class="required">*</span>Documento de Identidad</label>
                <?php
                    $cedVal   = $atleta['cedula'];
                    $cedPref  = 'V';
                    $cedNum   = '';
                    if (!empty($cedVal)) {
                        if (str_contains($cedVal, '-')) {
                            [$cedPref, $cedNum] = explode('-', $cedVal, 2);
                        } else {
                            $firstChar = strtoupper($cedVal[0]);
                            if (in_array($firstChar, ['V', 'E', 'P', 'N'])) {
                                $cedPref = $firstChar;
                                $cedNum = substr($cedVal, 1);
                            } else {
                                $cedNum = $cedVal;
                            }
                        }
                    }
                ?>
                <div class="phone-field" id="phone-wrap-cedula" style="max-width: 280px;">
                    <select class="phone-prefix" id="cedula_prefix" aria-label="Prefijo">
                        <option value="V" <?= $cedPref==='V'?'selected':'' ?>>V</option>
                        <option value="E" <?= $cedPref==='E'?'selected':'' ?>>E</option>
                        <option value="P" <?= $cedPref==='P'?'selected':'' ?>>P</option>
                        <option value="N" <?= $cedPref==='N'?'selected':'' ?>>N</option>
                    </select>
                    <span class="phone-sep">-</span>
                    
                    <!-- Input para Cédula o Pasaporte -->
                    <input type="text" class="phone-number" id="cedula_number" style="display: <?= $cedPref !== 'N' ? 'block' : 'none' ?>;" maxlength="13" placeholder="12345678" value="<?= $cedPref !== 'N' ? e($cedNum) : '' ?>">
                    
                    <!-- Inputs para Partida -->
                    <div id="folio_inputs" style="display: <?= $cedPref === 'N' ? 'flex' : 'none' ?>; flex: 1; align-items: center;">
                        <?php
                            $fYear = ''; $fActa = '';
                            if ($cedPref === 'N') {
                                $fParts = explode('-', $cedNum);
                                if (count($fParts) >= 2) {
                                    $fYear = $fParts[0]; $fActa = $fParts[1];
                                } else {
                                    $fYear = $cedNum;
                                }
                            }
                        ?>
                        <input type="text" id="folio_year" class="phone-number" style="flex: 1; min-width: 0; width: 0; text-align: center;" placeholder="Año" maxlength="4" value="<?= e($fYear) ?>">
                        <span class="phone-sep">-</span>
                        <input type="text" id="folio_acta" class="phone-number" style="flex: 1; min-width: 0; width: 0; text-align: center;" placeholder="Acta" maxlength="5" value="<?= e($fActa) ?>">
                    </div>
                    
                    <input type="hidden" name="cedula" id="cedula" value="<?= e($cedVal) ?>" required>
                </div>
            </div>

            <div class="modal-grid-2">
                <div class="form-group">
                    <label class="form-label" id="label-telefono" data-tooltip="Teléfono móvil de contacto. Obligatorio para mayores de 18 años (11 dígitos, ej: 0412-1234567)." data-tooltip-pos="top"><span class="required">*</span> Teléfono Personal</label>
                    <?php
                        $telVal   = $atleta['telefono'];
                        $telPref  = '';
                        $telNum   = '';
                        foreach (['0412','0414','0416','0422','0424','0426','0255','0256'] as $_p) {
                            if (str_starts_with($telVal, $_p)) { $telPref = $_p; $telNum = substr($telVal, 4); break; }
                        }
                    ?>
                    <div class="phone-field" id="phone-wrap-telefono">
                        <select class="phone-prefix" id="telefono_prefix" aria-label="Prefijo">
                            <option value="0412" <?= $telPref==='0412'?'selected':'' ?>>0412</option>
                            <option value="0414" <?= $telPref==='0414'?'selected':'' ?>>0414</option>
                            <option value="0416" <?= $telPref==='0416'?'selected':'' ?>>0416</option>
                            <option value="0422" <?= $telPref==='0422'?'selected':'' ?>>0422</option>
                            <option value="0424" <?= $telPref==='0424'?'selected':'' ?>>0424</option>
                            <option value="0426" <?= $telPref==='0426'?'selected':'' ?>>0426</option>
                            <option value="0255" <?= $telPref==='0255'?'selected':'' ?>>0255</option>
                            <option value="0256" <?= $telPref==='0256'?'selected':'' ?>>0256</option>
                        </select>
                        <span class="phone-sep">-</span>
                        <input type="text" class="phone-number" id="telefono_number" maxlength="7" placeholder="1234567" value="<?= e($telNum) ?>">
                        <input type="hidden" name="telefono" id="telefono" value="<?= e($telVal) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" data-tooltip="Estado actual del atleta en el club (activo, suspendido o inactivo)." data-tooltip-pos="top">Estatus</label>
                    <select name="estatus" class="form-control">
                        <?php foreach (ESTATUS_ATLETA as $val => $lbl): ?>
                            <option value="<?= $val ?>" <?= (int) $atleta['estatus'] === $val ? 'selected' : '' ?>>
                                <?= e($lbl) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>



            <div class="modal-grid-2">
                <div class="form-group">
                    <label class="form-label" data-tooltip="Fecha de nacimiento del atleta. El rango de edad oficial permitido en el club es de 6 a 70 años." data-tooltip-pos="top"><span class="required">*</span> Fecha Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" class="form-control"
                        value="<?= e($atleta['fecha_nac']) ?>" required
                        max="<?= date('Y-m-d', strtotime('-6 years')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" data-tooltip="Perfil natural del atleta para el golpeo del balón (derecha, izquierda o ambidiestro)." data-tooltip-pos="top">Pierna Dominante</label>
                    <select name="pierna_dominante" class="form-control">
                        <option value="">— Seleccionar —</option>
                        <?php foreach (PIERNA_DOMINANTE as $p): ?>
                            <option value="<?= $p ?>" <?= $atleta['pierna_dominante'] === $p ? 'selected' : '' ?>>
                                <?= e(ucfirst($p)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="modal-grid-2">
                <div class="form-group">
                    <label class="form-label" data-tooltip="Género del atleta. Determina en qué categorías puede ser enrolado." data-tooltip-pos="top"><span class="required">*</span> Género</label>
                    <select name="sexo" class="form-control" required>
                        <option value="M" <?= $atleta['sexo'] === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= $atleta['sexo'] === 'F' ? 'selected' : '' ?>>Femenino</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> Guardar Cambios</button>
            <button type="button" class="btn-help" id="btn-help-basico" title="¿Cómo llenar esta sección?">
                <i class="ph ph-question"></i>
            </button>
        </div>
    </form>
</div>

<!-- Modal: Editar Foto -->
<div id="modal-editar-foto" class="modal-overlay" style="display:none;">
    <form id="form-editar-foto" action="<?= e(url("/admin/atletas/{$atleta['atleta_id']}")) ?>" method="POST"
        enctype="multipart/form-data" class="modal-container" style="max-width: 400px;" novalidate>
        <?= csrf_field() ?>
        <div class="modal-header">
            <h3 class="modal-title"><i class="ph ph-camera"></i> Actualizar Foto</h3>
            <button type="button" class="modal-close" data-close-modal>&times;</button>
        </div>
        <div class="modal-body" style="text-align: center;">
            <div id="error-foto" class="alert alert-danger" style="display:none; margin-bottom: 16px;"></div>

            <div
                style="width: 150px; height: 150px; border-radius: 50%; overflow: hidden; margin: 0 auto 20px; border: 3px solid var(--color-primary-light);">
                <?php if (!empty($atleta['foto'])): ?>
                    <img src="<?= e(url($atleta['foto'])) ?>" style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>
                    <div
                        style="width:100%; height:100%; background:var(--color-bg-alt); display:flex; align-items:center; justify-content:center; color:var(--color-text-muted);">
                        <i class="ph ph-user" style="font-size: 64px;"></i>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <div class="upload-zone" id="upload-zone-foto">
                    <input type="file" name="foto" id="input-foto-file" accept="image/*" style="display:none;">
                    <div class="upload-content">
                        <i class="ph ph-cloud-arrow-up"></i>
                        <p>Seleccionar nueva imagen</p>
                        <span id="foto-filename">Formatos: JPG, PNG</span>
                    </div>
                </div>
                <p style="font-size: 11px; color: var(--color-text-muted); margin-top: 8px;">Recomendado: Imagen
                    cuadrada (1:1).</p>
            </div>

            <?php if (!empty($atleta['foto'])): ?>
                <div style="margin-top: 16px;">
                    <label
                        style="display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="eliminar_foto" value="1">
                        <span style="font-size: 14px; color: var(--color-danger);">Eliminar foto actual</span>
                    </label>
                </div>
            <?php endif; ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="ph ph-upload"></i> Subir Foto</button>
        </div>
    </form>
</div>

<!-- Modal: Editar Representante -->
<div id="modal-editar-representante" class="modal-overlay" style="display:none;">
    <form id="form-editar-representante" action="<?= e(url("/admin/atletas/{$atleta['atleta_id']}")) ?>"
        method="POST" class="modal-container" style="max-width: 500px;" novalidate>
        <?= csrf_field() ?>
        <div class="modal-header">
            <h3 class="modal-title"><i class="ph ph-users"></i> Editar Representante</h3>
            <button type="button" class="modal-close" data-close-modal>&times;</button>
        </div>
        <div class="modal-body">
            <div id="error-representante" class="alert alert-danger" style="display:none; margin-bottom: 16px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label" data-tooltip="Nombres completos del tutor legal. Requerido para atletas menores de 18 años." data-tooltip-pos="top"><span class="required">*</span> Nombres</label>
                    <input type="text" name="tutor_nombres" class="form-control"
                        value="<?= e($atleta['tutor_nombres']) ?>" placeholder="Ej: Juan Carlos" required>
                </div>
                <div class="form-group">
                    <label class="form-label" data-tooltip="Apellidos del representante legal del atleta." data-tooltip-pos="top"><span class="required">*</span> Apellidos</label>
                    <input type="text" name="tutor_apellidos" class="form-control"
                        value="<?= e($atleta['tutor_apellidos']) ?>" placeholder="Ej: Pérez Rodríguez" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label" data-tooltip="Vínculo familiar o legal entre el representante y el atleta (padre, madre, tutor, etc.)." data-tooltip-pos="top"><span class="required">*</span> Parentesco</label>
                    <select name="tutor_relacion" class="form-control" required>
                        <option value="">— Seleccionar —</option>
                        <?php foreach (TIPO_RELACION_REPRESENTANTE as $rel): ?>
                            <option value="<?= $rel ?>" <?= $atleta['tutor_relacion'] === $rel ? 'selected' : '' ?>>
                                <?= e(ucfirst($rel)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" data-tooltip="Cédula de identidad del representante (V/E-Número) o Pasaporte." data-tooltip-pos="top"><span class="required">*</span> Cédula</label>
                    <?php
                        $tcedVal   = $atleta['tutor_cedula'];
                        $tcedPref  = 'V';
                        $tcedNum   = '';
                        if (!empty($tcedVal)) {
                            if (str_contains($tcedVal, '-')) {
                                [$tcedPref, $tcedNum] = explode('-', $tcedVal, 2);
                            } else {
                                $firstChar = strtoupper($tcedVal[0]);
                                if (in_array($firstChar, ['V', 'E', 'P'])) {
                                    $tcedPref = $firstChar;
                                    $tcedNum = substr($tcedVal, 1);
                                } else {
                                    $tcedNum = $tcedVal;
                                }
                            }
                        }
                    ?>
                    <div class="phone-field" id="phone-wrap-tutor_cedula">
                        <select class="phone-prefix" id="tutor_cedula_prefix" aria-label="Prefijo">
                            <option value="V" <?= $tcedPref==='V'?'selected':'' ?>>V</option>
                            <option value="E" <?= $tcedPref==='E'?'selected':'' ?>>E</option>
                            <option value="P" <?= $tcedPref==='P'?'selected':'' ?>>P</option>
                        </select>
                        <span class="phone-sep">-</span>
                        <input type="text" class="phone-number" id="tutor_cedula_number" maxlength="13" placeholder="12345678" value="<?= e($tcedNum) ?>">
                        <input type="hidden" name="tutor_cedula" id="tutor_cedula" value="<?= e($tcedVal) ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" data-tooltip="Teléfono móvil del representante para contacto de emergencia (11 dígitos, ej: 0412-1234567)." data-tooltip-pos="top"><span class="required">*</span> Teléfono de Contacto</label>
                <?php
                    $ttelVal   = $atleta['tutor_telefono'];
                    $ttelPref  = '';
                    $ttelNum   = '';
                    foreach (['0412','0414','0416','0422','0424','0426','0255','0256'] as $_p) {
                        if (str_starts_with($ttelVal, $_p)) { $ttelPref = $_p; $ttelNum = substr($ttelVal, 4); break; }
                    }
                ?>
                <div class="phone-field" id="phone-wrap-tutor_telefono">
                    <select class="phone-prefix" id="tutor_telefono_prefix" aria-label="Prefijo">
                        <option value="0412" <?= $ttelPref==='0412'?'selected':'' ?>>0412</option>
                        <option value="0414" <?= $ttelPref==='0414'?'selected':'' ?>>0414</option>
                        <option value="0416" <?= $ttelPref==='0416'?'selected':'' ?>>0416</option>
                        <option value="0422" <?= $ttelPref==='0422'?'selected':'' ?>>0422</option>
                        <option value="0424" <?= $ttelPref==='0424'?'selected':'' ?>>0424</option>
                        <option value="0426" <?= $ttelPref==='0426'?'selected':'' ?>>0426</option>
                        <option value="0255" <?= $ttelPref==='0255'?'selected':'' ?>>0255</option>
                        <option value="0256" <?= $ttelPref==='0256'?'selected':'' ?>>0256</option>
                    </select>
                    <span class="phone-sep">-</span>
                    <input type="text" class="phone-number" id="tutor_telefono_number" maxlength="7" placeholder="1234567" value="<?= e($ttelNum) ?>">
                    <input type="hidden" name="tutor_telefono" id="tutor_telefono" value="<?= e($ttelVal) ?>" required>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> Guardar</button>
            <button type="button" class="btn-help" id="btn-help-representante" title="¿Cómo llenar esta sección?">
                <i class="ph ph-question"></i>
            </button>
        </div>
    </form>
</div>

<!-- Modal: Editar Dirección Detallada -->
<div id="modal-editar-direccion" class="modal-overlay" style="display:none;">
    <form id="form-editar-direccion" action="<?= e(url("/admin/atletas/{$atleta['atleta_id']}")) ?>"
        method="POST" class="modal-container" style="max-width: 600px;" novalidate>
        <?= csrf_field() ?>
        <div class="modal-header">
            <h3 class="modal-title"><i class="ph ph-map-pin"></i> Editar Dirección Detallada</h3>
            <button type="button" class="modal-close" data-close-modal>&times;</button>
        </div>
        <div class="modal-body">
            <div id="error-direccion" class="alert alert-danger" style="display:none; margin-bottom: 16px;">
            </div>

            <input type="hidden" id="select-pais" value="<?= $paises[0]['id'] ?? 1 ?>">
            <div style="margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label" data-tooltip="Seleccione el estado venezolano correspondiente a la residencia actual del atleta." data-tooltip-pos="top"><span class="required">*</span> Estado</label>
                    <select id="select-estado" class="form-control" required>
                        <option value="">— Seleccionar —</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label" data-tooltip="Municipio dentro del estado seleccionado donde reside el atleta." data-tooltip-pos="top"><span class="required">*</span> Municipio</label>
                    <select id="select-municipio" class="form-control" required>
                        <option value="">— Seleccionar —</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" data-tooltip="Parroquia dentro del municipio donde reside el atleta." data-tooltip-pos="top"><span class="required">*</span> Parroquia</label>
                    <select name="parroquia_id" id="select-parroquia" class="form-control" required>
                        <option value="">— Seleccionar —</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label" data-tooltip="Urbanización, barrio, sector o comunidad donde vive el atleta (mínimo 2 caracteres)." data-tooltip-pos="top"><span class="required">*</span> Localidad / Sector</label>
                    <input type="text" name="localidad" class="form-control"
                        value="<?= e($atleta['localidad']) ?>" required placeholder="Ej: Urb. La Goajira">
                </div>
                <div class="form-group">
                    <label class="form-label" data-tooltip="Estructura habitacional en la que reside el atleta (casa, apartamento o edificio)." data-tooltip-pos="top"><span class="required">*</span> Tipo de Vivienda</label>
                    <select name="tipo_vivienda" class="form-control" required>
                        <option value="">— Seleccionar —</option>
                        <option value="casa" <?= ($atleta['tipo_vivienda'] ?? '') === 'casa' ? 'selected' : '' ?>>Casa</option>
                        <option value="apto" <?= ($atleta['tipo_vivienda'] ?? '') === 'apto' ? 'selected' : '' ?>>Apartamento</option>
                        <option value="edificio" <?= ($atleta['tipo_vivienda'] ?? '') === 'edificio' ? 'selected' : '' ?>>Edificio</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" data-tooltip="Dirección detallada: número de casa, calle, vereda, punto de referencia (mínimo 2 caracteres)." data-tooltip-pos="top"><span class="required">*</span> Ubicación Específica (Calle, Nro...)</label>
                <textarea name="ubicacion_vivienda" class="form-control"
                    rows="2" required placeholder="Ej: Calle 3, Vereda 5, Casa 12"><?= e($atleta['ubicacion_vivienda']) ?></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> Guardar</button>
            <button type="button" class="btn-help" id="btn-help-direccion" title="¿Cómo llenar esta sección?">
                <i class="ph ph-question"></i>
            </button>
        </div>
    </form>
</div>

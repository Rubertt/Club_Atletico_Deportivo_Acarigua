            <div id="tab-tutor" class="form-tab-panel">
                <div class="af-section-header">
                    <div class="af-section-icon"><i class="ph ph-users"></i></div>
                    <div class="af-section-info">
                        <h3>Representante Legal</h3>
                        <p>Persona responsable del menor de edad</p>
                    </div>
                </div>

                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Datos obligatorios del tutor legal requeridos para atletas menores de edad." data-tooltip-pos="top"><span class="required">*</span> Nombres</label>
                        <input type="text" name="tutor_nombres" class="form-control" id="tutor_nombres" maxlength="100" value="<?= e($get('tutor_nombres', $a['tutor_nombres'] ?? '')) ?>" placeholder="Nombres del representante">
                    </div>
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Apellidos completos del representante legal del atleta menor de edad." data-tooltip-pos="top"><span class="required">*</span> Apellidos</label>
                        <input type="text" name="tutor_apellidos" class="form-control" id="tutor_apellidos" maxlength="100" value="<?= e($get('tutor_apellidos', $a['tutor_apellidos'] ?? '')) ?>" placeholder="Apellidos del representante">
                    </div>
                </div>

                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Cédula de identidad del representante (V/E-Número) o Pasaporte. Requerida para atletas menores de 18 años." data-tooltip-pos="top"><span class="required">*</span> Cédula</label>
                        <?php
                            $tcedVal   = $get('tutor_cedula', $a['tutor_cedula'] ?? '');
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
                            <input type="text" class="phone-number" id="tutor_cedula_number"
                                   maxlength="10" placeholder="12345678"
                                   autocomplete="off"
                                   value="<?= e($tcedNum) ?>">
                            <input type="hidden" name="tutor_cedula" id="tutor_cedula" value="<?= e($tcedVal) ?>">
                        </div>
                        <span class="field-error" id="tutor_cedula-error"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Teléfono móvil del representante para contacto de emergencia (11 dígitos, ej: 0412-1234567)." data-tooltip-pos="top"><span class="required">*</span> Tel&eacute;fono</label>
                        <?php
                            $repTelVal  = $get('tutor_telefono', $a['tutor_telefono'] ?? '');
                            $repTelPref = '';
                            $repTelNum  = '';
                            foreach (['0412','0414','0416','0422','0424','0426','0255','0256'] as $_p) {
                                if (str_starts_with($repTelVal, $_p)) { $repTelPref = $_p; $repTelNum = substr($repTelVal, 4); break; }
                            }
                        ?>
                        <div class="phone-field" id="phone-wrap-tutor_telefono">
                            <select class="phone-prefix" id="tutor_telefono_prefix" aria-label="Prefijo">
                                <option value="0412" <?= $repTelPref==='0412'?'selected':'' ?>>0412</option>
                                <option value="0414" <?= $repTelPref==='0414'?'selected':'' ?>>0414</option>
                                <option value="0416" <?= $repTelPref==='0416'?'selected':'' ?>>0416</option>
                                <option value="0422" <?= $repTelPref==='0422'?'selected':'' ?>>0422</option>
                                <option value="0424" <?= $repTelPref==='0424'?'selected':'' ?>>0424</option>
                                <option value="0426" <?= $repTelPref==='0426'?'selected':'' ?>>0426</option>
                                <option value="0255" <?= $repTelPref==='0255'?'selected':'' ?>>0255</option>
                                <option value="0256" <?= $repTelPref==='0256'?'selected':'' ?>>0256</option>
                            </select>
                            <span class="phone-sep">-</span>
                            <input type="text" class="phone-number" id="tutor_telefono_number"
                                   maxlength="7" placeholder="1234567"
                                   autocomplete="off" inputmode="numeric"
                                   value="<?= e($repTelNum) ?>">
                            <input type="hidden" name="tutor_telefono" id="tutor_telefono">
                        </div>
                        <span class="field-error" id="tutor_telefono-error"></span>
                    </div>
                </div>
                
                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Vínculo familiar o legal entre el representante y el atleta (padre, madre, tutor, etc.)." data-tooltip-pos="top"><span class="required">*</span> Tipo de Relación</label>
                        <select name="tutor_relacion" class="form-control" required>
                            <option value="">— Seleccione —</option>
                            <?php foreach (TIPO_RELACION_REPRESENTANTE as $op):
                                $cur = $get('tutor_relacion', $a['tutor_relacion'] ?? ''); ?>
                                <option value="<?= e($op) ?>" <?= $cur === $op ? 'selected' : '' ?>><?= e(ucfirst($op)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

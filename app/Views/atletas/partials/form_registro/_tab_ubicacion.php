            <div id="tab-direccion" class="form-tab-panel">
                <div class="af-section-header">
                    <div class="af-section-icon"><i class="ph ph-map-pin-line"></i></div>
                    <div class="af-section-info">
                        <h3>Datos de Residencia</h3>
                        <p>Ubicación geográfica del domicilio del atleta</p>
                    </div>
                </div>

                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Seleccione la división geográfica correspondiente a la residencia actual del atleta." data-tooltip-pos="top"><span class="required">*</span> Estado</label>
                        <select id="sel-estado" name="estado_id" class="form-control" required data-current="<?= (int) old('estado_id', $a['estado_id'] ?? 0) ?>">
                            <option value="">— Seleccione Estado —</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Municipio dentro del estado seleccionado donde reside el atleta." data-tooltip-pos="top"><span class="required">*</span> Municipio</label>
                        <select id="sel-municipio" name="municipio_id" class="form-control" required data-current="<?= (int) old('municipio_id', $a['municipio_id'] ?? 0) ?>" disabled>
                            <option value="">— Seleccione Municipio —</option>
                        </select>
                    </div>
                </div>

                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Parroquia dentro del municipio donde reside el atleta." data-tooltip-pos="top"><span class="required">*</span> Parroquia</label>
                        <select id="sel-parroquia" name="parroquia_id" class="form-control" required data-current="<?= (int) old('parroquia_id', $a['parroquias_id'] ?? 0) ?>" disabled>
                            <option value="">— Seleccione Parroquia —</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Estructura habitacional en la que reside el atleta (casa, apartamento o edificio)." data-tooltip-pos="top"><span class="required">*</span> Tipo de Vivienda</label>
                        <select name="tipo_vivienda" class="form-control" required>
                            <option value="">— Seleccione —</option>
                            <?php $tv = $get('tipo_vivienda', $a['tipo_vivienda'] ?? ''); ?>
                            <option value="casa" <?= $tv === 'casa' ? 'selected' : '' ?>>Casa</option>
                            <option value="apto" <?= $tv === 'apto' ? 'selected' : '' ?>>Apartamento</option>
                            <option value="edificio" <?= $tv === 'edificio' ? 'selected' : '' ?>>Edificio</option>
                        </select>
                    </div>
                </div>

                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Urbanización, barrio, sector o comunidad donde vive el atleta (mínimo 2 caracteres)." data-tooltip-pos="top"><span class="required">*</span> Localidad (Barrio / Urbanización)</label>
                        <input type="text" name="localidad" class="form-control" required maxlength="100" value="<?= e($get('localidad', '')) ?>" placeholder="Ej: Urb. La Goajira">
                    </div>
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Dirección detallada: número de casa, calle, vereda, punto de referencia (mínimo 2 caracteres)." data-tooltip-pos="top"><span class="required">*</span> Dirección Exacta</label>
                        <input type="text" name="ubicacion_vivienda" class="form-control" required maxlength="100" value="<?= e($get('ubicacion_vivienda', '')) ?>" placeholder="Ej: Calle 3, Vereda 5, Casa 12">
                    </div>
                </div>
            </div>

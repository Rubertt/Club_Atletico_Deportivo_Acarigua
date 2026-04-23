-- =============================================================
-- Migración 001: Corrección de errores de esquema en cada_db
-- Fecha: 2026-04-18
-- Descripción:
--   1. Renombra `estadoi_id` -> `estado_id` en municipios
--   2. Renombra `cateogira_id` -> `categoria_id` en historial_partidos
--   3. Renombra `ubicación vivienda` -> `ubicacion_vivienda` en direcciones
--   4. Amplía `password` en usuarios de VARCHAR(32) a VARCHAR(255) para bcrypt
--   5. Encripta las contraseñas existentes con bcrypt
-- =============================================================

USE `cada_db`;

-- 1. Corregir typo en municipios: estadoi_id -> estado_id
ALTER TABLE `municipios` CHANGE `estadoi_id` `estado_id` int(11) NOT NULL;

-- 2. Corregir typo en historial_partidos: cateogira_id -> categoria_id
ALTER TABLE `historial_partidos` CHANGE `cateogira_id` `categoria_id` int(11) NOT NULL;

-- 3. Corregir nombre con acento/espacio en direcciones
ALTER TABLE `direcciones` CHANGE `ubicación vivienda` `ubicacion_vivienda` varchar(100) NOT NULL COMMENT 'ej:calle#15 vereda#12 casa#4';

-- 4. Ampliar columna password para soportar hashes bcrypt (60+ caracteres)
ALTER TABLE `usuarios` MODIFY `password` varchar(255) NOT NULL;

-- 5. Actualizar contraseñas a bcrypt (hash de '12345678')
-- El hash fue generado con: password_hash('12345678', PASSWORD_BCRYPT, ['cost' => 12])
UPDATE `usuarios` SET `password` = '$2y$12$Hn8oxN4Z84I8hGPhrwSPBeA.SMCDaTdjp9sx9.ifeCeePYzXxcOlG' WHERE `password` = '12345678';

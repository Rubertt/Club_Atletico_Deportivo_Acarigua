SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!40101 SET NAMES utf8mb4 */
;
--
-- Database: `club_atletico_db_normalized`
--
DROP DATABASE IF EXISTS `club_atletico_db_normalized`;
CREATE DATABASE IF NOT EXISTS `club_atletico_db_normalized` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `club_atletico_db_normalized`;
-- --------------------------------------------------------
-- 1. NORMALIZACIÓN GEOGRÁFICA (Resuelve redundancia de Direcciones)
-- --------------------------------------------------------
CREATE TABLE `ubicacion_pais` (
    `pais_id` int(11) NOT NULL AUTO_INCREMENT,
    `nombre` varchar(50) NOT NULL,
    PRIMARY KEY (`pais_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
CREATE TABLE `ubicacion_estado` (
    `estado_id` int(11) NOT NULL AUTO_INCREMENT,
    `pais_id` int(11) NOT NULL,
    `nombre` varchar(50) NOT NULL,
    PRIMARY KEY (`estado_id`),
    KEY `fk_estado_pais` (`pais_id`),
    CONSTRAINT `fk_estado_pais` FOREIGN KEY (`pais_id`) REFERENCES `ubicacion_pais` (`pais_id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
CREATE TABLE `ubicacion_municipio` (
    `municipio_id` int(11) NOT NULL AUTO_INCREMENT,
    `estado_id` int(11) NOT NULL,
    `nombre` varchar(50) NOT NULL,
    PRIMARY KEY (`municipio_id`),
    KEY `fk_municipio_estado` (`estado_id`),
    CONSTRAINT `fk_municipio_estado` FOREIGN KEY (`estado_id`) REFERENCES `ubicacion_estado` (`estado_id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
CREATE TABLE `ubicacion_parroquia` (
    `parroquia_id` int(11) NOT NULL AUTO_INCREMENT,
    `municipio_id` int(11) NOT NULL,
    `nombre` varchar(50) NOT NULL,
    PRIMARY KEY (`parroquia_id`),
    KEY `fk_parroquia_municipio` (`municipio_id`),
    CONSTRAINT `fk_parroquia_municipio` FOREIGN KEY (`municipio_id`) REFERENCES `ubicacion_municipio` (`municipio_id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- Tabla de Direcciones Normalizada
-- Ya no guarda texto repetido, sino referencias.
CREATE TABLE `direcciones` (
    `direccion_id` int(11) NOT NULL AUTO_INCREMENT,
    `parroquia_id` int(11) DEFAULT NULL,
    `punto_referencia` varchar(255) DEFAULT NULL,
    `calle_avenida` varchar(100) DEFAULT NULL,
    `casa_edificio` varchar(50) DEFAULT NULL,
    PRIMARY KEY (`direccion_id`),
    KEY `fk_direccion_parroquia` (`parroquia_id`),
    CONSTRAINT `fk_direccion_parroquia` FOREIGN KEY (`parroquia_id`) REFERENCES `ubicacion_parroquia` (`parroquia_id`) ON DELETE
    SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- --------------------------------------------------------
-- 2. TABLAS MAESTRAS Y CATÁLOGOS
-- --------------------------------------------------------
CREATE TABLE `posicion_juego` (
    `posicion_id` int(11) NOT NULL AUTO_INCREMENT,
    `nombre_posicion` varchar(50) NOT NULL,
    PRIMARY KEY (`posicion_id`),
    UNIQUE KEY `nombre_posicion` (`nombre_posicion`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
CREATE TABLE `rol_usuarios` (
    `rol_id` int(11) NOT NULL AUTO_INCREMENT,
    `nombre_rol` varchar(50) NOT NULL,
    `descripcion` varchar(255) DEFAULT NULL,
    `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`rol_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
CREATE TABLE `preguntas_seguridad` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `pregunta` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- --------------------------------------------------------
-- 3. ENTIDADES PRINCIPALES (PERSONAS)
-- --------------------------------------------------------
CREATE TABLE `tutor` (
    `tutor_id` int(11) NOT NULL AUTO_INCREMENT,
    `nombres` varchar(100) NOT NULL,
    -- Normalizado (antes nombre_completo)
    `apellidos` varchar(100) NOT NULL,
    -- Normalizado
    `cedula` varchar(20) NOT NULL,
    -- Corregido INT -> VARCHAR
    `telefono` varchar(20) NOT NULL,
    -- Corregido formato
    `correo` varchar(100) DEFAULT NULL,
    `tipo_relacion` enum(
        'Padre',
        'Madre',
        'Abuelo/a',
        'Tío/a',
        'Hermano/a',
        'Tutor Legal',
        'Otro'
    ) NOT NULL DEFAULT 'Padre',
    `direccion_id` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`tutor_id`),
    UNIQUE KEY `cedula` (`cedula`),
    KEY `fk_tutor_direccion` (`direccion_id`),
    CONSTRAINT `fk_tutor_direccion` FOREIGN KEY (`direccion_id`) REFERENCES `direcciones` (`direccion_id`) ON DELETE
    SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
CREATE TABLE `plantel` (
    `plantel_id` int(11) NOT NULL AUTO_INCREMENT,
    `nombre` varchar(100) NOT NULL,
    `apellido` varchar(100) NOT NULL,
    `cedula` varchar(20) DEFAULT NULL,
    `telefono` varchar(20) NOT NULL,
    `fecha_nac` date DEFAULT NULL,
    `rol_id` int(11) NOT NULL,
    `direccion_id` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`plantel_id`),
    UNIQUE KEY `cedula` (`cedula`),
    KEY `fk_plantel_rol` (`rol_id`),
    KEY `fk_plantel_direccion` (`direccion_id`),
    CONSTRAINT `fk_plantel_direccion` FOREIGN KEY (`direccion_id`) REFERENCES `direcciones` (`direccion_id`) ON DELETE
    SET NULL,
        CONSTRAINT `fk_plantel_rol` FOREIGN KEY (`rol_id`) REFERENCES `rol_usuarios` (`rol_id`) ON DELETE NO ACTION
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
CREATE TABLE `categoria` (
    `categoria_id` int(11) NOT NULL AUTO_INCREMENT,
    `nombre_categoria` varchar(50) NOT NULL,
    `edad_min` int(11) NOT NULL,
    `edad_max` int(11) NOT NULL,
    `entrenador_id` int(11) DEFAULT NULL,
    `estatus` enum('Activa', 'Inactiva') DEFAULT 'Activa',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`categoria_id`),
    UNIQUE KEY `nombre_categoria` (`nombre_categoria`),
    KEY `fk_categoria_entrenador` (`entrenador_id`),
    CONSTRAINT `fk_categoria_entrenador` FOREIGN KEY (`entrenador_id`) REFERENCES `plantel` (`plantel_id`) ON DELETE
    SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
CREATE TABLE `atletas` (
    `atleta_id` int(11) NOT NULL AUTO_INCREMENT,
    `nombre` varchar(100) NOT NULL,
    `apellido` varchar(100) NOT NULL,
    `cedula` varchar(20) DEFAULT NULL,
    -- Corregido
    `telefono` varchar(20) DEFAULT NULL,
    -- Corregido INT -> VARCHAR
    `direccion_id` int(11) DEFAULT NULL,
    `fecha_nacimiento` date NOT NULL,
    `posicion_de_juego` int(11) DEFAULT NULL,
    `pierna_dominante` enum('Derecha', 'Izquierda', 'Ambidiestro') DEFAULT NULL,
    `categoria_id` int(11) DEFAULT NULL,
    `tutor_id` int(11) DEFAULT NULL,
    `foto` varchar(255) DEFAULT NULL,
    `estatus` enum('Activo', 'Inactivo', 'Lesionado', 'Suspendido') DEFAULT 'Activo',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`atleta_id`),
    KEY `fk_atletas_categoria` (`categoria_id`),
    KEY `fk_atletas_tutor` (`tutor_id`),
    KEY `fk_atletas_direccion` (`direccion_id`),
    KEY `fk_atletas_posicion` (`posicion_de_juego`),
    CONSTRAINT `fk_atletas_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`categoria_id`) ON DELETE
    SET NULL,
        CONSTRAINT `fk_atletas_direccion` FOREIGN KEY (`direccion_id`) REFERENCES `direcciones` (`direccion_id`) ON DELETE
    SET NULL,
        CONSTRAINT `fk_atletas_posicion` FOREIGN KEY (`posicion_de_juego`) REFERENCES `posicion_juego` (`posicion_id`) ON DELETE NO ACTION,
        CONSTRAINT `fk_atletas_tutor` FOREIGN KEY (`tutor_id`) REFERENCES `tutor` (`tutor_id`) ON DELETE
    SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- --------------------------------------------------------
-- 4. SISTEMA Y SEGURIDAD
-- --------------------------------------------------------
CREATE TABLE `usuarios` (
    `usuario_id` int(11) NOT NULL AUTO_INCREMENT,
    `email` varchar(100) NOT NULL,
    `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    -- Bin para Case Sensitive en pass
    `token` varchar(500) DEFAULT NULL,
    `rol_id` int(11) NOT NULL,
    -- Renombrado de 'rol' a 'rol_id' para consistencia
    `plantel_id` int(11) DEFAULT NULL,
    `estatus` enum('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
    `foto` varchar(255) DEFAULT NULL,
    `ultimo_acceso` datetime DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`usuario_id`),
    UNIQUE KEY `email` (`email`),
    KEY `fk_usuarios_rol` (`rol_id`),
    KEY `fk_usuarios_plantel` (`plantel_id`),
    CONSTRAINT `fk_usuarios_plantel` FOREIGN KEY (`plantel_id`) REFERENCES `plantel` (`plantel_id`) ON DELETE
    SET NULL,
        CONSTRAINT `fk_usuarios_rol` FOREIGN KEY (`rol_id`) REFERENCES `rol_usuarios` (`rol_id`) ON DELETE NO ACTION
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
CREATE TABLE `usuario_preguntas` (
    `usuario_id` int(11) NOT NULL,
    `pregunta_id` int(11) NOT NULL,
    `respuesta` varchar(255) NOT NULL,
    -- Debería estar hasheada idealmente
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`usuario_id`, `pregunta_id`),
    KEY `fk_usuariopreg_pregunta` (`pregunta_id`),
    CONSTRAINT `fk_usuariopreg_pregunta` FOREIGN KEY (`pregunta_id`) REFERENCES `preguntas_seguridad` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_usuariopreg_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`usuario_id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- --------------------------------------------------------
-- 5. OPERACIONES DEPORTIVAS Y MÉDICAS
-- --------------------------------------------------------
CREATE TABLE `evento_deportivo` (
    `evento_id` int(11) NOT NULL AUTO_INCREMENT,
    `entrenador_id` int(11) NOT NULL,
    `tipo_evento` enum(
        'Entrenamiento',
        'Partido',
        'Pruebas',
        'Evento especial'
    ) NOT NULL,
    `fecha_evento` date NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`evento_id`),
    KEY `fk_evento_entrenador` (`entrenador_id`),
    CONSTRAINT `fk_evento_entrenador` FOREIGN KEY (`entrenador_id`) REFERENCES `plantel` (`plantel_id`) ON DELETE NO ACTION
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
CREATE TABLE `detalle_asistencia` (
    `asistencia_id` int(11) NOT NULL AUTO_INCREMENT,
    `evento_id` int(11) NOT NULL,
    `atleta_id` int(11) NOT NULL,
    `estatus` enum('Presente', 'Ausente', 'Justificado') NOT NULL,
    `observaciones` text DEFAULT NULL,
    PRIMARY KEY (`asistencia_id`),
    UNIQUE KEY `unique_asistencia` (`evento_id`, `atleta_id`),
    -- Evita duplicar asistencia del mismo atleta al mismo evento
    KEY `fk_asistencia_atleta` (`atleta_id`),
    CONSTRAINT `fk_asistencia_atleta` FOREIGN KEY (`atleta_id`) REFERENCES `atletas` (`atleta_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_asistencia_evento` FOREIGN KEY (`evento_id`) REFERENCES `evento_deportivo` (`evento_id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
CREATE TABLE `ficha_medica` (
    `ficha_id` int(11) NOT NULL AUTO_INCREMENT,
    `atleta_id` int(11) NOT NULL,
    `alergias` text DEFAULT NULL,
    `tipo_sanguineo` varchar(5) DEFAULT NULL,
    `lesion` text DEFAULT NULL,
    `condicion_medica` text DEFAULT NULL,
    `observacion` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`ficha_id`),
    UNIQUE KEY `idx_ficha_atleta_unique` (`atleta_id`),
    -- 1 a 1 estricto
    CONSTRAINT `fk_ficha_atleta` FOREIGN KEY (`atleta_id`) REFERENCES `atletas` (`atleta_id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
CREATE TABLE `medidas_antropometricas` (
    `medidas_id` int(11) NOT NULL AUTO_INCREMENT,
    `atleta_id` int(11) NOT NULL,
    `fecha_medicion` date NOT NULL,
    `peso` decimal(5, 2) DEFAULT NULL,
    `altura` decimal(5, 2) DEFAULT NULL,
    `porcentaje_grasa` decimal(5, 2) DEFAULT NULL,
    `porcentaje_musculatura` decimal(5, 2) DEFAULT NULL,
    `envergadura` decimal(5, 2) DEFAULT NULL,
    `largo_de_pierna` decimal(5, 2) DEFAULT NULL,
    `largo_de_torso` decimal(5, 2) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`medidas_id`),
    KEY `fk_medidas_atleta` (`atleta_id`),
    CONSTRAINT `fk_medidas_atleta` FOREIGN KEY (`atleta_id`) REFERENCES `atletas` (`atleta_id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
CREATE TABLE `resultado_pruebas` (
    `test_id` int(11) NOT NULL AUTO_INCREMENT,
    `evento_id` int(11) NOT NULL,
    `atleta_id` int(11) NOT NULL,
    `test_de_fuerza` decimal(10, 2) DEFAULT NULL,
    `test_resistencia` decimal(10, 2) DEFAULT NULL,
    `test_velocidad` decimal(10, 2) DEFAULT NULL,
    `test_coordinacion` decimal(10, 2) DEFAULT NULL,
    `test_de_reaccion` decimal(10, 2) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`test_id`),
    UNIQUE KEY `unique_test_evento_atleta` (`evento_id`, `atleta_id`),
    -- Evita duplicados en el mismo evento
    KEY `fk_pruebas_atleta` (`atleta_id`),
    CONSTRAINT `fk_pruebas_atleta` FOREIGN KEY (`atleta_id`) REFERENCES `atletas` (`atleta_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pruebas_evento` FOREIGN KEY (`evento_id`) REFERENCES `evento_deportivo` (`evento_id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
CREATE TABLE `implementos_deportivos` (
    `implemento_id` int(11) NOT NULL AUTO_INCREMENT,
    `tipo` varchar(100) NOT NULL,
    `cantidad` int(11) DEFAULT 0,
    `estatus` enum('Disponible', 'En uso', 'Mantenimiento', 'Dañado') DEFAULT 'Disponible',
    `ubicacion` varchar(100) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`implemento_id`),
    UNIQUE KEY `tipo` (`tipo`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;
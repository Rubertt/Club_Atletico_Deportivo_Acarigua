-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-02-2026 a las 16:55:56
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cada_db`
--

-- Crear o usar la base de datos
CREATE DATABASE IF NOT EXISTS `cada_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `cada_db`;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `actividades`
--

CREATE TABLE `actividades` (
  `actividad_id` int(11) NOT NULL,
  `tipo_actividad` tinyint(4) NOT NULL COMMENT 'ej: partid=0, entrenamiento=1, ',
  `objetivo_principal` varchar(150) NOT NULL COMMENT 'Ej: Transiciones defensivas, Resistencia aeróbica',
  `fecha` date NOT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `ubicacion` varchar(100) DEFAULT '''Cancha uptp''',
  `clima` tinyint(4) DEFAULT NULL,
  `estatus` tinyint(4) DEFAULT 1 COMMENT '0: Cancelado, 1: Programado, 2: Finalizado',
  `micro_id` int(11) DEFAULT NULL COMMENT 'Relación con el microciclo de planificación',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `asignacion`
--

CREATE TABLE `asignacion` (
  `asignacion_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `tipo_asignacion` enum('diaria','semanal','','') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `asistencias`
--

CREATE TABLE `asistencias` (
  `asistencia_id` int(11) NOT NULL,
  `actividad_id` int(11) NOT NULL,
  `atleta_id` int(11) NOT NULL,
  `estatus` tinyint(4) NOT NULL COMMENT 'definir: 0=Ausente, 1=Presente, 2=Justificado',
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `atencion_medica`
--

CREATE TABLE `atencion_medica` (
  `atencion_id` int(11) NOT NULL,
  `atleta_id` int(11) NOT NULL,
  `tipo_registro` tinyint(4) NOT NULL COMMENT '1:Lesion, 2:Enfermedad, 3:Control',
  `descripcion` text NOT NULL,
  `diagnostico` varchar(255) DEFAULT NULL,
  `fecha_suceso` date NOT NULL,
  `fecha_alta_estimada` date DEFAULT NULL,
  `fecha_alta_real` date DEFAULT NULL,
  `tratamiento_indicado` text DEFAULT NULL,
  `especialista_id` int(11) NOT NULL COMMENT 'FK a personal (medico/fisio)',
  `estado_disponibilidad` tinyint(4) DEFAULT 0 COMMENT '0: No apto, 1: Trabajo diferenciado, 2: Apto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `atletas`
--

CREATE TABLE `atletas` (
  `atleta_id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `sexo` char(1) NOT NULL,
  `cedula` varchar(12) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `posicion_de_juego` int(11) DEFAULT NULL,
  `pierna_dominante` enum('derecha','izquierda','ambidiestro','') DEFAULT 'derecha',
  `direccion_id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `representante_id` int(11) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `estatus` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'usar logica boleana para definir, ejemplo: 0:suspendido, 1:activo, 2:lesionado, 3:inactivo''',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `atletas`
--

INSERT INTO `atletas` (`atleta_id`, `nombre`, `apellido`, `fecha_nacimiento`, `sexo`, `cedula`, `telefono`, `posicion_de_juego`, `pierna_dominante`, `direccion_id`, `categoria_id`, `representante_id`, `foto`, `estatus`, `created_at`, `updated_at`) VALUES
(25, 'leonel', 'messi', '2001-02-20', 'm', '15234567', '04121234567', 8, 'izquierda', 18, 13, 1, NULL, 1, '2026-02-10 23:39:20', '2026-02-10 23:39:20');

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `carnet_discapacidad`
--

CREATE TABLE `carnet_discapacidad` (
  `id` int(20) UNSIGNED NOT NULL,
  `ficha_id` int(11) DEFAULT NULL,
  `tipo_discapacidad_id` int(11) NOT NULL,
  `nro_carnet` varchar(20) DEFAULT NULL,
  `porcentaje_discapacidad` int(11) DEFAULT NULL,
  `fecha_registro` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `categoria_id` int(11) NOT NULL,
  `nombre_categoria` varchar(50) NOT NULL,
  `sexo_categoria` char(1) NOT NULL,
  `edad_min` int(2) NOT NULL,
  `edad_max` int(2) NOT NULL,
  `entrenador_id` int(11) DEFAULT NULL,
  `estatus` enum('Activa','Inactiva') DEFAULT 'Activa',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`categoria_id`, `nombre_categoria`, `sexo_categoria`, `edad_min`, `edad_max`, `entrenador_id`, `estatus`, `created_at`, `updated_at`) VALUES
(13, 'sub-18(m)', '', 17, 18, NULL, 'Activa', '2026-02-10 18:23:12', '2026-02-10 18:23:12'),
(14, 'sub-18(f)', '', 17, 18, NULL, 'Activa', '2026-02-10 18:23:12', '2026-02-10 18:23:12');

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `cat_tipos_discapacidad`
--

CREATE TABLE `cat_tipos_discapacidad` (
  `tipos_discapacidad_id` int(11) NOT NULL,
  `nombre_tipo` varchar(50) NOT NULL,
  `descripcion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `detalle_asignacion`
--

CREATE TABLE `detalle_asignacion` (
  `detalle_asignacion_id` int(11) NOT NULL,
  `asignacion_id` int(11) NOT NULL,
  `implemento_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `direcciones`
--

CREATE TABLE `direcciones` (
  `direccion_id` int(11) NOT NULL,
  `parroquias_id` int(11) NOT NULL,
  `localidad` varchar(100) NOT NULL COMMENT 'nombre=urbanismo, barrio, sector...',
  `tipo_vivienda` enum('casa','apto','edificio','') NOT NULL,
  `ubicación vivienda` varchar(100) NOT NULL COMMENT 'ej:calle#15 vereda#12 casa#4'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `direcciones`
--

INSERT INTO `direcciones` (`direccion_id`, `parroquias_id`, `localidad`, `tipo_vivienda`, `ubicación vivienda`) VALUES
(18, 739, 'la gonzalo', 'casa', 'calle#15 vereda#12 casa#4'),
(19, 722, 'baraure 2', '', 'calle#4 casa#2');

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `estados`
--

CREATE TABLE `estados` (
  `estado_id` int(11) NOT NULL,
  `estado` varchar(250) NOT NULL,
  `iso_3166-2` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `estados` (datos de todos los estados de Venezuela)
--

INSERT INTO `estados` (`estado_id`, `estado`, `iso_3166-2`) VALUES
(1, 'Amazonas', 'VE-X'),
(2, 'Anzoátegui', 'VE-B'),
(3, 'Apure', 'VE-C'),
(4, 'Aragua', 'VE-D'),
(5, 'Barinas', 'VE-E'),
(6, 'Bolívar', 'VE-F'),
(7, 'Carabobo', 'VE-G'),
(8, 'Cojedes', 'VE-H'),
(9, 'Delta Amacuro', 'VE-Y'),
(10, 'Falcón', 'VE-I'),
(11, 'Guárico', 'VE-J'),
(12, 'Lara', 'VE-K'),
(13, 'Mérida', 'VE-L'),
(14, 'Miranda', 'VE-M'),
(15, 'Monagas', 'VE-N'),
(16, 'Nueva Esparta', 'VE-O'),
(17, 'Portuguesa', 'VE-P'),
(18, 'Sucre', 'VE-R'),
(19, 'Táchira', 'VE-S'),
(20, 'Trujillo', 'VE-T'),
(21, 'La Guaira', 'VE-W'),
(22, 'Yaracuy', 'VE-U'),
(23, 'Zulia', 'VE-V'),
(24, 'Distrito Capital', 'VE-A'),
(25, 'Dependencias Federales', 'VE-Z');

-- Tabla de municipios y parroquias (datos truncados para brevedad)
-- Los datos completos están en el archivo original

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `ficha_medica`
--

CREATE TABLE `ficha_medica` (
  `ficha_id` int(11) NOT NULL,
  `atleta_id` int(11) NOT NULL,
  `grupo_sanguineo` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `alergias` text DEFAULT NULL COMMENT 'Medicamentos, alimentos o ambientales',
  `antecedentes_familiares` text DEFAULT NULL COMMENT 'Problemas cardíacos, diabetes, etc.',
  `antecedentes_quirurgicos` text DEFAULT NULL COMMENT 'Cirugías previas',
  `condicion_cronica` text DEFAULT NULL COMMENT 'Asma, diabetes, etc.',
  `medicacion_actual` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `historial_partidos`
--

CREATE TABLE `historial_partidos` (
  `partido_id` int(11) NOT NULL,
  `cateogira_id` int(11) NOT NULL,
  `nombre_rival` varchar(100) NOT NULL,
  `tipo_partido` enum('liga','clasificatorio','amistoso','torneo','benefico') NOT NULL,
  `fecha_partido` date NOT NULL,
  `terreno` tinyint(4) NOT NULL COMMENT 'ej:cesped natural, artificial, tierra, altitud',
  `clima` tinyint(4) NOT NULL COMMENT 'ej:lluvia,niebla, viento, calor',
  `goles_recibidos` tinyint(4) NOT NULL,
  `goles_anotados` tinyint(4) NOT NULL,
  `resultado` tinyint(4) NOT NULL COMMENT 'Codificación: 2=Victoria, 1=Empate, 0=Derrota',
  `observaciones` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `implementos_deportivos`
--

CREATE TABLE `implementos_deportivos` (
  `implemento_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `existencia` int(11) NOT NULL DEFAULT 0,
  `cant_uso` tinyint(4) NOT NULL,
  `cant_dañado` int(11) NOT NULL,
  `cant_disponible` tinyint(4) NOT NULL,
  `lugar_almacen` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `medidas_antropometricas`
--

CREATE TABLE `medidas_antropometricas` (
  `medidas_id` int(11) NOT NULL,
  `atleta_id` int(11) NOT NULL,
  `fecha_medicion` date NOT NULL,
  `peso` double(5,2) DEFAULT NULL,
  `altura` double(5,2) DEFAULT NULL,
  `porcentaje_grasa` double(5,2) DEFAULT NULL,
  `porcentaje_musculatura` double(5,2) DEFAULT NULL,
  `envergadura` double(5,2) DEFAULT NULL,
  `largo_de_pierna` double(5,2) DEFAULT NULL,
  `largo_de_torso` double(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `municipios`
--

CREATE TABLE `municipios` (
  `municipio_id` int(11) NOT NULL,
  `estadoi_id` int(11) NOT NULL,
  `municipio` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `parroquias`
--

CREATE TABLE `parroquias` (
  `parroquia_id` int(11) NOT NULL,
  `municipio_id` int(11) NOT NULL,
  `parroquia` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `personal`
--

CREATE TABLE `personal` (
  `personal_id` int(11) NOT NULL,
  `email_id` varchar(100) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `cedula` varchar(12) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `fecha_nac` date NOT NULL,
  `direccion_id` int(11) NOT NULL,
  `rol_personal` tinyint(4) NOT NULL COMMENT 'ej: 0:Obrero, 1:Entrenador, 2:Medico, 3:Admin, 4:Directivo, 5:Vigilante, 6:Fisio, 7:Utillero''',
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `personal`
--

INSERT INTO `personal` (`personal_id`, `email_id`, `nombre`, `apellido`, `cedula`, `telefono`, `fecha_nac`, `direccion_id`, `rol_personal`, `foto`, `created_at`, `updated_at`) VALUES
(9, 'entrenador@gmail.com', 'leonel', 'messi', '15234567', '04245530385', '1996-02-01', 19, 1, NULL, '2026-02-10 18:20:48', '2026-02-10 18:20:48');

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `plan_macrociclo`
--

CREATE TABLE `plan_macrociclo` (
  `macro_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `objetivo_general` text DEFAULT NULL COMMENT 'Ej: Desarrollo de fuerza y técnica avanzada',
  `estado` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `plan_mesociclo`
--

CREATE TABLE `plan_mesociclo` (
  `meso_id` int(11) NOT NULL,
  `macro_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('Base','Desarrollo','Choque','Competición','Recuperación') NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `objetivo_especifico` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `plan_microciclo`
--

CREATE TABLE `plan_microciclo` (
  `micro_id` int(11) NOT NULL,
  `meso_id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT 'Semana X',
  `numero_semana` tinyint(4) NOT NULL,
  `carga_trabajo` enum('Baja','Media','Alta','Muy Alta') NOT NULL COMMENT 'Intensidad planificada',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `posicion_juego`
--

CREATE TABLE `posicion_juego` (
  `posicion_id` int(11) NOT NULL,
  `nombre_posicion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `posicion_juego`
--

INSERT INTO `posicion_juego` (`posicion_id`, `nombre_posicion`) VALUES
(1, 'portero'),
(2, 'defensa central'),
(3, 'lateral derecho'),
(4, 'lateral izquierdo'),
(5, 'medio centro'),
(6, 'extremo izquierdo'),
(7, 'extremo derecho'),
(8, 'delantero');

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `preguntas_seguridad`
--

CREATE TABLE `preguntas_seguridad` (
  `preguntas_id` int(11) NOT NULL,
  `preguntas` varchar(50) NOT NULL,
  `grupo` tinyint(4) NOT NULL COMMENT 'Define a qué select pertenece (1, 2, 3 o 4)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `preguntas_seguridad`
--

INSERT INTO `preguntas_seguridad` (`preguntas_id`, `preguntas`, `grupo`, `created_at`, `updated_at`) VALUES
(5, '¿Cómo se llamaba tu primera mascota?', 1, '2026-02-10 17:11:21', '2026-02-10 17:11:21'),
(6, '¿En qué ciudad se conocieron tus padres?', 1, '2026-02-10 17:11:21', '2026-02-10 17:11:21'),
(7, '¿Cuál es el nombre de la calle donde creciste?', 1, '2026-02-10 17:11:21', '2026-02-10 17:11:21'),
(8, '¿Cómo se llamaba tu primer profesor de primaria?', 1, '2026-02-10 17:11:21', '2026-02-10 17:11:21'),
(9, '¿Cuál era tu apodo cuando eras niño/a?', 1, '2026-02-10 17:11:21', '2026-02-10 17:11:21'),
(10, '¿Cuál fue el primer modelo de coche que condujiste', 1, '2026-02-10 17:11:21', '2026-02-10 17:11:21'),
(11, '¿En qué hospital naciste?', 1, '2026-02-10 17:11:21', '2026-02-10 17:11:21'),
(12, '¿A donde fue tu primer viaje en avión?', 1, '2026-02-10 17:11:21', '2026-02-10 17:11:21'),
(13, '¿Cómo se llamaba tu peluche o juguete favorito?', 1, '2026-02-10 17:11:21', '2026-02-10 17:11:21'),
(14, '¿Cuál fue el nombre de tu primera escuela?', 1, '2026-02-10 17:11:21', '2026-02-10 17:11:21'),
(15, '¿Cuál es tu película favorita de todos los tiempos', 2, '2026-02-10 17:14:32', '2026-02-10 17:14:32'),
(16, '¿Cuál es el nombre de tu banda o artista musical f', 2, '2026-02-10 17:14:32', '2026-02-10 17:14:32'),
(17, '¿Cuál es el nombre de tu libro preferido?', 2, '2026-02-10 17:14:32', '2026-02-10 17:14:32'),
(18, '¿Cuál es tu plato de comida favorito para cocinar?', 2, '2026-02-10 17:14:32', '2026-02-10 17:14:32'),
(19, '¿Cuál es tu destino de vacaciones soñado?', 2, '2026-02-10 17:14:32', '2026-02-10 17:14:32'),
(20, '¿Cuál es tu color favorito de la infancia?', 2, '2026-02-10 17:14:32', '2026-02-10 17:14:32'),
(21, '¿Cuál es el nombre de tu personaje de ficción favo', 2, '2026-02-10 17:14:32', '2026-02-10 17:14:32'),
(22, '¿Qué marca era tu primer teléfono móvil?', 2, '2026-02-10 17:14:32', '2026-02-10 17:14:32'),
(23, '¿Cuál es tu estación del año favorita?', 2, '2026-02-10 17:14:32', '2026-02-10 17:14:32'),
(24, '¿Cuál es el nombre de tu restaurante preferido?', 2, '2026-02-10 17:14:32', '2026-02-10 17:14:32'),
(25, '¿Cuál es el segundo nombre de tu abuelo paterno?', 3, '2026-02-10 17:17:46', '2026-02-10 17:17:46'),
(26, '¿En qué ciudad nació tu madre?', 3, '2026-02-10 17:17:46', '2026-02-10 17:17:46'),
(27, '¿Cómo se llama tu mejor amigo/a de la infancia?', 3, '2026-02-10 17:17:46', '2026-02-10 17:17:46'),
(28, '¿Cuál es el nombre de tu primo/a más cercano/a?', 3, '2026-02-10 17:17:46', '2026-02-10 17:17:46'),
(29, '¿En qué ciudad te casaste o tuviste tu primera cit', 3, '2026-02-10 17:17:46', '2026-02-10 17:17:46'),
(30, '¿Cuál es el apellido de soltera de tu abuela mater', 3, '2026-02-10 17:17:46', '2026-02-10 17:17:46'),
(31, '¿Cuál es el segundo nombre de tu hermano/a mayor?', 3, '2026-02-10 17:17:46', '2026-02-10 17:17:46'),
(32, '¿Cómo se llamaba el jefe de tu primer trabajo?', 3, '2026-02-10 17:17:46', '2026-02-10 17:17:46'),
(33, '¿Cuál es el nombre de tu padrino o madrina?', 3, '2026-02-10 17:17:46', '2026-02-10 17:17:46'),
(34, '¿Cuál es el nombre de la empresa donde tuviste tu ', 3, '2026-02-10 17:17:46', '2026-02-10 17:17:46'),
(35, '¿Cuál es el nombre del primer equipo de fútbol en ', 4, '2026-02-10 17:20:39', '2026-02-10 17:20:39'),
(36, '¿Cuál fue el primer estadio de fútbol que visitast', 4, '2026-02-10 17:20:39', '2026-02-10 17:20:39'),
(37, '¿Cuál era el número de tu primera camiseta deporti', 4, '2026-02-10 17:20:39', '2026-02-10 17:20:39'),
(38, '¿Cómo se llamaba tu primer entrenador de fútbol?', 4, '2026-02-10 17:20:39', '2026-02-10 17:20:39'),
(39, '¿Cuál es tu deportista profesional favorito?', 4, '2026-02-10 17:20:39', '2026-02-10 17:20:39'),
(40, '¿Cuál fue el primer deporte que aprendiste a pract', 4, '2026-02-10 17:20:39', '2026-02-10 17:20:39'),
(41, '¿Cuál es el nombre de tu equipo de fútbol favorito', 4, '2026-02-10 17:20:39', '2026-02-10 17:20:39'),
(42, '¿En qué año asististe a tu primer partido profesio', 4, '2026-02-10 17:20:39', '2026-02-10 17:20:39'),
(43, '¿Cuál es tu marca de calzado deportivo preferida?', 4, '2026-02-10 17:20:39', '2026-02-10 17:20:39'),
(44, '¿Cuál es el nombre de la liga o torneo que más te ', 4, '2026-02-10 17:20:39', '2026-02-10 17:20:39');

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `representante`
--

CREATE TABLE `representante` (
  `representante_id` int(11) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `cedula` varchar(12) NOT NULL,
  `tipo_relacion` enum('abuelo/a','padres','tio/a','hermano/a','primo/a','representante') NOT NULL,
  `direccion_id` int(11) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `representante`
--

INSERT INTO `representante` (`representante_id`, `nombre_completo`, `telefono`, `cedula`, `tipo_relacion`, `direccion_id`, `foto`, `created_at`, `updated_at`) VALUES
(1, 'albany', '04245530385', '15234567', 'abuelo/a', 19, NULL, '2026-02-10 23:30:40', '2026-02-10 23:30:40');

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `respuesta_seguridad`
--

CREATE TABLE `respuesta_seguridad` (
  `respuesta_id` int(11) NOT NULL,
  `email_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `respuesta` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `respuesta_seguridad`
--

INSERT INTO `respuesta_seguridad` (`respuesta_id`, `email_id`, `pregunta_id`, `respuesta`, `created_at`) VALUES
(1, 'directivo@gmail.com', 5, 'money', '2026-02-10 17:35:18'),
(2, 'directivo@gmail.com', 19, 'money', '2026-02-10 17:35:18');

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `resultado_pruebas`
--

CREATE TABLE `resultado_pruebas` (
  `test_id` int(11) NOT NULL,
  `actividad_id` int(11) NOT NULL,
  `atleta_id` int(11) NOT NULL,
  `test_de_fuerza` double(10,2) DEFAULT NULL,
  `test_resistencia` double(10,2) DEFAULT NULL,
  `test_velocidad` double(10,2) DEFAULT NULL,
  `test_coordinacion` double(10,2) DEFAULT NULL,
  `test_de_reaccion` double(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `rol_usuarios`
--

CREATE TABLE `rol_usuarios` (
  `rol_id` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol_usuarios`
--

INSERT INTO `rol_usuarios` (`rol_id`, `nombre_rol`, `descripcion`, `fecha_creacion`) VALUES
(1, 'super_user', 'rol desarrollador, puede ver, modificar y eliminar todo tipo de vistas, maestras, y el sistema en general, usarlo solo para programarar.', '2025-12-03 20:08:31'),
(2, 'administrador', 'usado por el personal administrativo del club, puede ver los implementos deportivos, control de asistencia, tutor, pagos.', '2025-12-03 20:14:19'),
(3, 'entrenador', 'puede ver: los atletas, categorias, control asistencia, medidas antropometricas, test rendimiento.', '2025-12-03 20:13:54'),
(4, 'medico', 'puede ver, el historial medico y tutor', '2025-12-03 20:15:57');

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `email` varchar(100) NOT NULL,
  `password` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `token` varchar(500) DEFAULT NULL,
  `rol` int(11) NOT NULL,
  `estatus` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `foto` varchar(255) DEFAULT NULL,
  `ultimo_acceso` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`email`, `password`, `token`, `rol`, `estatus`, `foto`, `ultimo_acceso`, `created_at`, `updated_at`) VALUES
('admin@gmail.com', '12345678', NULL, 2, 'Activo', NULL, NULL, '2026-02-10 17:32:05', '2026-02-10 17:32:05'),
('directivo@gmail.com', '12345678', NULL, 1, 'Activo', NULL, NULL, '2026-02-10 17:33:31', '2026-02-10 17:33:31'),
('entrenador@gmail.com', '12345678', NULL, 3, 'Activo', NULL, NULL, '2026-02-10 17:31:04', '2026-02-10 17:31:04'),
('medico@gmail.com', '12345678', NULL, 4, 'Activo', NULL, NULL, '2026-02-10 17:32:05', '2026-02-10 17:32:05'),
('test@gmail.com', '$2y$10$bJ0Vm/Ie/QG7kLpcaXAJLOHR5', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VySWQiOjEsImVtYWlsIjoidGVzdEBnbWFpbC5jb20iLCJyb2wiOjEsImlhdCI6MTc3MDUwMzQyNiwiZXhwIjoxNzcwNTg5ODI2fQ.nIvG0OqVNKeBtzAsMw3a67cAey524ZpcgaZocYxgqN4', 1, 'Activo', NULL, NULL, '2026-02-10 17:01:51', '2026-02-10 17:01:51');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD PRIMARY KEY (`actividad_id`),
  ADD KEY `fecha` (`fecha`),
  ADD KEY `fk_actividad_entrenador` (`micro_id`);

--
-- Indices de la tabla `asignacion`
--
ALTER TABLE `asignacion`
  ADD PRIMARY KEY (`asignacion_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`asistencia_id`),
  ADD KEY `evento_id` (`actividad_id`),
  ADD KEY `atleta_id` (`atleta_id`);

--
-- Indices de la tabla `atencion_medica`
--
ALTER TABLE `atencion_medica`
  ADD PRIMARY KEY (`atencion_id`),
  ADD KEY `especialista_id` (`especialista_id`),
  ADD KEY `ficha_id` (`atleta_id`);

--
-- Indices de la tabla `atletas`
--
ALTER TABLE `atletas`
  ADD PRIMARY KEY (`atleta_id`),
  ADD KEY `idx_atletas_categoria` (`categoria_id`),
  ADD KEY `idx_atletas_tutor` (`representante_id`),
  ADD KEY `fk_atletas_direcciones` (`direccion_id`),
  ADD KEY `posicion_de_juego` (`posicion_de_juego`);

--
-- Indices de la tabla `carnet_discapacidad`
--
ALTER TABLE `carnet_discapacidad`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nro_carnet` (`nro_carnet`),
  ADD KEY `tipo_discapacidad_id` (`tipo_discapacidad_id`),
  ADD KEY `ficha_id` (`ficha_id`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`categoria_id`),
  ADD UNIQUE KEY `nombre_categoria` (`nombre_categoria`),
  ADD KEY `entrenador_id` (`entrenador_id`);

--
-- Indices de la tabla `cat_tipos_discapacidad`
--
ALTER TABLE `cat_tipos_discapacidad`
  ADD PRIMARY KEY (`tipos_discapacidad_id`);

--
-- Indices de la tabla `detalle_asignacion`
--
ALTER TABLE `detalle_asignacion`
  ADD PRIMARY KEY (`detalle_asignacion_id`),
  ADD KEY `asignacion_id` (`asignacion_id`),
  ADD KEY `implemento_id` (`implemento_id`);

--
-- Indices de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD PRIMARY KEY (`direccion_id`),
  ADD KEY `parroquias_id` (`parroquias_id`);

--
-- Indices de la tabla `estados`
--
ALTER TABLE `estados`
  ADD PRIMARY KEY (`estado_id`);

--
-- Indices de la tabla `ficha_medica`
--
ALTER TABLE `ficha_medica`
  ADD PRIMARY KEY (`ficha_id`),
  ADD KEY `atleta_id` (`atleta_id`);

--
-- Indices de la tabla `historial_partidos`
--
ALTER TABLE `historial_partidos`
  ADD PRIMARY KEY (`partido_id`),
  ADD KEY `cateogira_id` (`cateogira_id`);

--
-- Indices de la tabla `implementos_deportivos`
--
ALTER TABLE `implementos_deportivos`
  ADD PRIMARY KEY (`implemento_id`),
  ADD UNIQUE KEY `tipo` (`nombre`);

--
-- Indices de la tabla `medidas_antropometricas`
--
ALTER TABLE `medidas_antropometricas`
  ADD PRIMARY KEY (`medidas_id`),
  ADD KEY `idx_medidas_atleta` (`atleta_id`);

--
-- Indices de la tabla `municipios`
--
ALTER TABLE `municipios`
  ADD PRIMARY KEY (`municipio_id`),
  ADD KEY `id_estado` (`estadoi_id`);

--
-- Indices de la tabla `parroquias`
--
ALTER TABLE `parroquias`
  ADD PRIMARY KEY (`parroquia_id`),
  ADD KEY `id_municipio` (`municipio_id`);

--
-- Indices de la tabla `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`personal_id`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD UNIQUE KEY `email_id` (`email_id`) USING BTREE,
  ADD KEY `direccion_id` (`direccion_id`);

--
-- Indices de la tabla `plan_macrociclo`
--
ALTER TABLE `plan_macrociclo`
  ADD PRIMARY KEY (`macro_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `plan_mesociclo`
--
ALTER TABLE `plan_mesociclo`
  ADD PRIMARY KEY (`meso_id`),
  ADD KEY `macro_id` (`macro_id`);

--
-- Indices de la tabla `plan_microciclo`
--
ALTER TABLE `plan_microciclo`
  ADD PRIMARY KEY (`micro_id`),
  ADD KEY `meso_id` (`meso_id`);

--
-- Indices de la tabla `posicion_juego`
--
ALTER TABLE `posicion_juego`
  ADD PRIMARY KEY (`posicion_id`),
  ADD UNIQUE KEY `nombre_posicion` (`nombre_posicion`);

--
-- Indices de la tabla `preguntas_seguridad`
--
ALTER TABLE `preguntas_seguridad`
  ADD PRIMARY KEY (`preguntas_id`);

--
-- Indices de la tabla `representante`
--
ALTER TABLE `representante`
  ADD PRIMARY KEY (`representante_id`),
  ADD KEY `fk_tutor_direccion` (`direccion_id`);

--
-- Indices de la tabla `respuesta_seguridad`
--
ALTER TABLE `respuesta_seguridad`
  ADD PRIMARY KEY (`respuesta_id`),
  ADD KEY `pregunta_id` (`pregunta_id`),
  ADD KEY `email_id` (`email_id`);

--
-- Indices de la tabla `resultado_pruebas`
--
ALTER TABLE `resultado_pruebas`
  ADD PRIMARY KEY (`test_id`),
  ADD KEY `idx_test_atleta` (`atleta_id`),
  ADD KEY `pruebas_id` (`actividad_id`);

--
-- Indices de la tabla `rol_usuarios`
--
ALTER TABLE `rol_usuarios`
  ADD PRIMARY KEY (`rol_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`email`),
  ADD KEY `rol` (`rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividades`
--
ALTER TABLE `actividades`
  MODIFY `actividad_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignacion`
--
ALTER TABLE `asignacion`
  MODIFY `asignacion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `asistencia_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `atencion_medica`
--
ALTER TABLE `atencion_medica`
  MODIFY `atencion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `atletas`
--
ALTER TABLE `atletas`
  MODIFY `atleta_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `carnet_discapacidad`
--
ALTER TABLE `carnet_discapacidad`
  MODIFY `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `categoria_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `cat_tipos_discapacidad`
--
ALTER TABLE `cat_tipos_discapacidad`
  MODIFY `tipos_discapacidad_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_asignacion`
--
ALTER TABLE `detalle_asignacion`
  MODIFY `detalle_asignacion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  MODIFY `direccion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `estados`
--
ALTER TABLE `estados`
  MODIFY `estado_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `ficha_medica`
--
ALTER TABLE `ficha_medica`
  MODIFY `ficha_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_partidos`
--
ALTER TABLE `historial_partidos`
  MODIFY `partido_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `implementos_deportivos`
--
ALTER TABLE `implementos_deportivos`
  MODIFY `implemento_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `medidas_antropometricas`
--
ALTER TABLE `medidas_antropometricas`
  MODIFY `medidas_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `municipios`
--
ALTER TABLE `municipios`
  MODIFY `municipio_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=463;

--
-- AUTO_INCREMENT de la tabla `parroquias`
--
ALTER TABLE `parroquias`
  MODIFY `parroquia_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1139;

--
-- AUTO_INCREMENT de la tabla `personal`
--
ALTER TABLE `personal`
  MODIFY `personal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `plan_macrociclo`
--
ALTER TABLE `plan_macrociclo`
  MODIFY `macro_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `plan_mesociclo`
--
ALTER TABLE `plan_mesociclo`
  MODIFY `meso_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `plan_microciclo`
--
ALTER TABLE `plan_microciclo`
  MODIFY `micro_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `posicion_juego`
--
ALTER TABLE `posicion_juego`
  MODIFY `posicion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `preguntas_seguridad`
--
ALTER TABLE `preguntas_seguridad`
  MODIFY `preguntas_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `representante`
--
ALTER TABLE `representante`
  MODIFY `representante_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `respuesta_seguridad`
--
ALTER TABLE `respuesta_seguridad`
  MODIFY `respuesta_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `resultado_pruebas`
--
ALTER TABLE `resultado_pruebas`
  MODIFY `test_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `rol_usuarios`
--
ALTER TABLE `rol_usuarios`
  MODIFY `rol_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD CONSTRAINT `fk_actividad_entrenador` FOREIGN KEY (`micro_id`) REFERENCES `plan_microciclo` (`micro_id`);

--
-- Filtros para la tabla `asignacion`
--
ALTER TABLE `asignacion`
  ADD CONSTRAINT `asignacion_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`categoria_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `asistencias_ibfk_2` FOREIGN KEY (`atleta_id`) REFERENCES `atletas` (`atleta_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `asistencias_ibfk_3` FOREIGN KEY (`actividad_id`) REFERENCES `actividades` (`actividad_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `atencion_medica`
--
ALTER TABLE `atencion_medica`
  ADD CONSTRAINT `atencion_medica_ibfk_2` FOREIGN KEY (`especialista_id`) REFERENCES `personal` (`personal_id`),
  ADD CONSTRAINT `atencion_medica_ibfk_3` FOREIGN KEY (`atleta_id`) REFERENCES `atletas` (`atleta_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `atletas`
--
ALTER TABLE `atletas`
  ADD CONSTRAINT `atletas_ibfk_4` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`categoria_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `atletas_ibfk_5` FOREIGN KEY (`posicion_de_juego`) REFERENCES `posicion_juego` (`posicion_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `atletas_ibfk_7` FOREIGN KEY (`direccion_id`) REFERENCES `direcciones` (`direccion_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `atletas_ibfk_8` FOREIGN KEY (`representante_id`) REFERENCES `representante` (`representante_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `carnet_discapacidad`
--
ALTER TABLE `carnet_discapacidad`
  ADD CONSTRAINT `carnet_discapacidad_ibfk_1` FOREIGN KEY (`tipo_discapacidad_id`) REFERENCES `cat_tipos_discapacidad` (`tipos_discapacidad_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `carnet_discapacidad_ibfk_2` FOREIGN KEY (`ficha_id`) REFERENCES `ficha_medica` (`ficha_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD CONSTRAINT `categoria_ibfk_1` FOREIGN KEY (`entrenador_id`) REFERENCES `personal` (`personal_id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `detalle_asignacion`
--
ALTER TABLE `detalle_asignacion`
  ADD CONSTRAINT `detalle_asignacion_ibfk_1` FOREIGN KEY (`asignacion_id`) REFERENCES `asignacion` (`asignacion_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `detalle_asignacion_ibfk_2` FOREIGN KEY (`implemento_id`) REFERENCES `implementos_deportivos` (`implemento_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD CONSTRAINT `direcciones_ibfk_1` FOREIGN KEY (`parroquias_id`) REFERENCES `parroquias` (`parroquia_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `ficha_medica`
--
ALTER TABLE `ficha_medica`
  ADD CONSTRAINT `fk_ficha_atleta` FOREIGN KEY (`atleta_id`) REFERENCES `atletas` (`atleta_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `historial_partidos`
--
ALTER TABLE `historial_partidos`
  ADD CONSTRAINT `historial_partidos_ibfk_1` FOREIGN KEY (`cateogira_id`) REFERENCES `categoria` (`categoria_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `medidas_antropometricas`
--
ALTER TABLE `medidas_antropometricas`
  ADD CONSTRAINT `medidas_antropometricas_ibfk_1` FOREIGN KEY (`atleta_id`) REFERENCES `atletas` (`atleta_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `municipios`
--
ALTER TABLE `municipios`
  ADD CONSTRAINT `municipios_ibfk_1` FOREIGN KEY (`estadoi_id`) REFERENCES `estados` (`estado_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `parroquias`
--
ALTER TABLE `parroquias`
  ADD CONSTRAINT `parroquias_ibfk_1` FOREIGN KEY (`municipio_id`) REFERENCES `municipios` (`municipio_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `personal`
--
ALTER TABLE `personal`
  ADD CONSTRAINT `personal_ibfk_5` FOREIGN KEY (`email_id`) REFERENCES `usuarios` (`email`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `personal_ibfk_6` FOREIGN KEY (`direccion_id`) REFERENCES `direcciones` (`direccion_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `plan_macrociclo`
--
ALTER TABLE `plan_macrociclo`
  ADD CONSTRAINT `plan_macrociclo_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`categoria_id`);

--
-- Filtros para la tabla `plan_mesociclo`
--
ALTER TABLE `plan_mesociclo`
  ADD CONSTRAINT `plan_mesociclo_ibfk_1` FOREIGN KEY (`macro_id`) REFERENCES `plan_macrociclo` (`macro_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `plan_microciclo`
--
ALTER TABLE `plan_microciclo`
  ADD CONSTRAINT `plan_microciclo_ibfk_1` FOREIGN KEY (`meso_id`) REFERENCES `plan_mesociclo` (`meso_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `representante`
--
ALTER TABLE `representante`
  ADD CONSTRAINT `representante_ibfk_1` FOREIGN KEY (`direccion_id`) REFERENCES `direcciones` (`direccion_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `respuesta_seguridad`
--
ALTER TABLE `respuesta_seguridad`
  ADD CONSTRAINT `respuesta_seguridad_ibfk_1` FOREIGN KEY (`pregunta_id`) REFERENCES `preguntas_seguridad` (`preguntas_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `respuesta_seguridad_ibfk_2` FOREIGN KEY (`email_id`) REFERENCES `usuarios` (`email`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `resultado_pruebas`
--
ALTER TABLE `resultado_pruebas`
  ADD CONSTRAINT `resultado_pruebas_ibfk_1` FOREIGN KEY (`atleta_id`) REFERENCES `atletas` (`atleta_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resultado_pruebas_ibfk_2` FOREIGN KEY (`actividad_id`) REFERENCES `actividades` (`actividad_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol`) REFERENCES `rol_usuarios` (`rol_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

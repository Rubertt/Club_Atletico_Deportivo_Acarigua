-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-04-2026 a las 20:21:39
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
-- Base de datos: `cada_db_clean`
--

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
  `ubicacion` varchar(255) DEFAULT '''Cancha uptp''',
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
  `observaciones` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atencion_medica`
--

CREATE TABLE `atencion_medica` (
  `atencion_id` int(11) NOT NULL,
  `atleta_id` int(11) NOT NULL,
  `tipo_registro` tinyint(4) NOT NULL COMMENT '1:Lesion, 2:Enfermedad, 3:Control',
  `descripcion` varchar(200) NOT NULL,
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carnet_discapacidad`
--

CREATE TABLE `carnet_discapacidad` (
  `carnet_id` int(20) UNSIGNED NOT NULL,
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
  `ubicacion_vivienda` varchar(100) NOT NULL COMMENT 'ej:calle#15 vereda#12 casa#4'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados`
--

CREATE TABLE `estados` (
  `estado_id` int(11) NOT NULL,
  `estado` varchar(250) NOT NULL,
  `iso_3166-2` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
  `categoria_id` int(11) NOT NULL,
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
  `fecha_medicion` datetime NOT NULL,
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
  `estado_id` int(11) NOT NULL,
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `email` varchar(100) NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `token` varchar(500) DEFAULT NULL,
  `rol` int(11) NOT NULL,
  `estatus` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `foto` varchar(255) DEFAULT NULL,
  `ultimo_acceso` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

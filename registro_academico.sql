-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: sql202.infinityfree.com
-- Tiempo de generación: 16-09-2026 a las 02:25:00
-- Versión del servidor: 11.4.13-MariaDB
-- Versión de PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `if0_42887369_registroacademico`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes` (
  `id` int(11) NOT NULL,
  `nie` varchar(15) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `grado` varchar(100) NOT NULL DEFAULT '9° Grado',
  `seccion` varchar(50) NOT NULL DEFAULT 'Sección A',
  `encargado` varchar(150) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `dui_encargado` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id`, `nie`, `nombre`, `apellido`, `correo`, `grado`, `seccion`, `encargado`, `telefono`, `direccion`, `fecha_nacimiento`, `genero`, `foto`, `dui_encargado`) VALUES
(14, 'CM10001', 'Carlos', 'Martinez', 'CM10001@inca.edu.sv', '1° Año Bachillerato', 'Sección A', 'Carmen Elena Pinto', '7123-4567', 'San Salvador', '2011-05-15', 'Masculino', 'perfil_42_1789188622.jpg', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

CREATE TABLE `materias` (
  `id` int(11) NOT NULL,
  `nombre_materia` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id`, `nombre_materia`) VALUES
(2, 'Ciencias'),
(7, 'Educación Física'),
(6, 'Informática'),
(5, 'Inglés'),
(4, 'Lenguaje'),
(1, 'Matemática'),
(3, 'Sociales');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas`
--

CREATE TABLE `notas` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `materia_id` int(11) NOT NULL,
  `periodo` int(1) NOT NULL DEFAULT 1,
  `actividad1` decimal(4,2) DEFAULT 0.00,
  `actividad2` decimal(4,2) DEFAULT 0.00,
  `examen_final` decimal(4,2) DEFAULT 0.00,
  `promedio_final` decimal(4,2) GENERATED ALWAYS AS (`actividad1` * 0.35 + `actividad2` * 0.35 + `examen_final` * 0.30) STORED,
  `act1` decimal(4,2) NOT NULL DEFAULT 0.00,
  `act2` decimal(4,2) NOT NULL DEFAULT 0.00,
  `examen` decimal(4,2) NOT NULL DEFAULT 0.00,
  `promedio` decimal(4,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas_periodos`
--

CREATE TABLE `notas_periodos` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `materia` varchar(100) NOT NULL,
  `periodo` int(11) NOT NULL,
  `act1` decimal(4,1) DEFAULT 0.0,
  `act2` decimal(4,1) DEFAULT 0.0,
  `examen` decimal(4,1) DEFAULT 0.0,
  `nota_periodo` decimal(4,1) DEFAULT 0.0,
  `actualizado_en` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notas_periodos`
--

INSERT INTO `notas_periodos` (`id`, `estudiante_id`, `materia`, `periodo`, `act1`, `act2`, `examen`, `nota_periodo`, `actualizado_en`) VALUES
(1, 14, 'Matemática', 1, '10.0', '10.0', '0.0', '7.0', '2026-09-16 05:03:10'),
(2, 14, 'Matemática', 2, '8.0', '8.0', '0.0', '5.6', '2026-09-16 05:03:20'),
(3, 14, 'Lenguaje y Literatura', 1, '8.0', '0.0', '0.0', '2.8', '2026-09-16 04:59:20'),
(5, 14, 'Lenguaje y Literatura', 3, '9.0', '0.0', '0.0', '3.2', '2026-09-16 04:59:57'),
(6, 14, 'Educación Física', 1, '10.0', '10.0', '0.0', '7.0', '2026-09-16 05:07:16'),
(8, 14, 'Matemática', 3, '9.0', '9.0', '0.0', '6.3', '2026-09-16 05:03:30'),
(13, 14, 'Lenguaje y Literatura', 4, '0.0', '7.0', '0.0', '2.5', '2026-09-16 05:00:09'),
(17, 14, 'Matemática', 4, '2.0', '2.0', '0.0', '1.4', '2026-09-16 05:03:40'),
(18, 14, 'Estudios Sociales y Cívica', 1, '8.0', '8.0', '0.0', '5.6', '2026-09-16 05:04:04'),
(19, 14, 'Estudios Sociales y Cívica', 2, '0.0', '8.0', '0.0', '2.8', '2026-09-16 05:04:14'),
(20, 14, 'Estudios Sociales y Cívica', 3, '0.7', '0.0', '0.0', '0.2', '2026-09-16 05:04:23'),
(21, 14, 'Estudios Sociales y Cívica', 4, '0.0', '9.0', '0.0', '3.2', '2026-09-16 05:04:37'),
(22, 14, 'Ciencia y Tecnología', 1, '0.0', '1.0', '0.0', '0.4', '2026-09-16 05:05:30'),
(23, 14, 'Ciencia y Tecnología', 2, '0.0', '9.0', '0.0', '3.2', '2026-09-16 05:05:39'),
(24, 14, 'Ciencia y Tecnología', 3, '10.0', '0.0', '0.0', '3.5', '2026-09-16 05:05:50'),
(25, 14, 'Ciencia y Tecnología', 4, '9.0', '0.0', '0.0', '3.2', '2026-09-16 05:05:59'),
(26, 14, 'Idioma Extranjero (Inglés)', 1, '10.0', '0.0', '10.0', '6.5', '2026-09-16 05:06:21'),
(27, 14, 'Idioma Extranjero (Inglés)', 2, '9.0', '0.0', '8.0', '5.6', '2026-09-16 05:06:33'),
(28, 14, 'Idioma Extranjero (Inglés)', 3, '10.0', '10.0', '10.0', '10.0', '2026-09-16 05:06:45'),
(29, 14, 'Idioma Extranjero (Inglés)', 4, '6.0', '6.0', '0.0', '4.2', '2026-09-16 05:07:00'),
(31, 14, 'Educación Física', 2, '8.0', '0.0', '0.0', '2.8', '2026-09-16 05:07:26'),
(32, 14, 'Educación Física', 3, '0.0', '0.0', '10.0', '3.0', '2026-09-16 05:07:35'),
(33, 14, 'Educación Física', 4, '0.0', '6.7', '0.0', '2.3', '2026-09-16 05:07:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('administrador','docente','estudiante') NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `dui` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `genero` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `escalafon` varchar(50) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `materias_imparte` varchar(255) DEFAULT NULL,
  `grado_orientacion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `rol`, `nombre`, `apellido`, `telefono`, `direccion`, `dui`, `email`, `foto`, `genero`, `correo`, `escalafon`, `cargo`, `materias_imparte`, `grado_orientacion`) VALUES
(4, 'Administrador1', 'admin4', 'administrador', 'Oswaldo', 'Diaz', '78787878', '', NULL, NULL, 'user_4_1789529360.jpg', 'Masculino', NULL, NULL, NULL, NULL, NULL),
(42, 'CM10001', '1234', 'estudiante', 'Carlos', 'Martinez', '77777777', '', NULL, NULL, 'perfil_42_1789188622.jpg', 'Masculino', NULL, NULL, NULL, NULL, NULL),
(46, 'jose_carrillo@inca.edu.sv', '123', 'docente', 'Jose', 'Carrillo', '7896-5202', NULL, '05555557-7', NULL, NULL, 'Masculino', 'jose_carrillo@inca.edu.sv', 'ESC-75412', NULL, 'Matemática, Ciencia y Tecnología', '1° Año Bachillerato');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nie` (`nie`);

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_materia` (`nombre_materia`);

--
-- Indices de la tabla `notas`
--
ALTER TABLE `notas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `estudiante_materia_periodo` (`estudiante_id`,`materia_id`,`periodo`),
  ADD KEY `materia_id` (`materia_id`);

--
-- Indices de la tabla `notas_periodos`
--
ALTER TABLE `notas_periodos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_estudiante_materia_periodo` (`estudiante_id`,`materia`,`periodo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `notas`
--
ALTER TABLE `notas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `notas_periodos`
--
ALTER TABLE `notas_periodos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `notas`
--
ALTER TABLE `notas`
  ADD CONSTRAINT `notas_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notas_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-06-2026 a las 23:04:40
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
-- Base de datos: `registro_academico`
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
  `grado` enum('1G','2G','3G','4G','5G','6G','7G','8G','9G','1B','2B') NOT NULL DEFAULT '7G',
  `seccion` enum('A','B') NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id`, `nie`, `nombre`, `apellido`, `correo`, `grado`, `seccion`) VALUES
(6, '258369', 'Josue', 'Arevalo', '258369@inca.com', '8G', 'A'),
(7, '789456', 'Cristina', 'Reyes', '789456@inca.com', '1B', 'A');

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
  `actividad1` decimal(4,2) DEFAULT 0.00,
  `actividad2` decimal(4,2) DEFAULT 0.00,
  `examen_final` decimal(4,2) DEFAULT 0.00,
  `promedio_final` decimal(4,2) GENERATED ALWAYS AS (`actividad1` * 0.35 + `actividad2` * 0.35 + `examen_final` * 0.30) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notas`
--

INSERT INTO `notas` (`id`, `estudiante_id`, `materia_id`, `actividad1`, `actividad2`, `examen_final`) VALUES
(4, 6, 2, 10.00, 10.00, 10.00),
(5, 7, 2, 2.00, 9.00, 8.00);

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
  `apellido` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `rol`, `nombre`, `apellido`) VALUES
(4, 'Administrador1', '$2y$10$Lyie.ux5JwyJZnahLTXFtOR1.B9L1R64MbpGMn0SXiIDFAXbRHhnC', 'administrador', 'Oswaldo', 'Diaz'),
(6, 'cd15016', '$2y$10$R8SvTt02L4s2V6fM6W6W8eIqVxeSE5q7e8zCmGUX7ZzQRgOoEpR4u', 'administrador', 'Oswaldo', 'Diaz'),
(7, 'cd15017', '$2y$10$w9pu5vgLD096GvEBi.pxGOHUfF00C1frctUcTs9bOQRoOmZPWiSr2', 'estudiante', 'Oswaldo', 'Diaz'),
(19, 'cd15015', '$2y$10$ytp52.Di7dXOZUqyYh7a6ec.IoobzvxhSc188RfhExchN4vwSBiEq', 'docente', 'Oswaldo', 'Cardoza'),
(20, '258369', '$2y$10$Ym3ZPphL9QgcPe1dE.NreONayRmS5qCc7G41gjhFhN1eoI.z7t3fK', 'estudiante', 'Josue', 'Arevalo'),
(28, '789456', '$2y$10$Bwe3no/ULpKbK0rFCotNveSnNDsfdJ9YHBqtn2U2dNDQ67t0DRoH2', 'estudiante', 'Cristina', 'Reyes'),
(29, '159357', '$2y$10$aQRSFhQo8yywkJjFlpqrBe8ztB243yIJy5UeJng7FDjevOZW6iLBe', 'administrador', 'Oswaldo', 'Diaz');

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
  ADD UNIQUE KEY `estudiante_materia` (`estudiante_id`,`materia_id`),
  ADD KEY `materia_id` (`materia_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `notas`
--
ALTER TABLE `notas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

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

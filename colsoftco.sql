-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-08-2026 a las 23:10:56
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
-- Base de datos: `colsoftco`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `areas`
--

CREATE TABLE `areas` (
  `id_area` int(11) NOT NULL,
  `nombre_area` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_movimientos`
--

CREATE TABLE `historial_movimientos` (
  `id` int(11) NOT NULL,
  `modulo` varchar(50) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `id_registro` int(11) DEFAULT NULL,
  `descripcion` text NOT NULL,
  `datos_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_anteriores`)),
  `datos_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_nuevos`)),
  `usuario_id` int(11) DEFAULT NULL,
  `usuario_nombre` varchar(100) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `historial_movimientos`
--

INSERT INTO `historial_movimientos` (`id`, `modulo`, `accion`, `id_registro`, `descripcion`, `datos_anteriores`, `datos_nuevos`, `usuario_id`, `usuario_nombre`, `ip`, `fecha_hora`) VALUES
(1, 'materia_prima', 'editar', 9, 'Se actualizó la materia prima \'Borde perimetral\'', NULL, '{\"nombre_material\":\"Borde perimetral\",\"stock_actual\":\"180.00\",\"stock_minimo\":\"40.00\",\"id_unidad\":\"2\",\"id_proveedor\":\"3\"}', NULL, 'Jafet Pineda', '::1', '2026-08-06 02:28:15'),
(2, 'proveedores', 'crear', NULL, 'Se registró el proveedor \'Res\'', NULL, '{\"nombre_empresa\":\"Res\",\"contacto_nombre\":\"David\",\"contacto_apellido\":\"Pineda\",\"telefono\":\"3116364875\",\"email\":\"jafetgatitos06@gmail.com\",\"nit\":\"455492131245\",\"direccion\":\"Cl. 14 #107-54\"}', NULL, 'Jafet Pineda', '::1', '2026-08-06 02:42:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias_primas`
--

CREATE TABLE `materias_primas` (
  `id_material` int(11) NOT NULL,
  `nombre_material` varchar(100) NOT NULL,
  `stock_actual` decimal(12,2) DEFAULT 0.00,
  `stock_minimo` decimal(12,2) NOT NULL,
  `id_unidad` int(11) DEFAULT NULL,
  `id_proveedor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materias_primas`
--

INSERT INTO `materias_primas` (`id_material`, `nombre_material`, `stock_actual`, `stock_minimo`, `id_unidad`, `id_proveedor`) VALUES
(1, 'Espuma de poliuretano', 50.00, 0.00, 1, 1),
(2, 'Tela Jacquard', 300.00, 50.00, 2, 2),
(3, 'Resortes Bonnell', 1200.00, 300.00, 3, 3),
(4, 'Fieltro aislante', 250.00, 50.00, 2, 1),
(5, 'Pegante industrial', 100.00, 20.00, 4, 4),
(6, 'Hilo de costura', 80.00, 15.00, 3, 2),
(7, 'Espuma viscoelastica', 60.00, 30.00, 1, 1),
(8, 'Tela antideslizante', 200.00, 40.00, 2, 2),
(9, 'Borde perimetral', 180.00, 40.00, 2, 3),
(10, 'Empaque plastico', 400.00, 80.00, 2, 4),
(11, 'Tela', 100.00, 30.00, 2, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modelos_colchon`
--

CREATE TABLE `modelos_colchon` (
  `id_modelo` int(11) NOT NULL,
  `nombre_modelo` varchar(80) NOT NULL,
  `serial` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `modelos_colchon`
--

INSERT INTO `modelos_colchon` (`id_modelo`, `nombre_modelo`, `serial`) VALUES
(1, 'Sueño Plus', 'COL-0001'),
(2, 'Descanso Real', 'COL-0002'),
(3, 'Confort Total', 'COL-0003'),
(4, 'Premium Gold', 'COL-0004'),
(5, 'Sencillo', ''),
(8, 'Semidoble', 'MOD-SEMIDOBLE'),
(9, 'Doble', 'MOD-DOBLE'),
(10, 'Queen', 'MOD-QUEEN'),
(11, 'King', 'MOD-KING');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `id_material` int(11) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `fecha_generada` timestamp NOT NULL DEFAULT current_timestamp(),
  `leida` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos_proveedor`
--

CREATE TABLE `pedidos_proveedor` (
  `id_pedido` int(11) NOT NULL,
  `id_proveedor` int(11) DEFAULT NULL,
  `id_material` int(11) DEFAULT NULL,
  `cantidad_pedida` decimal(12,2) NOT NULL,
  `estado` enum('PENDIENTE','RECIBIDO','CANCELADO') DEFAULT 'PENDIENTE',
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL,
  `nombre_empresa` varchar(80) NOT NULL,
  `nit` varchar(20) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `descripcion_empresa` text DEFAULT NULL,
  `contacto_nombre` varchar(80) NOT NULL,
  `contacto_apellido` varchar(80) NOT NULL,
  `telefono` varchar(10) DEFAULT NULL,
  `email` varchar(70) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor`, `nombre_empresa`, `nit`, `direccion`, `descripcion_empresa`, `contacto_nombre`, `contacto_apellido`, `telefono`, `email`) VALUES
(1, 'Espumas Colombia SAS', '900123456-1', 'Bogotá D.C.', 'Fabricación y comercialización de espumas para colchones', 'Carlos', 'Ramirez', '3101111111', 'ventas@espumascolombia.com'),
(2, 'Textiles Andinos SAS', '800234567-2', 'Medellín, Antioquia', 'Producción de textiles para la industria colchonera', 'Laura', 'Gomez', '3102222222', 'contacto@textilesandinos.com'),
(3, 'Resortes Nacionales SAS', '900345678-3', 'Cali, Valle del Cauca', 'Fabricación de resortes para colchones', 'Andres', 'Martinez', '3103333333', 'ventas@resortesnacionales.com'),
(4, 'Insumos Industriales SAS', '900456789-4', 'Barranquilla, Atlántico', 'Distribución de insumos industriales', 'Paula', 'Torres', '3104444444', 'compras@insumosindustriales.com'),
(5, 'Espumas y Colchones del Norte S.A.S.', '901456789-3', 'Carrera 15 # 45-20, Bogotá, Colombia', 'Proveedor especializado en espuma de poliuretano, telas para colchonería, resortes y materias primas para la fabricación de colchones y muebles.', 'Carlos', 'Ramírez', '3204567890', 'compras@espumasnorte.com'),
(7, 'Res', '455492131245', 'Cl. 14 #107-54', 'Resortes Especializados', 'David', 'Pineda', '3116364875', 'jafetgatitos06@gmail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `receta_colchon`
--

CREATE TABLE `receta_colchon` (
  `id_receta` int(11) NOT NULL,
  `id_modelo` int(11) DEFAULT NULL,
  `id_material` int(11) DEFAULT NULL,
  `cantidad_requerida` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `receta_colchon`
--

INSERT INTO `receta_colchon` (`id_receta`, `id_modelo`, `id_material`, `cantidad_requerida`) VALUES
(1, 5, 1, 15.00),
(2, 5, 2, 5.00),
(3, 5, 5, 1.00),
(4, 8, 1, 18.00),
(5, 8, 2, 6.00),
(6, 8, 5, 1.10),
(7, 9, 1, 21.00),
(8, 9, 2, 7.00),
(9, 9, 5, 1.30),
(10, 10, 1, 25.00),
(11, 10, 2, 8.00),
(12, 10, 5, 1.50),
(13, 11, 1, 30.00),
(14, 11, 2, 9.50),
(15, 11, 5, 1.80),
(16, 1, 1, 20.00),
(17, 1, 2, 6.50),
(18, 1, 3, 150.00),
(19, 1, 4, 3.80),
(20, 1, 5, 1.30),
(21, 1, 6, 0.45),
(22, 1, 8, 3.20),
(23, 1, 9, 9.50),
(24, 1, 10, 1.00),
(25, 2, 1, 26.00),
(26, 2, 2, 8.50),
(27, 2, 3, 190.00),
(28, 2, 4, 5.20),
(29, 2, 5, 1.80),
(30, 2, 6, 0.65),
(31, 2, 7, 4.00),
(32, 2, 8, 4.20),
(33, 2, 9, 11.20),
(34, 2, 10, 1.00),
(35, 3, 1, 23.00),
(36, 3, 2, 7.50),
(37, 3, 3, 170.00),
(38, 3, 4, 4.50),
(39, 3, 5, 1.60),
(40, 3, 6, 0.55),
(41, 3, 7, 2.00),
(42, 3, 8, 3.80),
(43, 3, 9, 10.50),
(44, 3, 10, 1.00),
(45, 4, 1, 32.00),
(46, 4, 2, 10.00),
(47, 4, 3, 230.00),
(48, 4, 4, 6.50),
(49, 4, 5, 2.20),
(50, 4, 6, 0.80),
(51, 4, 7, 8.00),
(52, 4, 8, 5.50),
(53, 4, 9, 12.50),
(54, 4, 10, 1.00),
(55, 5, 1, 15.00),
(56, 5, 2, 5.00),
(57, 5, 3, 120.00),
(58, 5, 4, 3.00),
(59, 5, 5, 1.00),
(60, 5, 6, 0.30),
(61, 5, 8, 2.50),
(62, 5, 9, 8.00),
(63, 5, 10, 1.00),
(64, 8, 1, 18.00),
(65, 8, 2, 6.00),
(66, 8, 3, 140.00),
(67, 8, 4, 3.50),
(68, 8, 5, 1.20),
(69, 8, 6, 0.40),
(70, 8, 8, 3.00),
(71, 8, 9, 9.00),
(72, 8, 10, 1.00),
(73, 9, 1, 22.00),
(74, 9, 2, 7.00),
(75, 9, 3, 160.00),
(76, 9, 4, 4.00),
(77, 9, 5, 1.50),
(78, 9, 6, 0.50),
(79, 9, 8, 3.50),
(80, 9, 9, 10.00),
(81, 9, 10, 1.00),
(82, 10, 1, 25.00),
(83, 10, 2, 8.00),
(84, 10, 3, 180.00),
(85, 10, 4, 5.00),
(86, 10, 5, 1.70),
(87, 10, 6, 0.60),
(88, 10, 7, 3.00),
(89, 10, 8, 4.00),
(90, 10, 9, 11.00),
(91, 10, 10, 1.00),
(92, 11, 1, 30.00),
(93, 11, 2, 9.00),
(94, 11, 3, 210.00),
(95, 11, 4, 6.00),
(96, 11, 5, 2.00),
(97, 11, 6, 0.70),
(98, 11, 7, 5.00),
(99, 11, 8, 5.00),
(100, 11, 9, 12.00),
(101, 11, 10, 1.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unidades_medida`
--

CREATE TABLE `unidades_medida` (
  `id_unidad` int(11) NOT NULL,
  `sigla` varchar(5) NOT NULL,
  `nombre_unidad` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `unidades_medida`
--

INSERT INTO `unidades_medida` (`id_unidad`, `sigla`, `nombre_unidad`) VALUES
(1, 'KG', 'Kilogramos'),
(2, 'MT', 'Metros'),
(3, 'UND', 'Unidades'),
(4, 'LT', 'Litros');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `documento` varchar(15) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `apellido` varchar(30) NOT NULL,
  `rol` enum('administrador','bodeguero','operario','') NOT NULL,
  `password_hash` varchar(60) NOT NULL,
  `request_password` enum('0','1') NOT NULL DEFAULT '0',
  `token_password` varchar(200) DEFAULT NULL,
  `expired_session` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `email`, `documento`, `nombre`, `apellido`, `rol`, `password_hash`, `request_password`, `token_password`, `expired_session`) VALUES
(7, 'juanjosemon19@gmail.com', '1068952619', 'Juan', 'Montaño', 'administrador', '$2y$10$WIOrMUGxtSwEkYs4M0GgOuVfDrb9x6e4P7uVC6hvp8GxRE3oHV2ue', '1', '189510a696b40e8fe8fad52f932f321cb629de31456b124461fc2fc44fa6f3f1', '1785468187'),
(8, 'avellanedamaldonadosantiago@gmail.com', '1025062749', 'Santiago', 'Avellaneda', 'administrador', '$2y$10$wlEKTrfvZKCGOKGzWbMhUuoj0n4sPO9bxLpBP7Ujaz27leCx6T4n6', '0', NULL, NULL),
(10, 'jafetdavidpi@gmail.com', '1072746605', 'Jafet', 'Pineda', 'bodeguero', '$2y$10$2kfEdDY7eG6cKtCMvC0/qeX38azsoVKHFXE2AI8R4QCv8pZ7qt2m.', '0', NULL, NULL),
(14, 'nicolaspolo096@gmail.com', '1013116788', 'Nicolas', 'Polo', 'administrador', '$2y$10$EZVWx.J.syl4M3CsNWiFLOQLRH3MLliTuZ19rIlHK8L/UO8/Azp2i', '1', 'ef9b6fe4769c7ac26885c7c42b214eacc75cddf6893561b167a9b1d58cb64cd8', '1782452857');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id_area`);

--
-- Indices de la tabla `historial_movimientos`
--
ALTER TABLE `historial_movimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_modulo` (`modulo`),
  ADD KEY `idx_accion` (`accion`),
  ADD KEY `idx_fecha` (`fecha_hora`),
  ADD KEY `idx_usuario` (`usuario_id`);

--
-- Indices de la tabla `materias_primas`
--
ALTER TABLE `materias_primas`
  ADD PRIMARY KEY (`id_material`),
  ADD KEY `id_unidad` (`id_unidad`),
  ADD KEY `id_proveedor` (`id_proveedor`);

--
-- Indices de la tabla `modelos_colchon`
--
ALTER TABLE `modelos_colchon`
  ADD PRIMARY KEY (`id_modelo`),
  ADD UNIQUE KEY `serial` (`serial`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `id_material` (`id_material`);

--
-- Indices de la tabla `pedidos_proveedor`
--
ALTER TABLE `pedidos_proveedor`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `id_proveedor` (`id_proveedor`),
  ADD KEY `id_material` (`id_material`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`);

--
-- Indices de la tabla `receta_colchon`
--
ALTER TABLE `receta_colchon`
  ADD PRIMARY KEY (`id_receta`),
  ADD KEY `id_modelo` (`id_modelo`),
  ADD KEY `id_material` (`id_material`);

--
-- Indices de la tabla `unidades_medida`
--
ALTER TABLE `unidades_medida`
  ADD PRIMARY KEY (`id_unidad`),
  ADD UNIQUE KEY `sigla` (`sigla`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `documento` (`documento`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `areas`
--
ALTER TABLE `areas`
  MODIFY `id_area` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_movimientos`
--
ALTER TABLE `historial_movimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `materias_primas`
--
ALTER TABLE `materias_primas`
  MODIFY `id_material` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `modelos_colchon`
--
ALTER TABLE `modelos_colchon`
  MODIFY `id_modelo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidos_proveedor`
--
ALTER TABLE `pedidos_proveedor`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `receta_colchon`
--
ALTER TABLE `receta_colchon`
  MODIFY `id_receta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT de la tabla `unidades_medida`
--
ALTER TABLE `unidades_medida`
  MODIFY `id_unidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `materias_primas`
--
ALTER TABLE `materias_primas`
  ADD CONSTRAINT `materias_primas_ibfk_1` FOREIGN KEY (`id_unidad`) REFERENCES `unidades_medida` (`id_unidad`),
  ADD CONSTRAINT `materias_primas_ibfk_2` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`);

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_material`) REFERENCES `materias_primas` (`id_material`);

--
-- Filtros para la tabla `pedidos_proveedor`
--
ALTER TABLE `pedidos_proveedor`
  ADD CONSTRAINT `pedidos_proveedor_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`),
  ADD CONSTRAINT `pedidos_proveedor_ibfk_2` FOREIGN KEY (`id_material`) REFERENCES `materias_primas` (`id_material`);

--
-- Filtros para la tabla `receta_colchon`
--
ALTER TABLE `receta_colchon`
  ADD CONSTRAINT `receta_colchon_ibfk_1` FOREIGN KEY (`id_modelo`) REFERENCES `modelos_colchon` (`id_modelo`),
  ADD CONSTRAINT `receta_colchon_ibfk_2` FOREIGN KEY (`id_material`) REFERENCES `materias_primas` (`id_material`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

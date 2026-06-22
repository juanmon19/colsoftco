-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-06-2026 a las 06:21:55
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
(1, 'Espuma de poliuretano', 500.00, 100.00, 1, 1),
(2, 'Tela Jacquard', 300.00, 50.00, 2, 2),
(3, 'Resortes Bonnell', 1200.00, 300.00, 3, 3),
(4, 'Fieltro aislante', 250.00, 50.00, 2, 1),
(5, 'Pegante industrial', 100.00, 20.00, 4, 4),
(6, 'Hilo de costura', 80.00, 15.00, 3, 2),
(7, 'Espuma viscoelastica', 150.00, 30.00, 1, 1),
(8, 'Tela antideslizante', 200.00, 40.00, 2, 2),
(9, 'Borde perimetral', 180.00, 30.00, 2, 3),
(10, 'Empaque plastico', 400.00, 80.00, 2, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modelos_colchon`
--

CREATE TABLE `modelos_colchon` (
  `id_modelo` int(11) NOT NULL,
  `nombre_modelo` varchar(80) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id_movimiento` int(11) NOT NULL,
  `id_material` int(11) DEFAULT NULL,
  `id_area_origen` int(11) DEFAULT NULL,
  `id_area_destino` int(11) DEFAULT NULL,
  `cantidad` decimal(12,2) NOT NULL,
  `tipo_movimiento` enum('ENTRADA','SALIDA','TRASLADO') NOT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `movimientos_inventario`
--
DELIMITER $$
CREATE TRIGGER `check_stock_minimo` AFTER INSERT ON `movimientos_inventario` FOR EACH ROW BEGIN
    DECLARE stock_actual_var DECIMAL(12,2);
    DECLARE stock_min_var DECIMAL(12,2);
    DECLARE nombre_mat_var VARCHAR(100);

    -- 1. Obtenemos los datos actuales del material afectado
    SELECT stock_actual, stock_minimo, nombre_material 
    INTO stock_actual_var, stock_min_var, nombre_mat_var
    FROM Materias_Primas 
    WHERE id_material = NEW.id_material;

    -- 2. Si el stock es menor o igual al mínimo, generamos la alerta
    IF stock_actual_var <= stock_min_var THEN
        INSERT INTO Notificaciones (id_material, mensaje)
        VALUES (
            NEW.id_material, 
            CONCAT('ALERTA: El material "', nombre_mat_var, 
                   '" ha alcanzado su nivel crítico. Stock actual: ', 
                   stock_actual_var)
        );
    END IF;
END
$$
DELIMITER ;

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
(5, 'prueba', '1234', 'CR CARMEN DE CARUPA CUNDINAMARCA CRA 4 #', 'prueba del crud', 'Juan', 'Montaño', '3229035224', 'juanjosemon19@gmail.com');

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
  `usuario` varchar(10) NOT NULL,
  `rol` enum('administrador','bodeguero','operario','') NOT NULL,
  `password_hash` varchar(60) NOT NULL,
  `request_password` enum('0','1') NOT NULL DEFAULT '0',
  `token_password` varchar(200) DEFAULT NULL,
  `expired_session` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `email`, `usuario`, `rol`, `password_hash`, `request_password`, `token_password`, `expired_session`) VALUES
(7, 'juanjosemon19@gmail.com', 'juan mont', 'administrador', '$2y$10$AR4/.AW21G2OPDJ0ZoffzOcnvadCc1dy.TbiTnKPWQTS3qssxcm5u', '1', '54444dcae0d4ab3e9e2a49435c415c683f6538ed8cdf9e0b91ca4a5abaf82f5e', '1782005773'),
(8, 'avellanedamaldonadosantiago@gmail.com', 'santiago', 'bodeguero', '$2y$10$wlEKTrfvZKCGOKGzWbMhUuoj0n4sPO9bxLpBP7Ujaz27leCx6T4n6', '0', NULL, NULL),
(9, 'nicolaspolo096@gmail.com', 'nicolas', 'operario', '$2y$10$uQHE1nhhHVS4cUKa/WBiE.2qEc7ctolJ9Us.EyKrqC1mjFr83/5Ca', '1', '838c2c089f879501b195b3ef0b2b3ba3e18c01eed09ff560a603d63970fc6c97', '1781892928'),
(10, 'jafetdavidpi@gmail.com', 'jafet', 'bodeguero', '$2y$10$2kfEdDY7eG6cKtCMvC0/qeX38azsoVKHFXE2AI8R4QCv8pZ7qt2m.', '0', NULL, NULL),
(11, 'diegogo3027@gmail.com', 'diego', 'administrador', '$2y$10$nibWlgfFwT/WsTKv1NDdC.TMY3SHE6xrUvXl874NAikuHEN9juyyC', '1', '5b285d2f93f0e98c94f9da45a630dce655da9803d9125b426403616fcb9bee15', '1781920147');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id_area`);

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
  ADD PRIMARY KEY (`id_modelo`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `id_material` (`id_material`),
  ADD KEY `id_area_origen` (`id_area_origen`),
  ADD KEY `id_area_destino` (`id_area_destino`),
  ADD KEY `id_usuario` (`id_usuario`);

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
  ADD PRIMARY KEY (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `areas`
--
ALTER TABLE `areas`
  MODIFY `id_area` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `materias_primas`
--
ALTER TABLE `materias_primas`
  MODIFY `id_material` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `modelos_colchon`
--
ALTER TABLE `modelos_colchon`
  MODIFY `id_modelo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `receta_colchon`
--
ALTER TABLE `receta_colchon`
  MODIFY `id_receta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `unidades_medida`
--
ALTER TABLE `unidades_medida`
  MODIFY `id_unidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
-- Filtros para la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `movimientos_inventario_ibfk_1` FOREIGN KEY (`id_material`) REFERENCES `materias_primas` (`id_material`),
  ADD CONSTRAINT `movimientos_inventario_ibfk_2` FOREIGN KEY (`id_area_origen`) REFERENCES `areas` (`id_area`),
  ADD CONSTRAINT `movimientos_inventario_ibfk_3` FOREIGN KEY (`id_area_destino`) REFERENCES `areas` (`id_area`),
  ADD CONSTRAINT `movimientos_inventario_ibfk_4` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

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

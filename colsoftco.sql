-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-09-2026 a las 06:06:01
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
(2, 'proveedores', 'crear', NULL, 'Se registró el proveedor \'Res\'', NULL, '{\"nombre_empresa\":\"Res\",\"contacto_nombre\":\"David\",\"contacto_apellido\":\"Pineda\",\"telefono\":\"3116364875\",\"email\":\"jafetgatitos06@gmail.com\",\"nit\":\"455492131245\",\"direccion\":\"Cl. 14 #107-54\"}', NULL, 'Jafet Pineda', '::1', '2026-08-06 02:42:38'),
(3, 'materia_prima', 'crear', 13, 'Se registró la materia prima \'Fieltro aislante\' con stock inicial de 20', NULL, '{\"nombre_material\":\"Fieltro aislante\",\"stock_actual\":\"20\",\"stock_minimo\":0,\"id_unidad\":\"3\",\"id_proveedor\":\"4\"}', NULL, 'Jafet Pineda', '::1', '2026-08-11 19:42:16'),
(4, 'producto_terminado', 'crear', 1, 'Se registró producción de 20 unidades de \'Colchon Queen\'', NULL, '{\"nombre_producto\":\"Colchon Queen\",\"cantidad\":\"20\"}', NULL, 'Jafet Pineda', '::1', '2026-08-11 19:52:11'),
(5, 'producto_terminado', 'crear', 2, 'Se agregó el producto terminado \'Colchon King\' con stock 12', NULL, '{\"nombre_producto\":\"Colchon King\",\"stock_actual\":\"12\"}', NULL, 'Jafet Pineda', '::1', '2026-08-11 19:56:06'),
(6, 'producto_terminado', 'crear', 3, 'Se registró producción de 15 unidades de \'Colchon Normal\'', NULL, '{\"nombre_producto\":\"Colchon Normal\",\"cantidad\":\"15\"}', NULL, 'Jafet Pineda', '::1', '2026-08-11 19:56:39'),
(7, 'producto_terminado', 'crear', 1, 'Se registró producción de 4576 unidades de \'Colchon Queen\'', NULL, '{\"nombre_producto\":\"Colchon Queen\",\"cantidad\":\"4576\"}', NULL, 'Jafet Pineda', '::1', '2026-08-11 19:58:06'),
(8, 'producto_terminado', 'actualizar', 1, 'Se actualizó el producto terminado \'Colchon Queen\' a stock 68887', NULL, '{\"nombre_producto\":\"Colchon Queen\",\"stock_actual\":\"68887\"}', NULL, 'Jafet Pineda', '::1', '2026-08-11 19:58:27'),
(9, 'producto_terminado', 'actualizar', 1, 'Se actualizó el producto terminado \'Colchon Queen\' a stock 20', NULL, '{\"nombre_producto\":\"Colchon Queen\",\"stock_actual\":\"20\"}', NULL, 'Jafet Pineda', '::1', '2026-08-11 19:58:45'),
(10, 'proveedores', 'crear', NULL, 'Se registró el proveedor \'Resortes Especiales S.A.S\'', NULL, '{\"nombre_empresa\":\"Resortes Especiales S.A.S\",\"contacto_nombre\":\"Jafet\",\"contacto_apellido\":\"Pineda\",\"telefono\":\"3116364875\",\"email\":\"jafetdavidpi@gmail.com\",\"nit\":\"83947593487\",\"direccion\":\"Cl 14 107-54\"}', NULL, 'Jafet Pineda', '::1', '2026-08-11 20:02:36'),
(11, 'proveedores', 'crear', NULL, 'Se registró el proveedor \'Textiles Andino\'', NULL, '{\"nombre_empresa\":\"Textiles Andino\",\"contacto_nombre\":\"Nicolas\",\"contacto_apellido\":\"Polas\",\"telefono\":\"3253659896\",\"email\":\"shasjahjshjajsjajs@gmail.com\",\"nit\":\"1220200202\",\"direccion\":\"Cra 12 29-29\",\"imagen\":\"proveedor_6a7caf8c18a07.jpg\"}', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 12:38:20'),
(12, 'proveedores', 'eliminar', 9, 'Se eliminó el proveedor \'Textiles Andino\'', '{\"id_proveedor\":9,\"nombre_empresa\":\"Textiles Andino\",\"nit\":\"1220200202\",\"direccion\":\"Cra 12 29-29\",\"descripcion_empresa\":\"Hola como están\",\"contacto_nombre\":\"Nicolas\",\"contacto_apellido\":\"Polas\",\"telefono\":\"3253659896\",\"email\":\"shasjahjshjajsjajs@gmail.com\",\"imagen\":\"proveedor_6a7caf8c18a07.jpg\"}', NULL, NULL, 'Santiago Avellaneda', '::1', '2026-08-12 12:42:20'),
(13, 'proveedores', 'crear', NULL, 'Se registró el proveedor \'Textiles andino\'', NULL, '{\"nombre_empresa\":\"Textiles andino\",\"contacto_nombre\":\"Nicolay\",\"contacto_apellido\":\"Polas\",\"telefono\":\"3124567898\",\"email\":\"nicorr@gmail.com\",\"nit\":\"123456789123\",\"direccion\":\"Cra 43 No 12-23\",\"imagen\":\"proveedor_6a7cb248cbea8.jpg\"}', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 12:50:00'),
(14, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 12:58:20'),
(15, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 12:58:29'),
(16, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 12:58:34'),
(17, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 12:58:36'),
(18, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 12:59:45'),
(19, 'proveedores', 'editar', 3, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":3,\"nombre_empresa\":\"Resortes Nacionales SAS\",\"nit\":\"900345678-3\",\"direccion\":\"Cali, Valle del Cauca\",\"descripcion_empresa\":\"Fabricación de resortes para colchones\",\"contacto_nombre\":\"Andres\",\"contacto_apellido\":\"Martinez\",\"telefono\":\"3103333333\",\"email\":\"ventas@resortesnacionales.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 13:06:18'),
(20, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 13:06:25'),
(21, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 13:07:08'),
(22, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 13:07:11'),
(23, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 13:07:13'),
(24, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 13:59:19'),
(25, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 13:59:38'),
(26, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:02:54'),
(27, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:02:56'),
(28, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:06:01'),
(29, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:08:49'),
(30, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:18:04'),
(31, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:19:18'),
(32, 'proveedores', 'editar', 10, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":10,\"nombre_empresa\":\"Textiles andino\",\"nit\":\"123456789123\",\"direccion\":\"Cra 43 No 12-23\",\"descripcion_empresa\":\"Empresa de textiles\",\"contacto_nombre\":\"Nicolay\",\"contacto_apellido\":\"Polas\",\"telefono\":\"3124567898\",\"email\":\"nicorr@gmail.com\",\"imagen\":\"proveedor_6a7cb248cbea8.jpg\"}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:19:25'),
(33, 'proveedores', 'editar', 5, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":5,\"nombre_empresa\":\"Espumas y Colchones del Norte S.A.S.\",\"nit\":\"901456789-3\",\"direccion\":\"Carrera 15 # 45-20, Bogotá, Colombia\",\"descripcion_empresa\":\"Proveedor especializado en espuma de poliuretano, telas para colchonería, resortes y materias primas para la fabricación de colchones y muebles.\",\"contacto_nombre\":\"Carlos\",\"contacto_apellido\":\"Ramírez\",\"telefono\":\"3204567890\",\"email\":\"compras@espumasnorte.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:19:26'),
(34, 'proveedores', 'editar', 5, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":5,\"nombre_empresa\":\"Espumas y Colchones del Norte S.A.S.\",\"nit\":\"901456789-3\",\"direccion\":\"Carrera 15 # 45-20, Bogotá, Colombia\",\"descripcion_empresa\":\"Proveedor especializado en espuma de poliuretano, telas para colchonería, resortes y materias primas para la fabricación de colchones y muebles.\",\"contacto_nombre\":\"Carlos\",\"contacto_apellido\":\"Ramírez\",\"telefono\":\"3204567890\",\"email\":\"compras@espumasnorte.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:19:27'),
(35, 'proveedores', 'editar', 4, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":4,\"nombre_empresa\":\"Insumos Industriales SAS\",\"nit\":\"900456789-4\",\"direccion\":\"Barranquilla, Atlántico\",\"descripcion_empresa\":\"Distribución de insumos industriales\",\"contacto_nombre\":\"Paula\",\"contacto_apellido\":\"Torres\",\"telefono\":\"3104444444\",\"email\":\"compras@insumosindustriales.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:19:31'),
(36, 'proveedores', 'editar', 1, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":1,\"nombre_empresa\":\"Espumas Colombia SAS\",\"nit\":\"900123456-1\",\"direccion\":\"Bogotá D.C.\",\"descripcion_empresa\":\"Fabricación y comercialización de espumas para colchones\",\"contacto_nombre\":\"Carlos\",\"contacto_apellido\":\"Ramirez\",\"telefono\":\"3101111111\",\"email\":\"ventas@espumascolombia.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:19:32'),
(37, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:19:50'),
(38, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:21:09'),
(39, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:24:09'),
(40, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:24:11'),
(41, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:24:13'),
(42, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '[]', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:27:55'),
(43, 'proveedores', 'editar', 2, 'Se actualizó el proveedor \'Textiles Andinos SAS\'', '{\"id_proveedor\":2,\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"imagen\":null}', '{\"nombre_empresa\":\"Textiles Andinos SAS\",\"nit\":\"800234567-2\",\"contacto_nombre\":\"Laura\",\"contacto_apellido\":\"Gomez\",\"telefono\":\"3102222222\",\"email\":\"contacto@textilesandinos.com\",\"direccion\":\"Medellín, Antioquia\",\"descripcion_empresa\":\"Producción de textiles para la industria colchonera\"}', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:43:47'),
(44, 'proveedores', 'eliminar', 10, 'Se eliminó el proveedor \'Textiles andino\'', '{\"id_proveedor\":10,\"nombre_empresa\":\"Textiles andino\",\"nit\":\"123456789123\",\"direccion\":\"Cra 43 No 12-23\",\"descripcion_empresa\":\"Empresa de textiles\",\"contacto_nombre\":\"Nicolay\",\"contacto_apellido\":\"Polas\",\"telefono\":\"3124567898\",\"email\":\"nicorr@gmail.com\",\"imagen\":\"proveedor_6a7cb248cbea8.jpg\"}', NULL, NULL, 'Santiago Avellaneda', '::1', '2026-08-12 14:43:52'),
(45, 'materia_prima', 'editar', 4, 'Se actualizó la materia prima \'Fieltro aislante\'', NULL, '{\"nombre_material\":\"Fieltro aislante\",\"stock_actual\":\"1\",\"stock_minimo\":\"15.00\",\"id_unidad\":\"2\",\"id_proveedor\":\"1\"}', NULL, 'Santiago Avellaneda', '::1', '2026-08-12 16:15:54'),
(46, 'materia_prima', 'editar', 1, 'Se actualizó la materia prima \'Espuma de poliuretano\'', '{\"id_material\":1,\"nombre_material\":\"Espuma de poliuretano\",\"stock_actual\":\"50.00\",\"stock_minimo\":\"0.00\",\"id_unidad\":1,\"id_proveedor\":1,\"notificar_email\":0,\"correo_notificacion\":null,\"alerta_enviada\":0}', '{\"nombre_material\":\"Espuma de poliuretano\",\"stock_actual\":\"50.00\",\"stock_minimo\":\"110.00\",\"id_unidad\":\"1\",\"id_proveedor\":\"1\"}', NULL, 'Juan Montaño', '::1', '2026-08-19 23:06:44'),
(47, 'materia_prima', 'editar', 1, 'Se actualizó la materia prima \'Espuma de poliuretano\'', '{\"id_material\":1,\"nombre_material\":\"Espuma de poliuretano\",\"stock_actual\":\"50.00\",\"stock_minimo\":\"110.00\",\"id_unidad\":1,\"id_proveedor\":1,\"notificar_email\":0,\"correo_notificacion\":null,\"alerta_enviada\":1}', '{\"nombre_material\":\"Espuma de poliuretano\",\"stock_actual\":\"50.00\",\"stock_minimo\":\"110.00\",\"id_unidad\":\"1\",\"id_proveedor\":\"1\"}', NULL, 'Juan Montaño', '::1', '2026-08-19 23:07:08'),
(48, 'proveedores', 'editar', 1, 'Se actualizó el proveedor \'Espumas Colombia SAS\'', '{\"id_proveedor\":1,\"nombre_empresa\":\"Espumas Colombia SAS\",\"nit\":\"900123456-1\",\"direccion\":\"Bogotá D.C.\",\"descripcion_empresa\":\"Fabricación y comercialización de espumas para colchones\",\"contacto_nombre\":\"Carlos\",\"contacto_apellido\":\"Ramirez\",\"telefono\":\"3101111111\",\"email\":\"ventas@espumascolombia.com\",\"imagen\":null}', '{\"nombre_empresa\":\"Espumas Colombia SAS\",\"nit\":\"900123456-1\",\"contacto_nombre\":\"Carlos\",\"contacto_apellido\":\"Ramirez\",\"telefono\":\"3101111111\",\"email\":\"ventas@espumascolombia.com\",\"direccion\":\"Bogotá D.C.\",\"descripcion_empresa\":\"Fabricación y comercialización de espumas para colchones\"}', NULL, 'Juan Montaño', '::1', '2026-08-20 10:24:22'),
(49, 'proveedores', 'editar', 1, 'Se actualizó el proveedor \'Espumas Colombia SAS\'', '{\"id_proveedor\":1,\"nombre_empresa\":\"Espumas Colombia SAS\",\"nit\":\"900123456-1\",\"direccion\":\"Bogotá D.C.\",\"descripcion_empresa\":\"Fabricación y comercialización de espumas para colchones\",\"contacto_nombre\":\"Carlos\",\"contacto_apellido\":\"Ramirez\",\"telefono\":\"3101111111\",\"email\":\"ventas@espumascolombia.com\",\"imagen\":\"proveedor_1_1787239462.jpeg\"}', '{\"nombre_empresa\":\"Espumas Colombia SAS\",\"nit\":\"900123456-1\",\"contacto_nombre\":\"Carlos\",\"contacto_apellido\":\"Ramirez\",\"telefono\":\"3101111111\",\"email\":\"ventas@espumascolombia.com\",\"direccion\":\"Bogotá D.C.\",\"descripcion_empresa\":\"Fabricación y comercialización de espumas para colchones\"}', NULL, 'Juan Montaño', '::1', '2026-08-20 10:55:31'),
(50, 'proveedores', 'editar', 1, 'Se actualizó el proveedor \'Espumas Colombia SAS\'', '{\"id_proveedor\":1,\"nombre_empresa\":\"Espumas Colombia SAS\",\"nit\":\"900123456-1\",\"direccion\":\"Bogotá D.C.\",\"descripcion_empresa\":\"Fabricación y comercialización de espumas para colchones\",\"contacto_nombre\":\"Carlos\",\"contacto_apellido\":\"Ramirez\",\"telefono\":\"3101111111\",\"email\":\"ventas@espumascolombia.com\",\"imagen\":\"proveedor_1_1787241331.jpg\"}', '{\"nombre_empresa\":\"Espumas Colombia SAS\",\"nit\":\"900123456-1\",\"contacto_nombre\":\"Carlos\",\"contacto_apellido\":\"Ramirez\",\"telefono\":\"3101111111\",\"email\":\"ventas@espumascolombia.com\",\"direccion\":\"Bogotá D.C.\",\"descripcion_empresa\":\"Fabricación y comercialización de espumas para colchones\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 13:20:11'),
(51, 'proveedores', 'eliminar', 5, 'Se eliminó el proveedor \'Espumas y Colchones del Norte S.A.S.\'', '{\"id_proveedor\":5,\"nombre_empresa\":\"Espumas y Colchones del Norte S.A.S.\",\"nit\":\"901456789-3\",\"direccion\":\"Carrera 15 # 45-20, Bogotá, Colombia\",\"descripcion_empresa\":\"Proveedor especializado en espuma de poliuretano, telas para colchonería, resortes y materias primas para la fabricación de colchones y muebles.\",\"contacto_nombre\":\"Carlos\",\"contacto_apellido\":\"Ramírez\",\"telefono\":\"3204567890\",\"email\":\"compras@espumasnorte.com\",\"imagen\":null}', NULL, NULL, 'Jose Montaño', '::1', '2026-08-20 13:20:31'),
(52, 'proveedores', 'eliminar', 7, 'Se eliminó el proveedor \'Res\'', '{\"id_proveedor\":7,\"nombre_empresa\":\"Res\",\"nit\":\"455492131245\",\"direccion\":\"Cl. 14 #107-54\",\"descripcion_empresa\":\"Resortes Especializados\",\"contacto_nombre\":\"David\",\"contacto_apellido\":\"Pineda\",\"telefono\":\"3116364875\",\"email\":\"jafetgatitos06@gmail.com\",\"imagen\":null}', NULL, NULL, 'Jose Montaño', '::1', '2026-08-20 13:20:38'),
(53, 'proveedores', 'editar', 4, 'Se actualizó el proveedor \'Insumos beatex SAS\'', '{\"id_proveedor\":4,\"nombre_empresa\":\"Insumos Industriales SAS\",\"nit\":\"900456789-4\",\"direccion\":\"Barranquilla, Atlántico\",\"descripcion_empresa\":\"Distribución de insumos industriales\",\"contacto_nombre\":\"Paula\",\"contacto_apellido\":\"Torres\",\"telefono\":\"3104444444\",\"email\":\"compras@insumosindustriales.com\",\"imagen\":null}', '{\"nombre_empresa\":\"Insumos beatex SAS\",\"nit\":\"900456789-4\",\"contacto_nombre\":\"Paula\",\"contacto_apellido\":\"Torres\",\"telefono\":\"3104444444\",\"email\":\"compras@insumosindustriales.com\",\"direccion\":\"Barranquilla, Atlántico\",\"descripcion_empresa\":\"Distribución de insumos industriales\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 13:21:52'),
(54, 'proveedores', 'editar', 8, 'Se actualizó el proveedor \'Resortes Inalres S.A.S\'', '{\"id_proveedor\":8,\"nombre_empresa\":\"Resortes Especiales S.A.S\",\"nit\":\"83947593487\",\"direccion\":\"Cl 14 107-54\",\"descripcion_empresa\":\"Empresa de resortes reforzados\",\"contacto_nombre\":\"Jafet\",\"contacto_apellido\":\"Pineda\",\"telefono\":\"3116364875\",\"email\":\"jafetdavidpi@gmail.com\",\"imagen\":null}', '{\"nombre_empresa\":\"Resortes Inalres S.A.S\",\"nit\":\"83947593487\",\"contacto_nombre\":\"Jafet\",\"contacto_apellido\":\"Pineda\",\"telefono\":\"3116364875\",\"email\":\"jafetdavidpi@gmail.com\",\"direccion\":\"Cl 14 107-54\",\"descripcion_empresa\":\"Empresa de resortes reforzados\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 13:23:38'),
(55, 'proveedores', 'editar', 3, 'Se actualizó el proveedor \'Grupo Espumados S.A.S\'', '{\"id_proveedor\":3,\"nombre_empresa\":\"Resortes Nacionales SAS\",\"nit\":\"900345678-3\",\"direccion\":\"Cali, Valle del Cauca\",\"descripcion_empresa\":\"Fabricación de resortes para colchones\",\"contacto_nombre\":\"Andres\",\"contacto_apellido\":\"Martinez\",\"telefono\":\"3103333333\",\"email\":\"ventas@resortesnacionales.com\",\"imagen\":null}', '{\"nombre_empresa\":\"Grupo Espumados S.A.S\",\"nit\":\"900345678-3\",\"contacto_nombre\":\"Andres\",\"contacto_apellido\":\"Martinez\",\"telefono\":\"3103333333\",\"email\":\"ventas@resortesnacionales.com\",\"direccion\":\"Cali, Valle del Cauca\",\"descripcion_empresa\":\"Fabricación de resortes para colchones\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 13:24:16'),
(56, 'proveedores', 'crear', NULL, 'Se registró el proveedor \'Espumlandia S.A.S\'', NULL, '{\"nombre_empresa\":\"Espumlandia S.A.S\",\"contacto_nombre\":\"David\",\"contacto_apellido\":\"Pineda\",\"telefono\":\"3154709434\",\"email\":\"juanjosemon19@gmail.com\",\"nit\":\"901078545-6\",\"direccion\":\"CRA 113a #78-43 Bogot\",\"imagen\":\"proveedor_6a87469b1a176.png\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 13:25:31'),
(57, 'materia_prima', 'editar', 1, 'Se actualizó la materia prima \'Espuma de poliuretano\'', '{\"id_material\":1,\"nombre_material\":\"Espuma de poliuretano\",\"stock_actual\":\"50.00\",\"stock_minimo\":\"5000.00\",\"id_unidad\":1,\"id_proveedor\":1,\"notificar_email\":1,\"correo_notificacion\":\"juanjosemon19@gmail.com\",\"alerta_enviada\":1}', '{\"nombre_material\":\"Espuma de poliuretano\",\"stock_actual\":\"500\",\"stock_minimo\":\"200\",\"id_unidad\":\"1\",\"id_proveedor\":\"1\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 13:32:40'),
(58, 'materia_prima', 'editar', 4, 'Se actualizó la materia prima \'Fieltro aislante\'', '{\"id_material\":4,\"nombre_material\":\"Fieltro aislante\",\"stock_actual\":\"1.00\",\"stock_minimo\":\"15.00\",\"id_unidad\":2,\"id_proveedor\":1,\"notificar_email\":1,\"correo_notificacion\":\"nicolaspolo096@gmail.com\",\"alerta_enviada\":0}', '{\"nombre_material\":\"Fieltro aislante\",\"stock_actual\":\"25\",\"stock_minimo\":\"15.00\",\"id_unidad\":\"2\",\"id_proveedor\":\"1\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 13:32:49'),
(59, 'proveedores', 'editar', 11, 'Se actualizó el proveedor \'Espumlandia S.A.S\'', '{\"id_proveedor\":11,\"nombre_empresa\":\"Espumlandia S.A.S\",\"nit\":\"901078545-6\",\"direccion\":\"CRA 113a #78-43 Bogot\",\"descripcion_empresa\":\"Espumas Especiales Tamaño King\",\"contacto_nombre\":\"David\",\"contacto_apellido\":\"Pineda\",\"telefono\":\"3154709434\",\"email\":\"juanjosemon19@gmail.com\",\"imagen\":\"proveedor_6a87469b1a176.png\"}', '{\"nombre_empresa\":\"Espumlandia S.A.S\",\"nit\":\"901078545-6\",\"contacto_nombre\":\"David\",\"contacto_apellido\":\"Pineda\",\"telefono\":\"3154709434\",\"email\":\"juanjosemon19@gmail.com\",\"direccion\":\"CRA 113a #78-43 Bogot\",\"descripcion_empresa\":\"Espumas Especiales Tamaño King\"}', NULL, 'Juan Montaño', '::1', '2026-08-20 14:01:43'),
(60, 'proveedores', 'eliminar', 11, 'Se eliminó el proveedor \'Espumlandia S.A.S\'', '{\"id_proveedor\":11,\"nombre_empresa\":\"Espumlandia S.A.S\",\"nit\":\"901078545-6\",\"direccion\":\"CRA 113a #78-43 Bogot\",\"descripcion_empresa\":\"Espumas Especiales Tamaño King\",\"contacto_nombre\":\"David\",\"contacto_apellido\":\"Pineda\",\"telefono\":\"3154709434\",\"email\":\"juanjosemon19@gmail.com\",\"imagen\":\"proveedor_6a87469b1a176.png\"}', NULL, NULL, 'Juan Montaño', '::1', '2026-08-20 14:01:46'),
(61, 'materia_prima', 'crear', 14, 'Se registró la materia prima \'Resortes Bonnell\' con stock inicial de 23', NULL, '{\"nombre_material\":\"Resortes Bonnell\",\"stock_actual\":\"23\",\"stock_minimo\":0,\"id_unidad\":\"3\",\"id_proveedor\":\"8\"}', NULL, 'Juan Montaño', '::1', '2026-08-20 14:03:32'),
(62, 'producto_terminado', 'crear', 4, 'Se agregó el producto terminado \'Colchon doble\' con stock 23', NULL, '{\"nombre_producto\":\"Colchon doble\",\"stock_actual\":\"23\"}', NULL, 'Juan Montaño', '::1', '2026-08-20 14:04:39'),
(63, 'producto_terminado', 'eliminar', 4, 'Se eliminó el producto terminado \'Colchon doble\'', NULL, NULL, NULL, 'Juan Montaño', '::1', '2026-08-20 14:04:48'),
(64, 'produccion', 'salida', 1, 'Salida de 54 Kilogramos de \'Espuma de poliuretano\' para fabricar 3 unidades de \'Semidoble\'', '{\"stock_actual\":500}', '{\"stock_actual\":446}', NULL, 'Juan Montaño', '::1', '2026-08-20 14:05:36'),
(65, 'produccion', 'salida', 2, 'Salida de 18 Metros de \'Tela Jacquard\' para fabricar 3 unidades de \'Semidoble\'', '{\"stock_actual\":300}', '{\"stock_actual\":282}', NULL, 'Juan Montaño', '::1', '2026-08-20 14:05:36'),
(66, 'produccion', 'salida', 5, 'Salida de 3.3 Litros de \'Pegante industrial\' para fabricar 3 unidades de \'Semidoble\'', '{\"stock_actual\":100}', '{\"stock_actual\":96.7}', NULL, 'Juan Montaño', '::1', '2026-08-20 14:05:36'),
(67, 'produccion', 'salida', 1, 'Salida de 54 Kilogramos de \'Espuma de poliuretano\' para fabricar 3 unidades de \'Semidoble\'', '{\"stock_actual\":500}', '{\"stock_actual\":446}', NULL, 'Juan Montaño', '::1', '2026-08-20 14:05:36'),
(68, 'produccion', 'salida', 2, 'Salida de 18 Metros de \'Tela Jacquard\' para fabricar 3 unidades de \'Semidoble\'', '{\"stock_actual\":300}', '{\"stock_actual\":282}', NULL, 'Juan Montaño', '::1', '2026-08-20 14:05:36'),
(69, 'produccion', 'salida', 3, 'Salida de 420 Unidades de \'Resortes Bonnell\' para fabricar 3 unidades de \'Semidoble\'', '{\"stock_actual\":1200}', '{\"stock_actual\":780}', NULL, 'Juan Montaño', '::1', '2026-08-20 14:05:36'),
(70, 'produccion', 'salida', 4, 'Salida de 10.5 Metros de \'Fieltro aislante\' para fabricar 3 unidades de \'Semidoble\'', '{\"stock_actual\":25}', '{\"stock_actual\":14.5}', NULL, 'Juan Montaño', '::1', '2026-08-20 14:05:36'),
(71, 'produccion', 'salida', 5, 'Salida de 3.6 Litros de \'Pegante industrial\' para fabricar 3 unidades de \'Semidoble\'', '{\"stock_actual\":100}', '{\"stock_actual\":96.4}', NULL, 'Juan Montaño', '::1', '2026-08-20 14:05:36'),
(72, 'produccion', 'salida', 6, 'Salida de 1.2 Unidades de \'Hilo de costura\' para fabricar 3 unidades de \'Semidoble\'', '{\"stock_actual\":80}', '{\"stock_actual\":78.8}', NULL, 'Juan Montaño', '::1', '2026-08-20 14:05:36'),
(73, 'produccion', 'salida', 8, 'Salida de 9 Metros de \'Tela antideslizante\' para fabricar 3 unidades de \'Semidoble\'', '{\"stock_actual\":200}', '{\"stock_actual\":191}', NULL, 'Juan Montaño', '::1', '2026-08-20 14:05:36'),
(74, 'produccion', 'salida', 9, 'Salida de 27 Metros de \'Borde perimetral\' para fabricar 3 unidades de \'Semidoble\'', '{\"stock_actual\":180}', '{\"stock_actual\":153}', NULL, 'Juan Montaño', '::1', '2026-08-20 14:05:36'),
(75, 'produccion', 'salida', 10, 'Salida de 3 Metros de \'Empaque plastico\' para fabricar 3 unidades de \'Semidoble\'', '{\"stock_actual\":400}', '{\"stock_actual\":397}', NULL, 'Juan Montaño', '::1', '2026-08-20 14:05:36'),
(76, 'produccion', 'entrada', 8, 'Se fabricaron 3 unidades de \'Semidoble\'', NULL, '{\"unidades_fabricadas\":3}', NULL, 'Juan Montaño', '::1', '2026-08-20 14:05:36'),
(77, 'pedidos_proveedor', 'crear', 1, 'Se solicitó un pedido de \'Espuma de poliuretano\' (cantidad: 100) al proveedor \'Espumas Colombia SAS\'', NULL, '{\"id_proveedor\":\"1\",\"id_material\":\"1\",\"cantidad_pedida\":\"100\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 22:21:49'),
(78, 'proveedores', 'editar', 1, 'Se actualizó el proveedor \'Espumas Colombia SAS\'', '{\"id_proveedor\":1,\"nombre_empresa\":\"Espumas Colombia SAS\",\"nit\":\"900123456-1\",\"direccion\":\"Bogotá D.C.\",\"descripcion_empresa\":\"Fabricación y comercialización de espumas para colchones\",\"contacto_nombre\":\"Carlos\",\"contacto_apellido\":\"Ramirez\",\"telefono\":\"3101111111\",\"email\":\"ventas@espumascolombia.com\",\"imagen\":\"proveedor_1_1787250011.jpeg\"}', '{\"nombre_empresa\":\"Espumas Colombia SAS\",\"nit\":\"900123456-1\",\"contacto_nombre\":\"Carlos\",\"contacto_apellido\":\"Ramirez\",\"telefono\":\"3101111111\",\"email\":\"juanjosemon19@gmail.com\",\"direccion\":\"Bogotá D.C.\",\"descripcion_empresa\":\"Fabricación y comercialización de espumas para colchones\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 22:22:21'),
(79, 'pedidos_proveedor', 'crear', 2, 'Se solicitó un pedido de \'Espuma de poliuretano\' (cantidad: 1000) al proveedor \'Espumas Colombia SAS\'', NULL, '{\"id_proveedor\":\"1\",\"id_material\":\"1\",\"cantidad_pedida\":\"1000\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 22:22:36'),
(80, 'proveedores', 'editar', 1, 'Se actualizó el proveedor \'Espumas Colombia SAS\'', '{\"id_proveedor\":1,\"nombre_empresa\":\"Espumas Colombia SAS\",\"nit\":\"900123456-1\",\"direccion\":\"Bogotá D.C.\",\"descripcion_empresa\":\"Fabricación y comercialización de espumas para colchones\",\"contacto_nombre\":\"Carlos\",\"contacto_apellido\":\"Ramirez\",\"telefono\":\"3101111111\",\"email\":\"juanjosemon19@gmail.com\",\"imagen\":\"proveedor_1_1787250011.jpeg\"}', '{\"nombre_empresa\":\"Espumas Colombia SAS\",\"nit\":\"900123456-1\",\"contacto_nombre\":\"Carlos\",\"contacto_apellido\":\"Ramirez\",\"telefono\":\"3101111111\",\"email\":\"majog4892@gmail.com\",\"direccion\":\"Bogotá D.C.\",\"descripcion_empresa\":\"Fabricación y comercialización de espumas para colchones\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 22:27:44'),
(81, 'pedidos_proveedor', 'crear', 3, 'Se solicitó un pedido de \'Fieltro aislante\' (cantidad: 12) al proveedor \'Espumas Colombia SAS\'', NULL, '{\"id_proveedor\":\"1\",\"id_material\":\"4\",\"cantidad_pedida\":\"12\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 22:27:57'),
(82, 'proveedores', 'deshabilitar', 1, 'Se deshabilitó el proveedor \'Espumas Colombia SAS\'', '{\"estado\":\"activo\"}', '{\"estado\":\"inactivo\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 22:41:37'),
(83, 'proveedores', 'habilitar', 1, 'Se habilitó el proveedor \'Espumas Colombia SAS\'', '{\"estado\":\"inactivo\"}', '{\"estado\":\"activo\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 22:41:51'),
(84, 'proveedores', 'deshabilitar', 1, 'Se deshabilitó el proveedor \'Espumas Colombia SAS\'', '{\"estado\":\"activo\"}', '{\"estado\":\"inactivo\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 22:43:18'),
(85, 'proveedores', 'deshabilitar', 3, 'Se deshabilitó el proveedor \'Grupo Espumados S.A.S\'', '{\"estado\":\"activo\"}', '{\"estado\":\"inactivo\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 22:49:29'),
(86, 'proveedores', 'habilitar', 1, 'Se habilitó el proveedor \'Espumas Colombia SAS\'', '{\"estado\":\"inactivo\"}', '{\"estado\":\"activo\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 22:49:35'),
(87, 'proveedores', 'habilitar', 3, 'Se habilitó el proveedor \'Grupo Espumados S.A.S\'', '{\"estado\":\"inactivo\"}', '{\"estado\":\"activo\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 22:49:37'),
(88, 'proveedores', 'deshabilitar', 1, 'Se deshabilitó el proveedor \'Espumas Colombia SAS\'', '{\"estado\":\"activo\"}', '{\"estado\":\"inactivo\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 22:57:24'),
(89, 'proveedores', 'deshabilitar', 3, 'Se deshabilitó el proveedor \'Grupo Espumados S.A.S\'', '{\"estado\":\"activo\"}', '{\"estado\":\"inactivo\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 22:57:35'),
(90, 'proveedores', 'deshabilitar', 4, 'Se deshabilitó el proveedor \'Insumos beatex SAS\'', '{\"estado\":\"activo\"}', '{\"estado\":\"inactivo\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 22:59:18'),
(91, 'proveedores', 'habilitar', 4, 'Se habilitó el proveedor \'Insumos beatex SAS\'', '{\"estado\":\"inactivo\"}', '{\"estado\":\"activo\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 22:59:44'),
(92, 'proveedores', 'deshabilitar', 4, 'Se deshabilitó el proveedor \'Insumos beatex SAS\'', '{\"estado\":\"activo\"}', '{\"estado\":\"inactivo\"}', NULL, 'Jose Montaño', '::1', '2026-08-20 22:59:48'),
(93, 'proveedores', 'habilitar', 1, 'Se habilitó el proveedor \'Espumas Colombia SAS\'', '{\"estado\":\"inactivo\"}', '{\"estado\":\"activo\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 09:49:25'),
(94, 'proveedores', 'editar', 1, 'Se actualizó el proveedor \'Espumas Colombia SAS\'', '{\"id_proveedor\":1,\"nombre_empresa\":\"Espumas Colombia SAS\",\"nit\":\"900123456-1\",\"direccion\":\"Bogotá D.C.\",\"descripcion_empresa\":\"Fabricación y comercialización de espumas para colchones\",\"contacto_nombre\":\"Carlos\",\"contacto_apellido\":\"Ramirez\",\"telefono\":\"3101111111\",\"email\":\"majog4892@gmail.com\",\"imagen\":\"proveedor_1_1787250011.jpeg\",\"estado\":\"activo\",\"activo\":1}', '{\"nombre_empresa\":\"Espumas Colombia SAS\",\"nit\":\"900123456-1\",\"contacto_nombre\":\"Carlos\",\"contacto_apellido\":\"Ramirez\",\"telefono\":\"3101111111\",\"email\":\"nayivebarragan09@gmail.com\",\"direccion\":\"Bogotá D.C.\",\"descripcion_empresa\":\"Fabricación y comercialización de espumas para colchones\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 17:48:54'),
(95, 'pedidos_proveedor', 'crear', 4, 'Se solicitó un pedido de \'Espuma de poliuretano\' (cantidad: 15) al proveedor \'Espumas Colombia SAS\'', NULL, '{\"id_proveedor\":\"1\",\"id_material\":\"1\",\"cantidad_pedida\":\"15\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 17:49:06'),
(96, 'produccion', 'salida', 1, 'Salida de 46 Kilogramos de \'Espuma de poliuretano\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":392}', '{\"stock_actual\":346}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:06:35'),
(97, 'produccion', 'salida', 2, 'Salida de 15 Metros de \'Tela Jacquard\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":264}', '{\"stock_actual\":249}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:06:36'),
(98, 'produccion', 'salida', 3, 'Salida de 340 Unidades de \'Resortes Bonnell\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":780}', '{\"stock_actual\":440}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:06:36'),
(99, 'produccion', 'salida', 4, 'Salida de 9 Metros de \'Fieltro aislante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":14.5}', '{\"stock_actual\":5.5}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:06:36'),
(100, 'produccion', 'salida', 5, 'Salida de 3.2 Litros de \'Pegante industrial\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":93.1}', '{\"stock_actual\":89.89999999999999}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:06:36'),
(101, 'produccion', 'salida', 6, 'Salida de 1.1 Unidades de \'Hilo de costura\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":78.8}', '{\"stock_actual\":77.7}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:06:36'),
(102, 'produccion', 'salida', 7, 'Salida de 4 Kilogramos de \'Espuma viscoelastica\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":60}', '{\"stock_actual\":56}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:06:36'),
(103, 'produccion', 'salida', 8, 'Salida de 7.6 Metros de \'Tela antideslizante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":191}', '{\"stock_actual\":183.4}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:06:36'),
(104, 'produccion', 'salida', 9, 'Salida de 21 Metros de \'Borde perimetral\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":153}', '{\"stock_actual\":132}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:06:36'),
(105, 'produccion', 'salida', 10, 'Salida de 2 Metros de \'Empaque plastico\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":397}', '{\"stock_actual\":395}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:06:36'),
(106, 'produccion', 'entrada', 3, 'Se fabricaron 2 unidades de \'Confort Total\'', NULL, '{\"unidades_fabricadas\":2}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:06:36'),
(107, 'produccion', 'salida', 1, 'Salida de 26 Kilogramos de \'Espuma de poliuretano\' para fabricar 1 unidades de \'Descanso Real\'', '{\"stock_actual\":346}', '{\"stock_actual\":320}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:10:20'),
(108, 'produccion', 'salida', 2, 'Salida de 8.5 Metros de \'Tela Jacquard\' para fabricar 1 unidades de \'Descanso Real\'', '{\"stock_actual\":249}', '{\"stock_actual\":240.5}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:10:20'),
(109, 'produccion', 'salida', 3, 'Salida de 190 Unidades de \'Resortes Bonnell\' para fabricar 1 unidades de \'Descanso Real\'', '{\"stock_actual\":440}', '{\"stock_actual\":250}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:10:20'),
(110, 'produccion', 'salida', 4, 'Salida de 5.2 Metros de \'Fieltro aislante\' para fabricar 1 unidades de \'Descanso Real\'', '{\"stock_actual\":5.5}', '{\"stock_actual\":0.2999999999999998}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:10:20'),
(111, 'produccion', 'salida', 5, 'Salida de 1.8 Litros de \'Pegante industrial\' para fabricar 1 unidades de \'Descanso Real\'', '{\"stock_actual\":89.9}', '{\"stock_actual\":88.10000000000001}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:10:20'),
(112, 'produccion', 'salida', 6, 'Salida de 0.65 Unidades de \'Hilo de costura\' para fabricar 1 unidades de \'Descanso Real\'', '{\"stock_actual\":77.7}', '{\"stock_actual\":77.05}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:10:20'),
(113, 'produccion', 'salida', 7, 'Salida de 4 Kilogramos de \'Espuma viscoelastica\' para fabricar 1 unidades de \'Descanso Real\'', '{\"stock_actual\":56}', '{\"stock_actual\":52}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:10:20'),
(114, 'produccion', 'salida', 8, 'Salida de 4.2 Metros de \'Tela antideslizante\' para fabricar 1 unidades de \'Descanso Real\'', '{\"stock_actual\":183.4}', '{\"stock_actual\":179.20000000000002}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:10:20'),
(115, 'produccion', 'salida', 9, 'Salida de 11.2 Metros de \'Borde perimetral\' para fabricar 1 unidades de \'Descanso Real\'', '{\"stock_actual\":132}', '{\"stock_actual\":120.8}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:10:20'),
(116, 'produccion', 'salida', 10, 'Salida de 1 Metros de \'Empaque plastico\' para fabricar 1 unidades de \'Descanso Real\'', '{\"stock_actual\":395}', '{\"stock_actual\":394}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:10:20'),
(117, 'produccion', 'entrada', 2, 'Se fabricaron 1 unidades de \'Descanso Real\'', NULL, '{\"unidades_fabricadas\":1}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:10:20'),
(118, 'materia_prima', 'editar', 9, 'Se actualizó la materia prima \'Borde perimetral\'', '{\"id_material\":9,\"nombre_material\":\"Borde perimetral\",\"stock_actual\":\"120.80\",\"stock_minimo\":\"40.00\",\"id_unidad\":2,\"id_proveedor\":3,\"notificar_email\":0,\"correo_notificacion\":null,\"alerta_enviada\":0}', '{\"nombre_material\":\"Borde perimetral\",\"stock_actual\":\"5000\",\"stock_minimo\":\"40.00\",\"id_unidad\":\"2\",\"id_proveedor\":\"3\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:10:57'),
(119, 'materia_prima', 'editar', 10, 'Se actualizó la materia prima \'Empaque plastico\'', '{\"id_material\":10,\"nombre_material\":\"Empaque plastico\",\"stock_actual\":\"394.00\",\"stock_minimo\":\"80.00\",\"id_unidad\":2,\"id_proveedor\":4,\"notificar_email\":0,\"correo_notificacion\":null,\"alerta_enviada\":0}', '{\"nombre_material\":\"Empaque plastico\",\"stock_actual\":\"5000\",\"stock_minimo\":\"80.00\",\"id_unidad\":\"2\",\"id_proveedor\":\"4\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:11:02'),
(120, 'materia_prima', 'editar', 1, 'Se actualizó la materia prima \'Espuma de poliuretano\'', '{\"id_material\":1,\"nombre_material\":\"Espuma de poliuretano\",\"stock_actual\":\"320.00\",\"stock_minimo\":\"200.00\",\"id_unidad\":1,\"id_proveedor\":1,\"notificar_email\":1,\"correo_notificacion\":\"juanjosemon19@gmail.com\",\"alerta_enviada\":0}', '{\"nombre_material\":\"Espuma de poliuretano\",\"stock_actual\":\"5000\",\"stock_minimo\":\"200.00\",\"id_unidad\":\"1\",\"id_proveedor\":\"1\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:11:09'),
(121, 'materia_prima', 'editar', 7, 'Se actualizó la materia prima \'Espuma viscoelastica\'', '{\"id_material\":7,\"nombre_material\":\"Espuma viscoelastica\",\"stock_actual\":\"52.00\",\"stock_minimo\":\"70.00\",\"id_unidad\":1,\"id_proveedor\":1,\"notificar_email\":1,\"correo_notificacion\":\"juanjosemon19@gmail.com\",\"alerta_enviada\":1}', '{\"nombre_material\":\"Espuma viscoelastica\",\"stock_actual\":\"5000\",\"stock_minimo\":\"70.00\",\"id_unidad\":\"1\",\"id_proveedor\":\"1\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:11:14'),
(122, 'materia_prima', 'editar', 4, 'Se actualizó la materia prima \'Fieltro aislante\'', '{\"id_material\":4,\"nombre_material\":\"Fieltro aislante\",\"stock_actual\":\"0.30\",\"stock_minimo\":\"15.00\",\"id_unidad\":2,\"id_proveedor\":1,\"notificar_email\":1,\"correo_notificacion\":\"nicolaspolo096@gmail.com\",\"alerta_enviada\":0}', '{\"nombre_material\":\"Fieltro aislante\",\"stock_actual\":\"5000\",\"stock_minimo\":\"15.00\",\"id_unidad\":\"2\",\"id_proveedor\":\"1\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:11:20'),
(123, 'materia_prima', 'editar', 13, 'Se actualizó la materia prima \'Fieltro aislante\'', '{\"id_material\":13,\"nombre_material\":\"Fieltro aislante\",\"stock_actual\":\"20.00\",\"stock_minimo\":\"0.00\",\"id_unidad\":3,\"id_proveedor\":4,\"notificar_email\":0,\"correo_notificacion\":null,\"alerta_enviada\":0}', '{\"nombre_material\":\"Fieltro aislante\",\"stock_actual\":\"5000\",\"stock_minimo\":\"0.00\",\"id_unidad\":\"3\",\"id_proveedor\":\"4\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:11:25'),
(124, 'materia_prima', 'editar', 6, 'Se actualizó la materia prima \'Hilo de costura\'', '{\"id_material\":6,\"nombre_material\":\"Hilo de costura\",\"stock_actual\":\"77.05\",\"stock_minimo\":\"15.00\",\"id_unidad\":3,\"id_proveedor\":2,\"notificar_email\":0,\"correo_notificacion\":null,\"alerta_enviada\":0}', '{\"nombre_material\":\"Hilo de costura\",\"stock_actual\":\"77.05\",\"stock_minimo\":\"5000\",\"id_unidad\":\"3\",\"id_proveedor\":\"2\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:11:32'),
(125, 'materia_prima', 'editar', 6, 'Se actualizó la materia prima \'Hilo de costura\'', '{\"id_material\":6,\"nombre_material\":\"Hilo de costura\",\"stock_actual\":\"77.05\",\"stock_minimo\":\"5000.00\",\"id_unidad\":3,\"id_proveedor\":2,\"notificar_email\":0,\"correo_notificacion\":null,\"alerta_enviada\":1}', '{\"nombre_material\":\"Hilo de costura\",\"stock_actual\":\"5000\",\"stock_minimo\":\"50.00\",\"id_unidad\":\"3\",\"id_proveedor\":\"2\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:11:47'),
(126, 'materia_prima', 'editar', 5, 'Se actualizó la materia prima \'Pegante industrial\'', '{\"id_material\":5,\"nombre_material\":\"Pegante industrial\",\"stock_actual\":\"88.10\",\"stock_minimo\":\"20.00\",\"id_unidad\":4,\"id_proveedor\":4,\"notificar_email\":0,\"correo_notificacion\":null,\"alerta_enviada\":0}', '{\"nombre_material\":\"Pegante industrial\",\"stock_actual\":\"5000\",\"stock_minimo\":\"20.00\",\"id_unidad\":\"4\",\"id_proveedor\":\"4\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:11:53'),
(127, 'materia_prima', 'editar', 3, 'Se actualizó la materia prima \'Resortes Bonnell\'', '{\"id_material\":3,\"nombre_material\":\"Resortes Bonnell\",\"stock_actual\":\"250.00\",\"stock_minimo\":\"300.00\",\"id_unidad\":3,\"id_proveedor\":3,\"notificar_email\":0,\"correo_notificacion\":null,\"alerta_enviada\":0}', '{\"nombre_material\":\"Resortes Bonnell\",\"stock_actual\":\"5000\",\"stock_minimo\":\"300.00\",\"id_unidad\":\"3\",\"id_proveedor\":\"3\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:12:01');
INSERT INTO `historial_movimientos` (`id`, `modulo`, `accion`, `id_registro`, `descripcion`, `datos_anteriores`, `datos_nuevos`, `usuario_id`, `usuario_nombre`, `ip`, `fecha_hora`) VALUES
(128, 'materia_prima', 'editar', 14, 'Se actualizó la materia prima \'Resortes Bonnell\'', '{\"id_material\":14,\"nombre_material\":\"Resortes Bonnell\",\"stock_actual\":\"23.00\",\"stock_minimo\":\"0.00\",\"id_unidad\":3,\"id_proveedor\":8,\"notificar_email\":0,\"correo_notificacion\":null,\"alerta_enviada\":0}', '{\"nombre_material\":\"Resortes Bonnell\",\"stock_actual\":\"5000\",\"stock_minimo\":\"0.00\",\"id_unidad\":\"3\",\"id_proveedor\":\"8\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:12:07'),
(129, 'materia_prima', 'editar', 11, 'Se actualizó la materia prima \'Tela \'', '{\"id_material\":11,\"nombre_material\":\"Tela \",\"stock_actual\":\"100.00\",\"stock_minimo\":\"30.00\",\"id_unidad\":2,\"id_proveedor\":2,\"notificar_email\":0,\"correo_notificacion\":null,\"alerta_enviada\":0}', '{\"nombre_material\":\"Tela \",\"stock_actual\":\"5000\",\"stock_minimo\":\"30.00\",\"id_unidad\":\"2\",\"id_proveedor\":\"2\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:12:16'),
(130, 'materia_prima', 'editar', 8, 'Se actualizó la materia prima \'Tela antideslizante\'', '{\"id_material\":8,\"nombre_material\":\"Tela antideslizante\",\"stock_actual\":\"179.20\",\"stock_minimo\":\"40.00\",\"id_unidad\":2,\"id_proveedor\":2,\"notificar_email\":0,\"correo_notificacion\":null,\"alerta_enviada\":0}', '{\"nombre_material\":\"Tela antideslizante\",\"stock_actual\":\"5000\",\"stock_minimo\":\"40.00\",\"id_unidad\":\"2\",\"id_proveedor\":\"2\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:12:22'),
(131, 'materia_prima', 'editar', 2, 'Se actualizó la materia prima \'Tela Jacquard\'', '{\"id_material\":2,\"nombre_material\":\"Tela Jacquard\",\"stock_actual\":\"240.50\",\"stock_minimo\":\"50.00\",\"id_unidad\":2,\"id_proveedor\":2,\"notificar_email\":0,\"correo_notificacion\":null,\"alerta_enviada\":0}', '{\"nombre_material\":\"Tela Jacquard\",\"stock_actual\":\"5000\",\"stock_minimo\":\"50.00\",\"id_unidad\":\"2\",\"id_proveedor\":\"2\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:12:29'),
(132, 'produccion', 'salida', 1, 'Salida de 60 Kilogramos de \'Espuma de poliuretano\' para fabricar 2 unidades de \'King\'', '{\"stock_actual\":5000}', '{\"stock_actual\":4940}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:13:12'),
(133, 'produccion', 'salida', 2, 'Salida de 19 Metros de \'Tela Jacquard\' para fabricar 2 unidades de \'King\'', '{\"stock_actual\":5000}', '{\"stock_actual\":4981}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:13:12'),
(134, 'produccion', 'salida', 5, 'Salida de 3.6 Litros de \'Pegante industrial\' para fabricar 2 unidades de \'King\'', '{\"stock_actual\":5000}', '{\"stock_actual\":4996.4}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:13:12'),
(135, 'produccion', 'salida', 1, 'Salida de 60 Kilogramos de \'Espuma de poliuretano\' para fabricar 2 unidades de \'King\'', '{\"stock_actual\":5000}', '{\"stock_actual\":4940}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:13:12'),
(136, 'produccion', 'salida', 2, 'Salida de 18 Metros de \'Tela Jacquard\' para fabricar 2 unidades de \'King\'', '{\"stock_actual\":5000}', '{\"stock_actual\":4982}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:13:12'),
(137, 'produccion', 'salida', 3, 'Salida de 420 Unidades de \'Resortes Bonnell\' para fabricar 2 unidades de \'King\'', '{\"stock_actual\":5000}', '{\"stock_actual\":4580}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:13:12'),
(138, 'produccion', 'salida', 4, 'Salida de 12 Metros de \'Fieltro aislante\' para fabricar 2 unidades de \'King\'', '{\"stock_actual\":5000}', '{\"stock_actual\":4988}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:13:12'),
(139, 'produccion', 'salida', 5, 'Salida de 4 Litros de \'Pegante industrial\' para fabricar 2 unidades de \'King\'', '{\"stock_actual\":5000}', '{\"stock_actual\":4996}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:13:12'),
(140, 'produccion', 'salida', 6, 'Salida de 1.4 Unidades de \'Hilo de costura\' para fabricar 2 unidades de \'King\'', '{\"stock_actual\":5000}', '{\"stock_actual\":4998.6}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:13:12'),
(141, 'produccion', 'salida', 7, 'Salida de 10 Kilogramos de \'Espuma viscoelastica\' para fabricar 2 unidades de \'King\'', '{\"stock_actual\":5000}', '{\"stock_actual\":4990}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:13:12'),
(142, 'produccion', 'salida', 8, 'Salida de 10 Metros de \'Tela antideslizante\' para fabricar 2 unidades de \'King\'', '{\"stock_actual\":5000}', '{\"stock_actual\":4990}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:13:12'),
(143, 'produccion', 'salida', 9, 'Salida de 24 Metros de \'Borde perimetral\' para fabricar 2 unidades de \'King\'', '{\"stock_actual\":5000}', '{\"stock_actual\":4976}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:13:12'),
(144, 'produccion', 'salida', 10, 'Salida de 2 Metros de \'Empaque plastico\' para fabricar 2 unidades de \'King\'', '{\"stock_actual\":5000}', '{\"stock_actual\":4998}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:13:12'),
(145, 'produccion', 'entrada', 11, 'Se fabricaron 2 unidades de \'King\'', NULL, '{\"unidades_fabricadas\":2}', NULL, 'Jose Montaño', '::1', '2026-08-25 20:13:12'),
(146, 'produccion', 'salida', 1, 'Salida de 23 Kilogramos de \'Espuma de poliuretano\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4880}', '{\"stock_actual\":4857}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:41:37'),
(147, 'produccion', 'salida', 2, 'Salida de 7.5 Metros de \'Tela Jacquard\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4963}', '{\"stock_actual\":4955.5}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:41:37'),
(148, 'produccion', 'salida', 3, 'Salida de 170 Unidades de \'Resortes Bonnell\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4580}', '{\"stock_actual\":4410}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:41:37'),
(149, 'produccion', 'salida', 4, 'Salida de 4.5 Metros de \'Fieltro aislante\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4988}', '{\"stock_actual\":4983.5}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:41:37'),
(150, 'produccion', 'salida', 5, 'Salida de 1.6 Litros de \'Pegante industrial\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4992.4}', '{\"stock_actual\":4990.799999999999}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:41:37'),
(151, 'produccion', 'salida', 6, 'Salida de 0.55 Unidades de \'Hilo de costura\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4998.6}', '{\"stock_actual\":4998.05}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:41:37'),
(152, 'produccion', 'salida', 7, 'Salida de 2 Kilogramos de \'Espuma viscoelastica\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4990}', '{\"stock_actual\":4988}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:41:37'),
(153, 'produccion', 'salida', 8, 'Salida de 3.8 Metros de \'Tela antideslizante\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4990}', '{\"stock_actual\":4986.2}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:41:37'),
(154, 'produccion', 'salida', 9, 'Salida de 10.5 Metros de \'Borde perimetral\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4976}', '{\"stock_actual\":4965.5}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:41:37'),
(155, 'produccion', 'salida', 10, 'Salida de 1 Metros de \'Empaque plastico\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4998}', '{\"stock_actual\":4997}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:41:37'),
(156, 'produccion', 'entrada', 3, 'Se fabricaron 1 unidades de \'Confort Total\'', NULL, '{\"unidades_fabricadas\":1}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:41:37'),
(157, 'produccion', 'salida', 1, 'Salida de 46 Kilogramos de \'Espuma de poliuretano\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4857}', '{\"stock_actual\":4811}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:55:13'),
(158, 'produccion', 'salida', 2, 'Salida de 15 Metros de \'Tela Jacquard\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4955.5}', '{\"stock_actual\":4940.5}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:55:13'),
(159, 'produccion', 'salida', 3, 'Salida de 340 Unidades de \'Resortes Bonnell\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4410}', '{\"stock_actual\":4070}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:55:13'),
(160, 'produccion', 'salida', 4, 'Salida de 9 Metros de \'Fieltro aislante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4983.5}', '{\"stock_actual\":4974.5}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:55:13'),
(161, 'produccion', 'salida', 5, 'Salida de 3.2 Litros de \'Pegante industrial\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4990.8}', '{\"stock_actual\":4987.6}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:55:13'),
(162, 'produccion', 'salida', 6, 'Salida de 1.1 Unidades de \'Hilo de costura\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4998.05}', '{\"stock_actual\":4996.95}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:55:13'),
(163, 'produccion', 'salida', 7, 'Salida de 4 Kilogramos de \'Espuma viscoelastica\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4988}', '{\"stock_actual\":4984}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:55:13'),
(164, 'produccion', 'salida', 8, 'Salida de 7.6 Metros de \'Tela antideslizante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4986.2}', '{\"stock_actual\":4978.599999999999}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:55:13'),
(165, 'produccion', 'salida', 9, 'Salida de 21 Metros de \'Borde perimetral\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4965.5}', '{\"stock_actual\":4944.5}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:55:13'),
(166, 'produccion', 'salida', 10, 'Salida de 2 Metros de \'Empaque plastico\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4997}', '{\"stock_actual\":4995}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:55:13'),
(167, 'produccion', 'entrada', 3, 'Se fabricaron 2 unidades de \'Confort Total\'', NULL, '{\"unidades_fabricadas\":2}', NULL, 'Jose Montaño', '::1', '2026-08-25 21:55:13'),
(168, 'produccion', 'salida', 1, 'Salida de 46 Kilogramos de \'Espuma de poliuretano\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4811}', '{\"stock_actual\":4765}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:00:03'),
(169, 'produccion', 'salida', 2, 'Salida de 15 Metros de \'Tela Jacquard\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4940.5}', '{\"stock_actual\":4925.5}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:00:03'),
(170, 'produccion', 'salida', 3, 'Salida de 340 Unidades de \'Resortes Bonnell\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4070}', '{\"stock_actual\":3730}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:00:03'),
(171, 'produccion', 'salida', 4, 'Salida de 9 Metros de \'Fieltro aislante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4974.5}', '{\"stock_actual\":4965.5}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:00:03'),
(172, 'produccion', 'salida', 5, 'Salida de 3.2 Litros de \'Pegante industrial\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4987.6}', '{\"stock_actual\":4984.400000000001}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:00:03'),
(173, 'produccion', 'salida', 6, 'Salida de 1.1 Unidades de \'Hilo de costura\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4996.95}', '{\"stock_actual\":4995.849999999999}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:00:03'),
(174, 'produccion', 'salida', 7, 'Salida de 4 Kilogramos de \'Espuma viscoelastica\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4984}', '{\"stock_actual\":4980}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:00:03'),
(175, 'produccion', 'salida', 8, 'Salida de 7.6 Metros de \'Tela antideslizante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4978.6}', '{\"stock_actual\":4971}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:00:03'),
(176, 'produccion', 'salida', 9, 'Salida de 21 Metros de \'Borde perimetral\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4944.5}', '{\"stock_actual\":4923.5}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:00:03'),
(177, 'produccion', 'salida', 10, 'Salida de 2 Metros de \'Empaque plastico\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4995}', '{\"stock_actual\":4993}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:00:03'),
(178, 'produccion', 'entrada', 3, 'Se fabricaron 2 unidades de \'Confort Total\'', NULL, '{\"unidades_fabricadas\":2}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:00:03'),
(179, 'materia_prima', 'salida', 1, 'Salida de 46 Kilogramos de \'Espuma de poliuretano\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4765}', '{\"stock_actual\":4719}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:02'),
(180, 'materia_prima', 'salida', 2, 'Salida de 15 Metros de \'Tela Jacquard\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4925.5}', '{\"stock_actual\":4910.5}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:02'),
(181, 'materia_prima', 'salida', 3, 'Salida de 340 Unidades de \'Resortes Bonnell\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":3730}', '{\"stock_actual\":3390}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:02'),
(182, 'materia_prima', 'salida', 4, 'Salida de 9 Metros de \'Fieltro aislante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4965.5}', '{\"stock_actual\":4956.5}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:02'),
(183, 'materia_prima', 'salida', 5, 'Salida de 3.2 Litros de \'Pegante industrial\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4984.4}', '{\"stock_actual\":4981.2}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:02'),
(184, 'materia_prima', 'salida', 6, 'Salida de 1.1 Unidades de \'Hilo de costura\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4995.85}', '{\"stock_actual\":4994.75}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:02'),
(185, 'materia_prima', 'salida', 7, 'Salida de 4 Kilogramos de \'Espuma viscoelastica\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4980}', '{\"stock_actual\":4976}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:02'),
(186, 'materia_prima', 'salida', 8, 'Salida de 7.6 Metros de \'Tela antideslizante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4971}', '{\"stock_actual\":4963.4}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:02'),
(187, 'materia_prima', 'salida', 9, 'Salida de 21 Metros de \'Borde perimetral\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4923.5}', '{\"stock_actual\":4902.5}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:02'),
(188, 'materia_prima', 'salida', 10, 'Salida de 2 Metros de \'Empaque plastico\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4993}', '{\"stock_actual\":4991}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:02'),
(189, 'producto_terminado', 'crear', 3, 'Se fabricaron 2 unidades de \'Confort Total\'', NULL, '{\"unidades_fabricadas\":2,\"nombre_producto\":\"Confort Total\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:02'),
(190, 'materia_prima', 'salida', 1, 'Salida de 46 Kilogramos de \'Espuma de poliuretano\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4719}', '{\"stock_actual\":4673}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:03'),
(191, 'materia_prima', 'salida', 2, 'Salida de 15 Metros de \'Tela Jacquard\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4910.5}', '{\"stock_actual\":4895.5}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:03'),
(192, 'materia_prima', 'salida', 3, 'Salida de 340 Unidades de \'Resortes Bonnell\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":3390}', '{\"stock_actual\":3050}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:03'),
(193, 'materia_prima', 'salida', 4, 'Salida de 9 Metros de \'Fieltro aislante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4956.5}', '{\"stock_actual\":4947.5}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:03'),
(194, 'materia_prima', 'salida', 5, 'Salida de 3.2 Litros de \'Pegante industrial\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4981.2}', '{\"stock_actual\":4978}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:03'),
(195, 'materia_prima', 'salida', 6, 'Salida de 1.1 Unidades de \'Hilo de costura\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4994.75}', '{\"stock_actual\":4993.65}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:03'),
(196, 'materia_prima', 'salida', 7, 'Salida de 4 Kilogramos de \'Espuma viscoelastica\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4976}', '{\"stock_actual\":4972}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:03'),
(197, 'materia_prima', 'salida', 8, 'Salida de 7.6 Metros de \'Tela antideslizante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4963.4}', '{\"stock_actual\":4955.799999999999}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:03'),
(198, 'materia_prima', 'salida', 9, 'Salida de 21 Metros de \'Borde perimetral\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4902.5}', '{\"stock_actual\":4881.5}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:03'),
(199, 'materia_prima', 'salida', 10, 'Salida de 2 Metros de \'Empaque plastico\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4991}', '{\"stock_actual\":4989}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:03'),
(200, 'producto_terminado', 'crear', 3, 'Se fabricaron 2 unidades de \'Confort Total\'', NULL, '{\"unidades_fabricadas\":2,\"nombre_producto\":\"Confort Total\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 22:06:03'),
(201, 'materia_prima', 'eliminar', 14, 'Se eliminó la materia prima \'Resortes Bonnell\'', '{\"id_material\":14,\"nombre_material\":\"Resortes Bonnell\",\"stock_actual\":\"5000.00\",\"stock_minimo\":\"0.00\",\"id_unidad\":3,\"id_proveedor\":8,\"notificar_email\":0,\"correo_notificacion\":null,\"alerta_enviada\":0}', NULL, NULL, 'Jose Montaño', '::1', '2026-08-25 22:39:08'),
(202, 'materia_prima', 'salida', 1, 'Salida de 160 Kilogramos de \'Espuma de poliuretano\' para fabricar 5 unidades de \'Premium Gold\'', '{\"stock_actual\":4673}', '{\"stock_actual\":4513}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:45:43'),
(203, 'materia_prima', 'salida', 2, 'Salida de 50 Metros de \'Tela Jacquard\' para fabricar 5 unidades de \'Premium Gold\'', '{\"stock_actual\":4895.5}', '{\"stock_actual\":4845.5}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:45:43'),
(204, 'materia_prima', 'salida', 3, 'Salida de 1150 Unidades de \'Resortes Bonnell\' para fabricar 5 unidades de \'Premium Gold\'', '{\"stock_actual\":3050}', '{\"stock_actual\":1900}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:45:43'),
(205, 'materia_prima', 'salida', 4, 'Salida de 32.5 Metros de \'Fieltro aislante\' para fabricar 5 unidades de \'Premium Gold\'', '{\"stock_actual\":4947.5}', '{\"stock_actual\":4915}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:45:43'),
(206, 'materia_prima', 'salida', 5, 'Salida de 11 Litros de \'Pegante industrial\' para fabricar 5 unidades de \'Premium Gold\'', '{\"stock_actual\":4978}', '{\"stock_actual\":4967}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:45:43'),
(207, 'materia_prima', 'salida', 6, 'Salida de 4 Unidades de \'Hilo de costura\' para fabricar 5 unidades de \'Premium Gold\'', '{\"stock_actual\":4993.65}', '{\"stock_actual\":4989.65}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:45:43'),
(208, 'materia_prima', 'salida', 7, 'Salida de 40 Kilogramos de \'Espuma viscoelastica\' para fabricar 5 unidades de \'Premium Gold\'', '{\"stock_actual\":4972}', '{\"stock_actual\":4932}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:45:43'),
(209, 'materia_prima', 'salida', 8, 'Salida de 27.5 Metros de \'Tela antideslizante\' para fabricar 5 unidades de \'Premium Gold\'', '{\"stock_actual\":4955.8}', '{\"stock_actual\":4928.3}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:45:43'),
(210, 'materia_prima', 'salida', 9, 'Salida de 62.5 Metros de \'Borde perimetral\' para fabricar 5 unidades de \'Premium Gold\'', '{\"stock_actual\":4881.5}', '{\"stock_actual\":4819}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:45:43'),
(211, 'materia_prima', 'salida', 10, 'Salida de 5 Metros de \'Empaque plastico\' para fabricar 5 unidades de \'Premium Gold\'', '{\"stock_actual\":4989}', '{\"stock_actual\":4984}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:45:43'),
(212, 'producto_terminado', 'crear', 4, 'Se fabricaron 5 unidades de \'Premium Gold\'', NULL, '{\"unidades_fabricadas\":5,\"nombre_producto\":\"Premium Gold\"}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:45:43'),
(213, 'materia_prima', 'salida', 1, 'Salida de 23 Kilogramos de \'Espuma de poliuretano\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4513}', '{\"stock_actual\":4490}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:47:45'),
(214, 'materia_prima', 'salida', 2, 'Salida de 7.5 Metros de \'Tela Jacquard\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4845.5}', '{\"stock_actual\":4838}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:47:45'),
(215, 'materia_prima', 'salida', 3, 'Salida de 170 Unidades de \'Resortes Bonnell\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":1900}', '{\"stock_actual\":1730}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:47:45'),
(216, 'materia_prima', 'salida', 4, 'Salida de 4.5 Metros de \'Fieltro aislante\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4915}', '{\"stock_actual\":4910.5}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:47:45'),
(217, 'materia_prima', 'salida', 5, 'Salida de 1.6 Litros de \'Pegante industrial\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4967}', '{\"stock_actual\":4965.4}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:47:45'),
(218, 'materia_prima', 'salida', 6, 'Salida de 0.55 Unidades de \'Hilo de costura\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4989.65}', '{\"stock_actual\":4989.099999999999}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:47:45'),
(219, 'materia_prima', 'salida', 7, 'Salida de 2 Kilogramos de \'Espuma viscoelastica\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4932}', '{\"stock_actual\":4930}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:47:45'),
(220, 'materia_prima', 'salida', 8, 'Salida de 3.8 Metros de \'Tela antideslizante\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4928.3}', '{\"stock_actual\":4924.5}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:47:45'),
(221, 'materia_prima', 'salida', 9, 'Salida de 10.5 Metros de \'Borde perimetral\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4819}', '{\"stock_actual\":4808.5}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:47:45'),
(222, 'materia_prima', 'salida', 10, 'Salida de 1 Metros de \'Empaque plastico\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4984}', '{\"stock_actual\":4983}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:47:45'),
(223, 'producto_terminado', 'crear', 3, 'Se fabricaron 1 unidades de \'Confort Total\'', NULL, '{\"unidades_fabricadas\":1,\"nombre_producto\":\"Confort Total\"}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:47:45'),
(224, 'materia_prima', 'salida', 1, 'Salida de 23 Kilogramos de \'Espuma de poliuretano\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4490}', '{\"stock_actual\":4467}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:49:52'),
(225, 'materia_prima', 'salida', 2, 'Salida de 7.5 Metros de \'Tela Jacquard\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4838}', '{\"stock_actual\":4830.5}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:49:52'),
(226, 'materia_prima', 'salida', 3, 'Salida de 170 Unidades de \'Resortes Bonnell\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":1730}', '{\"stock_actual\":1560}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:49:52'),
(227, 'materia_prima', 'salida', 4, 'Salida de 4.5 Metros de \'Fieltro aislante\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4910.5}', '{\"stock_actual\":4906}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:49:52'),
(228, 'materia_prima', 'salida', 5, 'Salida de 1.6 Litros de \'Pegante industrial\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4965.4}', '{\"stock_actual\":4963.799999999999}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:49:52'),
(229, 'materia_prima', 'salida', 6, 'Salida de 0.55 Unidades de \'Hilo de costura\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4989.1}', '{\"stock_actual\":4988.55}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:49:52'),
(230, 'materia_prima', 'salida', 7, 'Salida de 2 Kilogramos de \'Espuma viscoelastica\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4930}', '{\"stock_actual\":4928}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:49:52'),
(231, 'materia_prima', 'salida', 8, 'Salida de 3.8 Metros de \'Tela antideslizante\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4924.5}', '{\"stock_actual\":4920.7}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:49:52'),
(232, 'materia_prima', 'salida', 9, 'Salida de 10.5 Metros de \'Borde perimetral\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4808.5}', '{\"stock_actual\":4798}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:49:52'),
(233, 'materia_prima', 'salida', 10, 'Salida de 1 Metros de \'Empaque plastico\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4983}', '{\"stock_actual\":4982}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:49:52'),
(234, 'producto_terminado', 'crear', 3, 'Se fabricaron 1 unidades de \'Confort Total\'', NULL, '{\"unidades_fabricadas\":1,\"nombre_producto\":\"Confort Total\"}', NULL, 'Juan Montaño', '::1', '2026-08-25 22:49:52'),
(235, 'materia_prima', 'salida', 1, 'Salida de 46 Kilogramos de \'Espuma de poliuretano\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4467}', '{\"stock_actual\":4421}', NULL, 'Jose Montaño', '::1', '2026-08-25 23:02:10'),
(236, 'materia_prima', 'salida', 2, 'Salida de 15 Metros de \'Tela Jacquard\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4830.5}', '{\"stock_actual\":4815.5}', NULL, 'Jose Montaño', '::1', '2026-08-25 23:02:10'),
(237, 'materia_prima', 'salida', 3, 'Salida de 340 Unidades de \'Resortes Bonnell\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":1560}', '{\"stock_actual\":1220}', NULL, 'Jose Montaño', '::1', '2026-08-25 23:02:10'),
(238, 'materia_prima', 'salida', 4, 'Salida de 9 Metros de \'Fieltro aislante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4906}', '{\"stock_actual\":4897}', NULL, 'Jose Montaño', '::1', '2026-08-25 23:02:10'),
(239, 'materia_prima', 'salida', 5, 'Salida de 3.2 Litros de \'Pegante industrial\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4963.8}', '{\"stock_actual\":4960.6}', NULL, 'Jose Montaño', '::1', '2026-08-25 23:02:10'),
(240, 'materia_prima', 'salida', 6, 'Salida de 1.1 Unidades de \'Hilo de costura\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4988.55}', '{\"stock_actual\":4987.45}', NULL, 'Jose Montaño', '::1', '2026-08-25 23:02:10'),
(241, 'materia_prima', 'salida', 7, 'Salida de 4 Kilogramos de \'Espuma viscoelastica\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4928}', '{\"stock_actual\":4924}', NULL, 'Jose Montaño', '::1', '2026-08-25 23:02:10'),
(242, 'materia_prima', 'salida', 8, 'Salida de 7.6 Metros de \'Tela antideslizante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4920.7}', '{\"stock_actual\":4913.099999999999}', NULL, 'Jose Montaño', '::1', '2026-08-25 23:02:10'),
(243, 'materia_prima', 'salida', 9, 'Salida de 21 Metros de \'Borde perimetral\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4798}', '{\"stock_actual\":4777}', NULL, 'Jose Montaño', '::1', '2026-08-25 23:02:10'),
(244, 'materia_prima', 'salida', 10, 'Salida de 2 Metros de \'Empaque plastico\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4982}', '{\"stock_actual\":4980}', NULL, 'Jose Montaño', '::1', '2026-08-25 23:02:10'),
(245, 'producto_terminado', 'crear', 3, 'Se fabricaron 2 unidades de \'Confort Total\'', NULL, '{\"unidades_fabricadas\":2,\"nombre_producto\":\"Confort Total\"}', NULL, 'Jose Montaño', '::1', '2026-08-25 23:02:10'),
(246, 'materia_prima', 'salida', 1, 'Salida de 69 Kilogramos de \'Espuma de poliuretano\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":4421}', '{\"stock_actual\":4352}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:53'),
(247, 'materia_prima', 'salida', 2, 'Salida de 22.5 Metros de \'Tela Jacquard\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":4815.5}', '{\"stock_actual\":4793}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:53'),
(248, 'materia_prima', 'salida', 3, 'Salida de 510 Unidades de \'Resortes Bonnell\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":1220}', '{\"stock_actual\":710}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:53'),
(249, 'materia_prima', 'salida', 4, 'Salida de 13.5 Metros de \'Fieltro aislante\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":4897}', '{\"stock_actual\":4883.5}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:53'),
(250, 'materia_prima', 'salida', 5, 'Salida de 4.8 Litros de \'Pegante industrial\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":4960.6}', '{\"stock_actual\":4955.8}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:53'),
(251, 'materia_prima', 'salida', 6, 'Salida de 1.65 Unidades de \'Hilo de costura\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":4987.45}', '{\"stock_actual\":4985.8}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:53'),
(252, 'materia_prima', 'salida', 7, 'Salida de 6 Kilogramos de \'Espuma viscoelastica\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":4924}', '{\"stock_actual\":4918}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:53'),
(253, 'materia_prima', 'salida', 8, 'Salida de 11.4 Metros de \'Tela antideslizante\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":4913.1}', '{\"stock_actual\":4901.700000000001}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:53'),
(254, 'materia_prima', 'salida', 9, 'Salida de 31.5 Metros de \'Borde perimetral\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":4777}', '{\"stock_actual\":4745.5}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:53'),
(255, 'materia_prima', 'salida', 10, 'Salida de 3 Metros de \'Empaque plastico\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":4980}', '{\"stock_actual\":4977}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:53'),
(256, 'producto_terminado', 'crear', 3, 'Se fabricaron 3 unidades de \'Confort Total\'', NULL, '{\"unidades_fabricadas\":3,\"nombre_producto\":\"Confort Total\"}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:53'),
(257, 'materia_prima', 'salida', 1, 'Salida de 69 Kilogramos de \'Espuma de poliuretano\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":4352}', '{\"stock_actual\":4283}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:54'),
(258, 'materia_prima', 'salida', 2, 'Salida de 22.5 Metros de \'Tela Jacquard\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":4793}', '{\"stock_actual\":4770.5}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:54'),
(259, 'materia_prima', 'salida', 3, 'Salida de 510 Unidades de \'Resortes Bonnell\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":710}', '{\"stock_actual\":200}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:54'),
(260, 'materia_prima', 'salida', 4, 'Salida de 13.5 Metros de \'Fieltro aislante\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":4883.5}', '{\"stock_actual\":4870}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:54'),
(261, 'materia_prima', 'salida', 5, 'Salida de 4.8 Litros de \'Pegante industrial\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":4955.8}', '{\"stock_actual\":4951}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:54'),
(262, 'materia_prima', 'salida', 6, 'Salida de 1.65 Unidades de \'Hilo de costura\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":4985.8}', '{\"stock_actual\":4984.150000000001}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:54'),
(263, 'materia_prima', 'salida', 7, 'Salida de 6 Kilogramos de \'Espuma viscoelastica\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":4918}', '{\"stock_actual\":4912}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:54'),
(264, 'materia_prima', 'salida', 8, 'Salida de 11.4 Metros de \'Tela antideslizante\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":4901.7}', '{\"stock_actual\":4890.3}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:54'),
(265, 'materia_prima', 'salida', 9, 'Salida de 31.5 Metros de \'Borde perimetral\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":4745.5}', '{\"stock_actual\":4714}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:54'),
(266, 'materia_prima', 'salida', 10, 'Salida de 3 Metros de \'Empaque plastico\' para fabricar 3 unidades de \'Confort Total\'', '{\"stock_actual\":4977}', '{\"stock_actual\":4974}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:54'),
(267, 'producto_terminado', 'crear', 3, 'Se fabricaron 3 unidades de \'Confort Total\'', NULL, '{\"unidades_fabricadas\":3,\"nombre_producto\":\"Confort Total\"}', NULL, 'Juan Montaño', '::1', '2026-08-25 23:03:54'),
(268, 'materia_prima', 'salida', 1, 'Salida de 23 Kilogramos de \'Espuma de poliuretano\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4283}', '{\"stock_actual\":4260}', NULL, 'Juan Montaño', '::1', '2026-08-26 16:51:16'),
(269, 'materia_prima', 'salida', 2, 'Salida de 7.5 Metros de \'Tela Jacquard\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4770.5}', '{\"stock_actual\":4763}', NULL, 'Juan Montaño', '::1', '2026-08-26 16:51:16'),
(270, 'materia_prima', 'salida', 3, 'Salida de 170 Unidades de \'Resortes Bonnell\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":200}', '{\"stock_actual\":30}', NULL, 'Juan Montaño', '::1', '2026-08-26 16:51:16'),
(271, 'materia_prima', 'salida', 4, 'Salida de 4.5 Metros de \'Fieltro aislante\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4870}', '{\"stock_actual\":4865.5}', NULL, 'Juan Montaño', '::1', '2026-08-26 16:51:17'),
(272, 'materia_prima', 'salida', 5, 'Salida de 1.6 Litros de \'Pegante industrial\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4951}', '{\"stock_actual\":4949.4}', NULL, 'Juan Montaño', '::1', '2026-08-26 16:51:17'),
(273, 'materia_prima', 'salida', 6, 'Salida de 0.55 Unidades de \'Hilo de costura\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4984.15}', '{\"stock_actual\":4983.599999999999}', NULL, 'Juan Montaño', '::1', '2026-08-26 16:51:17'),
(274, 'materia_prima', 'salida', 7, 'Salida de 2 Kilogramos de \'Espuma viscoelastica\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4912}', '{\"stock_actual\":4910}', NULL, 'Juan Montaño', '::1', '2026-08-26 16:51:17'),
(275, 'materia_prima', 'salida', 8, 'Salida de 3.8 Metros de \'Tela antideslizante\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4890.3}', '{\"stock_actual\":4886.5}', NULL, 'Juan Montaño', '::1', '2026-08-26 16:51:17'),
(276, 'materia_prima', 'salida', 9, 'Salida de 10.5 Metros de \'Borde perimetral\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4714}', '{\"stock_actual\":4703.5}', NULL, 'Juan Montaño', '::1', '2026-08-26 16:51:17'),
(277, 'materia_prima', 'salida', 10, 'Salida de 1 Metros de \'Empaque plastico\' para fabricar 1 unidades de \'Confort Total\'', '{\"stock_actual\":4974}', '{\"stock_actual\":4973}', NULL, 'Juan Montaño', '::1', '2026-08-26 16:51:17'),
(278, 'producto_terminado', 'crear', 3, 'Se fabricaron 1 unidades de \'Confort Total\'', NULL, '{\"unidades_fabricadas\":1,\"nombre_producto\":\"Confort Total\"}', NULL, 'Juan Montaño', '::1', '2026-08-26 16:51:17'),
(279, 'proveedores', 'editar', 1, 'Se actualizó el proveedor \'Espumas Colombia SAS\'', '{\"id_proveedor\":1,\"nombre_empresa\":\"Espumas Colombia SAS\",\"nit\":\"900123456-1\",\"direccion\":\"Bogotá D.C.\",\"descripcion_empresa\":\"Fabricación y comercialización de espumas para colchones\",\"contacto_nombre\":\"Carlos\",\"contacto_apellido\":\"Ramirez\",\"telefono\":\"3101111111\",\"email\":\"nayivebarragan09@gmail.com\",\"imagen\":\"proveedor_1_1787250011.jpeg\",\"estado\":\"activo\",\"activo\":1}', '{\"nombre_empresa\":\"Espumas Colombia SAS\",\"nit\":\"900123456-1\",\"contacto_nombre\":\"Carlos\",\"contacto_apellido\":\"Ramirez\",\"telefono\":\"3101111111\",\"email\":\"karolgomez1603@gmail.com\",\"direccion\":\"Bogotá D.C.\",\"descripcion_empresa\":\"Fabricación y comercialización de espumas para colchones\"}', NULL, 'Juan Montaño', '::1', '2026-08-26 19:44:11'),
(280, 'pedidos_proveedor', 'crear', 5, 'Se solicitó un pedido de \'Espuma de poliuretano\' (cantidad: 8) al proveedor \'Espumas Colombia SAS\'', NULL, '{\"id_proveedor\":\"1\",\"id_material\":\"1\",\"cantidad_pedida\":\"8\"}', NULL, 'Juan Montaño', '::1', '2026-08-26 19:44:35'),
(281, 'materia_prima', 'editar', 3, 'Se actualizó la materia prima \'Resortes Bonnell\'', '{\"id_material\":3,\"nombre_material\":\"Resortes Bonnell\",\"stock_actual\":\"30.00\",\"stock_minimo\":\"300.00\",\"id_unidad\":3,\"id_proveedor\":3,\"notificar_email\":0,\"correo_notificacion\":null,\"alerta_enviada\":0}', '{\"nombre_material\":\"Resortes Bonnell\",\"stock_actual\":\"5000.00\",\"stock_minimo\":\"300.00\",\"id_unidad\":\"3\",\"id_proveedor\":\"3\"}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:44:55'),
(282, 'materia_prima', 'salida', 1, 'Salida de 46 Kilogramos de \'Espuma de poliuretano\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4260}', '{\"stock_actual\":4214}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:28'),
(283, 'materia_prima', 'salida', 2, 'Salida de 15 Metros de \'Tela Jacquard\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4763}', '{\"stock_actual\":4748}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:28'),
(284, 'materia_prima', 'salida', 3, 'Salida de 340 Unidades de \'Resortes Bonnell\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":5000}', '{\"stock_actual\":4660}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:28'),
(285, 'materia_prima', 'salida', 4, 'Salida de 9 Metros de \'Fieltro aislante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4865.5}', '{\"stock_actual\":4856.5}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:28'),
(286, 'materia_prima', 'salida', 5, 'Salida de 3.2 Litros de \'Pegante industrial\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4949.4}', '{\"stock_actual\":4946.2}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:28'),
(287, 'materia_prima', 'salida', 6, 'Salida de 1.1 Unidades de \'Hilo de costura\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4983.6}', '{\"stock_actual\":4982.5}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:28'),
(288, 'materia_prima', 'salida', 7, 'Salida de 4 Kilogramos de \'Espuma viscoelastica\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4910}', '{\"stock_actual\":4906}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:28'),
(289, 'materia_prima', 'salida', 8, 'Salida de 7.6 Metros de \'Tela antideslizante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4886.5}', '{\"stock_actual\":4878.9}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:28'),
(290, 'materia_prima', 'salida', 9, 'Salida de 21 Metros de \'Borde perimetral\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4703.5}', '{\"stock_actual\":4682.5}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:28'),
(291, 'materia_prima', 'salida', 10, 'Salida de 2 Metros de \'Empaque plastico\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4973}', '{\"stock_actual\":4971}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:28'),
(292, 'producto_terminado', 'crear', 3, 'Se fabricaron 2 unidades de \'Confort Total\'', NULL, '{\"unidades_fabricadas\":2,\"nombre_producto\":\"Confort Total\"}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:28'),
(293, 'materia_prima', 'salida', 1, 'Salida de 46 Kilogramos de \'Espuma de poliuretano\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4214}', '{\"stock_actual\":4168}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:29'),
(294, 'materia_prima', 'salida', 2, 'Salida de 15 Metros de \'Tela Jacquard\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4748}', '{\"stock_actual\":4733}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:29'),
(295, 'materia_prima', 'salida', 3, 'Salida de 340 Unidades de \'Resortes Bonnell\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4660}', '{\"stock_actual\":4320}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:29'),
(296, 'materia_prima', 'salida', 4, 'Salida de 9 Metros de \'Fieltro aislante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4856.5}', '{\"stock_actual\":4847.5}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:29'),
(297, 'materia_prima', 'salida', 5, 'Salida de 3.2 Litros de \'Pegante industrial\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4946.2}', '{\"stock_actual\":4943}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:29'),
(298, 'materia_prima', 'salida', 6, 'Salida de 1.1 Unidades de \'Hilo de costura\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4982.5}', '{\"stock_actual\":4981.4}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:29'),
(299, 'materia_prima', 'salida', 7, 'Salida de 4 Kilogramos de \'Espuma viscoelastica\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4906}', '{\"stock_actual\":4902}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:29'),
(300, 'materia_prima', 'salida', 8, 'Salida de 7.6 Metros de \'Tela antideslizante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4878.9}', '{\"stock_actual\":4871.299999999999}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:29'),
(301, 'materia_prima', 'salida', 9, 'Salida de 21 Metros de \'Borde perimetral\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4682.5}', '{\"stock_actual\":4661.5}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:29'),
(302, 'materia_prima', 'salida', 10, 'Salida de 2 Metros de \'Empaque plastico\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4971}', '{\"stock_actual\":4969}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:29'),
(303, 'producto_terminado', 'crear', 3, 'Se fabricaron 2 unidades de \'Confort Total\'', NULL, '{\"unidades_fabricadas\":2,\"nombre_producto\":\"Confort Total\"}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:29'),
(304, 'materia_prima', 'salida', 1, 'Salida de 46 Kilogramos de \'Espuma de poliuretano\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4168}', '{\"stock_actual\":4122}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:32'),
(305, 'materia_prima', 'salida', 2, 'Salida de 15 Metros de \'Tela Jacquard\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4733}', '{\"stock_actual\":4718}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:32'),
(306, 'materia_prima', 'salida', 3, 'Salida de 340 Unidades de \'Resortes Bonnell\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4320}', '{\"stock_actual\":3980}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:32'),
(307, 'materia_prima', 'salida', 4, 'Salida de 9 Metros de \'Fieltro aislante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4847.5}', '{\"stock_actual\":4838.5}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:32'),
(308, 'materia_prima', 'salida', 5, 'Salida de 3.2 Litros de \'Pegante industrial\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4943}', '{\"stock_actual\":4939.8}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:32'),
(309, 'materia_prima', 'salida', 6, 'Salida de 1.1 Unidades de \'Hilo de costura\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4981.4}', '{\"stock_actual\":4980.299999999999}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:32'),
(310, 'materia_prima', 'salida', 7, 'Salida de 4 Kilogramos de \'Espuma viscoelastica\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4902}', '{\"stock_actual\":4898}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:32'),
(311, 'materia_prima', 'salida', 8, 'Salida de 7.6 Metros de \'Tela antideslizante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4871.3}', '{\"stock_actual\":4863.7}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:32'),
(312, 'materia_prima', 'salida', 9, 'Salida de 21 Metros de \'Borde perimetral\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4661.5}', '{\"stock_actual\":4640.5}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:32'),
(313, 'materia_prima', 'salida', 10, 'Salida de 2 Metros de \'Empaque plastico\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4969}', '{\"stock_actual\":4967}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:32'),
(314, 'producto_terminado', 'crear', 3, 'Se fabricaron 2 unidades de \'Confort Total\'', NULL, '{\"unidades_fabricadas\":2,\"nombre_producto\":\"Confort Total\"}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:32'),
(315, 'materia_prima', 'salida', 1, 'Salida de 46 Kilogramos de \'Espuma de poliuretano\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4122}', '{\"stock_actual\":4076}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:45'),
(316, 'materia_prima', 'salida', 2, 'Salida de 15 Metros de \'Tela Jacquard\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4718}', '{\"stock_actual\":4703}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:45'),
(317, 'materia_prima', 'salida', 3, 'Salida de 340 Unidades de \'Resortes Bonnell\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":3980}', '{\"stock_actual\":3640}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:45'),
(318, 'materia_prima', 'salida', 4, 'Salida de 9 Metros de \'Fieltro aislante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4838.5}', '{\"stock_actual\":4829.5}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:45'),
(319, 'materia_prima', 'salida', 5, 'Salida de 3.2 Litros de \'Pegante industrial\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4939.8}', '{\"stock_actual\":4936.6}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:45'),
(320, 'materia_prima', 'salida', 6, 'Salida de 1.1 Unidades de \'Hilo de costura\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4980.3}', '{\"stock_actual\":4979.2}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:45'),
(321, 'materia_prima', 'salida', 7, 'Salida de 4 Kilogramos de \'Espuma viscoelastica\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4898}', '{\"stock_actual\":4894}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:45'),
(322, 'materia_prima', 'salida', 8, 'Salida de 7.6 Metros de \'Tela antideslizante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4863.7}', '{\"stock_actual\":4856.099999999999}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:45'),
(323, 'materia_prima', 'salida', 9, 'Salida de 21 Metros de \'Borde perimetral\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4640.5}', '{\"stock_actual\":4619.5}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:45'),
(324, 'materia_prima', 'salida', 10, 'Salida de 2 Metros de \'Empaque plastico\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4967}', '{\"stock_actual\":4965}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:45'),
(325, 'producto_terminado', 'crear', 3, 'Se fabricaron 2 unidades de \'Confort Total\'', NULL, '{\"unidades_fabricadas\":2,\"nombre_producto\":\"Confort Total\"}', NULL, 'Juan Montaño', '::1', '2026-08-27 11:52:45'),
(326, 'proveedores', 'habilitar', 3, 'Se habilitó el proveedor \'Grupo Espumados S.A.S\'', '{\"estado\":\"inactivo\"}', '{\"estado\":\"activo\"}', NULL, 'Jafet Pineda', '::1', '2026-08-27 13:56:23'),
(327, 'proveedores', 'habilitar', 4, 'Se habilitó el proveedor \'Insumos beatex SAS\'', '{\"estado\":\"inactivo\"}', '{\"estado\":\"activo\"}', NULL, 'Jafet Pineda', '::1', '2026-08-27 13:56:28'),
(328, 'proveedores', 'deshabilitar', 1, 'Se deshabilitó el proveedor \'Espumas Colombia SAS\'', '{\"estado\":\"activo\"}', '{\"estado\":\"inactivo\"}', NULL, 'Jafet Pineda', '::1', '2026-08-27 13:56:41'),
(329, 'proveedores', 'habilitar', 1, 'Se habilitó el proveedor \'Espumas Colombia SAS\'', '{\"estado\":\"inactivo\"}', '{\"estado\":\"activo\"}', NULL, 'Jafet Pineda', '::1', '2026-08-27 13:56:49'),
(330, 'proveedores', 'deshabilitar', 1, 'Se deshabilitó el proveedor \'Espumas Colombia SAS\'', '{\"estado\":\"activo\"}', '{\"estado\":\"inactivo\"}', NULL, 'Jafet Pineda', '::1', '2026-08-27 13:58:36');
INSERT INTO `historial_movimientos` (`id`, `modulo`, `accion`, `id_registro`, `descripcion`, `datos_anteriores`, `datos_nuevos`, `usuario_id`, `usuario_nombre`, `ip`, `fecha_hora`) VALUES
(331, 'materia_prima', 'salida', 1, 'Salida de 42 Kilogramos de \'Espuma de poliuretano\' para fabricar 2 unidades de \'Doble\'', '{\"stock_actual\":4076}', '{\"stock_actual\":4034}', NULL, 'Jafet Pineda', '::1', '2026-08-27 13:59:31'),
(332, 'materia_prima', 'salida', 2, 'Salida de 14 Metros de \'Tela Jacquard\' para fabricar 2 unidades de \'Doble\'', '{\"stock_actual\":4703}', '{\"stock_actual\":4689}', NULL, 'Jafet Pineda', '::1', '2026-08-27 13:59:31'),
(333, 'materia_prima', 'salida', 5, 'Salida de 2.6 Litros de \'Pegante industrial\' para fabricar 2 unidades de \'Doble\'', '{\"stock_actual\":4936.6}', '{\"stock_actual\":4934}', NULL, 'Jafet Pineda', '::1', '2026-08-27 13:59:31'),
(334, 'materia_prima', 'salida', 1, 'Salida de 44 Kilogramos de \'Espuma de poliuretano\' para fabricar 2 unidades de \'Doble\'', '{\"stock_actual\":4076}', '{\"stock_actual\":4032}', NULL, 'Jafet Pineda', '::1', '2026-08-27 13:59:31'),
(335, 'materia_prima', 'salida', 2, 'Salida de 14 Metros de \'Tela Jacquard\' para fabricar 2 unidades de \'Doble\'', '{\"stock_actual\":4703}', '{\"stock_actual\":4689}', NULL, 'Jafet Pineda', '::1', '2026-08-27 13:59:31'),
(336, 'materia_prima', 'salida', 3, 'Salida de 320 Unidades de \'Resortes Bonnell\' para fabricar 2 unidades de \'Doble\'', '{\"stock_actual\":3640}', '{\"stock_actual\":3320}', NULL, 'Jafet Pineda', '::1', '2026-08-27 13:59:31'),
(337, 'materia_prima', 'salida', 4, 'Salida de 8 Metros de \'Fieltro aislante\' para fabricar 2 unidades de \'Doble\'', '{\"stock_actual\":4829.5}', '{\"stock_actual\":4821.5}', NULL, 'Jafet Pineda', '::1', '2026-08-27 13:59:31'),
(338, 'materia_prima', 'salida', 5, 'Salida de 3 Litros de \'Pegante industrial\' para fabricar 2 unidades de \'Doble\'', '{\"stock_actual\":4936.6}', '{\"stock_actual\":4933.6}', NULL, 'Jafet Pineda', '::1', '2026-08-27 13:59:31'),
(339, 'materia_prima', 'salida', 6, 'Salida de 1 Unidades de \'Hilo de costura\' para fabricar 2 unidades de \'Doble\'', '{\"stock_actual\":4979.2}', '{\"stock_actual\":4978.2}', NULL, 'Jafet Pineda', '::1', '2026-08-27 13:59:31'),
(340, 'materia_prima', 'salida', 8, 'Salida de 7 Metros de \'Tela antideslizante\' para fabricar 2 unidades de \'Doble\'', '{\"stock_actual\":4856.1}', '{\"stock_actual\":4849.1}', NULL, 'Jafet Pineda', '::1', '2026-08-27 13:59:31'),
(341, 'materia_prima', 'salida', 9, 'Salida de 20 Metros de \'Borde perimetral\' para fabricar 2 unidades de \'Doble\'', '{\"stock_actual\":4619.5}', '{\"stock_actual\":4599.5}', NULL, 'Jafet Pineda', '::1', '2026-08-27 13:59:31'),
(342, 'materia_prima', 'salida', 10, 'Salida de 2 Metros de \'Empaque plastico\' para fabricar 2 unidades de \'Doble\'', '{\"stock_actual\":4965}', '{\"stock_actual\":4963}', NULL, 'Jafet Pineda', '::1', '2026-08-27 13:59:31'),
(343, 'producto_terminado', 'crear', 9, 'Se fabricaron 2 unidades de \'Doble\'', NULL, '{\"unidades_fabricadas\":2,\"nombre_producto\":\"Doble\"}', NULL, 'Jafet Pineda', '::1', '2026-08-27 13:59:31'),
(344, 'proveedores', 'editar', 3, 'Se actualizó el proveedor \'Grupo Espumados S.A.S\'', '{\"id_proveedor\":3,\"nombre_empresa\":\"Grupo Espumados S.A.S\",\"nit\":\"900345678-3\",\"direccion\":\"Cali, Valle del Cauca\",\"descripcion_empresa\":\"Fabricación de resortes para colchones\",\"contacto_nombre\":\"Andres\",\"contacto_apellido\":\"Martinez\",\"telefono\":\"3103333333\",\"email\":\"ventas@resortesnacionales.com\",\"imagen\":\"proveedor_3_1787250256.jpeg\",\"estado\":\"activo\",\"activo\":1}', '{\"nombre_empresa\":\"Grupo Espumados S.A.S\",\"nit\":\"900345678-3\",\"contacto_nombre\":\"Andres\",\"contacto_apellido\":\"Martinez\",\"telefono\":\"3103333333\",\"email\":\"jafetdavidpi@gmail.com\",\"direccion\":\"Cali, Valle del Cauca\",\"descripcion_empresa\":\"Fabricación de resortes para colchones\"}', NULL, 'Jafet Pineda', '::1', '2026-08-27 15:05:47'),
(345, 'pedidos_proveedor', 'crear', 6, 'Se solicitó un pedido de \'Resortes Bonnell\' (cantidad: 500) al proveedor \'Grupo Espumados S.A.S\'', NULL, '{\"id_proveedor\":\"3\",\"id_material\":\"3\",\"cantidad_pedida\":\"500\"}', NULL, 'Jafet Pineda', '::1', '2026-08-27 15:07:21'),
(346, 'materia_prima', 'salida', 1, 'Salida de 46 Kilogramos de \'Espuma de poliuretano\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":3990}', '{\"stock_actual\":3944}', NULL, 'Juan Montaño', '::1', '2026-08-28 20:32:46'),
(347, 'materia_prima', 'salida', 2, 'Salida de 15 Metros de \'Tela Jacquard\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4675}', '{\"stock_actual\":4660}', NULL, 'Juan Montaño', '::1', '2026-08-28 20:32:46'),
(348, 'materia_prima', 'salida', 3, 'Salida de 340 Unidades de \'Resortes Bonnell\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":3320}', '{\"stock_actual\":2980}', NULL, 'Juan Montaño', '::1', '2026-08-28 20:32:46'),
(349, 'materia_prima', 'salida', 4, 'Salida de 9 Metros de \'Fieltro aislante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4821.5}', '{\"stock_actual\":4812.5}', NULL, 'Juan Montaño', '::1', '2026-08-28 20:32:46'),
(350, 'materia_prima', 'salida', 5, 'Salida de 3.2 Litros de \'Pegante industrial\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4931}', '{\"stock_actual\":4927.8}', NULL, 'Juan Montaño', '::1', '2026-08-28 20:32:46'),
(351, 'materia_prima', 'salida', 6, 'Salida de 1.1 Unidades de \'Hilo de costura\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4978.2}', '{\"stock_actual\":4977.099999999999}', NULL, 'Juan Montaño', '::1', '2026-08-28 20:32:46'),
(352, 'materia_prima', 'salida', 7, 'Salida de 4 Kilogramos de \'Espuma viscoelastica\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4894}', '{\"stock_actual\":4890}', NULL, 'Juan Montaño', '::1', '2026-08-28 20:32:46'),
(353, 'materia_prima', 'salida', 8, 'Salida de 7.6 Metros de \'Tela antideslizante\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4849.1}', '{\"stock_actual\":4841.5}', NULL, 'Juan Montaño', '::1', '2026-08-28 20:32:46'),
(354, 'materia_prima', 'salida', 9, 'Salida de 21 Metros de \'Borde perimetral\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4599.5}', '{\"stock_actual\":4578.5}', NULL, 'Juan Montaño', '::1', '2026-08-28 20:32:46'),
(355, 'materia_prima', 'salida', 10, 'Salida de 2 Metros de \'Empaque plastico\' para fabricar 2 unidades de \'Confort Total\'', '{\"stock_actual\":4963}', '{\"stock_actual\":4961}', NULL, 'Juan Montaño', '::1', '2026-08-28 20:32:46'),
(356, 'producto_terminado', 'crear', 3, 'Se fabricaron 2 unidades de \'Confort Total\'', NULL, '{\"unidades_fabricadas\":2,\"nombre_producto\":\"Confort Total\"}', NULL, 'Juan Montaño', '::1', '2026-08-28 20:32:46'),
(357, 'proveedores', 'editar', 4, 'Se actualizó el proveedor \'Insumos beatex SAS\'', '{\"id_proveedor\":4,\"nombre_empresa\":\"Insumos beatex SAS\",\"nit\":\"900456789-4\",\"direccion\":\"Barranquilla, Atlántico\",\"descripcion_empresa\":\"Distribución de insumos industriales\",\"contacto_nombre\":\"Paula\",\"contacto_apellido\":\"Torres\",\"telefono\":\"3104444444\",\"email\":\"compras@insumosindustriales.com\",\"imagen\":\"proveedor_4_1787250112.jpg\",\"estado\":\"activo\",\"activo\":1}', '{\"nombre_empresa\":\"Insumos beatex SAS\",\"nit\":\"900456789-4\",\"contacto_nombre\":\"Paula\",\"contacto_apellido\":\"Torres\",\"telefono\":\"3104444444\",\"email\":\"diegogo3027@gmail.com\",\"direccion\":\"Barranquilla, Atlántico\",\"descripcion_empresa\":\"Distribución de insumos industriales\"}', NULL, 'Juan Montaño', '::1', '2026-08-28 20:34:23'),
(358, 'pedidos_proveedor', 'crear', 7, 'Se solicitó un pedido de \'Empaque plastico\' (cantidad: 45) al proveedor \'Insumos beatex SAS\'', NULL, '{\"id_proveedor\":\"4\",\"id_material\":\"10\",\"cantidad_pedida\":\"45\"}', NULL, 'Juan Montaño', '::1', '2026-08-28 20:34:37'),
(359, 'Seguridad', 'sesion_duplicada', 7, 'Se detectó un nuevo inicio de sesión para Juan Montaño (Doc: 1068952619) mientras ya existía una sesión activa. IP: ::1.', NULL, NULL, 7, 'Juan Montaño', '::1', '2026-08-31 16:41:11'),
(360, 'Seguridad', 'sesion_duplicada', 7, 'Se detectó un nuevo inicio de sesión para Juan Montaño (Doc: 1068952619) mientras ya existía una sesión activa. IP: ::1.', NULL, NULL, 7, 'Juan Montaño', '::1', '2026-08-31 16:45:51'),
(361, 'Seguridad', 'sesion_duplicada', 7, 'Se detectó un nuevo inicio de sesión para Juan Montaño (Doc: 1068952619) mientras ya existía una sesión activa. IP: ::1.', NULL, NULL, 7, 'Juan Montaño', '::1', '2026-08-31 16:49:32'),
(362, 'Seguridad', 'sesion_duplicada', 18, 'Se detectó un nuevo inicio de sesión para Jafet Pineda (Doc: 1072746605) mientras ya existía una sesión activa. IP: ::1.', NULL, NULL, 18, 'Jafet Pineda', '::1', '2026-08-31 16:50:32'),
(363, 'Seguridad', 'sesion_duplicada', 18, 'Se detectó un nuevo inicio de sesión para Jafet Pineda (Doc: 1072746605) mientras ya existía una sesión activa. IP: ::1.', NULL, NULL, 18, 'Jafet Pineda', '::1', '2026-08-31 17:14:22'),
(364, 'Usuarios', 'Registro', 19, 'Se registró el usuario Maria José Gonzalez Rodriguez (Doc: 1068952788) con el rol administrador.', NULL, '{\"email\":\"majog4892@gmail.com\",\"documento\":\"1068952788\",\"nombre\":\"Maria José\",\"apellido\":\"Gonzalez Rodriguez\",\"rol\":\"administrador\",\"foto\":\"public\\/uploads\\/usuarios\\/usr_1788230450_6a963b320b027.jpg\"}', NULL, 'Juan', '::1', '2026-08-31 21:40:50'),
(365, 'Seguridad', 'sesion_duplicada', 19, 'Se detectó un nuevo inicio de sesión para Maria José Gonzalez Rodriguez (Doc: 1068952788) mientras ya existía una sesión activa. IP: ::1.', NULL, NULL, 19, 'Maria José Gonzalez Rodriguez', '::1', '2026-08-31 21:46:25'),
(366, 'Seguridad', 'sesion_duplicada', 19, 'Se detectó un nuevo inicio de sesión para Maria José Gonzalez Rodriguez (Doc: 1068952788) mientras ya existía una sesión activa. IP: ::1.', NULL, NULL, 19, 'Maria José Gonzalez Rodriguez', '::1', '2026-08-31 21:46:40'),
(367, 'Seguridad', 'sesion_duplicada', 19, 'Se detectó un nuevo inicio de sesión para Maria José Gonzalez Rodriguez (Doc: 1068952788) mientras ya existía una sesión activa. IP: ::1.', NULL, NULL, 19, 'Maria José Gonzalez Rodriguez', '::1', '2026-08-31 21:50:12'),
(368, 'Seguridad', 'sesion_duplicada', 19, 'Se detectó un nuevo inicio de sesión para Maria José Gonzalez Rodriguez (Doc: 1068952788) mientras ya existía una sesión activa. IP: ::1.', NULL, NULL, 19, 'Maria José Gonzalez Rodriguez', '::1', '2026-08-31 21:54:26'),
(369, 'Seguridad', 'sesion_duplicada', 19, 'Se detectó un nuevo inicio de sesión para Maria José Gonzalez Rodriguez (Doc: 1068952788) mientras ya existía una sesión activa. IP: ::1.', NULL, NULL, 19, 'Maria José Gonzalez Rodriguez', '::1', '2026-08-31 22:50:26'),
(370, 'Seguridad', 'sesion_duplicada', 7, 'Se detectó un nuevo inicio de sesión para Juan Montaño (Doc: 1068952619) mientras ya existía una sesión activa. IP: ::1.', NULL, NULL, 7, 'Juan Montaño', '::1', '2026-08-31 23:21:41'),
(371, 'Seguridad', 'sesion_duplicada', 7, 'Se detectó un nuevo inicio de sesión para Juan Montaño (Doc: 1068952619) mientras ya existía una sesión activa. IP: ::1.', NULL, NULL, 7, 'Juan Montaño', '::1', '2026-08-31 23:26:59'),
(372, 'Seguridad', 'sesion_duplicada', 7, 'Se detectó un nuevo inicio de sesión para Juan Montaño (Doc: 1068952619) mientras ya existía una sesión activa. IP: ::1.', NULL, NULL, 7, 'Juan Montaño', '::1', '2026-08-31 23:34:18'),
(373, 'Seguridad', 'sesion_duplicada', 7, 'Se detectó un nuevo inicio de sesión para Juan Montaño (Doc: 1068952619) mientras ya existía una sesión activa. IP: ::1.', NULL, NULL, 7, 'Juan Montaño', '::1', '2026-09-01 14:04:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_produccion`
--

CREATE TABLE `historial_produccion` (
  `id` int(11) NOT NULL,
  `id_modelo` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha_fabricacion` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_produccion`
--

INSERT INTO `historial_produccion` (`id`, `id_modelo`, `cantidad`, `fecha_fabricacion`, `usuario`) VALUES
(1, 3, 2, '2026-08-25 20:06:35', 'Jose Montaño'),
(2, 2, 1, '2026-08-25 20:10:20', 'Jose Montaño'),
(3, 11, 2, '2026-08-25 20:13:12', 'Jose Montaño'),
(4, 3, 1, '2026-08-25 21:41:37', 'Jose Montaño'),
(5, 3, 2, '2026-08-25 21:55:13', 'Jose Montaño'),
(6, 3, 2, '2026-08-25 22:00:03', 'Jose Montaño'),
(7, 3, 2, '2026-08-25 22:06:02', 'Jose Montaño'),
(8, 3, 2, '2026-08-25 22:06:03', 'Jose Montaño'),
(9, 4, 5, '2026-08-25 22:45:43', 'Juan Montaño'),
(10, 3, 1, '2026-08-25 22:47:45', 'Juan Montaño'),
(11, 3, 1, '2026-08-25 22:49:52', 'Juan Montaño'),
(12, 3, 2, '2026-08-25 23:02:10', 'Jose Montaño'),
(13, 3, 3, '2026-08-25 23:03:53', 'Juan Montaño'),
(14, 3, 3, '2026-08-25 23:03:54', 'Juan Montaño'),
(15, 3, 1, '2026-08-26 16:51:16', 'Juan Montaño'),
(16, 3, 2, '2026-08-27 11:52:28', 'Juan Montaño'),
(17, 3, 2, '2026-08-27 11:52:29', 'Juan Montaño'),
(18, 3, 2, '2026-08-27 11:52:32', 'Juan Montaño'),
(19, 3, 2, '2026-08-27 11:52:45', 'Juan Montaño'),
(20, 9, 2, '2026-08-27 13:59:31', 'Jafet Pineda'),
(21, 3, 2, '2026-08-28 20:32:46', 'Juan Montaño');

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
  `id_proveedor` int(11) DEFAULT NULL,
  `notificar_email` tinyint(1) NOT NULL DEFAULT 0,
  `correo_notificacion` varchar(150) DEFAULT NULL,
  `alerta_enviada` tinyint(1) NOT NULL DEFAULT 0,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materias_primas`
--

INSERT INTO `materias_primas` (`id_material`, `nombre_material`, `stock_actual`, `stock_minimo`, `id_unidad`, `id_proveedor`, `notificar_email`, `correo_notificacion`, `alerta_enviada`, `estado`) VALUES
(1, 'Espuma de poliuretano', 3944.00, 200.00, 1, 1, 1, 'juanjosemon19@gmail.com', 0, 'activo'),
(2, 'Tela Jacquard', 4660.00, 50.00, 2, 2, 0, NULL, 0, 'activo'),
(3, 'Resortes Bonnell', 2980.00, 300.00, 3, 3, 0, NULL, 0, 'activo'),
(4, 'Fieltro aislante', 4812.50, 15.00, 2, 1, 1, 'nicolaspolo096@gmail.com', 0, 'activo'),
(5, 'Pegante industrial', 4927.80, 20.00, 4, 4, 0, NULL, 0, 'activo'),
(6, 'Hilo de costura', 4977.10, 50.00, 3, 2, 0, NULL, 0, 'activo'),
(7, 'Espuma viscoelastica', 4890.00, 70.00, 1, 1, 1, 'juanjosemon19@gmail.com', 0, 'activo'),
(8, 'Tela antideslizante', 4841.50, 40.00, 2, 2, 0, NULL, 0, 'activo'),
(9, 'Borde perimetral', 4578.50, 40.00, 2, 3, 0, NULL, 0, 'activo'),
(10, 'Empaque plastico', 4961.00, 80.00, 2, 4, 0, NULL, 0, 'activo'),
(11, 'Tela ', 5000.00, 30.00, 2, 2, 0, NULL, 0, 'activo'),
(13, 'Fieltro aislante', 5000.00, 0.00, 3, 4, 0, NULL, 0, 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `id_mensaje` int(11) NOT NULL,
  `id_remitente` int(11) NOT NULL,
  `id_destinatario` int(11) NOT NULL,
  `asunto` varchar(150) NOT NULL,
  `contenido` text NOT NULL,
  `leido` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_envio` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mensajes`
--

INSERT INTO `mensajes` (`id_mensaje`, `id_remitente`, `id_destinatario`, `asunto`, `contenido`, `leido`, `fecha_envio`) VALUES
(1, 7, 19, 'maquinas', 'quiero que revises si la maquina de cerrar esta en buen estado', 1, '2026-08-31 22:52:03'),
(2, 8, 14, 'Maquinaria con presunta falla', 'Hola Nicolás, puedes revisar que la maquina de cocido este funcionando correctamente, espero tu respuesta. Gracias', 0, '2026-09-01 22:27:38');

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

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `id_material`, `mensaje`, `fecha_generada`, `leida`) VALUES
(1, 1, 'El material \'Espuma de poliuretano\' alcanzó su stock mínimo (actual: 50.00, mínimo: 110.00).', '2026-08-20 04:06:44', 0),
(2, 7, 'El material \'Espuma viscoelastica\' alcanzó su stock mínimo (actual: 60.00, mínimo: 70.00).', '2026-08-25 22:50:46', 0),
(3, 6, 'El material \'Hilo de costura\' alcanzó su stock mínimo (actual: 77.05, mínimo: 5000.00).', '2026-08-26 01:11:32', 0);

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

--
-- Volcado de datos para la tabla `pedidos_proveedor`
--

INSERT INTO `pedidos_proveedor` (`id_pedido`, `id_proveedor`, `id_material`, `cantidad_pedida`, `estado`, `fecha_pedido`) VALUES
(1, 1, 1, 100.00, 'PENDIENTE', '2026-08-21 03:21:45'),
(2, 1, 1, 1000.00, 'PENDIENTE', '2026-08-21 03:22:32'),
(3, 1, 4, 12.00, 'PENDIENTE', '2026-08-21 03:27:52'),
(4, 1, 1, 15.00, 'PENDIENTE', '2026-08-25 22:49:02'),
(5, 1, 1, 8.00, 'PENDIENTE', '2026-08-27 00:44:30'),
(6, 3, 3, 500.00, 'PENDIENTE', '2026-08-27 20:07:15'),
(7, 4, 10, 45.00, 'PENDIENTE', '2026-08-29 01:34:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_terminados`
--

CREATE TABLE `productos_terminados` (
  `id_producto` int(11) NOT NULL,
  `nombre_producto` varchar(100) NOT NULL,
  `stock_actual` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock_minimo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos_terminados`
--

INSERT INTO `productos_terminados` (`id_producto`, `nombre_producto`, `stock_actual`, `stock_minimo`, `fecha_creacion`) VALUES
(1, 'Colchon Queen', 20.00, 0.00, '2026-08-11 19:52:11'),
(2, 'Colchon King', 12.00, 0.00, '2026-08-11 19:56:06'),
(3, 'Colchon Normal', 15.00, 0.00, '2026-08-11 19:56:39'),
(5, 'Semidoble', 3.00, 0.00, '2026-08-20 14:05:36'),
(6, 'Confort Total', 32.00, 0.00, '2026-08-25 20:06:35'),
(7, 'Descanso Real', 1.00, 0.00, '2026-08-25 20:10:20'),
(8, 'King', 2.00, 0.00, '2026-08-25 20:13:12'),
(9, 'Premium Gold', 5.00, 0.00, '2026-08-25 22:45:43'),
(10, 'Doble', 2.00, 0.00, '2026-08-27 13:59:31');

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
  `email` varchar(70) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor`, `nombre_empresa`, `nit`, `direccion`, `descripcion_empresa`, `contacto_nombre`, `contacto_apellido`, `telefono`, `email`, `imagen`, `estado`, `activo`) VALUES
(1, 'Espumas Colombia SAS', '900123456-1', 'Bogotá D.C.', 'Fabricación y comercialización de espumas para colchones', 'Carlos', 'Ramirez', '3101111111', 'karolgomez1603@gmail.com', 'proveedor_1_1787250011.jpeg', 'inactivo', 1),
(2, 'Textiles Andinos SAS', '800234567-2', 'Medellín, Antioquia', 'Producción de textiles para la industria colchonera', 'Laura', 'Gomez', '3102222222', 'contacto@textilesandinos.com', 'proveedor_2_1786563827.jpg', 'activo', 1),
(3, 'Grupo Espumados S.A.S', '900345678-3', 'Cali, Valle del Cauca', 'Fabricación de resortes para colchones', 'Andres', 'Martinez', '3103333333', 'jafetdavidpi@gmail.com', 'proveedor_3_1787250256.jpeg', 'activo', 1),
(4, 'Insumos beatex SAS', '900456789-4', 'Barranquilla, Atlántico', 'Distribución de insumos industriales', 'Paula', 'Torres', '3104444444', 'diegogo3027@gmail.com', 'proveedor_4_1787250112.jpg', 'activo', 1),
(8, 'Resortes Inalres S.A.S', '83947593487', 'Cl 14 107-54', 'Empresa de resortes reforzados', 'Jafet', 'Pineda', '3116364875', 'jafetdavidpi@gmail.com', 'proveedor_8_1787250218.png', 'activo', 1);

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
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id_tarea` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `prioridad` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `fecha_vencimiento` date DEFAULT NULL,
  `estado` enum('pendiente','por-hacer','terminado') NOT NULL DEFAULT 'pendiente',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tareas`
--

INSERT INTO `tareas` (`id_tarea`, `titulo`, `prioridad`, `fecha_vencimiento`, `estado`, `fecha_creacion`) VALUES
(1, 'Solicitar espuma', 'medium', '2026-05-20', 'pendiente', '2026-08-19 23:35:28'),
(2, 'Pedido N° 346', 'high', '2026-05-18', 'pendiente', '2026-08-19 23:35:28'),
(3, 'Verificar inventario', 'medium', '2026-05-21', 'pendiente', '2026-08-19 23:35:28'),
(4, 'Supervisar el área de producción', 'high', '2026-05-19', 'pendiente', '2026-08-19 23:35:28'),
(5, 'Contactar proveedor N° 12', 'low', '2026-05-22', 'terminado', '2026-08-19 23:35:28');

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
  `foto` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `rol` enum('administrador','bodeguero','operario','') NOT NULL,
  `password_hash` varchar(60) NOT NULL,
  `request_password` enum('0','1') NOT NULL DEFAULT '0',
  `token_password` varchar(200) DEFAULT NULL,
  `expired_session` varchar(40) DEFAULT NULL,
  `token_sesion` varchar(64) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `ultima_actividad` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `email`, `documento`, `nombre`, `apellido`, `foto`, `telefono`, `rol`, `password_hash`, `request_password`, `token_password`, `expired_session`, `token_sesion`, `activo`, `ultima_actividad`) VALUES
(7, 'juanjosemon19@gmail.com', '1068952619', 'Juan', 'Montaño', 'usuario_7_1787250604.png', '3229035224', 'administrador', '$2y$10$qxT0RGurIZCj3dPj7c7gX.NlqVNM7bJGvRGnTNiVXO0QC8SVAzVB2', '1', '6049e28c28e03d47a5d564194e804e2f6ad2f4e84ebf05f24a24b0ebd61dc039', '1787856046', 'cf7b239575d7117435abdb03929a3799f55c54287cd148e4602203eb85d853dc', 1, '2026-09-01 17:35:56'),
(8, 'avellanedamaldonadosantiago@gmail.com', '1025062749', 'Santiago', 'Avellaneda', 'usuario_8_1788319477.jpg', NULL, 'administrador', '$2y$10$mwe6pjtnKSgAIvC0donWSeDPygDJ7/IwEhAlSb59NT3OxA5RRnVG.', '', NULL, '0', 'eff898d43b5911bba9f8a8ba5b7b89fe61f8b5dd6fc9c5ddec92f95a961de09b', 1, '2026-09-01 22:26:12'),
(14, 'nicolaspolo096@gmail.com', '1013116788', 'Nicolas', 'Polo', NULL, NULL, 'administrador', '$2y$10$EZVWx.J.syl4M3CsNWiFLOQLRH3MLliTuZ19rIlHK8L/UO8/Azp2i', '1', 'ef9b6fe4769c7ac26885c7c42b214eacc75cddf6893561b167a9b1d58cb64cd8', '1782452857', NULL, 1, NULL),
(18, 'jafetdavidpi@gmail.com', '1072746605', 'Jafet', 'Pineda', NULL, NULL, 'bodeguero', '$2y$10$CR9Aw8rydge4FXpuFKn8...fME51QFfpgxeJkv0wGEwYi6UQmNKhC', '0', NULL, NULL, 'f6bcb428f16d58fe458a79078edac69c5a868754ba064716f535e1b75bac1806', 1, '2026-08-31 22:45:36'),
(19, 'majog4892@gmail.com', '1068952788', 'Maria José', 'Gonzalez Rodriguez', 'usuario_19_1788231237.jpg', '3115726095', 'bodeguero', '$2y$10$3LL/y4Si9zVL4rQCEmJE.OAcr2NO6rlP9aIIDEsMpDE3lIjrbBXHa', '0', NULL, NULL, NULL, 1, '2026-08-31 23:18:34');

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
-- Indices de la tabla `historial_produccion`
--
ALTER TABLE `historial_produccion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_historial_modelo` (`id_modelo`),
  ADD KEY `idx_historial_fecha` (`fecha_fabricacion`);

--
-- Indices de la tabla `materias_primas`
--
ALTER TABLE `materias_primas`
  ADD PRIMARY KEY (`id_material`),
  ADD KEY `id_unidad` (`id_unidad`),
  ADD KEY `id_proveedor` (`id_proveedor`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id_mensaje`),
  ADD KEY `fk_msg_remitente` (`id_remitente`),
  ADD KEY `fk_msg_destinatario` (`id_destinatario`);

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
-- Indices de la tabla `productos_terminados`
--
ALTER TABLE `productos_terminados`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `uq_nombre_producto` (`nombre_producto`);

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
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id_tarea`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=374;

--
-- AUTO_INCREMENT de la tabla `historial_produccion`
--
ALTER TABLE `historial_produccion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `materias_primas`
--
ALTER TABLE `materias_primas`
  MODIFY `id_material` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id_mensaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `modelos_colchon`
--
ALTER TABLE `modelos_colchon`
  MODIFY `id_modelo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pedidos_proveedor`
--
ALTER TABLE `pedidos_proveedor`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `productos_terminados`
--
ALTER TABLE `productos_terminados`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `receta_colchon`
--
ALTER TABLE `receta_colchon`
  MODIFY `id_receta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id_tarea` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `unidades_medida`
--
ALTER TABLE `unidades_medida`
  MODIFY `id_unidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `historial_produccion`
--
ALTER TABLE `historial_produccion`
  ADD CONSTRAINT `fk_historial_modelo` FOREIGN KEY (`id_modelo`) REFERENCES `modelos_colchon` (`id_modelo`);

--
-- Filtros para la tabla `materias_primas`
--
ALTER TABLE `materias_primas`
  ADD CONSTRAINT `materias_primas_ibfk_1` FOREIGN KEY (`id_unidad`) REFERENCES `unidades_medida` (`id_unidad`),
  ADD CONSTRAINT `materias_primas_ibfk_2` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`);

--
-- Filtros para la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `fk_msg_destinatario` FOREIGN KEY (`id_destinatario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_msg_remitente` FOREIGN KEY (`id_remitente`) REFERENCES `usuarios` (`id_usuario`);

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

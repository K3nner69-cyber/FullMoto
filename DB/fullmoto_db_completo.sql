-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-09-2026 a las 14:02:15
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `fullmoto_db`
--
CREATE DATABASE IF NOT EXISTS `fullmoto_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
USE `fullmoto_db`;

DELIMITER $$
--
-- Procedimientos
--
DROP PROCEDURE IF EXISTS `sp_actualizar_estado_envio`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_actualizar_estado_envio` (IN `p_id_pedido` INT, IN `p_nuevo_estado` VARCHAR(30))   BEGIN
    -- Validamos que el estado ingresado sea uno de los permitidos
    IF p_nuevo_estado NOT IN ('Asignado', 'En Ruta', 'Entregado', 'Devuelto') THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Estado de envio invalido. Use: Asignado, En Ruta, Entregado o Devuelto.';
    END IF;

    -- Si el pedido no tiene un envio registrado, avisamos en lugar de fallar en silencio
    IF NOT EXISTS (SELECT 1 FROM distribucion WHERE id_pedido = p_id_pedido) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El pedido indicado no tiene un envio de distribucion asignado.';
    END IF;

    -- Actualizamos el estado; si pasa a 'Entregado' tambien registramos la fecha real
    IF p_nuevo_estado = 'Entregado' THEN
        UPDATE distribucion
        SET estado_envio = p_nuevo_estado,
            fecha_entrega_real = NOW()
        WHERE id_pedido = p_id_pedido;
    ELSE
        UPDATE distribucion
        SET estado_envio = p_nuevo_estado
        WHERE id_pedido = p_id_pedido;
    END IF;

    -- Devolvemos el registro actualizado para confirmar el cambio
    SELECT
        id_pedido        AS 'No. Pedido',
        estado_envio     AS 'Estado Actual',
        fecha_entrega_real AS 'Fecha Entrega Real'
    FROM distribucion
    WHERE id_pedido = p_id_pedido;
END$$

DROP PROCEDURE IF EXISTS `sp_detalle_envio_pedido`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_detalle_envio_pedido` (IN `p_id_pedido` INT)   BEGIN
    -- Seleccionamos las columnas que queremos mostrar
    SELECT
        ped.id_pedido                          AS 'No. Pedido',
        CONCAT(cli.nombre, ' ', cli.apellido)   AS 'Cliente',
        prod.nombre                            AS 'Producto',
        ped.cantidad                           AS 'Cantidad',
        ped.subtotal                           AS 'Subtotal',
        CONCAT(age.nombre, ' ', age.apellido)   AS 'Agente Logistico',
        dist.direccion_entrega                 AS 'Direccion de Entrega',
        dist.fecha_asignacion                  AS 'Fecha Asignacion',
        dist.fecha_entrega_estimada            AS 'Entrega Estimada',
        dist.fecha_entrega_real                AS 'Entrega Real',
        dist.estado_envio                      AS 'Estado Logistico'
    FROM
        distribucion dist
    INNER JOIN pedido ped ON dist.id_pedido = ped.id_pedido
    INNER JOIN venta v ON ped.id_venta = v.id_venta
    INNER JOIN clientefinal cf ON v.id_usuario = cf.idClienteFinal
    INNER JOIN usuario cli ON cf.idClienteFinal = cli.idUsuario
    INNER JOIN productos prod ON ped.id_producto = prod.id_producto
    INNER JOIN agentelogistico al ON dist.id_agente_logistico = al.idAgenteLogistico
    INNER JOIN usuario age ON al.idAgenteLogistico = age.idUsuario
    WHERE
        ped.id_pedido = p_id_pedido;
END$$

DROP PROCEDURE IF EXISTS `sp_historial_entregas_agente`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_historial_entregas_agente` (IN `p_id_agente` INT)   BEGIN
    -- Seleccionamos las columnas que queremos mostrar
    SELECT
        dist.id_distribucion                   AS 'No. Envio',
        ped.id_pedido                          AS 'No. Pedido',
        CONCAT(cli.nombre, ' ', cli.apellido)   AS 'Cliente',
        prod.nombre                            AS 'Producto/Accesorio',
        dist.fecha_asignacion                  AS 'Fecha Asignacion',
        dist.fecha_entrega_estimada            AS 'Entrega Estimada',
        dist.fecha_entrega_real                AS 'Entrega Real',
        dist.estado_envio                      AS 'Estado Logistico'
    FROM
        distribucion dist
    INNER JOIN pedido ped ON dist.id_pedido = ped.id_pedido
    INNER JOIN venta v ON ped.id_venta = v.id_venta
    INNER JOIN clientefinal cf ON v.id_usuario = cf.idClienteFinal
    INNER JOIN usuario cli ON cf.idClienteFinal = cli.idUsuario
    INNER JOIN productos prod ON ped.id_producto = prod.id_producto
    WHERE
        dist.id_agente_logistico = p_id_agente
    ORDER BY
        dist.fecha_asignacion DESC;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agentelogistico`
--

DROP TABLE IF EXISTS `agentelogistico`;
CREATE TABLE `agentelogistico` (
  `idAgenteLogistico` int(11) NOT NULL,
  `nombreContacto` varchar(120) DEFAULT NULL,
  `empresa` varchar(120) DEFAULT NULL,
  `vehiculo` varchar(80) DEFAULT NULL,
  `placa` varchar(15) DEFAULT NULL,
  `zonaCobertura` varchar(120) DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `agentelogistico`
--

INSERT INTO `agentelogistico` (`idAgenteLogistico`, `nombreContacto`, `empresa`, `vehiculo`, `placa`, `zonaCobertura`, `estado`) VALUES
(6, 'Kenner Londono', 'Envios Rapidos S.A.S', 'Camioneta', 'ABC123', 'Bogota Norte', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificaciones`
--

DROP TABLE IF EXISTS `calificaciones`;
CREATE TABLE `calificaciones` (
  `id_calificacion` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `puntaje` int(11) NOT NULL CHECK (`puntaje` between 1 and 5),
  `comentario` varchar(255) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `calificaciones`
--

INSERT INTO `calificaciones` (`id_calificacion`, `id_pedido`, `puntaje`, `comentario`, `fecha`) VALUES
(3, 3, 5, 'Disco de freno de muy buena calidad', '2026-05-16 09:00:00'),
(4, 7, 3, 'Demoro un poco mas de lo esperado', '2026-06-11 08:30:00'),
(7, 3, 5, 'Disco de freno de muy buena calidad', '2026-05-16 09:00:00'),
(8, 7, 3, 'Demoro un poco mas de lo esperado', '2026-06-11 08:30:00'),
(11, 3, 5, 'Disco de freno de muy buena calidad', '2026-05-16 09:00:00'),
(12, 7, 3, 'Demoro un poco mas de lo esperado', '2026-06-11 08:30:00'),
(15, 3, 5, 'Disco de freno de muy buena calidad', '2026-05-16 09:00:00'),
(16, 7, 3, 'Demoro un poco mas de lo esperado', '2026-06-11 08:30:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias` (
  `idCategoria` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`idCategoria`, `nombre`, `descripcion`) VALUES
(1, 'Motor', 'Repuestos relacionados con el motor'),
(2, 'Frenos', 'Sistemas y componentes de frenado'),
(3, 'Electrico', 'Componentes electricos y de iluminacion'),
(4, 'Carroceria', 'Partes de carroceria y estetica');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientefinal`
--

DROP TABLE IF EXISTS `clientefinal`;
CREATE TABLE `clientefinal` (
  `idClienteFinal` int(11) NOT NULL,
  `preferencias` varchar(255) DEFAULT NULL,
  `nivel` varchar(40) NOT NULL DEFAULT 'Estandar'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `clientefinal`
--

INSERT INTO `clientefinal` (`idClienteFinal`, `preferencias`, `nivel`) VALUES
(2, 'Repuestos para motos deportivas', 'Premium'),
(3, 'Mantenimiento basico', 'Estandar');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `distribucion`
--

DROP TABLE IF EXISTS `distribucion`;
CREATE TABLE `distribucion` (
  `id_distribucion` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_agente_logistico` int(11) NOT NULL,
  `fecha_asignacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_entrega_estimada` date DEFAULT NULL,
  `fecha_entrega_real` datetime DEFAULT NULL,
  `estado_envio` varchar(30) NOT NULL DEFAULT 'Asignado',
  `direccion_entrega` varchar(150) DEFAULT NULL,
  `observaciones` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `distribucion`
--

INSERT INTO `distribucion` (`id_distribucion`, `id_pedido`, `id_agente_logistico`, `fecha_asignacion`, `fecha_entrega_estimada`, `fecha_entrega_real`, `estado_envio`, `direccion_entrega`, `observaciones`) VALUES
(3, 3, 6, '2026-05-15 15:00:00', '2026-05-16', '2026-05-16 11:40:00', 'Entregado', 'Carrera 5 # 6-7, Cali', 'Cliente recibio conforme'),
(4, 4, 6, '2026-05-15 15:00:00', '2026-05-16', '2026-05-16 11:40:00', 'Entregado', 'Carrera 5 # 6-7, Cali', 'Cliente recibio conforme'),
(5, 5, 6, '2026-05-15 15:00:00', '2026-05-16', '2026-05-16 11:40:00', 'Entregado', 'Carrera 5 # 6-7, Cali', 'Cliente recibio conforme'),
(6, 6, 6, '2026-06-01 12:00:00', '2026-06-03', NULL, 'En Ruta', 'Av. Siempre Viva 123, Medellin', 'Pendiente confirmacion de pago antes de entrega final'),
(7, 7, 6, '2026-06-10 17:00:00', '2026-06-13', NULL, 'En Ruta', 'Carrera 5 # 6-7, Cali', 'A la espera de despacho desde bodega'),
(8, 8, 6, '2026-06-10 17:00:00', '2026-06-13', NULL, 'Asignado', 'Carrera 5 # 6-7, Cali', 'A la espera de despacho desde bodega');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `motos`
--

DROP TABLE IF EXISTS `motos`;
CREATE TABLE `motos` (
  `id_moto` int(11) NOT NULL,
  `marca` varchar(80) NOT NULL,
  `modelo` varchar(80) NOT NULL,
  `cilindrada` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `motos`
--

INSERT INTO `motos` (`id_moto`, `marca`, `modelo`, `cilindrada`) VALUES
(1, 'Yamaha', 'FZ', 150),
(2, 'Honda', 'CB1', 125),
(3, 'Suzuki', 'GN125', 125),
(4, 'AKT', 'NKD', 125),
(5, 'Yamaha', 'MT-03', 321);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

DROP TABLE IF EXISTS `pagos`;
CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `fecha_pago` datetime NOT NULL DEFAULT current_timestamp(),
  `metodo_pago` varchar(40) NOT NULL,
  `estado_pago` varchar(30) NOT NULL DEFAULT 'Pendiente',
  `valor` decimal(12,2) NOT NULL CHECK (`valor` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pago`, `id_pedido`, `fecha_pago`, `metodo_pago`, `estado_pago`, `valor`) VALUES
(3, 3, '2026-05-15 14:05:00', 'PSE', 'Pagado', 120000.00),
(4, 4, '2026-05-15 14:06:00', 'PSE', 'Pagado', 28000.00),
(5, 6, '2026-06-01 11:25:00', 'Efectivo', 'Pendiente', 95000.00),
(6, 7, '2026-06-10 16:50:00', 'WOMPI', 'Pagado', 64000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

DROP TABLE IF EXISTS `pedido`;
CREATE TABLE `pedido` (
  `id_pedido` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL CHECK (`cantidad` > 0),
  `precio_unitario` decimal(12,2) NOT NULL CHECK (`precio_unitario` >= 0),
  `subtotal` decimal(12,2) NOT NULL CHECK (`subtotal` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`id_pedido`, `id_venta`, `id_producto`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(3, 2, 4, 1, 120000.00, 120000.00),
(4, 2, 6, 1, 28000.00, 28000.00),
(5, 2, 7, 1, 4000.00, 4000.00),
(6, 3, 5, 1, 95000.00, 95000.00),
(7, 4, 3, 2, 32000.00, 64000.00),
(8, 4, 6, 1, 2000.00, 2000.00),
(9, 5, 6, 2, 28000.00, 56000.00),
(10, 5, 9, 1, 90000.00, 90000.00),
(11, 6, 6, 1, 28000.00, 28000.00),
(12, 6, 5, 1, 95000.00, 95000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productomoto`
--

DROP TABLE IF EXISTS `productomoto`;
CREATE TABLE `productomoto` (
  `id_productoMoto` int(11) NOT NULL,
  `idProductos` int(11) NOT NULL,
  `id_moto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `productomoto`
--

INSERT INTO `productomoto` (`id_productoMoto`, `idProductos`, `id_moto`) VALUES
(1, 1, 1),
(2, 1, 5),
(6, 3, 2),
(7, 3, 4),
(8, 4, 1),
(9, 4, 5),
(10, 5, 1),
(11, 5, 2),
(12, 5, 3),
(13, 5, 4),
(14, 5, 5),
(15, 6, 1),
(16, 6, 4),
(17, 7, 2),
(18, 7, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

DROP TABLE IF EXISTS `productos`;
CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `precio` decimal(12,2) NOT NULL CHECK (`precio` >= 0),
  `stock` int(11) NOT NULL DEFAULT 0 CHECK (`stock` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `id_categoria`, `nombre`, `descripcion`, `precio`, `stock`) VALUES
(1, 1, 'Piston 150cc', 'Recupera la potencia, este pistón está fabricado para soportar el trato del día a día, ya sea que uses tu moto para trabajar, moverte por la ciudad o salir a carretera. Invierte en repuestos confiables y olvídate de fallas mecánicas prematuras.', 85000.00, 40),
(3, 2, 'Juego de Pastillas de Freno', 'Este kit está desarrollado para el motorista que busca el equilibrio perfecto entre máxima potencia de frenado y larga duración. Ya sea que uses tu moto para el trabajo diario en la ciudad, viajes en carretera o rutas fuera de pista', 32000.00, 60),
(4, 2, 'Disco de Freno Trasero', 'Disco de freno trasero de acero inoxidable para moto de enduro, de alta resistencia diseñado para soportar las exigentes condiciones del manejo fuera de pista (off-road), como el barro, la arena, el agua y los impactos constantes contra rocas.', 150000.00, 15),
(5, 3, 'Bateria 12V', 'Componente eléctrico esencial que almacena y suministra energía para encender el motor, alimentar las luces y hacer funcionar los tableros digitales o sistemas de inyección', 95000.00, 30),
(6, 3, 'Bombillo LED', 'Bombillo LED H7 para moto es una actualización de iluminación diseñada específicamente para motocicletas que utilizan focos de dos pines (base PX26d), común en motos de media y alta cilindrada. Su función principal es reemplazar la luz amarilla halógena d', 28000.00, 80),
(7, 4, 'Espejo Retrovisor', 'Mejora la estética de tu moto. Diseñados con un estilo aerodinámico y moderno, ofrecen un campo de visión amplio minimiza las vibraciones en altas velocidades, garantizando una imagen clara de la vía.', 38000.00, 50),
(8, 4, 'Llanta Delantera', 'Domina los terrenos más difíciles con la Llanta IRC de Taco Alto Rin 24, diseñada especialmente para motocicletas tipo Cross y Enduro de Yamaha (Líneas XTZ y YZ). Ofrece el agarre y la durabilidad que necesitas para superar barro, arena, tierra.', 280000.00, 4),
(9, 3, 'Chaqueta Impermeable FOX', 'La chaqueta impermeable Fox Ranger 2.5L ofrece protección avanzada contra la lluvia y el viento gracias a su tejido técnico de 2.5 capas con membrana 5K/5K y costuras totalmente selladas desde la talla S,M,L hasta LA XXL', 90000.00, 20),
(12, 1, 'Kit de Aceite', 'Este paquete incluye la cantidad exacta de lubricante y los componentes de filtración necesarios para realizar un cambio de aceite completo, garantizando que el corazón de tu moto se mantenga libre de impurezas y con la presión correcta en todo momento.', 85000.00, 30),
(16, 4, 'Producto de prueba API (editado)', 'Actualizado desde Postman con PUT', 55000.00, 12);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `idRol` int(11) NOT NULL,
  `nombreRol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`idRol`, `nombreRol`) VALUES
(1, 'Administrador'),
(4, 'Agente Logistico'),
(2, 'Cliente'),
(3, 'Vendedor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles_usuarios`
--

DROP TABLE IF EXISTS `roles_usuarios`;
CREATE TABLE `roles_usuarios` (
  `idRolesUsuarios` int(11) NOT NULL,
  `idRol` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `roles_usuarios`
--

INSERT INTO `roles_usuarios` (`idRolesUsuarios`, `idRol`, `idUsuario`) VALUES
(1, 1, 1),
(7, 1, 6),
(2, 2, 2),
(3, 2, 3),
(4, 3, 4),
(5, 3, 5),
(6, 4, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `idUsuario` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `apellido` varchar(80) NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'Activo',
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(120) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `ciudad` varchar(80) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idUsuario`, `nombre`, `apellido`, `estado`, `telefono`, `email`, `contrasena`, `ciudad`, `direccion`) VALUES
(1, 'Adriana Marcela', 'Brausin', 'Activo', '3143778055', 'brausin.adriana@fullmoto.com', '$2y$10$n/09rusm5.O3uu9rFwpSfOi0UkPEx9Cfwj/cVL9NumOYc6sW8IM9S', 'Bogota', 'Calle 10 # 20-30'),
(2, 'Andrea', 'Lopez', 'Activo', '3002223344', 'andrea.lopez@fullmoto.com', '$2y$10$n/09rusm5.O3uu9rFwpSfOi0UkPEx9Cfwj/cVL9NumOYc6sW8IM9S', 'Medellin', 'Av. Siempre Viva 123'),
(3, 'Sofia', 'Torres', 'Activo', '3003334455', 'sofia.torres@fullmoto.com', '$2y$10$n/09rusm5.O3uu9rFwpSfOi0UkPEx9Cfwj/cVL9NumOYc6sW8IM9S', 'Cali', 'Vereda Blanca Int 8 # 15-22'),
(4, 'Fernanda', 'Marin', 'Activo', '3004445566', 'fernanda.marin@fullmoto.com', '$2y$10$n/09rusm5.O3uu9rFwpSfOi0UkPEx9Cfwj/cVL9NumOYc6sW8IM9S', 'Bogota', 'Calle 45 # 12-08'),
(5, 'Camilo', 'Diaz', 'Activo', '3005556677', 'camilo.diaz@fullmoto.com', '$2y$10$n/09rusm5.O3uu9rFwpSfOi0UkPEx9Cfwj/cVL9NumOYc6sW8IM9S', 'Manizales', 'Calle 80 # 45-10'),
(6, 'Kenner', 'Londono', 'Activo', '3208116313', 'kenner.londono@fullmoto.com', '$2y$10$n/09rusm5.O3uu9rFwpSfOi0UkPEx9Cfwj/cVL9NumOYc6sW8IM9S', 'Bogota', 'Calle 100 # 15-20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vendedores`
--

DROP TABLE IF EXISTS `vendedores`;
CREATE TABLE `vendedores` (
  `idVendedor` int(11) NOT NULL,
  `nombreComercial` varchar(120) DEFAULT NULL,
  `nombreContacto` varchar(120) DEFAULT NULL,
  `nit_cc` varchar(30) NOT NULL,
  `fechaRegistro` date NOT NULL DEFAULT curdate(),
  `categoria` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `vendedores`
--

INSERT INTO `vendedores` (`idVendedor`, `nombreComercial`, `nombreContacto`, `nit_cc`, `fechaRegistro`, `categoria`) VALUES
(4, 'Taller Fernanda SAS', 'Fernanda Marin', '900654321-2', '2024-05-10', 'Repuestos Genericos'),
(5, 'MotoPartes Camilo', 'Camilo Diaz', '900123456-1', '2024-02-15', 'Repuestos Originales');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta`
--

DROP TABLE IF EXISTS `venta`;
CREATE TABLE `venta` (
  `id_venta` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_vendedor` int(11) NOT NULL,
  `fecha_pedido` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(30) NOT NULL DEFAULT 'Pendiente',
  `total` decimal(12,2) NOT NULL DEFAULT 0.00 CHECK (`total` >= 0),
  `origen` enum('Carrito','Manual') NOT NULL DEFAULT 'Manual'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `venta`
--

INSERT INTO `venta` (`id_venta`, `id_usuario`, `id_vendedor`, `fecha_pedido`, `estado`, `total`, `origen`) VALUES
(2, 3, 5, '2026-05-15 14:00:00', 'Completada', 152000.00, 'Manual'),
(3, 2, 4, '2026-06-01 11:20:00', 'Pendiente', 95000.00, 'Manual'),
(4, 3, 4, '2026-06-10 16:45:00', 'En proceso', 66000.00, 'Manual'),
(5, 3, 4, '2026-09-20 05:24:28', 'Pendiente', 146000.00, 'Carrito'),
(6, 2, 4, '2026-09-20 07:01:42', 'Pendiente', 123000.00, 'Carrito');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `agentelogistico`
--
ALTER TABLE `agentelogistico`
  ADD PRIMARY KEY (`idAgenteLogistico`);

--
-- Indices de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD PRIMARY KEY (`id_calificacion`),
  ADD KEY `idx_calificaciones_pedido` (`id_pedido`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`idCategoria`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `clientefinal`
--
ALTER TABLE `clientefinal`
  ADD PRIMARY KEY (`idClienteFinal`);

--
-- Indices de la tabla `distribucion`
--
ALTER TABLE `distribucion`
  ADD PRIMARY KEY (`id_distribucion`),
  ADD UNIQUE KEY `uq_distribucion_pedido` (`id_pedido`),
  ADD KEY `idx_distribucion_agente` (`id_agente_logistico`),
  ADD KEY `idx_distribucion_estado` (`estado_envio`);

--
-- Indices de la tabla `motos`
--
ALTER TABLE `motos`
  ADD PRIMARY KEY (`id_moto`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `idx_pagos_pedido` (`id_pedido`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `idx_pedido_venta` (`id_venta`),
  ADD KEY `idx_pedido_producto` (`id_producto`);

--
-- Indices de la tabla `productomoto`
--
ALTER TABLE `productomoto`
  ADD PRIMARY KEY (`id_productoMoto`),
  ADD UNIQUE KEY `uq_producto_moto` (`idProductos`,`id_moto`),
  ADD KEY `fk_productomoto_moto` (`id_moto`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `idx_productos_categoria` (`id_categoria`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`idRol`),
  ADD UNIQUE KEY `nombreRol` (`nombreRol`);

--
-- Indices de la tabla `roles_usuarios`
--
ALTER TABLE `roles_usuarios`
  ADD PRIMARY KEY (`idRolesUsuarios`),
  ADD UNIQUE KEY `uq_rol_usuario` (`idRol`,`idUsuario`),
  ADD KEY `fk_rolesusuarios_usuario` (`idUsuario`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idUsuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `vendedores`
--
ALTER TABLE `vendedores`
  ADD PRIMARY KEY (`idVendedor`),
  ADD UNIQUE KEY `nit_cc` (`nit_cc`);

--
-- Indices de la tabla `venta`
--
ALTER TABLE `venta`
  ADD PRIMARY KEY (`id_venta`),
  ADD KEY `idx_venta_usuario` (`id_usuario`),
  ADD KEY `idx_venta_vendedor` (`id_vendedor`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  MODIFY `id_calificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `idCategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `distribucion`
--
ALTER TABLE `distribucion`
  MODIFY `id_distribucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `motos`
--
ALTER TABLE `motos`
  MODIFY `id_moto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `productomoto`
--
ALTER TABLE `productomoto`
  MODIFY `id_productoMoto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `idRol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `roles_usuarios`
--
ALTER TABLE `roles_usuarios`
  MODIFY `idRolesUsuarios` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `venta`
--
ALTER TABLE `venta`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `agentelogistico`
--
ALTER TABLE `agentelogistico`
  ADD CONSTRAINT `fk_agentelogistico_usuario` FOREIGN KEY (`idAgenteLogistico`) REFERENCES `usuario` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD CONSTRAINT `fk_calificaciones_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `clientefinal`
--
ALTER TABLE `clientefinal`
  ADD CONSTRAINT `fk_clientefinal_usuario` FOREIGN KEY (`idClienteFinal`) REFERENCES `usuario` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `distribucion`
--
ALTER TABLE `distribucion`
  ADD CONSTRAINT `fk_distribucion_agente` FOREIGN KEY (`id_agente_logistico`) REFERENCES `agentelogistico` (`idAgenteLogistico`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_distribucion_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_pagos_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `fk_pedido_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pedido_venta` FOREIGN KEY (`id_venta`) REFERENCES `venta` (`id_venta`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `productomoto`
--
ALTER TABLE `productomoto`
  ADD CONSTRAINT `fk_productomoto_moto` FOREIGN KEY (`id_moto`) REFERENCES `motos` (`id_moto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_productomoto_producto` FOREIGN KEY (`idProductos`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`idCategoria`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `roles_usuarios`
--
ALTER TABLE `roles_usuarios`
  ADD CONSTRAINT `fk_rolesusuarios_rol` FOREIGN KEY (`idRol`) REFERENCES `roles` (`idRol`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rolesusuarios_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `vendedores`
--
ALTER TABLE `vendedores`
  ADD CONSTRAINT `fk_vendedores_usuario` FOREIGN KEY (`idVendedor`) REFERENCES `usuario` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `venta`
--
ALTER TABLE `venta`
  ADD CONSTRAINT `fk_venta_cliente` FOREIGN KEY (`id_usuario`) REFERENCES `clientefinal` (`idClienteFinal`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_venta_vendedor` FOREIGN KEY (`id_vendedor`) REFERENCES `vendedores` (`idVendedor`) ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

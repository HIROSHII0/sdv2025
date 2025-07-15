-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-07-2025 a las 04:17:15
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ventas2025`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `comprador_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `fecha_compra` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(50) NOT NULL DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datos_usuarios`
--

CREATE TABLE `datos_usuarios` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `telefono_movil` varchar(20) DEFAULT NULL,
  `telefono_casa` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(20) DEFAULT NULL,
  `genero` enum('Masculino','Femenino','Otro') DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `estado_civil` enum('Soltero','Casado','Divorciado','Viudo','Otro') DEFAULT NULL,
  `tipo_documento` enum('Cédula','Pasaporte','Licencia','Otro') DEFAULT NULL,
  `numero_documento` varchar(50) DEFAULT NULL,
  `ocupacion` varchar(100) DEFAULT NULL,
  `empresa` varchar(100) DEFAULT NULL,
  `nivel_educativo` enum('Primaria','Secundaria','Técnico','Universitario','Postgrado') DEFAULT NULL,
  `biografia` text DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `leida` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicaciones`
--

CREATE TABLE `publicaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` enum('producto','mascota','servicio') NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `detalles` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `edad` varchar(50) DEFAULT NULL,
  `raza` varchar(100) DEFAULT NULL,
  `tamano` varchar(50) DEFAULT NULL,
  `vacunado` tinyint(1) DEFAULT NULL,
  `desparasitado` tinyint(1) DEFAULT NULL,
  `duracion` varchar(50) DEFAULT NULL,
  `lugar` varchar(255) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `precio_producto` decimal(10,2) DEFAULT NULL,
  `estado_producto` tinyint(1) DEFAULT 1,
  `categoria_producto` varchar(100) DEFAULT NULL,
  `fecha_publicacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `visible` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `publicaciones`
--

INSERT INTO `publicaciones` (`id`, `usuario_id`, `tipo`, `titulo`, `descripcion`, `detalles`, `imagen`, `edad`, `raza`, `tamano`, `vacunado`, `desparasitado`, `duracion`, `lugar`, `precio`, `telefono`, `precio_producto`, `estado_producto`, `categoria_producto`, `fecha_publicacion`, `fecha_actualizacion`, `visible`) VALUES
(189, 1, 'producto', 'Ropa para mujer', 'Conjuntos modernos, vestidos y blusas para toda ocasión.', NULL, 'producto 1.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1200.00, NULL, 52.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:42:12', 1),
(190, 1, 'producto', 'Suéter para Hombre.', 'Prenda abrigadora, ideal para climas frescos o casuales.', NULL, 'producto 2.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 850.00, NULL, 55.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:40:45', 1),
(191, 1, 'producto', 'Tennis para hombre.', 'Calzado deportivo moderno y cómodo para el uso diario.', NULL, 'producto 3.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2200.00, NULL, 57.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:39:23', 1),
(192, 1, 'producto', 'Ropa para bebé', 'Ropa suave y cómoda para recién nacidos y bebés de hasta 2 años.', NULL, 'producto 4.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 550.00, NULL, 60.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:37:58', 1),
(193, 1, 'producto', 'Maquillaje', 'Kits de maquillaje completos o individuales para todo tipo de piel.', NULL, 'producto 5.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1000.00, NULL, 62.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:36:40', 1),
(194, 1, 'producto', 'Lámpara LED decorativa.', 'Lámpara LED con diseños creativos (luna, estrellas, nombres, figuras). Perfecta para habitaciones, escritorios o como regalo personalizado. Funciona con batería recargable o conexión USB.', NULL, 'producto 6.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 950.00, NULL, 65.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:46:04', 1),
(195, 1, 'producto', 'Artículos para el Pelo', 'Lazos, peinetas, ganchos y diademas de diferentes estilos.', NULL, 'producto 7.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 200.00, NULL, 67.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:35:51', 1),
(196, 1, 'producto', 'Aretes Personalizados', 'Aretes únicos, elaborados a mano con diseños personalizados, nombres o iniciales.', NULL, 'producto 8.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 180.00, NULL, 70.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:34:32', 1),
(197, 1, 'producto', 'Pulseras Personalizadas.', 'Pulseras hechas a mano con nombres, frases o colores especiales.', NULL, 'producto 9.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 150.00, NULL, 72.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:32:34', 1),
(198, 1, 'producto', 'Velas artesanales con fragancias relajantes.', 'Velas artesanales con fragancias relajantes.', NULL, 'producto 10.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 200.00, NULL, 75.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:31:29', 1),
(199, 1, 'producto', 'Colchones.', 'Colchones cómodos de espuma o resorte para un buen descanso.', NULL, 'producto 11.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6500.00, NULL, 77.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:30:18', 1),
(200, 1, 'producto', 'Muebles para el hogar.', 'Sofás, mesas, estanterías y otros para sala y comedo', NULL, 'producto 12.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8000.00, NULL, 80.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:29:05', 1),
(201, 1, 'producto', 'Funda para Celulares', 'Fundas resistentes y personalizadas para distintos modelos.', NULL, 'producto 13.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 300.00, NULL, 82.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:25:35', 1),
(202, 1, 'producto', 'AirPods.', 'Audífonos inalámbricos de alta calidad y duración.', NULL, 'producto 14.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4500.00, NULL, 85.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:24:41', 1),
(203, 1, 'producto', 'Cargadores Portátiles', 'Power banks para mantener tus dispositivos con energía.', NULL, 'producto 15.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 900.00, NULL, 87.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:23:19', 1),
(204, 1, 'producto', 'Computadoras', 'Laptops y PCs de alto rendimiento para trabajo o estudio.', NULL, 'producto 16.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 25000.00, NULL, 90.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:22:20', 1),
(205, 1, 'producto', 'Artículos de Decoración', 'Figuras, jarrones, luces LED y más para ambientar tu espacio.', NULL, 'producto 17.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 600.00, NULL, 92.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:21:15', 1),
(206, 1, 'producto', 'Carteras para Mujer', 'Bolsos modernos con amplio espacio y estilos variados.', NULL, 'producto 18.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1100.00, NULL, 95.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:20:09', 1),
(207, 1, 'producto', 'Relojes de Hombre', 'Relojes elegantes y casuales para cualquier ocasión.', NULL, 'producto 19.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1200.00, NULL, 97.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:19:02', 1),
(208, 1, 'producto', 'Lentes de Sol de Mujer', 'Accesorio moderno y funcional con protección UV.', NULL, 'producto 20.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 800.00, NULL, 100.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:17:51', 1),
(209, 1, 'producto', 'Libretas Personalizadas', 'Cuadernos únicos con portadas diseñadas al gusto del cliente.', NULL, 'producto 21.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 250.00, NULL, 102.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:16:48', 1),
(210, 1, 'producto', 'Termos Personalizados', 'Termos térmicos decorados con vinil o sublimados.', NULL, 'producto 22.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 700.00, NULL, 105.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:15:37', 1),
(211, 1, 'producto', 'Kit de pintura.', 'Incluye pinceles, témperas, paleta y lienzo para artistas.', NULL, 'producto 23.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 450.00, NULL, 107.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:14:35', 1),
(212, 1, 'producto', 'Materiales Desechables.', 'Platos, vasos, cubiertos y servilletas ideales para eventos.', NULL, 'producto 24.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 250.00, NULL, 110.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:13:24', 1),
(213, 1, 'producto', 'Ropa Interior Femenina', 'Conjuntos cómodos y elegantes para uso diario.', NULL, 'producto 25.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 600.00, NULL, 112.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:10:51', 1),
(214, 1, 'producto', 'Productos para Cocina', 'Utensilios prácticos como espátulas, cucharones, moldes y más.', NULL, 'producto 26.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 750.00, NULL, 115.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:09:39', 1),
(215, 1, 'producto', 'Stickers Decorativos', 'Adhesivos creativos para decorar cuadernos, laptops o paredes.', NULL, 'producto 27.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 100.00, NULL, 117.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:08:40', 1),
(216, 1, 'producto', 'Tazas Personalizadas.', 'Tazas con frases, nombres o imágenes al gusto del cliente.', NULL, 'producto 28.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 350.00, NULL, 120.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:07:47', 1),
(217, 1, 'producto', 'Tennis Personalizados.', 'Calzado con diseños únicos y personalizados a mano.', NULL, 'producto 29.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2500.00, NULL, 122.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:06:53', 1),
(218, 1, 'producto', 'Cuadros Decorativos.', 'Cuadros artísticos para embellecer tu hogar u oficina.', NULL, 'producto 30.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 850.00, NULL, 125.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 16:05:30', 1),
(219, 1, 'mascota', 'Perro.', 'Perro activo y protector. Ideal para vigilancia y compañía.', NULL, 'mascota 1.jfif', '3', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 52.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:47:58', 1),
(220, 1, 'mascota', 'Gato.', 'Gato juguetón, tranquilo y adaptado a interiores. Independiente.', NULL, 'mascota 2.jfif', '2', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 55.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:46:26', 1),
(221, 1, 'mascota', 'Conejo.', 'Conejo amistoso, ideal como compañero de niños. Fácil de cuidar.', NULL, 'mascota 3.jfif', '10', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 57.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:44:44', 1),
(222, 1, 'mascota', 'Tortuga.', 'Reptil tranquilo de larga vida. Requiere agua limpia y alimentación vegetal.', NULL, 'mascota 4.jfif', '3', '0', NULL, 0, 1, NULL, NULL, NULL, NULL, 60.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:43:26', 1),
(223, 1, 'mascota', 'Loro.', 'Ave tropical de colores vivos. Muy social y hablador.', NULL, 'mascota 5.jfif', '2', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 62.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:42:06', 1),
(224, 1, 'mascota', 'Hámster.', 'Roedor pequeño, ideal para niños. Muy activo en las noches.', NULL, 'mascota 6.jfif', '4', '0', NULL, 0, 1, NULL, NULL, NULL, NULL, 65.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:40:43', 1),
(225, 1, 'mascota', 'Zarigüeya', 'Zarigüeya', NULL, 'mascota 7.jfif', '1', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 67.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:39:08', 1),
(226, 1, 'mascota', 'Erizo.', 'Mamífero exótico de hábitos nocturnos. Requiere espacio limpio y tranquilo.', NULL, 'mascota 8.jfif', '1', '0', NULL, 0, 1, NULL, NULL, NULL, NULL, 70.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:37:38', 1),
(227, 1, 'mascota', 'Camaleón.', 'Reptil que cambia de color. Requiere ambiente cálido y cuidados especiales.', NULL, 'mascota 9.jfif', '5', '0', NULL, 0, 1, NULL, NULL, NULL, NULL, 72.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:36:25', 1),
(228, 1, 'mascota', 'Pez Beta.', 'Pez colorido y territorial. Ideal para acuarios pequeños.', NULL, 'mascota 10.jfif', '6', '0', NULL, 0, 0, NULL, NULL, NULL, NULL, 75.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:34:25', 1),
(229, 1, 'mascota', 'Pez Dorado.', 'Pez ornamental para pecera. Requiere oxigenación y agua limpia.', NULL, 'mascota 11.jfif', '5', '0', NULL, 0, 0, NULL, NULL, NULL, NULL, 77.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:33:12', 1),
(230, 1, 'mascota', 'Gato.', 'Mascota independiente, limpia y cariñosa. Ideal para departamentos.', NULL, 'mascota 12.jfif', '1', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 80.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:31:32', 1),
(231, 1, 'mascota', 'Perro.', 'Compañero fiel y juguetón. Ideal para hogares con niños o espacios abiertos.', NULL, 'mascota 13.jfif', '2', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 82.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:30:17', 1),
(232, 1, 'mascota', 'Conejo.', 'Animal tierno y tranquilo. Ideal como primera mascota.', NULL, 'mascota 14.jfif', '1', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 85.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:28:28', 1),
(233, 1, 'mascota', 'Aves Pequeñas.', 'Grupo de aves ornamentales como periquitos y canarios. Muy activas y alegres.', NULL, 'mascota 15.jfif', '4', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 87.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:27:19', 1),
(234, 1, 'mascota', 'Cotorra.', 'Ave tropical muy inteligente. Capaz de imitar palabras y sonidos.', NULL, 'mascota 16.jfif', '1', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 90.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:25:10', 1),
(235, 1, 'mascota', 'Camaleón.', 'Reptil que cambia de color según el ambiente. Muy tranquilo y visualmente atractivo.', NULL, 'mascota 17.jfif', '8', '0', NULL, 0, 1, NULL, NULL, NULL, NULL, 92.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:23:43', 1),
(236, 1, 'mascota', 'Tarántula.', 'Araña ornamental para terrario. De comportamiento tranquilo y bajo mantenimiento.', NULL, 'mascota 18.jfif', '1', '0', NULL, 0, 0, NULL, NULL, NULL, NULL, 95.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:22:20', 1),
(237, 1, 'mascota', 'Serpiente.', 'Reptil exótico no venenoso. Tranquilo y fácil de manejar con experiencia.', NULL, 'mascota 19.jfif', '2', '0', NULL, 0, 1, NULL, NULL, NULL, NULL, 97.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:20:14', 1),
(238, 1, 'mascota', 'Rana.', 'Anfibio silencioso y fácil de cuidar. Ideal para acuarios o terrarios húmedos.', NULL, 'mascota 20.jfif', '6', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 100.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:18:55', 1),
(239, 1, 'mascota', 'Cerdo.', 'Mamífero dócil e inteligente. Muy sociable y limpio.', NULL, 'mascota 21.jfif', '1', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 102.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:17:42', 1),
(240, 1, 'mascota', 'Ave.', 'Ave pequeña, activa y cantarina. Ideal para jaulas amplias.', NULL, 'mascota 22.jfif', '6', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 105.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:15:56', 1),
(241, 1, 'mascota', 'Cabra.', 'Animal de campo muy noble. Requiere espacio y alimentación balanceada.', NULL, 'mascota 23.jfif', '2', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 107.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:14:41', 1),
(242, 1, 'mascota', 'Pato.', 'Animal amigable y social. Ideal para ambientes húmedos o con estanques.', NULL, 'mascota 24.jfif', '8', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 110.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:09:11', 1),
(243, 1, 'mascota', 'Gallo.', 'Ave fuerte y territorial. Ideal para crianzas rurales o decorativas.', NULL, 'mascota 25.jfif', '1', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 112.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:07:31', 1),
(244, 1, 'mascota', 'Cisne.', 'Ave elegante y tranquila. Perfecta para lagos o estanques amplios.', NULL, 'mascota 26.jfif', '1', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 115.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:06:09', 1),
(245, 1, 'mascota', 'Zorro.', 'Mamífero inteligente y silencioso. Ideal para ambientes tranquilos y cerrados.', NULL, 'mascota 27.jfif', '2', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 117.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:04:05', 1),
(246, 1, 'mascota', 'Mono.', 'Animal juguetón y afectivo. Requiere atención constante y espacio para moverse.', NULL, 'mascota 28.jfif', '3', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 120.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 15:01:35', 1),
(247, 1, 'mascota', 'Pavo Real.', 'Ave ornamental de gran belleza, sociable y tranquila. Ideal para jardines espaciosos.', NULL, 'mascota 29.jfif', '2', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 122.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 14:59:17', 1),
(248, 1, 'mascota', 'Mapache', 'Mamífero nocturno, inteligente y curioso. Muy juguetón, ideal para cuidadores responsables.', NULL, 'mascota 30.jfif', '1', '0', NULL, 1, 1, NULL, NULL, NULL, NULL, 125.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 14:56:50', 1),
(249, 1, 'servicio', 'Barbería a Domicilio o en Local.', 'Corte, perfilado, afeitado, tintado y limpieza facial. Servicio moderno y personalizado.', NULL, 'servicio 1.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 300.00, NULL, 52.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 14:40:15', 1),
(250, 1, 'servicio', 'Manicura.', 'Diseño de uñas.', NULL, 'servicio 2.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1000.00, NULL, 55.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 14:38:45', 1),
(251, 1, 'servicio', 'Servicio de Delivery Rápido y Seguro', 'Entrega de paquetes, alimentos, productos o documentos en todo Santo Domingo.', NULL, 'servicio 3.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 150.00, NULL, 57.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 14:36:46', 1),
(252, 1, 'servicio', 'Limpieza General de Casas y Apartamentos.', 'Limpieza profunda de baños, cocina, habitaciones, ventanas y más. Traemos productos o usamos los del cliente.', NULL, 'servicio 4.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1000.00, NULL, 60.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 14:34:25', 1),
(253, 1, 'servicio', 'Paseador de Perros Responsable y Amoroso.', 'Caminatas seguras y con cariño, para liberar energía y mejorar el bienestar del perro. Fotos incluidas.', NULL, 'servicio 5.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 250.00, NULL, 62.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 14:32:43', 1),
(254, 1, 'servicio', 'Sesiones Fotográficas Personales.', 'Retratos profesionales para redes, eventos, CV o marcas personales. Fondos neutros o naturales.', NULL, 'servicio 6.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 800.00, NULL, 65.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 14:30:47', 1),
(255, 1, 'servicio', 'Clases Virtuales Personalizadas.', 'Ayuda en materias escolares, técnicas digitales, habilidades personales, entre otras. Modalidad flexible.', NULL, 'servicio 7.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 300.00, NULL, 67.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 14:29:12', 1),
(256, 1, 'servicio', 'Reparación de Celulares y Tablets.', 'Cambios de pantalla, batería, software, botones y cámaras. Servicio rápido y confiable.', NULL, 'servicio 8.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 600.00, NULL, 70.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 14:27:13', 1),
(257, 1, 'servicio', 'Lavado de Carros a Domicilio.', 'Lavado exterior e interior, abrillantado, limpieza profunda de asientos y motor (opcional).', NULL, 'servicio 9.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 500.00, NULL, 72.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 14:25:38', 1),
(258, 1, 'servicio', 'Entrenamiento Personal (Fitness)', 'Sesiones personalizadas de ejercicios físicos, rutinas de pérdida de peso, tonificación y fuerza.', NULL, 'servicio 10.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 700.00, NULL, 75.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 14:45:38', 1),
(259, 1, 'servicio', 'Servicio de Costura y Ajustes de Ropa', 'Arreglos de ropa, creación de piezas nuevas, bordados y personalización. Trabajo con calidad y entrega puntual.', NULL, 'servicio 11.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 250.00, NULL, 77.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 14:24:17', 1),
(260, 1, 'servicio', 'Servicio Técnico de Computadoras.', 'Formateo, instalación de programas, limpieza interna, cambio de piezas y reparación de fallos.', NULL, 'servicio 12.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 800.00, NULL, 80.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 14:22:06', 1),
(261, 1, 'servicio', 'Inglés Virtual Personalizado.', 'Clases en vivo por Zoom o WhatsApp, enfocadas en gramática, vocabulario, pronunciación y conversación.', NULL, 'servicio 13.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 400.00, NULL, 82.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 14:20:22', 1),
(262, 1, 'servicio', 'Servicios Eléctricos Generales', 'Instalaciones, cambio de breakers, luces, tomacorrientes, detección de fallas y mantenimiento general.', NULL, 'servicio 14.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 800.00, NULL, 85.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 03:32:30', 1),
(263, 1, 'servicio', 'Reparación de Neveras y Freezers', 'Solución a fallas de motor, gas, termostato y congelamiento. Diagnóstico sin compromiso.', NULL, 'servicio 15.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1000.00, NULL, 87.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 03:30:43', 1),
(264, 1, 'servicio', 'Clases de Apoyo Escolar para Niños', 'Refuerzo en lectura, matemáticas, ciencias y tareas escolares. Atención personalizada para mejorar el rendimiento.', NULL, 'servicio 16.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 300.00, NULL, 90.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 03:29:08', 1),
(265, 1, 'servicio', 'Servicio de Albañilería y Remodelación', 'Reparaciones, construcción de paredes, pisos, baños, pintura y terminaciones. Trabajo profesional y con garantía.', NULL, 'servicio 17.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1200.00, NULL, 92.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 03:27:12', 1),
(266, 1, 'servicio', 'Decoración Creativa para Todo Tipo de Eventos.', 'Cumpleaños, baby showers, bodas, fiestas temáticas. Se personalizan colores, estilos y detalles según el cliente.', NULL, 'servicio 18.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2500.00, NULL, 95.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 02:34:55', 1),
(267, 1, 'servicio', 'Chef Personal para Eventos y Comidas Caseras.', 'Preparación de platos a pedido, cocina criolla, menús especiales, eventos pequeños y atención personalizada.', NULL, 'servicio 19.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1500.00, NULL, 97.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 02:32:04', 1),
(268, 1, 'servicio', 'Servicio de Niñera Responsable y Cariñosa.', 'Cuidado de niños en casa, apoyo con tareas, alimentación y entretenimiento. Experiencia comprobada.', NULL, 'servicio 20.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1000.00, NULL, 100.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 02:30:37', 1),
(269, 1, 'servicio', 'Clases Particulares de Guitarra para Principiantes.', 'Aprende desde cero acordes, ritmo y técnica con clases dinámicas adaptadas a tu ritmo.', NULL, 'servicio 21.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 400.00, NULL, 102.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 02:28:18', 1),
(270, 1, 'servicio', 'Peluquería Canina a Domicilio.', 'Corte, baño, limpieza de oídos, uñas y perfumado. Tratamiento con productos especiales para mascotas.', NULL, 'servicio 22.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 600.00, NULL, 105.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 02:25:22', 1),
(271, 1, 'servicio', 'Creación de Currículum Vitae (CV) Profesional', 'Elaboración y diseño de currículums modernos, personalizados según el perfil del cliente y el tipo de empleo que busca.', NULL, 'servicio 23.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 500.00, NULL, 107.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 14:48:12', 1),
(272, 1, 'servicio', 'Masajes Relajantes y Terapéuticos', 'Sesiones de masajes para aliviar estrés, dolores musculares o mejorar circulación. Servicio profesional, cómodo y seguro.', NULL, 'servicio 24.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1000.00, NULL, 110.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 02:23:48', 1),
(273, 1, 'servicio', 'Servicios de Jardinería y Mantenimiento de Áreas Verdes', 'Corte de césped, diseño de jardines, siembra de plantas, limpieza de patios y mantenimiento general de exteriores.', NULL, 'servicio 25.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 500.00, NULL, 112.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 02:26:43', 1),
(274, 1, 'servicio', 'Reparación y Mantenimiento de Cámaras Fotográficas.', 'Solucionamos problemas técnicos de cámaras digitales y réflex: enfoque, lentes, pantallas o batería. Incluye diagnóstico y limpieza general.', NULL, 'servicio 26.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1200.00, NULL, 115.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 02:26:05', 1),
(276, 1, 'servicio', 'Diseño y Gestión de Publicidad Digital.', 'Creamos piezas publicitarias efectivas para redes sociales, catálogos, promociones y campañas locales. Ideal para negocios pequeños y emprendedores que necesitan presencia visual atractiva.', NULL, 'servicio 28.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 700.00, NULL, 120.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 02:17:51', 1),
(277, 1, 'servicio', 'Fotografía Profesional de productos y objetos.', 'Servicio especializado en capturar imágenes de alta calidad para catálogos, tiendas online, redes sociales u publicidad. Fotofrafías limpias,con buena iluminación y fondo neutro o creativo, ideal para destacar las características de tus productos. No incluye sesiones con personas.', NULL, 'servicio 29.jfif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 500.00, NULL, 122.50, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 02:12:41', 1),
(278, 1, 'servicio', 'Reparación de electromésticos a domicilio.', 'Servicio técnico especializado en reparación de neveras, lavadoras, abanicos, microondas, estufas y otros electrodomésticos. Atendemos a domicilio en toda el área metropolitana. Garantía en cada reparación, diagnóstico gratuito y trato confiable.', NULL, 'servicio 30.jfif', NULL, NULL, NULL, NULL, NULL, '2 horas', 'tu casa', 800.00, NULL, 125.00, 1, NULL, '2025-07-13 16:18:26', '2025-07-14 21:46:32', 0),
(279, 1, 'producto', 'Producto oculto', 'Este producto está oculto', NULL, 'producto_oculto.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 50.00, NULL, 50.00, 1, NULL, '2025-07-13 14:56:38', '2025-07-13 10:56:38', 0),
(280, 5, 'mascota', 'pez dorado', 'pez dorado grande ', NULL, 'pub_5_1752540139.jpg', '4', 'violeta', 'mediano', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-15 00:42:19', '2025-07-14 20:42:19', 1),
(281, 5, 'servicio', 'latas', 'latas ', NULL, 'pub_5_1752540303.jpeg', NULL, NULL, NULL, NULL, NULL, '6', 'tu casa', 10000.00, NULL, NULL, NULL, NULL, '2025-07-15 00:45:03', '2025-07-14 20:45:03', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tokens`
--

CREATE TABLE `tokens` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiracion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_completo`, `correo`, `nombre_usuario`, `contrasena`, `fecha_registro`) VALUES
(1, 'manu', 'larompe@gmail.com', 'Manu68', '$2y$10$77/8J6NgyYbrsEWukGZzPOhvyvAyEsiWd3vrX9YP6.bNDfQAaVfuq', '2025-06-17 23:08:26');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `datos_usuarios`
--
ALTER TABLE `datos_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_tipo` (`tipo`);

--
-- Indices de la tabla `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `datos_usuarios`
--
ALTER TABLE `datos_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=283;

--
-- AUTO_INCREMENT de la tabla `tokens`
--
ALTER TABLE `tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `datos_usuarios`
--
ALTER TABLE `datos_usuarios`
  ADD CONSTRAINT `datos_usuarios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `datos_usuarios_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tokens`
--
ALTER TABLE `tokens`
  ADD CONSTRAINT `tokens_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

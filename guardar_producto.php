<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rolActivo = $_SESSION['usuario']['rolActivo'] ?? null;

if ($rolActivo !== 'Vendedor' && $rolActivo !== 'Administrador') {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/CLASES/Conexion.php';
require_once __DIR__ . '/CLASES/Producto.php';

use FullMoto\Conexion;
use FullMoto\Producto;

// Este archivo NUNCA se abre directamente en el navegador; solo recibe
// los datos que envía formulario.php mediante POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$pdo = Conexion::obtenerConexion();

// Recogemos y limpiamos los datos del formulario
$idProducto  = isset($_POST['id_producto']) ? (int) $_POST['id_producto'] : 0;
$idCategoria = (int) $_POST['id_categoria'];
$nombre      = trim($_POST['nombre']);
$descripcion = trim($_POST['descripcion']);
$precio      = (float) $_POST['precio'];
$stock       = (int) $_POST['stock'];

// Validación mínima del lado del servidor (el navegador ya valida los "required",
// pero nunca hay que confiar solo en eso)
if ($nombre === '' || $idCategoria <= 0 || $precio < 0 || $stock < 0) {
    die("Datos inválidos. <a href='javascript:history.back()'>Volver</a>");
}

if ($idProducto > 0) {
    // Viene un id_producto -> es una EDICIÓN
    Producto::actualizar($pdo, $idProducto, $idCategoria, $nombre, $descripcion, $precio, $stock);
    $idFinal = $idProducto;
} else {
    // No viene id_producto -> es un producto NUEVO
    $idFinal = Producto::crear($pdo, $idCategoria, $nombre, $descripcion, $precio, $stock);
}

// Redirigimos al detalle del producto recién creado/editado
header('Location: detalle.php?id=' . $idFinal);
exit;

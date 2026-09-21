<?php
namespace FullMoto;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rolActivo = $_SESSION['usuario']['rolActivo'] ?? null;

if ($rolActivo !== 'Vendedor' && $rolActivo !== 'Administrador') {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../CLASES/Conexion.php';
require_once __DIR__ . '/../CLASES/Producto.php';

$idProducto = (int) ($_GET['id'] ?? 0);

if ($idProducto > 0) {
    $pdo = Conexion::obtenerConexion();
    Producto::eliminar($pdo, $idProducto);
}

header('Location: ../index.php');
exit;
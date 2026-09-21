<?php
namespace FullMoto;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rolActivo = $_SESSION['usuario']['rolActivo'] ?? null;

if ($rolActivo !== 'Cliente') {
    header('Location: ../index.php');
    exit;
}

if (empty($_SESSION['carrito'])) {
    header('Location: ../carrito.php');
    exit;
}

require_once __DIR__ . '/../CLASES/Conexion.php';
require_once __DIR__ . '/../CLASES/Producto.php';
require_once __DIR__ . '/../CLASES/Venta.php';
require_once __DIR__ . '/../CLASES/Pedido.php';

use PDO;
use PDOException;

$pdo = Conexion::obtenerConexion();
$idUsuario = $_SESSION['usuario']['idUsuario'];
$carrito = $_SESSION['carrito'];

try {
    $pdo->beginTransaction();

    $idVendedor = Venta::obtenerVendedorPorDefecto($pdo);

    // Calculamos el total real usando el precio actual de cada producto
    $total = 0;
    $lineas = [];
    foreach ($carrito as $idProducto => $cantidad) {
        $producto = Producto::obtenerPorId($pdo, $idProducto);
        if ($producto === null) {
            continue;
        }
        $subtotal = $producto->getPrecio() * $cantidad;
        $total += $subtotal;
        $lineas[] = ['idProducto' => $idProducto, 'cantidad' => $cantidad, 'precio' => $producto->getPrecio()];
    }

    $idVenta = Venta::crear($pdo, $idUsuario, $idVendedor, date('Y-m-d H:i:s'), 'Pendiente', $total, 'Carrito');

    foreach ($lineas as $linea) {
        Pedido::crear($pdo, $idVenta, $linea['idProducto'], $linea['cantidad'], $linea['precio']);
    }

    $pdo->commit();

    unset($_SESSION['carrito']);

    header('Location: ../index.php?compra=exitosa');
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    die('Error al procesar la compra: ' . $e->getMessage());
}
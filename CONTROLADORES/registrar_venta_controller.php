<?php
namespace FullMoto;

require_once __DIR__ . '/../CLASES/Conexion.php';
require_once __DIR__ . '/../CLASES/Venta.php';
require_once __DIR__ . '/../CLASES/Pedido.php';

use PDO;
use PDOException;

// Solo procesar si el formulario llegó por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../registrar_venta.php');
    exit;
}

// Datos que llegan del formulario
$idUsuario  = (int) ($_POST['id_usuario'] ?? 0);
$idVendedor = (int) ($_POST['id_vendedor'] ?? 0);
$idProducto = (int) ($_POST['id_producto'] ?? 0);
$cantidad   = (int) ($_POST['cantidad'] ?? 0);
$precio     = (float) ($_POST['precio'] ?? 0);
$fecha      = $_POST['fecha'] ?? date('Y-m-d H:i:s');

// Validación mínima antes de tocar la base de datos
if ($idUsuario <= 0 || $idVendedor <= 0 || $idProducto <= 0 || $cantidad <= 0 || $precio <= 0) {
    die('Datos incompletos o inválidos para registrar la venta.');
}

$pdo = Conexion::obtenerConexion();

try {
    $pdo->beginTransaction();

    $total = $cantidad * $precio;

    // 1. Crear el encabezado de la venta
    $idVenta = Venta::crear($pdo, $idUsuario, $idVendedor, $fecha, 'Pendiente', $total);

    // 2. Crear el pedido (línea de producto) asociado a esa venta
    Pedido::crear($pdo, $idVenta, $idProducto, $cantidad, $precio);

    $pdo->commit();

    header('Location: ../index.php?venta=exitosa');
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    die('Error al registrar la venta: ' . $e->getMessage());
}
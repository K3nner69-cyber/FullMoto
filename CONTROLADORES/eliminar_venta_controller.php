<?php
namespace FullMoto;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rolActivo = $_SESSION['usuario']['rolActivo'] ?? null;

if ($rolActivo !== 'Administrador') {
    header('Location: ../gestion_comercializacion.php');
    exit;
}

require_once __DIR__ . '/../CLASES/Conexion.php';
require_once __DIR__ . '/../CLASES/Venta.php';

$idVenta = (int) ($_GET['id'] ?? 0);

if ($idVenta > 0) {
    $pdo = Conexion::obtenerConexion();
    Venta::eliminar($pdo, $idVenta);
}

header('Location: ../gestion_comercializacion.php');
exit;
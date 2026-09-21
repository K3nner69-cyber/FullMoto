<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rolActivo = $_SESSION['usuario']['rolActivo'] ?? null;

if ($rolActivo !== 'Cliente') {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$idProducto = (int) ($_POST['id_producto'] ?? 0);
$cantidad   = (int) ($_POST['cantidad'] ?? 1);

if ($idProducto <= 0 || $cantidad <= 0) {
    header('Location: ../index.php');
    exit;
}

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if (isset($_SESSION['carrito'][$idProducto])) {
    $_SESSION['carrito'][$idProducto] += $cantidad;
} else {
    $_SESSION['carrito'][$idProducto] = $cantidad;
}

header('Location: ../index.php');
exit;
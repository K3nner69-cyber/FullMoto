<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$idProducto = (int) ($_GET['id'] ?? 0);

if (isset($_SESSION['carrito'][$idProducto])) {
    unset($_SESSION['carrito'][$idProducto]);
}

header('Location: ../carrito.php');
exit;
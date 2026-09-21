<?php
namespace FullMoto;

session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.php');
    exit;
}

$rolElegido = $_POST['rol'] ?? '';

// Seguridad: solo permite cambiar a un rol que el usuario realmente tenga
if (in_array($rolElegido, $_SESSION['usuario']['roles'], true)) {
    $_SESSION['usuario']['rolActivo'] = $rolElegido;
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
exit;
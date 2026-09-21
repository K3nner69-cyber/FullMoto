<?php
namespace FullMoto;

session_start();

require_once __DIR__ . '/../CLASES/Conexion.php';
require_once __DIR__ . '/../CLASES/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$email    = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$pdo = Conexion::obtenerConexion();
$usuario = Usuario::validarCredenciales($pdo, $email, $password);

if ($usuario === null) {
    header('Location: ../login.php?error=1');
    exit;
}

// Guardamos los datos del usuario en la sesión
$_SESSION['usuario'] = $usuario;

header('Location: ../index.php');
exit;
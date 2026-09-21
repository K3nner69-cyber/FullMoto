<?php
namespace FullMoto;

session_start();

require_once __DIR__ . '/../CLASES/Conexion.php';
require_once __DIR__ . '/../CLASES/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../registro.php');
    exit;
}

$nombre    = trim($_POST['nombre'] ?? '');
$apellido  = trim($_POST['apellido'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';
$confirmar = $_POST['confirmar'] ?? '';

if ($nombre === '' || $apellido === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    header('Location: ../registro.php?error=campos');
    exit;
}

if (strlen($password) < 6) {
    header('Location: ../registro.php?error=clave_corta');
    exit;
}

if ($password !== $confirmar) {
    header('Location: ../registro.php?error=clave_no_coincide');
    exit;
}

$pdo = Conexion::obtenerConexion();

if (Usuario::emailExiste($pdo, $email)) {
    header('Location: ../registro.php?error=email_existe');
    exit;
}

try {
    Usuario::registrarCliente($pdo, $nombre, $apellido, $email, $password);
} catch (\Throwable $e) {
    error_log($e->getMessage());
    header('Location: ../registro.php?error=bd');
    exit;
}

header('Location: ../login.php?registro=ok');
exit;
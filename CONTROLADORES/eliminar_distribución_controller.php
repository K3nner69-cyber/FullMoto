<?php
namespace FullMoto;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rolActivo = $_SESSION['usuario']['rolActivo'] ?? null;

if ($rolActivo !== 'Agente Logistico' && $rolActivo !== 'Administrador') {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../CLASES/Conexion.php';
require_once __DIR__ . '/../CLASES/Distribucion.php';

$idDistribucion = (int) ($_GET['id'] ?? 0);

if ($idDistribucion > 0) {
    $pdo = Conexion::obtenerConexion();
    Distribucion::eliminar($pdo, $idDistribucion);
}

header('Location: ../logistica_distribucion.php');
exit;
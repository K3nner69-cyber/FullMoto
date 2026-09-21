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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../CLASES/Conexion.php';
require_once __DIR__ . '/../CLASES/Distribucion.php';

$pdo = Conexion::obtenerConexion();

$idDistribucion = isset($_POST['id_distribucion']) ? (int) $_POST['id_distribucion'] : 0;
$idPedido       = (int) $_POST['id_pedido'];
$idAgente       = (int) $_POST['id_agente_logistico'];
$fechaAsig      = str_replace('T', ' ', $_POST['fecha_asignacion']) . ':00';
$fechaEst       = $_POST['fecha_entrega_estimada'];
$fechaReal      = !empty($_POST['fecha_entrega_real']) ? str_replace('T', ' ', $_POST['fecha_entrega_real']) . ':00' : null;
$estado         = $_POST['estado_envio'];
$direccion      = trim($_POST['direccion_entrega']);
$observaciones  = trim($_POST['observaciones']) ?: null;

if ($idPedido <= 0 || $idAgente <= 0 || $direccion === '') {
    die("Datos inválidos. <a href='javascript:history.back()'>Volver</a>");
}

if ($idDistribucion > 0) {
    Distribucion::actualizar($pdo, $idDistribucion, $idPedido, $idAgente, $fechaAsig, $fechaEst, $fechaReal, $estado, $direccion, $observaciones);
} else {
    Distribucion::crear($pdo, $idPedido, $idAgente, $fechaAsig, $fechaEst, $fechaReal, $estado, $direccion, $observaciones);
}

header('Location: ../logistica_distribucion.php');
exit;
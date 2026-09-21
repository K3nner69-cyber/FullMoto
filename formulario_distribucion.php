<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rolActivo = $_SESSION['usuario']['rolActivo'] ?? null;

if ($rolActivo !== 'Agente Logistico' && $rolActivo !== 'Administrador') {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/CLASES/Conexion.php';
require_once __DIR__ . '/CLASES/Distribucion.php';

use FullMoto\Conexion;
use FullMoto\Distribucion;

$pdo = Conexion::obtenerConexion();
$pedidos = Distribucion::obtenerPedidosDisponibles($pdo);
$agentes = Distribucion::obtenerAgentes($pdo);

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$distribucion = $id > 0 ? Distribucion::obtenerPorId($pdo, $id) : null;
$esEdicion = $distribucion !== null;

$idPedidoValor    = $esEdicion ? $distribucion->getIdPedido() : '';
$idAgenteValor    = $esEdicion ? $distribucion->getIdAgenteLogistico() : '';
$fechaAsigValor   = $esEdicion ? $distribucion->getFechaAsignacion() : date('Y-m-d\TH:i');
$fechaEstValor    = $esEdicion ? $distribucion->getFechaEntregaEstimada() : '';
$fechaRealValor   = $esEdicion ? $distribucion->getFechaEntregaReal() : '';
$estadoValor      = $esEdicion ? $distribucion->getEstadoEnvio() : 'Asignado';
$direccionValor   = $esEdicion ? $distribucion->getDireccionEntrega() : '';
$observValor      = $esEdicion ? $distribucion->getObservaciones() : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullMoto - <?php echo $esEdicion ? 'Editar' : 'Nuevo'; ?> Envío</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>

<?php include __DIR__ . '/nav.php'; ?>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            <a href="logistica_distribucion.php" class="text-white-50 d-inline-block mb-3 text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>Volver a Logística
            </a>

            <div class="card p-4">
                <h4 class="text-white mb-4"><?php echo $esEdicion ? 'Editar envío' : 'Asignar nuevo envío'; ?></h4>

                <form action="CONTROLADORES/guardar_distribucion_controller.php" method="POST">

                    <?php if ($esEdicion): ?>
                        <input type="hidden" name="id_distribucion" value="<?php echo $distribucion->getIdDistribucion(); ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label text-white">Pedido</label>
                        <select name="id_pedido" class="form-select" required>
                            <option value="">-- Selecciona un pedido --</option>
                            <?php foreach ($pedidos as $p): ?>
                                <option value="<?php echo $p['id_pedido']; ?>" <?php echo ($p['id_pedido'] == $idPedidoValor) ? 'selected' : ''; ?>>
                                    Pedido #<?php echo $p['id_pedido']; ?> — <?php echo htmlspecialchars($p['nombre_producto']); ?> (x<?php echo $p['cantidad']; ?>) — Venta #<?php echo $p['id_venta']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Agente Logístico</label>
                        <select name="id_agente_logistico" class="form-select" required>
                            <option value="">-- Selecciona un agente --</option>
                            <?php foreach ($agentes as $a): ?>
                                <option value="<?php echo $a['idAgenteLogistico']; ?>" <?php echo ($a['idAgenteLogistico'] == $idAgenteValor) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($a['nombreContacto']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Fecha de asignación</label>
                            <input type="datetime-local" name="fecha_asignacion" class="form-control" value="<?php echo htmlspecialchars(str_replace(' ', 'T', substr($fechaAsigValor, 0, 16))); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Entrega estimada</label>
                            <input type="date" name="fecha_entrega_estimada" class="form-control" value="<?php echo htmlspecialchars($fechaEstValor); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Entrega real (déjalo vacío si aún no se entrega)</label>
                        <input type="datetime-local" name="fecha_entrega_real" class="form-control" value="<?php echo htmlspecialchars($fechaRealValor ? str_replace(' ', 'T', substr($fechaRealValor, 0, 16)) : ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Estado del envío</label>
                        <select name="estado_envio" class="form-select" required>
                            <?php foreach (['Asignado', 'En Ruta', 'Entregado'] as $estado): ?>
                                <option value="<?php echo $estado; ?>" <?php echo ($estado === $estadoValor) ? 'selected' : ''; ?>><?php echo $estado; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Dirección de entrega</label>
                        <input type="text" name="direccion_entrega" class="form-control" value="<?php echo htmlspecialchars($direccionValor); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3"><?php echo htmlspecialchars($observValor ?? ''); ?></textarea>
                    </div>

                    <div class="d-grid mt-3">
                        <button type="submit" class="btn btn-primary py-2">
                            <i class="bi bi-floppy me-2"></i><?php echo $esEdicion ? 'Guardar cambios' : 'Asignar envío'; ?>
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
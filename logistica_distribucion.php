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
$distribuciones = Distribucion::obtenerTodos($pdo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullMoto - Logística y Distribución</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>

<?php include __DIR__ . '/nav.php'; ?>

<div class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Logística y Distribución</h1>
        <a href="formulario_distribucion.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Nuevo envío
        </a>
    </div>

    <?php if (empty($distribuciones)): ?>
        <p class="text-white-50">No hay envíos registrados todavía.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pedido</th>
                        <th>Agente</th>
                        <th>Asignación</th>
                        <th>Entrega estimada</th>
                        <th>Entrega real</th>
                        <th>Estado</th>
                        <th>Dirección</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($distribuciones as $d): ?>
                        <tr>
                            <td>#<?php echo $d->getIdDistribucion(); ?></td>
                            <td>#<?php echo $d->getIdPedido(); ?></td>
                            <td><?php echo htmlspecialchars($d->getNombreAgente()); ?></td>
                            <td><?php echo htmlspecialchars($d->getFechaAsignacion()); ?></td>
                            <td><?php echo htmlspecialchars($d->getFechaEntregaEstimada()); ?></td>
                            <td><?php echo htmlspecialchars($d->getFechaEntregaReal() ?? '—'); ?></td>
                            <td>
                                <?php
                                $colorBadge = match($d->getEstadoEnvio()) {
                                    'Entregado' => 'bg-success',
                                    'En Ruta'   => 'bg-warning text-dark',
                                    'Asignado'  => 'bg-secondary',
                                    default     => 'bg-dark',
                                };
                                ?>
                                <span class="badge <?php echo $colorBadge; ?>"><?php echo htmlspecialchars($d->getEstadoEnvio()); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($d->getDireccionEntrega()); ?></td>
                            <td class="d-flex gap-1">
                                <a href="formulario_distribucion.php?id=<?php echo $d->getIdDistribucion(); ?>" class="btn btn-sm btn-outline-light">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="CONTROLADORES/eliminar_distribucion_controller.php?id=<?php echo $d->getIdDistribucion(); ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('¿Eliminar este envío? Esta acción no se puede deshacer.');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
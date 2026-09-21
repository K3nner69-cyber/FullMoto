<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rolActivo = $_SESSION['usuario']['rolActivo'] ?? null;

if ($rolActivo !== 'Vendedor' && $rolActivo !== 'Administrador') {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/CLASES/Conexion.php';
require_once __DIR__ . '/CLASES/Venta.php';

use FullMoto\Conexion;
use FullMoto\Venta;

$pdo = Conexion::obtenerConexion();
$ventas = Venta::obtenerTodos($pdo);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullMoto - Gestión Comercial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
</head>

<body>

    <?php include __DIR__ . '/nav.php'; ?>

    <div class="container pb-5">
        <h1 class="fw-bold mb-4">Gestión Comercial</h1>

        <?php if (empty($ventas)): ?>
            <p class="text-white-50">No hay ventas registradas todavía.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Vendedor</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Origen</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas as $venta): ?>
                            <tr>
                                <td>#<?php echo $venta->getIdVenta(); ?></td>
                                <td><?php echo htmlspecialchars($venta->getNombreCliente()); ?></td>
                                <td><?php echo htmlspecialchars($venta->getNombreVendedor()); ?></td>
                                <td><?php echo htmlspecialchars($venta->getFechaPedido()); ?></td>
                                <td><?php echo htmlspecialchars($venta->getEstado()); ?></td>
                                <td><?php echo $venta->totalFormateado(); ?></td>
                                <td>
    <?php if ($venta->esDeCarrito()): ?>
                                        <span class="badge bg-success">Carrito</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Manual</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($rolActivo === 'Administrador'): ?>
                                        <a href="CONTROLADORES/eliminar_venta_controller.php?id=<?php echo $venta->getIdVenta(); ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('¿Eliminar esta venta? Esta acción no se puede deshacer.');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
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
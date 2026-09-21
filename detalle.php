<?php
require_once __DIR__ . '/CLASES/Conexion.php';
require_once __DIR__ . '/CLASES/Producto.php';

use FullMoto\Conexion;
use FullMoto\Producto;

$pdo = Conexion::obtenerConexion();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$producto = Producto::obtenerPorId($pdo, $id);
if ($producto === null) {
    include __DIR__ . '/error_404.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullMoto - Detalle de Producto</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
</head>

<body>

    <?php include __DIR__ . '/nav.php'; ?>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">

                <a href="index.php" class="text-white-50 d-inline-block mb-3 text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i>Volver al catálogo
                </a>

                <?php if ($producto === null): ?>
                    <div class="card p-4">
                        <p class="text-white mb-0">El producto solicitado no existe.</p>
                    </div>
                <?php else: ?>
                    <div class="card p-4">
                        <h2 class="fw-bold text-white"><?php echo htmlspecialchars($producto->getNombre()); ?></h2>
                        <p class="text-warning text-uppercase mb-3">
                            <?php echo htmlspecialchars($producto->getNombreCategoria()); ?></p>
                        <p class="text-white-50"><?php echo htmlspecialchars($producto->getDescripcion()); ?></p>
                        <p class="fs-4 fw-bold text-white mb-2"><?php echo $producto->precioFormateado(); ?></p>

                        <?php if ($producto->hayDisponibilidad()): ?>
                            <span class="badge bg-success align-self-start mb-3">Disponible
                                (<?php echo $producto->getStock(); ?> unidades)</span>
                        <?php else: ?>
                            <span class="badge bg-danger align-self-start mb-3">Agotado</span>
                        <?php endif; ?>

                        <?php
                        $rolActivo = $_SESSION['usuario']['rolActivo'] ?? null;
                        if ($rolActivo === 'Vendedor' || $rolActivo === 'Administrador'):
                            ?>
                            <a href="formulario.php?id=<?php echo $producto->getIdProducto(); ?>" class="btn btn-primary">
                                <i class="bi bi-pencil-square me-2"></i>Editar producto
                            </a>
                        <?php endif; ?>

                        <?php
                        $rolActivo = $_SESSION['usuario']['rolActivo'] ?? null;
                        if ($rolActivo === 'Vendedor' || $rolActivo === 'Administrador'):
                            ?>
                            <a href="CONTROLADORES/eliminar_producto_controller.php?id=<?php echo $producto->getIdProducto(); ?>"
                                class="btn btn-outline-danger"
                                onclick="return confirm('¿Seguro que quieres eliminar este producto? Esta acción no se puede deshacer.');">
                                <i class="bi bi-trash me-2"></i>Eliminar
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
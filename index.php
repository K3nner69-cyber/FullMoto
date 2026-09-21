<?php
require_once __DIR__ . '/CLASES/Conexion.php';
require_once __DIR__ . '/CLASES/Producto.php';

use FullMoto\Conexion;
use FullMoto\Producto;

$pdo = Conexion::obtenerConexion();
$productos = Producto::obtenerTodos($pdo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullMoto - Catálogo de Productos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
</head>

<body>
<?php include __DIR__ . '/nav.php'; ?>
<?php if ($rolActivo === 'Cliente'): ?>
    <div class="container pb-2">
        <div class="d-flex align-items-center justify-content-between flex-wrap py-3">
            <div>
                <h2 class="fw-bold mb-1">Hola, <?php echo htmlspecialchars($_SESSION['usuario']['nombre'] ?? 'invitado'); ?></h2>
                <p class="text-white-50 mb-0">Encuentra los repuestos que tu moto necesita</p>
            </div>
        </div>
    </div>

    <div class="hero-banner mb-4">
        <div class="hero-overlay">
            <h3 class="fw-bold mb-2">Repuestos y Accesorios FullMoto</h3>
            <a href="#catalogo" class="btn btn-primary">Ver catálogo</a>
        </div>
    </div>

<?php elseif ($rolActivo === 'Vendedor' || $rolActivo === 'Administrador'): ?>

    <div class="container pb-2">
        <div class="py-3">
            <h2 class="fw-normal fs-4 text-white-50 mb-0">
                Bienvenido/a, <?php echo htmlspecialchars($rolActivo); ?>
                <?php echo htmlspecialchars($_SESSION['usuario']['nombre'] ?? ''); ?>
            </h2>
        </div>
    </div>

<?php endif; ?>
<div class="container pb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Catálogo de Productos</h1>

        <?php if ($rolActivo === 'Vendedor' || $rolActivo === 'Administrador'): ?>
            <a href="formulario.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Agregar producto
            </a>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['compra']) && $_GET['compra'] === 'exitosa'): ?>
        <div class="alert alert-success">¡Compra registrada con éxito!</div>
    <?php endif; ?>

    <div class="row g-4" id="catalogo">
        <?php if (empty($productos)): ?>
            <p class="text-white-50">No hay productos registrados todavía.</p>
        <?php else: ?>
            <?php foreach ($productos as $producto): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card h-100 p-3">
                        <a href="detalle.php?id=<?php echo $producto->getIdProducto(); ?>" class="text-decoration-none">
                            <h5 class="text-white"><?php echo htmlspecialchars($producto->getNombre()); ?></h5>
                            <p class="text-warning small text-uppercase mb-1"><?php echo htmlspecialchars($producto->getNombreCategoria()); ?></p>
                            <p class="text-white-50 small"><?php echo htmlspecialchars($producto->getDescripcion()); ?></p>
                            <p class="fw-bold text-white mb-2"><?php echo $producto->precioFormateado(); ?></p>
                            <?php if ($producto->hayDisponibilidad()): ?>
                                <span class="badge bg-success align-self-start">Disponible (<?php echo $producto->getStock(); ?>)</span>
                            <?php else: ?>
                                <span class="badge bg-danger align-self-start">Agotado</span>
                            <?php endif; ?>
                        </a>

                        <?php if ($rolActivo === 'Cliente' && $producto->hayDisponibilidad()): ?>
                            <form action="CONTROLADORES/agregar_carrito_controller.php" method="POST" class="d-flex gap-2 mt-3">
                                <input type="hidden" name="id_producto" value="<?php echo $producto->getIdProducto(); ?>">
                                <input type="number" name="cantidad" value="1" min="1" max="<?php echo $producto->getStock(); ?>" class="form-control form-control-sm" style="width:65px;">
                                <button type="submit" class="btn btn-sm btn-success flex-grow-1">
                                    <i class="bi bi-cart-plus me-1"></i>Agregar
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php if ($rolActivo === 'Vendedor' || $rolActivo === 'Administrador'): ?>
    <a href="exportar_productos.php" class="btn btn-warning shadow"
       style="position: fixed; bottom: 20px; right: 20px; z-index: 1030;">
        <i class="bi bi-download me-2"></i><span class="d-none d-sm-inline">Exportar reporte</span>
    </a>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>

<?php if ($rolActivo === 'Administrador'): ?>
    <a href="detalle.php?id=99999" class="btn btn-sm btn-outline-secondary"
       style="position: fixed; bottom: 20px; left: 20px; z-index: 1030;">
        <i class="bi bi-bug me-1"></i>Probar error 404
    </a>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rolActivo = $_SESSION['usuario']['rolActivo'] ?? null;

if ($rolActivo !== 'Cliente') {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/CLASES/Conexion.php';
require_once __DIR__ . '/CLASES/Producto.php';

use FullMoto\Conexion;
use FullMoto\Producto;

$pdo = Conexion::obtenerConexion();
$carrito = $_SESSION['carrito'] ?? [];

$items = [];
$total = 0;

foreach ($carrito as $idProducto => $cantidad) {
    $producto = Producto::obtenerPorId($pdo, $idProducto);
    if ($producto === null) {
        continue; // por si el producto fue eliminado mientras estaba en el carrito
    }
    $subtotal = $producto->getPrecio() * $cantidad;
    $total += $subtotal;
    $items[] = [
        'producto'  => $producto,
        'cantidad'  => $cantidad,
        'subtotal'  => $subtotal,
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullMoto - Mi Carrito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>

<?php include __DIR__ . '/nav.php'; ?>

<div class="container pb-5">
    <h1 class="fw-bold mb-4">Mi Carrito</h1>

    <?php if (empty($items)): ?>
        <p class="text-white-50">Tu carrito está vacío. <a href="index.php">Ver catálogo</a></p>
    <?php else: ?>
        <div class="card p-3">
            <?php foreach ($items as $item): ?>
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div>
                        <h6 class="text-white mb-0"><?php echo htmlspecialchars($item['producto']->getNombre()); ?></h6>
                        <small class="text-white-50">Cantidad: <?php echo $item['cantidad']; ?> x <?php echo $item['producto']->precioFormateado(); ?></small>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-white fw-bold">$<?php echo number_format($item['subtotal'], 2, ',', '.'); ?></span>
                        <a href="CONTROLADORES/quitar_carrito_controller.php?id=<?php echo $item['producto']->getIdProducto(); ?>" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <h5 class="text-white mb-0">Total:</h5>
                <h5 class="text-white fw-bold mb-0">$<?php echo number_format($total, 2, ',', '.'); ?></h5>
            </div>

            <form action="CONTROLADORES/confirmar_compra_controller.php" method="POST" class="d-grid mt-4">
                <button type="submit" class="btn btn-primary py-2">
                    <i class="bi bi-check-circle me-2"></i>Confirmar compra
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php include __DIR__ . '/footer.php'; ?>
</html>
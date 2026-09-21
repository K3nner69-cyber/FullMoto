<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/CLASES/Conexion.php';
require_once __DIR__ . '/CLASES/Producto.php';

use FullMoto\Conexion;
use FullMoto\Producto;

$rolActivo = $_SESSION['usuario']['rolActivo'] ?? null;

if ($rolActivo !== 'Vendedor' && $rolActivo !== 'Administrador') {
    header('Location: index.php');
    exit;
}

$pdo = Conexion::obtenerConexion();
$categorias = Producto::obtenerCategorias($pdo);

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$producto = $id > 0 ? Producto::obtenerPorId($pdo, $id) : null;
$esEdicion = $producto !== null;

$nombreValor      = $esEdicion ? $producto->getNombre() : '';
$descripcionValor = $esEdicion ? $producto->getDescripcion() : '';
$precioValor      = $esEdicion ? $producto->getPrecio() : '';
$stockValor       = $esEdicion ? $producto->getStock() : '';
$idCategoriaValor = $esEdicion ? $producto->getIdCategoria() : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullMoto - <?php echo $esEdicion ? 'Editar' : 'Agregar'; ?> Producto</title>

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

            <div class="card p-4">
                <h4 class="text-white mb-4">
                    <i class="bi bi-box-seam me-2"></i><?php echo $esEdicion ? 'Editar producto' : 'Agregar nuevo producto'; ?>
                </h4>

                <form action="guardar_producto.php" method="POST">

                    <?php if ($esEdicion): ?>
                        <input type="hidden" name="id_producto" value="<?php echo $producto->getIdProducto(); ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label text-white">Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($nombreValor); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3"><?php echo htmlspecialchars($descripcionValor); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Categoría</label>
                        <select name="id_categoria" class="form-select" required>
                            <option value="">-- Selecciona una categoría --</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['idCategoria']; ?>"
                                    <?php echo ($categoria['idCategoria'] == $idCategoriaValor) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Precio</label>
                            <input type="number" name="precio" class="form-control" step="0.01" min="0" value="<?php echo htmlspecialchars($precioValor); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Stock</label>
                            <input type="number" name="stock" class="form-control" min="0" value="<?php echo htmlspecialchars($stockValor); ?>" required>
                        </div>
                    </div>

                    <div class="d-grid mt-3">
                        <button type="submit" class="btn btn-primary py-2">
                            <i class="bi bi-floppy me-2"></i><?php echo $esEdicion ? 'Guardar cambios' : 'Crear producto'; ?>
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

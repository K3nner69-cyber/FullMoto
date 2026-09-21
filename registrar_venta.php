<?php
namespace FullMoto;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rolActivo = $_SESSION['usuario']['rolActivo'] ?? null;

if ($rolActivo !== 'Vendedor' && $rolActivo !== 'Administrador') {
    header('Location: index.php');
    exit;
}
?>
<?php
require_once __DIR__ . '/CLASES/Conexion.php';
require_once __DIR__ . '/CLASES/Producto.php';

use PDO;

$pdo = Conexion::obtenerConexion();
$productos = Producto::obtenerTodos($pdo);

// Consultas simples para llenar los <select> de Cliente y Vendedor
// Después
$usuarios = $pdo->query("SELECT u.idUsuario, u.nombre, u.apellido
                          FROM usuario u
                          INNER JOIN clientefinal cf ON u.idUsuario = cf.idClienteFinal
                          WHERE u.estado = 'Activo'
                          ORDER BY u.nombre")
                ->fetchAll(PDO::FETCH_ASSOC);

$vendedores = $pdo->query("SELECT idVendedor, nombreComercial FROM vendedores ORDER BY nombreComercial")
    ->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullMoto - Registrar Venta</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
</head>

<body>

    <?php include __DIR__ . '/nav.php'; ?>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-9">

                <div class="card p-4">

                    <div class="text-center mb-4">
                        <img src="IMG/fullmoto_sin_fondo.png" alt="FullMoto Logo" style="width:280px;" class="img-fluid mb-2">
                        <h2 class="fw-bold text-white">FullMoto</h2>
                        <p class="text-white">Sistema de Gestión de Ventas</p>
                    </div>

                    <!--
                    Nota: Espacio dejado para recodar novedades o dejar recomendaciones para mí mismo.
                -->
                    <form action="CONTROLADORES/registrar_venta_controller.php" method="POST">

                        <div class="mb-3">
                            <label class="form-label text-white">Cliente</label>
                            <select name="id_usuario" class="form-control" required>
                                <option value="">-- Selecciona un cliente --</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?php echo $usuario['idUsuario']; ?>">
                                        <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white">Vendedor</label>
                            <select name="id_vendedor" class="form-control" required>
                                <option value="">-- Selecciona un vendedor --</option>
                                <?php foreach ($vendedores as $vendedor): ?>
                                    <option value="<?php echo $vendedor['idVendedor']; ?>">
                                        <?php echo htmlspecialchars($vendedor['nombreComercial']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">Producto</label>
                            <select name="id_producto" class="form-control" required>
                                <option value="">-- Selecciona un producto --</option>
                                <?php foreach ($productos as $producto): ?>
                                    <option value="<?php echo $producto->getIdProducto(); ?>">
                                        <?php echo htmlspecialchars($producto->getNombre()); ?> — <?php echo $producto->precioFormateado(); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!--Nota para mí, acá configuré con shift+alt+f para indentación desalineada, algo estético.-->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Cantidad</label>
                                <input type="number" name="cantidad" class="form-control" placeholder="0" min="1" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Precio</label>
                                <input type="number" name="precio" class="form-control" placeholder="0.00" min="0" step="0.01" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-white">Fecha</label>
                            <input type="date" name="fecha" class="form-control" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2 fw-bold">
                                <i class="bi bi-cart-check me-3"></i>Registrar Venta
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
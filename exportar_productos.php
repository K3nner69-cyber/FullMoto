<?php
session_start();

// Solo Vendedor y Administrador pueden descargar el reporte
$rol = $_SESSION['usuario']['rolActivo'] ?? null;
if ($rol !== 'Vendedor' && $rol !== 'Administrador') {
    header('Location: index.php');
    exit;
}

require_once 'CLASES/Conexion.php';

use FullMoto\Conexion;

$pdo = Conexion::obtenerConexion();
$sql = "SELECT p.id_producto, p.nombre, c.nombre AS categoria, p.precio, p.stock
        FROM Productos p
        INNER JOIN Categorias c ON p.id_categoria = c.idCategoria
        ORDER BY p.id_producto";
$filas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="reporte_productos.csv"');

$salida = fopen('php://output', 'w');
fwrite($salida, "\xEF\xBB\xBF");   // tildes en Excel
fwrite($salida, "sep=;\n");        // le dice a Excel que separe por ;
fputcsv($salida, ['ID', 'Producto', 'Categoría', 'Precio', 'Stock'], ';');
foreach ($filas as $fila) {
    fputcsv($salida, $fila, ';');
}
fclose($salida);
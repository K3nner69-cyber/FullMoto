<?php
namespace FullMoto;

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0); // que PHP no imprima errores en HTML

require_once __DIR__ . '/CLASES/Conexion.php';
require_once __DIR__ . '/CLASES/Producto.php';

use PDO;
use PDOException;
use Throwable;

function productoAArray(Producto $p): array {
    return [
        'id_producto'      => $p->getIdProducto(),
        'id_categoria'     => $p->getIdCategoria(),
        'nombre'           => $p->getNombre(),
        'descripcion'      => $p->getDescripcion(),
        'precio'           => $p->getPrecio(),
        'stock'            => $p->getStock(),
        'nombre_categoria' => $p->getNombreCategoria(),
    ];
}

$pdo = Conexion::obtenerConexion();
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    switch ($metodo) {

        case 'GET':
            if (isset($_GET['id'])) {
                $producto = Producto::obtenerPorId($pdo, (int) $_GET['id']);
                if ($producto === null) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Producto no encontrado']);
                    exit;
                }
                echo json_encode(productoAArray($producto), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            } else {
                $productos = Producto::obtenerTodos($pdo);
                echo json_encode(array_map('FullMoto\productoAArray', $productos), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }
            break;

        case 'POST':
            $datos = json_decode(file_get_contents('php://input'), true);

            if (!$datos || empty($datos['nombre']) || empty($datos['id_categoria']) || !isset($datos['precio']) || !isset($datos['stock'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Faltan campos requeridos: nombre, id_categoria, precio, stock']);
                exit;
            }

            $idNuevo = Producto::crear(
                $pdo,
                (int) $datos['id_categoria'],
                trim($datos['nombre']),
                trim($datos['descripcion'] ?? ''),
                (float) $datos['precio'],
                (int) $datos['stock']
            );

            http_response_code(201);
            echo json_encode(['mensaje' => 'Producto creado', 'id_producto' => $idNuevo]);
            break;

        case 'PUT':
            $datos = json_decode(file_get_contents('php://input'), true);

            if (!$datos || empty($datos['id_producto'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Se requiere id_producto para actualizar']);
                exit;
            }

            $existente = Producto::obtenerPorId($pdo, (int) $datos['id_producto']);
            if ($existente === null) {
                http_response_code(404);
                echo json_encode(['error' => 'Producto no encontrado']);
                exit;
            }

            Producto::actualizar(
                $pdo,
                (int) $datos['id_producto'],
                (int) ($datos['id_categoria'] ?? $existente->getIdCategoria()),
                trim($datos['nombre'] ?? $existente->getNombre()),
                trim($datos['descripcion'] ?? $existente->getDescripcion()),
                (float) ($datos['precio'] ?? $existente->getPrecio()),
                (int) ($datos['stock'] ?? $existente->getStock())
            );

            echo json_encode(['mensaje' => 'Producto actualizado']);
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Se requiere el parámetro id']);
                exit;
            }

            $idProducto = (int) $_GET['id'];
            $existente = Producto::obtenerPorId($pdo, $idProducto);
            if ($existente === null) {
                http_response_code(404);
                echo json_encode(['error' => 'Producto no encontrado']);
                exit;
            }

            Producto::eliminar($pdo, $idProducto);
            echo json_encode(['mensaje' => 'Producto eliminado']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            break;
    }
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}
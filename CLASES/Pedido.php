<?php
namespace FullMoto;

use PDO;

/**
 * Clase Pedido
 * Representa un registro de la tabla `pedido` de fullmoto_db (línea/detalle de una venta).
 * Columnas reales: id_pedido, id_venta, id_producto, cantidad, precio_unitario, subtotal
 */
class Pedido {
    private $idPedido;
    private $idVenta;
    private $idProducto;
    private $cantidad;
    private $precioUnitario;
    private $subtotal;
    private $nombreProducto; // dato "extra" del JOIN con Productos

    public function __construct($idPedido, $idVenta, $idProducto, $cantidad, $precioUnitario, $subtotal, $nombreProducto = null) {
        $this->idPedido = $idPedido;
        $this->idVenta = $idVenta;
        $this->idProducto = $idProducto;
        $this->cantidad = $cantidad;
        $this->precioUnitario = $precioUnitario;
        $this->subtotal = $subtotal;
        $this->nombreProducto = $nombreProducto;
    }

    // Getters
    public function getIdPedido() { return $this->idPedido; }
    public function getIdVenta() { return $this->idVenta; }
    public function getIdProducto() { return $this->idProducto; }
    public function getCantidad() { return $this->cantidad; }
    public function getPrecioUnitario() { return $this->precioUnitario; }
    public function getSubtotal() { return $this->subtotal; }
    public function getNombreProducto() { return $this->nombreProducto; }

    public function subtotalFormateado() {
        return "$" . number_format($this->subtotal, 2, ',', '.');
    }

    // ------------------------------------------------------------------
    // CONSULTAS (SELECT)
    // ------------------------------------------------------------------

    public static function obtenerTodos(PDO $pdo): array {
        $sql = "SELECT pe.id_pedido, pe.id_venta, pe.id_producto, pe.cantidad,
                       pe.precio_unitario, pe.subtotal, pr.nombre AS nombre_producto
                FROM pedido pe
                INNER JOIN Productos pr ON pe.id_producto = pr.id_producto
                ORDER BY pe.id_pedido";

        $stmt = $pdo->query($sql);
        return self::mapearFilas($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function obtenerPorVenta(PDO $pdo, int $idVenta): array {
        $sql = "SELECT pe.id_pedido, pe.id_venta, pe.id_producto, pe.cantidad,
                       pe.precio_unitario, pe.subtotal, pr.nombre AS nombre_producto
                FROM pedido pe
                INNER JOIN Productos pr ON pe.id_producto = pr.id_producto
                WHERE pe.id_venta = :id_venta";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id_venta' => $idVenta]);
        return self::mapearFilas($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function obtenerPorId(PDO $pdo, int $idPedido): ?Pedido {
        $sql = "SELECT pe.id_pedido, pe.id_venta, pe.id_producto, pe.cantidad,
                       pe.precio_unitario, pe.subtotal, pr.nombre AS nombre_producto
                FROM pedido pe
                INNER JOIN Productos pr ON pe.id_producto = pr.id_producto
                WHERE pe.id_pedido = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $idPedido]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        $pedidos = self::mapearFilas([$fila]);
        return $pedidos[0];
    }

    // ------------------------------------------------------------------
    // ESCRITURA (INSERT / UPDATE / DELETE)
    // ------------------------------------------------------------------

    public static function crear(PDO $pdo, int $idVenta, int $idProducto, int $cantidad, float $precioUnitario): int {
        $subtotal = $cantidad * $precioUnitario;

        $sql = "INSERT INTO pedido (id_venta, id_producto, cantidad, precio_unitario, subtotal)
                VALUES (:id_venta, :id_producto, :cantidad, :precio_unitario, :subtotal)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id_venta'        => $idVenta,
            'id_producto'     => $idProducto,
            'cantidad'        => $cantidad,
            'precio_unitario' => $precioUnitario,
            'subtotal'        => $subtotal,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function actualizar(PDO $pdo, int $idPedido, int $idProducto, int $cantidad, float $precioUnitario): bool {
        $subtotal = $cantidad * $precioUnitario;

        $sql = "UPDATE pedido
                SET id_producto = :id_producto,
                    cantidad = :cantidad,
                    precio_unitario = :precio_unitario,
                    subtotal = :subtotal
                WHERE id_pedido = :id_pedido";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'id_producto'     => $idProducto,
            'cantidad'        => $cantidad,
            'precio_unitario' => $precioUnitario,
            'subtotal'        => $subtotal,
            'id_pedido'       => $idPedido,
        ]);
    }

    public static function eliminar(PDO $pdo, int $idPedido): bool {
        $sql = "DELETE FROM pedido WHERE id_pedido = :id_pedido";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['id_pedido' => $idPedido]);
    }

    // ------------------------------------------------------------------
    // AYUDANTE INTERNO
    // ------------------------------------------------------------------

    private static function mapearFilas(array $filas): array {
        $pedidos = [];
        foreach ($filas as $fila) {
            $pedidos[] = new Pedido(
                $fila['id_pedido'],
                $fila['id_venta'],
                $fila['id_producto'],
                $fila['cantidad'],
                $fila['precio_unitario'],
                $fila['subtotal'],
                $fila['nombre_producto']
            );
        }
        return $pedidos;
    }
}
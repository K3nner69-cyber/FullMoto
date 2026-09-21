<?php
namespace FullMoto;

use PDO;

class Venta {
    private $idVenta;
    private $idUsuario;
    private $idVendedor;
    private $fechaPedido;
    private $estado;
    private $total;
    private $origen;
    private $nombreCliente;   // dato extra del JOIN
    private $nombreVendedor;  // dato extra del JOIN

    public function __construct($idVenta, $idUsuario, $idVendedor, $fechaPedido, $estado, $total, $origen = 'Manual', $nombreCliente = null, $nombreVendedor = null) {
        $this->idVenta = $idVenta;
        $this->idUsuario = $idUsuario;
        $this->idVendedor = $idVendedor;
        $this->fechaPedido = $fechaPedido;
        $this->estado = $estado;
        $this->total = $total;
        $this->origen = $origen;
        $this->nombreCliente = $nombreCliente;
        $this->nombreVendedor = $nombreVendedor;
    }

    // Getters
    public function getIdVenta() { return $this->idVenta; }
    public function getIdUsuario() { return $this->idUsuario; }
    public function getIdVendedor() { return $this->idVendedor; }
    public function getFechaPedido() { return $this->fechaPedido; }
    public function getEstado() { return $this->estado; }
    public function getTotal() { return $this->total; }
    public function getOrigen() { return $this->origen; }
    public function getNombreCliente() { return $this->nombreCliente; }
    public function getNombreVendedor() { return $this->nombreVendedor; }

    public function totalFormateado() {
        return "$" . number_format($this->total, 2, ',', '.');
    }

    public function esDeCarrito() {
        return $this->origen === 'Carrito';
    }

    // ------------------------------------------------------------------
    // CONSULTAS (SELECT)
    // ------------------------------------------------------------------

    public static function obtenerTodos(PDO $pdo): array {
        $sql = "SELECT v.id_venta, v.id_usuario, v.id_vendedor, v.fecha_pedido,
                       v.estado, v.total, v.origen,
                       CONCAT(u.nombre, ' ', u.apellido) AS nombre_cliente,
                       ve.nombreComercial AS nombre_vendedor
                FROM venta v
                INNER JOIN usuario u ON v.id_usuario = u.idUsuario
                INNER JOIN vendedores ve ON v.id_vendedor = ve.idVendedor
                ORDER BY v.fecha_pedido DESC";

        $stmt = $pdo->query($sql);
        return self::mapearFilas($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function obtenerPorId(PDO $pdo, int $idVenta): ?Venta {
        $sql = "SELECT v.id_venta, v.id_usuario, v.id_vendedor, v.fecha_pedido,
                       v.estado, v.total, v.origen,
                       CONCAT(u.nombre, ' ', u.apellido) AS nombre_cliente,
                       ve.nombreComercial AS nombre_vendedor
                FROM venta v
                INNER JOIN usuario u ON v.id_usuario = u.idUsuario
                INNER JOIN vendedores ve ON v.id_vendedor = ve.idVendedor
                WHERE v.id_venta = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $idVenta]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        $ventas = self::mapearFilas([$fila]);
        return $ventas[0];
    }

    /**
     * Trae el primer vendedor registrado, usado como vendedor
     * "por defecto" cuando un cliente compra solo desde el carrito.
     */
    public static function obtenerVendedorPorDefecto(PDO $pdo): int {
        $stmt = $pdo->query("SELECT idVendedor FROM vendedores ORDER BY idVendedor ASC LIMIT 1");
        return (int) $stmt->fetchColumn();
    }

    // ------------------------------------------------------------------
    // ESCRITURA (INSERT / UPDATE / DELETE)
    // ------------------------------------------------------------------

    public static function crear(PDO $pdo, int $idUsuario, int $idVendedor, string $fechaPedido, string $estado, float $total, string $origen = 'Manual'): int {
        $sql = "INSERT INTO venta (id_usuario, id_vendedor, fecha_pedido, estado, total, origen)
                VALUES (:id_usuario, :id_vendedor, :fecha_pedido, :estado, :total, :origen)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id_usuario'   => $idUsuario,
            'id_vendedor'  => $idVendedor,
            'fecha_pedido' => $fechaPedido,
            'estado'       => $estado,
            'total'        => $total,
            'origen'       => $origen,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function actualizar(PDO $pdo, int $idVenta, int $idUsuario, int $idVendedor, string $fechaPedido, string $estado, float $total): bool {
        $sql = "UPDATE venta
                SET id_usuario = :id_usuario,
                    id_vendedor = :id_vendedor,
                    fecha_pedido = :fecha_pedido,
                    estado = :estado,
                    total = :total
                WHERE id_venta = :id_venta";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'id_usuario'   => $idUsuario,
            'id_vendedor'  => $idVendedor,
            'fecha_pedido' => $fechaPedido,
            'estado'       => $estado,
            'total'        => $total,
            'id_venta'     => $idVenta,
        ]);
    }

    public static function eliminar(PDO $pdo, int $idVenta): bool {
        $sql = "DELETE FROM venta WHERE id_venta = :id_venta";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['id_venta' => $idVenta]);
    }

    // ------------------------------------------------------------------
    // AYUDANTE INTERNO
    // ------------------------------------------------------------------

    private static function mapearFilas(array $filas): array {
        $ventas = [];
        foreach ($filas as $fila) {
            $ventas[] = new Venta(
                $fila['id_venta'],
                $fila['id_usuario'],
                $fila['id_vendedor'],
                $fila['fecha_pedido'],
                $fila['estado'],
                $fila['total'],
                $fila['origen'],
                $fila['nombre_cliente'] ?? null,
                $fila['nombre_vendedor'] ?? null
            );
        }
        return $ventas;
    }
}
<?php
namespace FullMoto;

use PDO;

/**
 * Clase Distribucion
 * Representa un registro de la tabla `distribucion` de fullmoto_db.
 * Columnas reales: id_distribucion, id_pedido, id_agente_logistico,
 * fecha_asignacion, fecha_entrega_estimada, fecha_entrega_real,
 * estado_envio, direccion_entrega, observaciones
 */
class Distribucion {
    private $idDistribucion;
    private $idPedido;
    private $idAgenteLogistico;
    private $fechaAsignacion;
    private $fechaEntregaEstimada;
    private $fechaEntregaReal;
    private $estadoEnvio;
    private $direccionEntrega;
    private $observaciones;
    private $nombreAgente; // dato extra del JOIN

    public function __construct($idDistribucion, $idPedido, $idAgenteLogistico, $fechaAsignacion, $fechaEntregaEstimada, $fechaEntregaReal, $estadoEnvio, $direccionEntrega, $observaciones, $nombreAgente = null) {
        $this->idDistribucion = $idDistribucion;
        $this->idPedido = $idPedido;
        $this->idAgenteLogistico = $idAgenteLogistico;
        $this->fechaAsignacion = $fechaAsignacion;
        $this->fechaEntregaEstimada = $fechaEntregaEstimada;
        $this->fechaEntregaReal = $fechaEntregaReal;
        $this->estadoEnvio = $estadoEnvio;
        $this->direccionEntrega = $direccionEntrega;
        $this->observaciones = $observaciones;
        $this->nombreAgente = $nombreAgente;
    }

    // Getters
    public function getIdDistribucion() { return $this->idDistribucion; }
    public function getIdPedido() { return $this->idPedido; }
    public function getIdAgenteLogistico() { return $this->idAgenteLogistico; }
    public function getFechaAsignacion() { return $this->fechaAsignacion; }
    public function getFechaEntregaEstimada() { return $this->fechaEntregaEstimada; }
    public function getFechaEntregaReal() { return $this->fechaEntregaReal; }
    public function getEstadoEnvio() { return $this->estadoEnvio; }
    public function getDireccionEntrega() { return $this->direccionEntrega; }
    public function getObservaciones() { return $this->observaciones; }
    public function getNombreAgente() { return $this->nombreAgente; }

    // ------------------------------------------------------------------
    // CONSULTAS (SELECT)
    // ------------------------------------------------------------------

    public static function obtenerTodos(PDO $pdo): array {
        $sql = "SELECT d.id_distribucion, d.id_pedido, d.id_agente_logistico,
                       d.fecha_asignacion, d.fecha_entrega_estimada, d.fecha_entrega_real,
                       d.estado_envio, d.direccion_entrega, d.observaciones,
                       a.nombreContacto AS nombre_agente
                FROM distribucion d
                INNER JOIN agentelogistico a ON d.id_agente_logistico = a.idAgenteLogistico
                ORDER BY d.fecha_asignacion DESC";

        $stmt = $pdo->query($sql);
        return self::mapearFilas($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function obtenerPorId(PDO $pdo, int $idDistribucion): ?Distribucion {
        $sql = "SELECT d.id_distribucion, d.id_pedido, d.id_agente_logistico,
                       d.fecha_asignacion, d.fecha_entrega_estimada, d.fecha_entrega_real,
                       d.estado_envio, d.direccion_entrega, d.observaciones,
                       a.nombreContacto AS nombre_agente
                FROM distribucion d
                INNER JOIN agentelogistico a ON d.id_agente_logistico = a.idAgenteLogistico
                WHERE d.id_distribucion = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $idDistribucion]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        $distribuciones = self::mapearFilas([$fila]);
        return $distribuciones[0];
    }

    public static function obtenerPedidosDisponibles(PDO $pdo): array {
        $sql = "SELECT p.id_pedido, pr.nombre AS nombre_producto, p.cantidad, p.id_venta
                FROM pedido p
                INNER JOIN Productos pr ON p.id_producto = pr.id_producto
                ORDER BY p.id_pedido DESC";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerAgentes(PDO $pdo): array {
        $sql = "SELECT idAgenteLogistico, nombreContacto FROM agentelogistico ORDER BY nombreContacto";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // ------------------------------------------------------------------
    // ESCRITURA (INSERT / UPDATE / DELETE)
    // ------------------------------------------------------------------

    public static function crear(PDO $pdo, int $idPedido, int $idAgenteLogistico, string $fechaAsignacion, string $fechaEntregaEstimada, ?string $fechaEntregaReal, string $estadoEnvio, string $direccionEntrega, ?string $observaciones): int {
        $sql = "INSERT INTO distribucion (id_pedido, id_agente_logistico, fecha_asignacion, fecha_entrega_estimada, fecha_entrega_real, estado_envio, direccion_entrega, observaciones)
                VALUES (:id_pedido, :id_agente_logistico, :fecha_asignacion, :fecha_entrega_estimada, :fecha_entrega_real, :estado_envio, :direccion_entrega, :observaciones)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id_pedido'              => $idPedido,
            'id_agente_logistico'    => $idAgenteLogistico,
            'fecha_asignacion'       => $fechaAsignacion,
            'fecha_entrega_estimada' => $fechaEntregaEstimada,
            'fecha_entrega_real'     => $fechaEntregaReal,
            'estado_envio'           => $estadoEnvio,
            'direccion_entrega'      => $direccionEntrega,
            'observaciones'          => $observaciones,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function actualizar(PDO $pdo, int $idDistribucion, int $idPedido, int $idAgenteLogistico, string $fechaAsignacion, string $fechaEntregaEstimada, ?string $fechaEntregaReal, string $estadoEnvio, string $direccionEntrega, ?string $observaciones): bool {
        $sql = "UPDATE distribucion
                SET id_pedido = :id_pedido,
                    id_agente_logistico = :id_agente_logistico,
                    fecha_asignacion = :fecha_asignacion,
                    fecha_entrega_estimada = :fecha_entrega_estimada,
                    fecha_entrega_real = :fecha_entrega_real,
                    estado_envio = :estado_envio,
                    direccion_entrega = :direccion_entrega,
                    observaciones = :observaciones
                WHERE id_distribucion = :id_distribucion";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'id_pedido'              => $idPedido,
            'id_agente_logistico'    => $idAgenteLogistico,
            'fecha_asignacion'       => $fechaAsignacion,
            'fecha_entrega_estimada' => $fechaEntregaEstimada,
            'fecha_entrega_real'     => $fechaEntregaReal,
            'estado_envio'           => $estadoEnvio,
            'direccion_entrega'      => $direccionEntrega,
            'observaciones'          => $observaciones,
            'id_distribucion'        => $idDistribucion,
        ]);
    }

    public static function eliminar(PDO $pdo, int $idDistribucion): bool {
        $sql = "DELETE FROM distribucion WHERE id_distribucion = :id_distribucion";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['id_distribucion' => $idDistribucion]);
    }

    // ------------------------------------------------------------------
    // AYUDANTE INTERNO
    // ------------------------------------------------------------------

    private static function mapearFilas(array $filas): array {
        $distribuciones = [];
        foreach ($filas as $fila) {
            $distribuciones[] = new Distribucion(
                $fila['id_distribucion'],
                $fila['id_pedido'],
                $fila['id_agente_logistico'],
                $fila['fecha_asignacion'],
                $fila['fecha_entrega_estimada'],
                $fila['fecha_entrega_real'],
                $fila['estado_envio'],
                $fila['direccion_entrega'],
                $fila['observaciones'],
                $fila['nombre_agente'] ?? null
            );
        }
        return $distribuciones;
    }
}
<?php
namespace FullMoto;

use PDO;

/**
 * Clase Producto
 * Representa un registro de la tabla `Productos` de fullmoto_db.
 * Columnas reales: id_producto, id_categoria, nombre, descripcion, precio, stock
 */
class Producto
{
    private $idProducto;
    private $idCategoria;
    private $nombre;
    private $descripcion;
    private $precio;
    private $stock;
    private $nombreCategoria; // dato "extra" que viene del JOIN con Categorias

    public function __construct($idProducto, $idCategoria, $nombre, $descripcion, $precio, $stock, $nombreCategoria = null)
    {
        $this->idProducto = $idProducto;
        $this->idCategoria = $idCategoria;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->precio = $precio;
        $this->stock = $stock;
        $this->nombreCategoria = $nombreCategoria;
    }

    // Getters
    public function getIdProducto()
    {
        return $this->idProducto;
    }

    public function getIdCategoria()
    {
        return $this->idCategoria;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }

    public function getPrecio()
    {
        return $this->precio;
    }

    public function getStock()
    {
        return $this->stock;
    }

    public function getNombreCategoria()
    {
        return $this->nombreCategoria;
    }

    // Lógica propia del objeto
    public function hayDisponibilidad()
    {
        return $this->stock > 0;
    }

    public function precioFormateado()
    {
        return "$" . number_format($this->precio, 2, ',', '.');
    }

    /**
     * Tarjeta resumida, usada en el catálogo (index.php)
     */
    public function renderCard()
    {
        $disponible = $this->hayDisponibilidad()
            ? '<span class="badge-disponible">Disponible (' . $this->stock . ')</span>'
            : '<span class="badge-agotado">Agotado</span>';

        return "
        <div class='producto-card'>
            <h3>{$this->nombre}</h3>
            <p class='producto-categoria'>{$this->nombreCategoria}</p>
            <p class='producto-descripcion'>{$this->descripcion}</p>
            <p class='producto-precio'>{$this->precioFormateado()}</p>
            <p class='producto-stock'>$disponible</p>
        </div>";
    }

    /**
     * Vista detallada, usada en detalle.php
     */
    public function renderDetalle()
    {
        $disponible = $this->hayDisponibilidad()
            ? '<span class="badge-disponible">Disponible (' . $this->stock . ' unidades)</span>'
            : '<span class="badge-agotado">Agotado</span>';

        return "
        <div class='producto-detalle'>
            <h2>{$this->nombre}</h2>
            <p class='producto-categoria'>Categoría: {$this->nombreCategoria}</p>
            <p class='producto-descripcion'>{$this->descripcion}</p>
            <p class='producto-precio'>{$this->precioFormateado()}</p>
            <p class='producto-stock'>$disponible</p>
        </div>";
    }

    // ------------------------------------------------------------------
    // CONSULTAS (SELECT)
    // ------------------------------------------------------------------

    public static function obtenerTodos(PDO $pdo): array
    {
        $sql = "SELECT p.id_producto, p.id_categoria, p.nombre, p.descripcion,
                       p.precio, p.stock, c.nombre AS nombre_categoria
                FROM Productos p
                INNER JOIN Categorias c ON p.id_categoria = c.idCategoria
                ORDER BY p.nombre";

        $stmt = $pdo->query($sql);
        return self::mapearFilas($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function obtenerPorId(PDO $pdo, int $idProducto): ?Producto
    {
        $sql = "SELECT p.id_producto, p.id_categoria, p.nombre, p.descripcion,
                       p.precio, p.stock, c.nombre AS nombre_categoria
                FROM Productos p
                INNER JOIN Categorias c ON p.id_categoria = c.idCategoria
                WHERE p.id_producto = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $idProducto]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        $productos = self::mapearFilas([$fila]);
        return $productos[0];
    }

    /**
     * Lista simple de categorías (id, nombre) para llenar el <select> del formulario.
     */
    public static function obtenerCategorias(PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT idCategoria, nombre FROM Categorias ORDER BY nombre");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ------------------------------------------------------------------
    // ESCRITURA (INSERT / UPDATE / DELETE)
    // ------------------------------------------------------------------

    /**
     * Inserta un nuevo producto. Devuelve el id_producto generado.
     */
    public static function crear(PDO $pdo, int $idCategoria, string $nombre, string $descripcion, float $precio, int $stock): int
    {
        $sql = "INSERT INTO Productos (id_categoria, nombre, descripcion, precio, stock)
                VALUES (:id_categoria, :nombre, :descripcion, :precio, :stock)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id_categoria' => $idCategoria,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'stock' => $stock,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Actualiza un producto existente por su id_producto.
     */
    public static function actualizar(PDO $pdo, int $idProducto, int $idCategoria, string $nombre, string $descripcion, float $precio, int $stock): bool
    {
        $sql = "UPDATE Productos
                SET id_categoria = :id_categoria,
                    nombre = :nombre,
                    descripcion = :descripcion,
                    precio = :precio,
                    stock = :stock
                WHERE id_producto = :id_producto";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'id_categoria' => $idCategoria,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'stock' => $stock,
            'id_producto' => $idProducto,
        ]);
    }
    /**
     * Elimina un producto existente.
     */
    public static function eliminar(PDO $pdo, int $idProducto): bool
    {
        $sql = "DELETE FROM Productos WHERE id_producto = :id_producto";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'id_producto' => $idProducto,
        ]);
    }
    // ------------------------------------------------------------------
    // AYUDANTE INTERNO
    // ------------------------------------------------------------------

    private static function mapearFilas(array $filas): array
    {
        $productos = [];
        foreach ($filas as $fila) {
            $productos[] = new Producto(
                $fila['id_producto'],
                $fila['id_categoria'],
                $fila['nombre'],
                $fila['descripcion'],
                $fila['precio'],
                $fila['stock'],
                $fila['nombre_categoria']
            );
        }
        return $productos;
    }
}

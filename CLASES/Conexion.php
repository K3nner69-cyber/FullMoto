<?php
namespace FullMoto;

use PDO;
use PDOException;

/**
 * Clase Conexion
 * Centraliza la conexión PDO a la base de datos fullmoto_db.
 * Así, si en algún momento cambia el host, usuario o contraseña,
 * solo se modifica en un único lugar.
 */
class Conexion {
    private static $host = "localhost";
    private static $dbname = "fullmoto_db";
    private static $usuario = "root";       // Usuario por defecto de XAMPP
    private static $password = "";          // XAMPP no trae contraseña por defecto

    public static function obtenerConexion(): PDO {
        try {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=utf8mb4";
            $pdo = new PDO($dsn, self::$usuario, self::$password);

            // Que los errores de SQL lancen excepciones en vez de fallar en silencio
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
}

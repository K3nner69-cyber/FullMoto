<?php
namespace FullMoto;

use PDO;

class Usuario {
    public static function validarCredenciales(PDO $pdo, string $email, string $password): ?array {
        $sql = "SELECT idUsuario, nombre, apellido, email, contrasena
                FROM usuario
                WHERE email = :email AND estado = 'Activo'";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            return null;
        }

        if (!password_verify($password, $usuario['contrasena'])) {
            return null;
        }

        unset($usuario['contrasena']);

        // Traemos TODOS los roles que tenga este usuario
        $roles = self::obtenerRolesDeUsuario($pdo, $usuario['idUsuario']);
        $usuario['roles'] = $roles;
        $usuario['rolActivo'] = $roles[0] ?? null; // el de mayor jerarquía, por defecto

        return $usuario;
    }

    public static function obtenerRolesDeUsuario(PDO $pdo, int $idUsuario): array {
        $sql = "SELECT r.nombreRol
                FROM roles_usuarios ru
                INNER JOIN roles r ON ru.idRol = r.idRol
                WHERE ru.idUsuario = :id
                ORDER BY r.idRol ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
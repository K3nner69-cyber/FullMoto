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
        public static function emailExiste(PDO $pdo, string $email): bool {
        $stmt = $pdo->prepare("SELECT 1 FROM usuario WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return (bool) $stmt->fetchColumn();
    }

    public static function registrarCliente(PDO $pdo, string $nombre, string $apellido, string $email, string $password): int {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO usuario (nombre, apellido, email, contrasena, estado)
                 VALUES (:nombre, :apellido, :email, :contrasena, 'Activo')"
            );
            $stmt->execute([
                'nombre'     => $nombre,
                'apellido'   => $apellido,
                'email'      => $email,
                'contrasena' => $hash,
            ]);
            $idUsuario = (int) $pdo->lastInsertId();

            $stmt = $pdo->prepare("SELECT idRol FROM roles WHERE nombreRol = 'Cliente'");
            $stmt->execute();
            $idRol = $stmt->fetchColumn();
            if (!$idRol) {
                throw new \RuntimeException("No existe el rol Cliente");
            }

            $stmt = $pdo->prepare("INSERT INTO roles_usuarios (idUsuario, idRol) VALUES (:u, :r)");
            $stmt->execute(['u' => $idUsuario, 'r' => $idRol]);

            $pdo->commit();
            return $idUsuario;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
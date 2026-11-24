<?php

namespace App\Models;

use PDO;

class Usuario
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function obtenerTodos(): array
    {
        $sql = "
            SELECT
                u.IdUsuario,
                u.IdRol,
                r.Nombre AS Rol,
                u.Nombres,
                u.Apellidos,
                u.Email,
                u.Activo
            FROM Usuarios u
            INNER JOIN Roles r
                ON u.IdRol = r.IdRol
            WHERE u.Activo = 1
            ORDER BY u.Nombres, u.Apellidos
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $sql = "
            SELECT
                u.IdUsuario,
                u.IdRol,
                r.Nombre AS Rol,
                u.Nombres,
                u.Apellidos,
                u.Email,
                u.Activo
            FROM Usuarios u
            INNER JOIN Roles r
                ON u.IdRol = r.IdRol
            WHERE u.IdUsuario = :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch();
    }

    public function obtenerPorEmail(string $email): array|false
    {
        $sql = "
            SELECT *
            FROM Usuarios
            WHERE Email = :email
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);

        return $stmt->fetch();
    }

    public function crear(array $data): int
    {
        $sql = "
            INSERT INTO Usuarios
            (
                IdRol,
                Nombres,
                Apellidos,
                Email,
                Activo
            )
            VALUES
            (
                :id_rol,
                :nombres,
                :apellidos,
                :email,
                :activo
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_rol' => $data['IdRol'],
            ':nombres' => $data['Nombres'],
            ':apellidos' => $data['Apellidos'],
            ':email' => $data['Email'],
            ':activo' => isset($data['Activo']) ? (int) (bool) $data['Activo'] : 1
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $data): bool
    {
        $sql = "
            UPDATE Usuarios
            SET
                IdRol = :id_rol,
                Nombres = :nombres,
                Apellidos = :apellidos,
                Email = :email,
                Activo = :activo
            WHERE IdUsuario = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id_rol' => $data['IdRol'],
            ':nombres' => $data['Nombres'],
            ':apellidos' => $data['Apellidos'],
            ':email' => $data['Email'],
            ':activo' => isset($data['Activo']) ? (int) (bool) $data['Activo'] : 1,
            ':id' => $id
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = "
            UPDATE Usuarios
            SET Activo = 0
            WHERE IdUsuario = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }
}
<?php

namespace App\Models;

use PDO;

class Rol
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
                IdRol,
                Nombre,
                NivelAcceso
            FROM Roles
            ORDER BY NivelAcceso ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $sql = "
            SELECT
                IdRol,
                Nombre,
                NivelAcceso
            FROM Roles
            WHERE IdRol = :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch();
    }

    public function crear(array $data): int
    {
        $sql = "
            INSERT INTO Roles (Nombre, NivelAcceso)
            VALUES (:nombre, :nivel_acceso)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $data['Nombre'],
            ':nivel_acceso' => $data['NivelAcceso']
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $data): bool
    {
        $sql = "
            UPDATE Roles
            SET
                Nombre = :nombre,
                NivelAcceso = :nivel_acceso
            WHERE IdRol = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nombre' => $data['Nombre'],
            ':nivel_acceso' => $data['NivelAcceso'],
            ':id' => $id
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = "
            DELETE FROM Roles
            WHERE IdRol = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }
}
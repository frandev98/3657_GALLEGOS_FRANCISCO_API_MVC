<?php

namespace App\Models;

use PDO;

class Categoria
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
                IdCategoria,
                Nombre,
                Descripcion
            FROM Categorias
            ORDER BY Nombre
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $sql = "
            SELECT
                IdCategoria,
                Nombre,
                Descripcion
            FROM Categorias
            WHERE IdCategoria = :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch();
    }

    public function crear(array $data): int
    {
        $sql = "
            INSERT INTO Categorias (Nombre, Descripcion)
            VALUES (:nombre, :descripcion)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $data['Nombre'],
            ':descripcion' => $data['Descripcion'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $data): bool
    {
        $sql = "
            UPDATE Categorias
            SET
                Nombre = :nombre,
                Descripcion = :descripcion
            WHERE IdCategoria = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nombre' => $data['Nombre'],
            ':descripcion' => $data['Descripcion'] ?? null,
            ':id' => $id
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = "
            DELETE FROM Categorias
            WHERE IdCategoria = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }
}
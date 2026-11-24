<?php

namespace App\Models;

use PDO;

class Producto
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
                p.IdProducto,
                p.IdCategoria,
                c.Nombre AS Categoria,
                p.Nombre,
                p.Descripcion,
                p.Precio,
                p.Estado
            FROM Productos p
            INNER JOIN Categorias c
                ON p.IdCategoria = c.IdCategoria
            WHERE p.Estado = 1
            ORDER BY p.Nombre
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $sql = "
            SELECT
                p.IdProducto,
                p.IdCategoria,
                c.Nombre AS Categoria,
                p.Nombre,
                p.Descripcion,
                p.Precio,
                p.Estado
            FROM Productos p
            INNER JOIN Categorias c
                ON p.IdCategoria = c.IdCategoria
            WHERE p.IdProducto = :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch();
    }

    public function crear(array $data): int
    {
        $sql = "
            INSERT INTO Productos
            (
                IdCategoria,
                Nombre,
                Descripcion,
                Precio,
                Estado
            )
            VALUES
            (
                :id_categoria,
                :nombre,
                :descripcion,
                :precio,
                :estado
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_categoria' => $data['IdCategoria'],
            ':nombre' => $data['Nombre'],
            ':descripcion' => $data['Descripcion'] ?? null,
            ':precio' => $data['Precio'],
            ':estado' => isset($data['Estado']) ? (int) (bool) $data['Estado'] : 1
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $data): bool
    {
        $sql = "
            UPDATE Productos
            SET
                IdCategoria = :id_categoria,
                Nombre = :nombre,
                Descripcion = :descripcion,
                Precio = :precio,
                Estado = :estado
            WHERE IdProducto = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id_categoria' => $data['IdCategoria'],
            ':nombre' => $data['Nombre'],
            ':descripcion' => $data['Descripcion'] ?? null,
            ':precio' => $data['Precio'],
            ':estado' => isset($data['Estado']) ? (int) (bool) $data['Estado'] : 1,
            ':id' => $id
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = "
            UPDATE Productos
            SET Estado = 0
            WHERE IdProducto = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }
}
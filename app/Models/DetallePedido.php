<?php

namespace App\Models;

use PDO;

class DetallePedido
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function crear(array $data): int
    {
        // Subtotal es columna GENERATED ALWAYS AS (Cantidad * PrecioUnitario) STORED en MySQL
        $sql = "
            INSERT INTO DetallePedidos
            (
                IdPedido,
                IdProducto,
                Cantidad,
                PrecioUnitario
            )
            VALUES
            (
                :id_pedido,
                :id_producto,
                :cantidad,
                :precio_unitario
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_pedido' => $data['IdPedido'],
            ':id_producto' => $data['IdProducto'],
            ':cantidad' => $data['Cantidad'],
            ':precio_unitario' => $data['PrecioUnitario']
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function obtenerPorPedido(int $idPedido): array
    {
        $sql = "
            SELECT
                d.IdDetalle,
                d.IdProducto,
                p.Nombre AS Producto,
                d.Cantidad,
                d.PrecioUnitario,
                d.Subtotal
            FROM DetallePedidos d
            INNER JOIN Productos p
                ON d.IdProducto = p.IdProducto
            WHERE d.IdPedido = :id_pedido
            ORDER BY d.IdDetalle
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_pedido' => $idPedido]);

        return $stmt->fetchAll();
    }

    public function eliminarPorPedido(int $idPedido): bool
    {
        $sql = "
            DELETE FROM DetallePedidos
            WHERE IdPedido = :id_pedido
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([':id_pedido' => $idPedido]);
    }
}
<?php

namespace App\Models;

use PDO;

class Pedido
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
                p.IdPedido,
                p.IdUsuario,
                CONCAT(u.Nombres, ' ', u.Apellidos) AS Cajero,
                p.FechaPedido,
                p.Total,
                p.MetodoPago
            FROM Pedidos p
            INNER JOIN Usuarios u
                ON p.IdUsuario = u.IdUsuario
            ORDER BY p.FechaPedido DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $sql = "
            SELECT
                p.IdPedido,
                p.IdUsuario,
                CONCAT(u.Nombres, ' ', u.Apellidos) AS Cajero,
                p.FechaPedido,
                p.Total,
                p.MetodoPago
            FROM Pedidos p
            INNER JOIN Usuarios u
                ON p.IdUsuario = u.IdUsuario
            WHERE p.IdPedido = :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch();
    }

    public function crear(array $data): int
    {
        $sql = "
            INSERT INTO Pedidos
            (
                IdUsuario,
                FechaPedido,
                Total,
                MetodoPago
            )
            VALUES
            (
                :id_usuario,
                :fecha_pedido,
                :total,
                :metodo_pago
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $data['IdUsuario'],
            ':fecha_pedido' => $data['FechaPedido'] ?? date('Y-m-d H:i:s'),
            ':total' => $data['Total'],
            ':metodo_pago' => $data['MetodoPago'] ?? 'Efectivo'
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $data): bool
    {
        $sql = "
            UPDATE Pedidos
            SET
                IdUsuario = :id_usuario,
                Total = :total,
                MetodoPago = :metodo_pago
            WHERE IdPedido = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id_usuario' => $data['IdUsuario'],
            ':total' => $data['Total'],
            ':metodo_pago' => $data['MetodoPago'],
            ':id' => $id
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = "
            DELETE FROM Pedidos
            WHERE IdPedido = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }

    public function obtenerVentasPorRango(string $fechaInicio, string $fechaFin): array
    {
        $stmt = $this->db->prepare("CALL usp_ObtenerVentasPorRango(:inicio, :fin)");
        $stmt->execute([
            ':inicio' => $fechaInicio,
            ':fin' => $fechaFin
        ]);

        $resumen = $stmt->fetch() ?: ['TotalPedidos' => 0, 'IngresosTotales' => 0];
        $stmt->nextRowset();
        $pedidos = $stmt->fetchAll();
        $stmt->closeCursor();

        return [
            'resumen' => $resumen,
            'pedidos' => $pedidos
        ];
    }
}
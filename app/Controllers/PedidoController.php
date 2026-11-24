<?php

namespace App\Controllers;

use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Services\PedidoService;
use Core\Request;
use Core\Response;
use PDO;
use Throwable;

class PedidoController
{
    private Pedido $pedido;
    private DetallePedido $detalle;
    private PedidoService $service;

    public function __construct(PDO $db)
    {
        $this->pedido = new Pedido($db);
        $this->detalle = new DetallePedido($db);
        $this->service = new PedidoService($db);
    }

    public function index(): void
    {
        Response::success(
            $this->pedido->obtenerTodos(),
            'Pedidos obtenidos correctamente'
        );
    }

    public function show(int $id): void
    {
        $pedido = $this->pedido->obtenerPorId($id);

        if (!$pedido) {
            Response::error('Pedido no encontrado', 404);
        }

        $pedido['Productos'] = $this->detalle->obtenerPorPedido($id);

        Response::success($pedido, 'Pedido obtenido correctamente');
    }

    public function store(): void
    {
        try {
            $data = Request::body();
            $pedido = $this->service->crear($data);

            Response::success(
                $pedido,
                'Pedido registrado correctamente',
                201
            );
        } catch (Throwable $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    public function update(int $id): void
    {
        if (!$this->pedido->obtenerPorId($id)) {
            Response::error('Pedido no encontrado', 404);
        }

        try {
            $data = Request::body();
            $pedido = $this->service->actualizar($id, $data);

            Response::success($pedido, 'Pedido actualizado correctamente');
        } catch (Throwable $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    public function destroy(int $id): void
    {
        if (!$this->pedido->obtenerPorId($id)) {
            Response::error('Pedido no encontrado', 404);
        }

        try {
            $this->service->eliminar($id);
            Response::success(null, 'Pedido eliminado correctamente');
        } catch (Throwable $e) {
            Response::error('Error al eliminar el pedido', 500);
        }
    }

    public function ventasPorRango(): void
    {
        $inicio = $_GET['inicio'] ?? null;
        $fin = $_GET['fin'] ?? null;

        if (empty($inicio) || empty($fin)) {
            Response::error(
                'Los parámetros query "inicio" y "fin" son obligatorios (Ej: ?inicio=2026-10-01 00:00:00&fin=2026-10-31 23:59:59)',
                422
            );
        }

        try {
            $reporte = $this->pedido->obtenerVentasPorRango($inicio, $fin);
            Response::success($reporte, 'Reporte de ventas obtenido correctamente');
        } catch (Throwable $e) {
            Response::error('Error al consultar el reporte de ventas', 500);
        }
    }
}
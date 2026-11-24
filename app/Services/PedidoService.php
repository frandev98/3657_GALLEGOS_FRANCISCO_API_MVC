<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Producto;
use App\Models\Usuario;
use PDO;
use Exception;
use Throwable;

class PedidoService
{
    private PDO $db;
    private Pedido $pedido;
    private DetallePedido $detallePedido;
    private Producto $producto;
    private Usuario $usuario;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->pedido = new Pedido($db);
        $this->detallePedido = new DetallePedido($db);
        $this->producto = new Producto($db);
        $this->usuario = new Usuario($db);
    }

    public function crear(array $data): array
    {
        if (empty($data['IdUsuario'])) {
            throw new Exception('El IdUsuario es obligatorio.');
        }

        if (empty($data['Productos']) || !is_array($data['Productos'])) {
            throw new Exception('El pedido debe contener una lista de Productos.');
        }

        $usuario = $this->usuario->obtenerPorId((int) $data['IdUsuario']);
        if (!$usuario || !$usuario['Activo']) {
            throw new Exception('El usuario especificado no existe o está inactivo.');
        }

        try {
            $this->db->beginTransaction();

            $totalPedido = 0.0;
            $productosProcesados = [];

            foreach ($data['Productos'] as $item) {
                if (empty($item['IdProducto']) || !isset($item['Cantidad'])) {
                    throw new Exception('Cada ítem debe incluir IdProducto y Cantidad.');
                }

                $cantidad = (int) $item['Cantidad'];
                if ($cantidad <= 0) {
                    throw new Exception('La cantidad de cada producto debe ser mayor que cero.');
                }

                $producto = $this->producto->obtenerPorId((int) $item['IdProducto']);
                if (!$producto) {
                    throw new Exception('Uno de los productos indicados no existe.');
                }

                if (!$producto['Estado']) {
                    throw new Exception('El producto "' . $producto['Nombre'] . '" no está disponible.');
                }

                $precioUnitario = (float) $producto['Precio'];
                $subtotal = $precioUnitario * $cantidad;
                $totalPedido += $subtotal;

                $productosProcesados[] = [
                    'IdProducto' => (int) $producto['IdProducto'],
                    'Producto' => $producto['Nombre'],
                    'Cantidad' => $cantidad,
                    'PrecioUnitario' => $precioUnitario,
                    'Subtotal' => round($subtotal, 2)
                ];
            }

            $metodoPago = !empty($data['MetodoPago']) ? trim((string) $data['MetodoPago']) : 'Efectivo';

            $idPedido = $this->pedido->crear([
                'IdUsuario' => (int) $data['IdUsuario'],
                'FechaPedido' => $data['FechaPedido'] ?? date('Y-m-d H:i:s'),
                'Total' => round($totalPedido, 2),
                'MetodoPago' => $metodoPago
            ]);

            foreach ($productosProcesados as $detalle) {
                $this->detallePedido->crear([
                    'IdPedido' => $idPedido,
                    'IdProducto' => $detalle['IdProducto'],
                    'Cantidad' => $detalle['Cantidad'],
                    'PrecioUnitario' => $detalle['PrecioUnitario']
                ]);
            }

            $this->db->commit();

            return [
                'IdPedido' => $idPedido,
                'IdUsuario' => (int) $data['IdUsuario'],
                'Total' => round($totalPedido, 2),
                'MetodoPago' => $metodoPago,
                'Productos' => $productosProcesados
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function actualizar(int $idPedido, array $data): array
    {
        if (empty($data['IdUsuario'])) {
            throw new Exception('El IdUsuario es obligatorio.');
        }

        if (empty($data['Productos']) || !is_array($data['Productos'])) {
            throw new Exception('El pedido debe contener una lista de Productos.');
        }

        $usuario = $this->usuario->obtenerPorId((int) $data['IdUsuario']);
        if (!$usuario || !$usuario['Activo']) {
            throw new Exception('El usuario especificado no existe o está inactivo.');
        }

        try {
            $this->db->beginTransaction();

            $totalPedido = 0.0;
            $productosProcesados = [];

            foreach ($data['Productos'] as $item) {
                if (empty($item['IdProducto']) || !isset($item['Cantidad'])) {
                    throw new Exception('Cada ítem debe incluir IdProducto y Cantidad.');
                }

                $cantidad = (int) $item['Cantidad'];
                if ($cantidad <= 0) {
                    throw new Exception('La cantidad de cada producto debe ser mayor que cero.');
                }

                $producto = $this->producto->obtenerPorId((int) $item['IdProducto']);
                if (!$producto || !$producto['Estado']) {
                    throw new Exception('Uno de los productos no existe o está inactivo.');
                }

                $precioUnitario = (float) $producto['Precio'];
                $subtotal = $precioUnitario * $cantidad;
                $totalPedido += $subtotal;

                $productosProcesados[] = [
                    'IdProducto' => (int) $producto['IdProducto'],
                    'Producto' => $producto['Nombre'],
                    'Cantidad' => $cantidad,
                    'PrecioUnitario' => $precioUnitario,
                    'Subtotal' => round($subtotal, 2)
                ];
            }

            $metodoPago = !empty($data['MetodoPago']) ? trim((string) $data['MetodoPago']) : 'Efectivo';

            $this->pedido->actualizar($idPedido, [
                'IdUsuario' => (int) $data['IdUsuario'],
                'Total' => round($totalPedido, 2),
                'MetodoPago' => $metodoPago
            ]);

            $this->detallePedido->eliminarPorPedido($idPedido);

            foreach ($productosProcesados as $detalle) {
                $this->detallePedido->crear([
                    'IdPedido' => $idPedido,
                    'IdProducto' => $detalle['IdProducto'],
                    'Cantidad' => $detalle['Cantidad'],
                    'PrecioUnitario' => $detalle['PrecioUnitario']
                ]);
            }

            $this->db->commit();

            return [
                'IdPedido' => $idPedido,
                'IdUsuario' => (int) $data['IdUsuario'],
                'Total' => round($totalPedido, 2),
                'MetodoPago' => $metodoPago,
                'Productos' => $productosProcesados
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function eliminar(int $idPedido): void
    {
        try {
            $this->db->beginTransaction();

            $this->detallePedido->eliminarPorPedido($idPedido);
            $this->pedido->eliminar($idPedido);

            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
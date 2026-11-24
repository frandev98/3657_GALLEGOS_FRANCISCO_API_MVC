<?php

namespace App\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Core\Request;
use Core\Response;
use PDO;

class ProductoController
{
    private Producto $producto;
    private Categoria $categoria;

    public function __construct(PDO $db)
    {
        $this->producto = new Producto($db);
        $this->categoria = new Categoria($db);
    }

    public function index(): void
    {
        Response::success(
            $this->producto->obtenerTodos(),
            'Productos obtenidos correctamente'
        );
    }

    public function show(int $id): void
    {
        $producto = $this->producto->obtenerPorId($id);

        if (!$producto) {
            Response::error('Producto no encontrado', 404);
        }

        Response::success($producto, 'Producto obtenido correctamente');
    }

    public function store(): void
    {
        $data = Request::body();

        if (
            empty($data['IdCategoria']) ||
            empty($data['Nombre']) ||
            !isset($data['Precio'])
        ) {
            Response::error('IdCategoria, Nombre y Precio son obligatorios', 422);
        }

        if (!is_numeric($data['Precio']) || (float) $data['Precio'] < 0) {
            Response::error('El Precio debe ser un número mayor o igual a cero', 422);
        }

        if (!$this->categoria->obtenerPorId((int) $data['IdCategoria'])) {
            Response::error('La categoría especificada no existe', 404);
        }

        $id = $this->producto->crear([
            'IdCategoria' => (int) $data['IdCategoria'],
            'Nombre' => trim((string) $data['Nombre']),
            'Descripcion' => isset($data['Descripcion']) ? trim((string) $data['Descripcion']) : null,
            'Precio' => (float) $data['Precio'],
            'Estado' => $data['Estado'] ?? true
        ]);

        Response::success(
            ['IdProducto' => $id],
            'Producto registrado correctamente',
            201
        );
    }

    public function update(int $id): void
    {
        if (!$this->producto->obtenerPorId($id)) {
            Response::error('Producto no encontrado', 404);
        }

        $data = Request::body();

        if (
            empty($data['IdCategoria']) ||
            empty($data['Nombre']) ||
            !isset($data['Precio'])
        ) {
            Response::error('IdCategoria, Nombre y Precio son obligatorios', 422);
        }

        if (!is_numeric($data['Precio']) || (float) $data['Precio'] < 0) {
            Response::error('El Precio debe ser un número mayor o igual a cero', 422);
        }

        if (!$this->categoria->obtenerPorId((int) $data['IdCategoria'])) {
            Response::error('La categoría especificada no existe', 404);
        }

        $this->producto->actualizar($id, [
            'IdCategoria' => (int) $data['IdCategoria'],
            'Nombre' => trim((string) $data['Nombre']),
            'Descripcion' => isset($data['Descripcion']) ? trim((string) $data['Descripcion']) : null,
            'Precio' => (float) $data['Precio'],
            'Estado' => $data['Estado'] ?? true
        ]);

        Response::success(null, 'Producto actualizado correctamente');
    }

    public function destroy(int $id): void
    {
        if (!$this->producto->obtenerPorId($id)) {
            Response::error('Producto no encontrado', 404);
        }

        $this->producto->eliminar($id);

        Response::success(null, 'Producto desactivado correctamente');
    }
}
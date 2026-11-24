<?php

namespace App\Controllers;

use App\Models\Categoria;
use Core\Request;
use Core\Response;
use PDO;
use PDOException;

class CategoriaController
{
    private Categoria $categoria;

    public function __construct(PDO $db)
    {
        $this->categoria = new Categoria($db);
    }

    public function index(): void
    {
        Response::success(
            $this->categoria->obtenerTodos(),
            'Categorías obtenidas correctamente'
        );
    }

    public function show(int $id): void
    {
        $categoria = $this->categoria->obtenerPorId($id);

        if (!$categoria) {
            Response::error('Categoría no encontrada', 404);
        }

        Response::success($categoria, 'Categoría obtenida correctamente');
    }

    public function store(): void
    {
        $data = Request::body();

        if (empty($data['Nombre']) || !is_string($data['Nombre'])) {
            Response::error('El campo Nombre es obligatorio', 422);
        }

        if (mb_strlen(trim($data['Nombre'])) > 50) {
            Response::error('El Nombre no puede exceder 50 caracteres', 422);
        }

        $id = $this->categoria->crear([
            'Nombre' => trim($data['Nombre']),
            'Descripcion' => isset($data['Descripcion']) ? trim((string) $data['Descripcion']) : null
        ]);

        Response::success(
            ['IdCategoria' => $id],
            'Categoría registrada correctamente',
            201
        );
    }

    public function update(int $id): void
    {
        if (!$this->categoria->obtenerPorId($id)) {
            Response::error('Categoría no encontrada', 404);
        }

        $data = Request::body();

        if (empty($data['Nombre']) || !is_string($data['Nombre'])) {
            Response::error('El campo Nombre es obligatorio', 422);
        }

        if (mb_strlen(trim($data['Nombre'])) > 50) {
            Response::error('El Nombre no puede exceder 50 caracteres', 422);
        }

        $this->categoria->actualizar($id, [
            'Nombre' => trim($data['Nombre']),
            'Descripcion' => isset($data['Descripcion']) ? trim((string) $data['Descripcion']) : null
        ]);

        Response::success(null, 'Categoría actualizada correctamente');
    }

    public function destroy(int $id): void
    {
        if (!$this->categoria->obtenerPorId($id)) {
            Response::error('Categoría no encontrada', 404);
        }

        try {
            $this->categoria->eliminar($id);
            Response::success(null, 'Categoría eliminada correctamente');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                Response::error(
                    'No se puede eliminar la categoría porque tiene productos asociados',
                    409
                );
            }
            Response::error('Error interno al eliminar la categoría', 500);
        }
    }
}
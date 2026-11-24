<?php

namespace App\Controllers;

use App\Models\Rol;
use Core\Request;
use Core\Response;
use PDO;
use PDOException;

class RolController
{
    private Rol $rol;

    public function __construct(PDO $db)
    {
        $this->rol = new Rol($db);
    }

    public function index(): void
    {
        Response::success(
            $this->rol->obtenerTodos(),
            'Roles obtenidos correctamente'
        );
    }

    public function show(int $id): void
    {
        $rol = $this->rol->obtenerPorId($id);

        if (!$rol) {
            Response::error('Rol no encontrado', 404);
        }

        Response::success($rol, 'Rol obtenido correctamente');
    }

    public function store(): void
    {
        $data = Request::body();

        if (empty($data['Nombre']) || !isset($data['NivelAcceso'])) {
            Response::error('Nombre y NivelAcceso son obligatorios', 422);
        }

        $nivel = filter_var($data['NivelAcceso'], FILTER_VALIDATE_INT);
        if ($nivel === false || $nivel < 1 || $nivel > 10) {
            Response::error('NivelAcceso debe ser un número entero entre 1 y 10', 422);
        }

        $id = $this->rol->crear([
            'Nombre' => trim((string) $data['Nombre']),
            'NivelAcceso' => $nivel
        ]);

        Response::success(
            ['IdRol' => $id],
            'Rol registrado correctamente',
            201
        );
    }

    public function update(int $id): void
    {
        if (!$this->rol->obtenerPorId($id)) {
            Response::error('Rol no encontrado', 404);
        }

        $data = Request::body();

        if (empty($data['Nombre']) || !isset($data['NivelAcceso'])) {
            Response::error('Nombre y NivelAcceso son obligatorios', 422);
        }

        $nivel = filter_var($data['NivelAcceso'], FILTER_VALIDATE_INT);
        if ($nivel === false || $nivel < 1 || $nivel > 10) {
            Response::error('NivelAcceso debe ser un número entero entre 1 y 10', 422);
        }

        $this->rol->actualizar($id, [
            'Nombre' => trim((string) $data['Nombre']),
            'NivelAcceso' => $nivel
        ]);

        Response::success(null, 'Rol actualizado correctamente');
    }

    public function destroy(int $id): void
    {
        if (!$this->rol->obtenerPorId($id)) {
            Response::error('Rol no encontrado', 404);
        }

        try {
            $this->rol->eliminar($id);
            Response::success(null, 'Rol eliminado correctamente');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                Response::error(
                    'No se puede eliminar el rol porque tiene usuarios asignados',
                    409
                );
            }
            Response::error('Error interno al eliminar el rol', 500);
        }
    }
}
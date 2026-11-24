<?php

namespace App\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use Core\Request;
use Core\Response;
use PDO;

class UsuarioController
{
    private Usuario $usuario;
    private Rol $rol;

    public function __construct(PDO $db)
    {
        $this->usuario = new Usuario($db);
        $this->rol = new Rol($db);
    }

    public function index(): void
    {
        Response::success(
            $this->usuario->obtenerTodos(),
            'Usuarios obtenidos correctamente'
        );
    }

    public function show(int $id): void
    {
        $usuario = $this->usuario->obtenerPorId($id);

        if (!$usuario) {
            Response::error('Usuario no encontrado', 404);
        }

        Response::success($usuario, 'Usuario obtenido correctamente');
    }

    public function store(): void
    {
        $data = Request::body();

        if (
            empty($data['IdRol']) ||
            empty($data['Nombres']) ||
            empty($data['Apellidos']) ||
            empty($data['Email'])
        ) {
            Response::error('IdRol, Nombres, Apellidos y Email son obligatorios', 422);
        }

        $email = trim((string) $data['Email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('El formato del Email no es válido', 422);
        }

        if (!$this->rol->obtenerPorId((int) $data['IdRol'])) {
            Response::error('El rol especificado no existe', 404);
        }

        if ($this->usuario->obtenerPorEmail($email)) {
            Response::error('El Email ya se encuentra registrado', 409);
        }

        $id = $this->usuario->crear([
            'IdRol' => (int) $data['IdRol'],
            'Nombres' => trim((string) $data['Nombres']),
            'Apellidos' => trim((string) $data['Apellidos']),
            'Email' => $email,
            'Activo' => $data['Activo'] ?? true
        ]);

        Response::success(
            ['IdUsuario' => $id],
            'Usuario registrado correctamente',
            201
        );
    }

    public function update(int $id): void
    {
        if (!$this->usuario->obtenerPorId($id)) {
            Response::error('Usuario no encontrado', 404);
        }

        $data = Request::body();

        if (
            empty($data['IdRol']) ||
            empty($data['Nombres']) ||
            empty($data['Apellidos']) ||
            empty($data['Email'])
        ) {
            Response::error('IdRol, Nombres, Apellidos y Email son obligatorios', 422);
        }

        $email = trim((string) $data['Email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('El formato del Email no es válido', 422);
        }

        if (!$this->rol->obtenerPorId((int) $data['IdRol'])) {
            Response::error('El rol especificado no existe', 404);
        }

        $existente = $this->usuario->obtenerPorEmail($email);
        if ($existente && (int) $existente['IdUsuario'] !== $id) {
            Response::error('El Email ya pertenece a otro usuario', 409);
        }

        $this->usuario->actualizar($id, [
            'IdRol' => (int) $data['IdRol'],
            'Nombres' => trim((string) $data['Nombres']),
            'Apellidos' => trim((string) $data['Apellidos']),
            'Email' => $email,
            'Activo' => $data['Activo'] ?? true
        ]);

        Response::success(null, 'Usuario actualizado correctamente');
    }

    public function destroy(int $id): void
    {
        if (!$this->usuario->obtenerPorId($id)) {
            Response::error('Usuario no encontrado', 404);
        }

        $this->usuario->eliminar($id);

        Response::success(null, 'Usuario desactivado correctamente');
    }
}
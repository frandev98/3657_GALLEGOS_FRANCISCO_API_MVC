<?php

use App\Controllers\CategoriaController;
use App\Controllers\ProductoController;
use App\Controllers\RolController;
use App\Controllers\UsuarioController;
use App\Controllers\PedidoController;

// ==========================================================
// INSTANCIAR CONTROLLERS
// ==========================================================

$categoriaController = new CategoriaController($db);
$productoController  = new ProductoController($db);
$rolController       = new RolController($db);
$usuarioController   = new UsuarioController($db);
$pedidoController    = new PedidoController($db);

// ==========================================================
// CATEGORÍAS
// ==========================================================

$router->get('/api/categorias', [$categoriaController, 'index']);
$router->get('/api/categorias/{id}', [$categoriaController, 'show']);
$router->post('/api/categorias', [$categoriaController, 'store']);
$router->put('/api/categorias/{id}', [$categoriaController, 'update']);
$router->delete('/api/categorias/{id}', [$categoriaController, 'destroy']);

// ==========================================================
// PRODUCTOS
// ==========================================================

$router->get('/api/productos', [$productoController, 'index']);
$router->get('/api/productos/{id}', [$productoController, 'show']);
$router->post('/api/productos', [$productoController, 'store']);
$router->put('/api/productos/{id}', [$productoController, 'update']);
$router->delete('/api/productos/{id}', [$productoController, 'destroy']);

// ==========================================================
// ROLES
// ==========================================================

$router->get('/api/roles', [$rolController, 'index']);
$router->get('/api/roles/{id}', [$rolController, 'show']);
$router->post('/api/roles', [$rolController, 'store']);
$router->put('/api/roles/{id}', [$rolController, 'update']);
$router->delete('/api/roles/{id}', [$rolController, 'destroy']);

// ==========================================================
// USUARIOS
// ==========================================================

$router->get('/api/usuarios', [$usuarioController, 'index']);
$router->get('/api/usuarios/{id}', [$usuarioController, 'show']);
$router->post('/api/usuarios', [$usuarioController, 'store']);
$router->put('/api/usuarios/{id}', [$usuarioController, 'update']);
$router->delete('/api/usuarios/{id}', [$usuarioController, 'destroy']);

// ==========================================================
// PEDIDOS Y REPORTE DE VENTAS
// ==========================================================

$router->get('/api/pedidos/ventas', [$pedidoController, 'ventasPorRango']);
$router->get('/api/pedidos', [$pedidoController, 'index']);
$router->get('/api/pedidos/{id}', [$pedidoController, 'show']);
$router->post('/api/pedidos', [$pedidoController, 'store']);
$router->put('/api/pedidos/{id}', [$pedidoController, 'update']);
$router->delete('/api/pedidos/{id}', [$pedidoController, 'destroy']);
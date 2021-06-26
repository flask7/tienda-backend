<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\Login;
use App\Http\Controllers\recuperar;
use App\Http\Controllers\productos;
use App\Http\Controllers\productos_data;
use App\Http\Controllers\SubproductosController;
use App\Http\Controllers\RelacionadosController;
use App\Http\Controllers\BuscadorController;
use App\Http\Controllers\DireccionesController;
use App\Http\Controllers\carrito;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\MensajesController;
use App\Http\Controllers\FacturacionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('recuperar', [recuperar::class, 'recuperar']);

Route::post('recuperar2', [recuperar::class, 'recuperar2']);

Route::post('productos', [productos::class, 'productos']);

Route::post('productos_data', [productos_data::class, 'productos_data']);

Route::post('productos_info', [productos::class, 'productos_info']);

Route::post('imagenes', [productos_data::class, 'imagenes']);

Route::post('imagenes_data', [productos_data::class, 'imagenes_data']);

Route::post('imagenes_categorias', [productos_data::class, 'imagenes_categorias']);

Route::post('login', [Login::class, 'login']);

Route::post('sub_pruductos', [SubproductosController::class, 'sub_productos']);

Route::post('registro', [NoteController::class, 'registro']);

Route::post('relacionados', [RelacionadosController::class, 'relacionados']);

Route::post('buscador', [BuscadorController::class, 'buscador']);

Route::post('buscador_estado', [BuscadorController::class, 'buscador_estado']);

Route::get('buscador_estados', [BuscadorController::class, 'buscador_estados']);

Route::post('direcciones', [DireccionesController::class, 'add_direcciones']);

Route::post('actualizar_direcciones', [DireccionesController::class, 'actualizar_direcciones']);

Route::post('eliminar_direcciones', [DireccionesController::class, 'eliminar_direcciones']);

Route::post('get_direcciones', [DireccionesController::class, 'get_direcciones']);

Route::post('carrito', [carrito::class, 'add_carrito']);

Route::post('carrito_validation', [carrito::class, 'carrito_validation']);

Route::post('eliminar_carrito', [carrito::class, 'eliminar_carrito']);

Route::post('get_carrito', [carrito::class, 'get_carrito']);

Route::post('modificar_carrito', [carrito::class, 'modificar_producto_carrito']);

Route::post('perfil', [PerfilController::class, 'get_perfil']);

Route::post('actualizar_perfil', [PerfilController::class, 'actualizar_perfil']);

Route::post('mensajes_productos', [MensajesController::class, 'mensajes_productos']);

Route::post('mensajes_clientes', [MensajesController::class, 'mensajes_clientes']);

Route::post('orden_pago', [FacturacionController::class, 'orden_pago']);

Route::get('orden_pago', [FacturacionController::class, 'orden_pago']);
Route::get('completado', [FacturacionController::class, 'completado']);

Route::post('facturas', [FacturacionController::class, 'facturas']);

Route::post('historial_pedidos', [FacturacionController::class, 'historial_pedidos']);

// Route::post('pago', [FacturacionController::class, 'index']);

Route::post('pedidos_info', [FacturacionController::class, 'pedidos_info']);

Route::post('repetir_pedido', [FacturacionController::class, 'repetir_pedido']);

Route::post('total_orden', [FacturacionController::class, 'total_orden']);


Route::get('pagar', [FacturacionController::class, 'index']); 
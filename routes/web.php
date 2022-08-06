<?php

use App\Http\Controllers\AnexosController;
use App\Http\Controllers\AreasController;
use App\Http\Controllers\CapturaController;
use App\Http\Controllers\ConfiguracionesController;
use App\Http\Controllers\EntregasController;
use App\Http\Controllers\EscritorioController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\UsuariosController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', [EscritorioController::class, 'index'])->name('escritorio');

    Route::group(['prefix' => '/captura/{uuid}'], function () {
        Route::get('/', [CapturaController::class, 'index'])->name('captura');
        Route::get('/reportes', [CapturaController::class, 'reportesEntrega'])->name('captura.reportesEntrega');
        Route::get('/anexo/{id}', [CapturaController::class, 'anexo'])->name('captura.index');
        Route::get('/anexo/{id}/reportes', [CapturaController::class, 'reportes'])->name('captura.reportes');
        Route::post('/anexo/{id}/nuevo', [CapturaController::class, 'store'])->name('captura.store');
        Route::post('/anexo/{id}/edita/{registro_id}', [CapturaController::class, 'update'])->name('captura.update');
        Route::post('/anexo/{id}/elimina/{registro_id}', [CapturaController::class, 'destroy'])->name('captura.destroy');
        Route::get('/anexo/{id}/buscar/{filtro}', [CapturaController::class, 'search'])->name('captura.search');
    });

    Route::group(['prefix' => 'usuarios'], function () {
        Route::get('/', [UsuariosController::class, 'index'])->name('usuarios.index');
        Route::post('/nuevo', [UsuariosController::class, 'store'])->name('usuarios.store');
        Route::post('/edita/{id}', [UsuariosController::class, 'update'])->name('usuarios.update');
        Route::post('/elimina/{id}', [UsuariosController::class, 'destroy'])->name('usuarios.destroy');
        Route::get('/buscar/{filtro}', [UsuariosController::class, 'search'])->name('usuarios.search');
    });

    Route::group(['prefix' => 'anexos'], function () {
        Route::get('/', [AnexosController::class, 'index'])->name('anexos.index');
        Route::post('/nuevo', [AnexosController::class, 'store'])->name('anexos.store');
        Route::post('/edita/{id}', [AnexosController::class, 'update'])->name('anexos.update');
        Route::post('/elimina/{id}', [AnexosController::class, 'destroy'])->name('anexos.destroy');
        Route::get('/buscar/{filtro}', [AnexosController::class, 'search'])->name('anexos.search');
        Route::post('/actualiza_formato/{id}', [AnexosController::class, 'actualiza_formato'])->name('anexos.actualiza_formato');
    });

    Route::group(['prefix' => 'areas'], function () {
        Route::get('/', [AreasController::class, 'index'])->name('areas.index');
        Route::post('/nuevo', [AreasController::class, 'store'])->name('areas.store');
        Route::post('/edita/{id}', [AreasController::class, 'update'])->name('areas.update');
        Route::post('/elimina/{id}', [AreasController::class, 'destroy'])->name('areas.destroy');
        Route::get('/buscar/{filtro}', [AreasController::class, 'search'])->name('areas.search');
    });

    Route::group(['prefix' => 'entregas'], function () {
        Route::get('/', [EntregasController::class, 'index'])->name('entregas.index');
        Route::post('/nuevo', [EntregasController::class, 'store'])->name('entregas.store');
        Route::post('/edita/{id}', [EntregasController::class, 'update'])->name('entregas.update');
        Route::post('/clona/{id}', [EntregasController::class, 'clone'])->name('entregas.clone');
        Route::post('/elimina/{id}', [EntregasController::class, 'destroy'])->name('entregas.destroy');
        Route::get('/buscar/{filtro}', [EntregasController::class, 'search'])->name('entregas.search');
        Route::post('/finaliza_enlace/{id}', [EntregasController::class, 'finaliza_enlace'])->name('entregas.finaliza_enlace');
        Route::post('/finaliza_titular/{id}', [EntregasController::class, 'finaliza_titular'])->name('entregas.finaliza_titular');
    });

    Route::group(['prefix' => 'configuraciones'], function () {
        Route::get('/', [ConfiguracionesController::class, 'index'])->name('configuraciones.index');
        Route::post('/nuevo', [ConfiguracionesController::class, 'store'])->name('configuraciones.store');
        Route::post('/edita/{id}', [ConfiguracionesController::class, 'update'])->name('configuraciones.update');
        Route::post('/elimina/{id}', [ConfiguracionesController::class, 'destroy'])->name('configuraciones.destroy');
        Route::get('/buscar/{filtro}', [ConfiguracionesController::class, 'search'])->name('configuraciones.search');
    });

    Route::group(['prefix' => 'reportes'], function () {
        Route::get('/', [ReportesController::class, 'index'])->name('reportes.index');
        Route::get('/buscar/{filtro}', [ReportesController::class, 'search'])->name('reportes.search');
        Route::get('/descarga/{uuid}', [ReportesController::class, 'descarga'])->name('reportes.descarga');
    });

    Route::group(['prefix' => '/reporte/{uuid}/anexo/{id}'], function () {
        Route::post('/genera', [ReportesController::class, 'anexo'])->name('reportes.anexo');
    });

    Route::group(['prefix' => '/reporte/{uuid}'], function () {
        Route::post('/genera', [ReportesController::class, 'entrega'])->name('reportes.entrega');
    });

    /*
    Route::group(['prefix' => 'permisos'], function() {
        Route::get('/', [UsuariosController::class, 'permisos'])->name('permisos.index');
        Route::post('/nuevo', [UsuariosController::class, 'storePermiso'])->name('permisos.store');
        Route::post('/edita/{id}', [UsuariosController::class, 'updatePermiso'])->name('permisos.update');
        Route::post('/elimina/{id}', [UsuariosController::class, 'destroyPermiso'])->name('permisos.destroy');
        Route::get('/buscar/{filtro}', [UsuariosController::class, 'searchPermiso'])->name('permisos.search');
    });

    Route::group(['prefix' => 'formatos'], function() {
        Route::get('/', [UsuariosController::class, 'formatos'])->name('formatos.index');
        Route::post('/nuevo', [UsuariosController::class, 'storeFormato'])->name('formatos.store');
        Route::post('/edita/{id}', [UsuariosController::class, 'updateFormato'])->name('formatos.update');
        Route::post('/elimina/{id}', [UsuariosController::class, 'destroyFormato'])->name('formatos.destroy');
        Route::get('/buscar/{filtro}', [UsuariosController::class, 'searchFormato'])->name('formatos.search');
    });
    */
});
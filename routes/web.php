<?php
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
    "auth:sanctum",
    config("jetstream.auth_session"),
    "verified",
])->group(function () {
    Route::get("/", [EscritorioController::class, "index"])->name("escritorio");

    Route::group(["prefix" => "usuarios"], function () {
        Route::get("/", [UsuariosController::class, "index"])->name(
            "usuarios.index"
        );
        Route::post("/nuevo", [UsuariosController::class, "store"])->name(
            "usuarios.store"
        );
        Route::post("/edita/{id}", [UsuariosController::class, "update"])->name(
            "usuarios.update"
        );
        Route::post("/elimina/{id}", [
            UsuariosController::class,
            "destroy",
        ])->name("usuarios.destroy");
        Route::get("/buscar/{filtro}", [
            UsuariosController::class,
            "search",
        ])->name("usuarios.search");
    });

    Route::group(["prefix" => "areas"], function () {
        Route::get("/", [AreasController::class, "index"])->name("areas.index");
        Route::post("/nuevo", [AreasController::class, "store"])->name(
            "areas.store"
        );
        Route::post("/edita/{id}", [AreasController::class, "update"])->name(
            "areas.update"
        );
        Route::post("/elimina/{id}", [AreasController::class, "destroy"])->name(
            "areas.destroy"
        );
        Route::get("/buscar/{filtro}", [
            AreasController::class,
            "search",
        ])->name("areas.search");
    });

    Route::group(["prefix" => "configuraciones"], function () {
        Route::get("/", [ConfiguracionesController::class, "index"])->name(
            "configuraciones.index"
        );
        Route::post("/nuevo", [
            ConfiguracionesController::class,
            "store",
        ])->name("configuraciones.store");
        Route::post("/edita/{id}", [
            ConfiguracionesController::class,
            "update",
        ])->name("configuraciones.update");
        Route::post("/elimina/{id}", [
            ConfiguracionesController::class,
            "destroy",
        ])->name("configuraciones.destroy");
        Route::get("/buscar/{filtro}", [
            ConfiguracionesController::class,
            "search",
        ])->name("configuraciones.search");
    });

    Route::group(["prefix" => "reportes"], function () {
        Route::get("/", [ReportesController::class, "index"])->name(
            "reportes.index"
        );
        Route::get("/buscar/{filtro}", [
            ReportesController::class,
            "search",
        ])->name("reportes.search");
        Route::get("/descarga/{uuid}", [
            ReportesController::class,
            "descarga",
        ])->name("reportes.descarga");
    });

    Route::group(["prefix" => "/reporte/{uuid}/area/{id}"], function () {
        Route::post("/genera", [ReportesController::class, "area"])->name(
            "reportes.area"
        );
        Route::get("/", [ReportesController::class, "area_list"])->name(
            "reportes.area.list"
        );
    });

    Route::group(["prefix" => "/reporte/{uuid}"], function () {
        Route::post("/genera", [ReportesController::class, "pdi"])->name(
            "reportes.pdi"
        );
    });
});
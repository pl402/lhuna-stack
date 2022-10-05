<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TablaReportes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Crea tabla de reportes con los campos: id, uuid, proyecto_uuid, area_id, user_id, titulo, estatus, archivo, created_at, updated_at
        Schema::create("reportes", function (Blueprint $table) {
            $table->id();
            $table
                ->uuid("uuid")
                ->unique()
                ->default(DB::raw("(UUID())"));
            $table->uuid("proyecto_uuid");
            $table->unsignedBigInteger("area_id")->nullable();
            $table->unsignedBigInteger("user_id");
            $table->string("titulo");
            $table->string("estatus")->default("Generando");
            $table->string("archivo")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists("reportes");
    }
}
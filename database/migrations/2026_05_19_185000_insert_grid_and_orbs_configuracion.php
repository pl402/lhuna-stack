<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configuraciones')->insert([
            [
                'clave' => 'Mostrar Cuadrícula',
                'valor' => 'si',
                'tipo' => 'Interruptor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave' => 'Mostrar Orbes del Cursor',
                'valor' => 'si',
                'tipo' => 'Interruptor',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('configuraciones')->whereIn('clave', ['Mostrar Cuadrícula', 'Mostrar Orbes del Cursor'])->delete();
    }
};

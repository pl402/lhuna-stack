<?php

namespace Database\Seeders;

use App\Models\Anexo;
use App\Models\User;
use App\Models\Configuracion;
use App\Models\Entrega;                            
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //
        $admin = Role::create(['name' => 'Administrador']);
        $contraloria = Role::create(['name' => 'Contraloría']);
        $jefeArea = Role::create(['name' => 'Jefe de Área']);
        $enlace = Role::create(['name' => 'Enlace']);
        //Permisos Usuarios
        Permission::create(['name' => 'usuarios.index']);
        Permission::create(['name' => 'usuarios.store']);
        Permission::create(['name' => 'usuarios.update']);
        Permission::create(['name' => 'usuarios.destroy']);
        //Permisos Anexos
        Permission::create(['name' => 'anexos.index']);
        Permission::create(['name' => 'anexos.store']);
        Permission::create(['name' => 'anexos.update']);
        Permission::create(['name' => 'anexos.destroy']);
        //Permisos Areas
        Permission::create(['name' => 'areas.index']);
        Permission::create(['name' => 'areas.store']);
        Permission::create(['name' => 'areas.update']);
        Permission::create(['name' => 'areas.destroy']);
        //Permisos Entregas
        Permission::create(['name' => 'entregas.index']);
        Permission::create(['name' => 'entregas.store']);
        Permission::create(['name' => 'entregas.update']);
        Permission::create(['name' => 'entregas.destroy']);
        //Permisos Configuraciones
        Permission::create(['name' => 'configuraciones.index']);
        Permission::create(['name' => 'configuraciones.store']);
        Permission::create(['name' => 'configuraciones.update']);
        Permission::create(['name' => 'configuraciones.destroy']);
        //Permisos Reportes
        Permission::create(['name' => 'reportes.index']);
        Permission::create(['name' => 'reportes.store']);
        Permission::create(['name' => 'reportes.update']);
        Permission::create(['name' => 'reportes.destroy']);

        //Permisos de Administrador a Usuarios
        $admin->givePermissionTo('usuarios.index');
        $admin->givePermissionTo('usuarios.store');
        $admin->givePermissionTo('usuarios.update');
        $admin->givePermissionTo('usuarios.destroy');
        //Permisos de Administrador a Areas
        $admin->givePermissionTo('areas.index');
        $admin->givePermissionTo('areas.store');
        $admin->givePermissionTo('areas.update');
        $admin->givePermissionTo('areas.destroy');
        //Permisos de Administrador a Configuraciones
        $admin->givePermissionTo('configuraciones.index');
        $admin->givePermissionTo('configuraciones.store');
        $admin->givePermissionTo('configuraciones.update');
        $admin->givePermissionTo('configuraciones.destroy');
        //Permisos de Administrador a Reportes
        $admin->givePermissionTo('reportes.index');
        $admin->givePermissionTo('reportes.store');
        $admin->givePermissionTo('reportes.update');
        $admin->givePermissionTo('reportes.destroy');

        //Permisos de Contraloría a Usuarios
        $contraloria->givePermissionTo('usuarios.index');
        $contraloria->givePermissionTo('usuarios.store');
        $contraloria->givePermissionTo('usuarios.update');
        //Permisos de Contraloria a Usuarios
        $contraloria->givePermissionTo('reportes.index');
        $contraloria->givePermissionTo('reportes.store');
        $contraloria->givePermissionTo('reportes.update');
        $contraloria->givePermissionTo('reportes.destroy');

        $user = new User;
        $user->name = 'Administrador del Sistema';
        $user->email = 'correo@admin.com';
        $user->password = bcrypt('C0n7r4s3ña!');
        $user->save();
        $user->assignRole($admin);
        
        $configuracion = new Configuracion;
        $configuracion->clave = 'Reporte / Titulo / Primera Línea';
        $configuracion->valor = 'PODER LEGISLATIVO';
        $configuracion->tipo = 'Texto';
        $configuracion->save();

        $configuracion = new Configuracion;
        $configuracion->clave = 'Reporte / Titulo / Segunda Línea';
        $configuracion->valor = 'H. CONGRESO DEL ESTADO DE NAYARIT';
        $configuracion->tipo = 'Texto';
        $configuracion->save();

        $configuracion = new Configuracion;
        $configuracion->clave = 'Reporte / Titulo / Tercera Línea';
        $configuracion->tipo = 'Texto';
        $configuracion->valor = 'XXXIII LEGISLATURA';
        $configuracion->save();

        $configuracion = new Configuracion;
        $configuracion->clave = 'Reporte / Logo / Izquierda';
        $configuracion->tipo = 'Imagen';
        $configuracion->valor = '/img/logo_doc.png';
        $configuracion->save();

        $configuracion = new Configuracion;
        $configuracion->clave = 'Reporte / Logo / Derecha';
        $configuracion->tipo = 'Imagen';
        $configuracion->valor = '/img/logo.png';
        $configuracion->save();

        $configuracion = new Configuracion;
        $configuracion->clave = 'Reporte / Firma / Nombre Contralor(a)';
        $configuracion->tipo = 'Texto';
        $configuracion->valor = 'Salma Judith Sepúlveda López';
        $configuracion->save();

        $configuracion = new Configuracion;
        $configuracion->clave = 'Reporte / Firma / Cargo Contralor(a)';
        $configuracion->tipo = 'Texto';
        $configuracion->valor = 'Contralora Interna del Congreso del Estado de Nayarit';
        $configuracion->save();

        $configuracion = new Configuracion;
        $configuracion->clave = 'Reporte / Firma / Leyenda Contralor(a)';
        $configuracion->tipo = 'Texto';
        $configuracion->valor = 'Quien interviene para dar fe de la celebración del acto';
        $configuracion->save();
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder{
    /**
     * Run the database seeds.
     */
    public function run(): void{
        #User
        Permission::create(['name' => 'crear usuarios']);
        Permission::create(['name' => 'editar usuarios']);
        Permission::create(['name' => 'eliminar usuarios']);
        Permission::create(['name' => 'ver usuarios']);
        # Roles
        Permission::create(['name' =>'crear roles']);
        Permission::create(['name' =>'editar roles']);
        Permission::create(['name' =>'eliminar roles']);
        Permission::create(['name' =>'ver roles']);
        # Permisos
        Permission::create(['name' =>'crear permisos']);
        Permission::create(['name' =>'editar permisos']);
        Permission::create(['name' =>'eliminar permisos']);
        Permission::create(['name' =>'ver permisos']);
        # Espacios
        Permission::create(['name' => 'crear espacios']);
        Permission::create(['name' => 'editar espacios']);
        Permission::create(['name' => 'eliminar espacios']);
        Permission::create(['name' => 'ver espacios']);
        #empleado
        Permission::create(['name' => 'crear empleados']);
        Permission::create(['name' => 'editar empleados']);
        Permission::create(['name' => 'eliminar empleados']);
        Permission::create(['name' => 'ver empleados']);
        #Tipo Empleados
        Permission::create(['name' => 'crear tipos_empleados']);
        Permission::create(['name' => 'editar tipos_empleados']);
        Permission::create(['name' => 'eliminar tipos_empleados']);
        Permission::create(['name' => 'ver tipos_empleados']);
        #Horarios
        Permission::create(['name' => 'crear horarios']);
        Permission::create(['name' => 'editar horarios']);
        Permission::create(['name' => 'eliminar horarios']);
        Permission::create(['name' => 'ver horarios']);
        #movimientos
        Permission::create(['name' => 'crear movimientos']);
        Permission::create(['name' => 'editar movimientos']);
        Permission::create(['name' => 'eliminar movimientos']);
        Permission::create(['name' => 'ver movimientos']);
        #alertas
        Permission::create(['name' => 'crear alertas']);
        Permission::create(['name' => 'editar alertas']);
        Permission::create(['name' => 'eliminar alertas']);
        Permission::create(['name' => 'ver alertas']);
         # Configuracion de alertas
        Permission::create(['name' => 'crear configuraciones de alerta']);
        Permission::create(['name' => 'editar configuraciones de alerta']);
        Permission::create(['name' => 'eliminar configuraciones de alerta']);
        Permission::create(['name' => 'ver configuraciones de alerta']);
    }
}

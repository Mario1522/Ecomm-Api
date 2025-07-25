<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => Hash::make('12345678'),
            'type' => 'customer'
        ]);
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('12345678'),
            'type' => 'admin'
        ]);



        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);
        Permission::create(['name' => 'view roles']);
        Permission::create(['name' => 'create roles']);
        Permission::create(['name' => 'edit roles']);
        Permission::create(['name' => 'delete roles']);
        Permission::create(['name' => 'view permissions']);
        Permission::create(['name' => 'view products']);
        Permission::create(['name' => 'create products']);
        Permission::create(['name' => 'edit products']);
        Permission::create(['name' => 'delete products']);
        Permission::create(['name' => 'view orders']);
        Permission::create(['name' => 'create orders']);
        Permission::create(['name' => 'view categories']);
        Permission::create(['name' => 'create categories']);
        Permission::create(['name' => 'edit categories']);
        Permission::create(['name' => 'delete categories']);
        Permission::create(['name' => 'assign permissions']);
        $productAdmin = Role::where('name', 'product admin')->first();
        $productAdmin->givePermissionTo([
            'assign permissions'
        ]);
        $orderAdmin = Role::create(['name' => 'order admin']);
        $orderAdmin->givePermissionTo([
            'view orders',
            'create orders',
        ]);
        $usersAdmin = Role::where('name', 'users admin')->first();
        $usersAdmin->givePermissionTo([
            'assign permissions'
        ]);
        $admin = Role::where('name', 'admin')->first();
        $admin->givePermissionTo([
            'assign permissions'
            // 'view users',
            // 'create users',
            // 'edit users',
            // 'delete users',
            // 'view roles',
            // 'create roles',
            // 'edit roles',
            // 'delete roles',
            // 'view permissions',
            // 'view products',
            // 'create products',
            // 'edit products',
            // 'delete products',
            // 'view orders',
            // 'create orders',
            // 'view categories',
            // 'create categories',
            // 'edit categories',
            // 'delete categories',
        ]);

        $admin = User::create([
                "name" => "admin",
                "email" => "Admin@example.com",
                 "password" => Hash::make("12345678")             
        ]);
        $admin->assignRole('admin');


    }
}

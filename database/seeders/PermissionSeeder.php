<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions  = Permission::all()->pluck('name')->toArray();

        if(!in_array('users.read', $permissions))
            Permission::create(['name' => 'users.read']);
        if(!in_array('users.write', $permissions))
            Permission::create(['name' => 'users.write']);
        if(!in_array('users.delete', $permissions))
            Permission::create(['name' => 'users.delete']);

        if(!in_array('roles.read', $permissions))
            Permission::create(['name' => 'roles.read']);
        if(!in_array('roles.write', $permissions))
            Permission::create(['name' => 'roles.write']);
        if(!in_array('roles.delete', $permissions))
            Permission::create(['name' => 'roles.delete']);

        if(!in_array('occasions.read', $permissions))
            Permission::create(['name' => 'occasions.read']);
        if(!in_array('occasions.write', $permissions))
            Permission::create(['name' => 'occasions.write']);
        if(!in_array('occasions.delete', $permissions))
            Permission::create(['name' => 'occasions.delete']);

        if(!in_array('join_requests.read', $permissions))
            Permission::create(['name' => 'join_requests.read']);
        if(!in_array('join_requests.write', $permissions))
            Permission::create(['name' => 'join_requests.write']);
        
        if(!in_array('rawadeds.read', $permissions))
            Permission::create(['name' => 'rawadeds.read']);
        if(!in_array('rawadeds.write', $permissions))
            Permission::create(['name' => 'rawadeds.write']);
        if(!in_array('rawadeds.delete', $permissions))
            Permission::create(['name' => 'rawadeds.delete']);

        if(!in_array('poem_types.read', $permissions))
            Permission::create(['name' => 'poem_types.read']);
        if(!in_array('poem_types.write', $permissions))
            Permission::create(['name' => 'poem_types.write']);
        if(!in_array('poem_types.delete', $permissions))
            Permission::create(['name' => 'poem_types.delete']);

        if(!in_array('categories.read', $permissions))
            Permission::create(['name' => 'categories.read']);
        if(!in_array('categories.write', $permissions))
            Permission::create(['name' => 'categories.write']);
        if(!in_array('categories.delete', $permissions))
            Permission::create(['name' => 'categories.delete']);

        if(!in_array('languages.read', $permissions))
            Permission::create(['name' => 'languages.read']);
        if(!in_array('languages.write', $permissions))
            Permission::create(['name' => 'languages.write']);
        if(!in_array('languages.delete', $permissions))
            Permission::create(['name' => 'languages.delete']);

        if(!in_array('poetry_collections.read', $permissions))
            Permission::create(['name' => 'poetry_collections.read']);
        if(!in_array('poetry_collections.write', $permissions))
            Permission::create(['name' => 'poetry_collections.write']);
        if(!in_array('poetry_collections.delete', $permissions))
            Permission::create(['name' => 'poetry_collections.delete']);

        if(!in_array('lessons.read', $permissions))
            Permission::create(['name' => 'lessons.read']);
        if(!in_array('lessons.write', $permissions))
            Permission::create(['name' => 'lessons.write']);
        if(!in_array('lessons.delete', $permissions))
            Permission::create(['name' => 'lessons.delete']);

        if(!in_array('pages.read', $permissions))
            Permission::create(['name' => 'pages.read']);
        if(!in_array('pages.write', $permissions))
            Permission::create(['name' => 'pages.write']);

        if(!in_array('messages.read', $permissions))
            Permission::create(['name' => 'messages.read']);
        if(!in_array('messages.write', $permissions))
            Permission::create(['name' => 'messages.write']);

        if(!Role::where('name', 'admin')->exists())
            Role::create([
                'id'         => 1,
                'name'       => 'admin',
                'guard_name' => 'web',
            ]);
        if(!Role::where('name', 'user')->exists())
            Role::create([
                'id'         => 2,
                'name'       => 'user',
                'guard_name' => 'web',
            ]);
        if(!Role::where('name', 'poet')->exists())
            Role::create([
                'id'         => 3,
                'name'       => 'poet',
                'guard_name' => 'web',
            ]);

        $admin_role = Role::where('name', 'admin')->first();
        $admin_permissions = Permission::pluck('id')->toArray();
        $admin_role->syncPermissions($admin_permissions);
    }
}

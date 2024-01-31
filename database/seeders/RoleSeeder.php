<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $admin = User::create([
            'firstname' => 'admin',
            'lastname' => 'admin',
            'username' => 'admin',
            'email' => 'enligneservices5@gmail.com',
            'password' => Hash::make('admin'),
            'verification_code' =>'999999',
            'email_verify_at' => now(),
        ]);

        $adminRole = Role::create(['name' => 'admin']);
     /*    $userRole = Role::create(['name' => 'user']);  */

        $admin->assignRole($adminRole);

        $admin->givePermissionTo(
           /*  'all' */
            'addCategory',
            'getCategory',
            'editCategory',
            'deleteCategory'
        );
        $admin->can('addCategory');
 
    }
}

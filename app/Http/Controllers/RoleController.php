<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{

    /**
     * @param $id
     * attribuer le rôle d'admin
     */

   public function assignAdminRole($id)
{
    $user = User::find($id);

    if (!$user->hasRole('admin')) {
        $adminRole = Role::where('name', 'admin')->first();
        $user->assignRole($adminRole);

        return response()->json([
            'status' => 200,
            'message' => "Le rôle d'administrateur a été attribué à l'utilisateur avec succès.",
        ], 200);
    }

    return response()->json([
        "status" => 403,
        'message' => "L'utilisateur a déjà le rôle d'administrateur.",
    ], 403);
}


    /**
     * @param $id
     * retirer le rôle d'admin 
     */
    public function removeRole($id)
    {

        $user = User::find($id);

        if (!$user->hasRole('admin')) {

            return response()->json([
                'status' => 422,
                'message' => "L'utilisateur n'a pas le rôle d'admin.",
            ], 422);
        } else {

            $user->removeRole('admin');

            return response()->json([
                'status' => 200,
                'message' => "Le rôle d'admin a été retiré avec succès pour cet utilisateur.",
            ], 200);
        }
    }



}

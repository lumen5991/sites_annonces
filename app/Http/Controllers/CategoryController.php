<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CategoryController extends Controller
{
    /**
     * @param Request $request
     * ajouter catégorie
     */
    public function addCategory(Request $request)
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        if (!$authUser->hasRole('admin')) {
            return response()->json([
                'status' => 403,
                'error' => true,
                'message' => "Vous n'êtes pas autorisé à ajouter une catégorie.",
            ], 403);
        }

        $data = $request->all();

        $request->validate([
            "name" => ["string", "min:2", "required"],
            "description" => ["string"]
        ]);

        $category = Category::where('name', $data['name'])->first();

        if (!is_null($category)) {
            return response()->json([
                'status' => 422,
                'error' => true,
                'message' => 'Une catégorie avec ce nom existe déjà.',
            ], 422);
        }

        $category = Category::create([
            "name" => $data["name"],
            "description" => $data["description"]
        ]);

        return response()->json([
            "status" => 200,
            'error' => false,
            "message" => "Catégorie ajoutée avec succès",
            "category" => $category
        ], 200);
    }


    /**
     * afficher toutes les catégories
     */
    public function getAllCategories()
    {
        $categories = Category::all();

        return response()->json([
            'status' => 200,
            'categories' => $categories,
        ], 200);
    }


    /**
     *  @param $id
     *  afficher une catégorie selon son id
     */

    public function getCategory($id)
    {

        /** @var User $authUser */
        $authUser = Auth::user();
        if (!$authUser->hasRole('admin')) {
            return response()->json([
                'status' => 403,
                'error' => true,
                'message' => "Vous n'êtes pas autorisé à afficher cette catégorie.",
            ], 403);
        }

        $category = Category::find($id);

        return response()->json([
            'category' => $category,
        ], 200);
    }

    /**
     * @param Request $request, $id
     * modifier une catégorie
     */
    public function editCategory(Request $request, $id)
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        if (!$authUser->hasRole('admin')) {
            return response()->json([
                'status' => 403,
                'error' => true,
                'message' => "Vous n'êtes pas autorisé à modifier une catégorie.",
            ], 403);
        }

        $request->validate([
            "new_name" => "required|string|min:2",
            "new_description" => "nullable|string",
        ]);

        $category = Category::find($id);

        if (is_null($category)) {
            return response()->json([
                'status' => 422,
                'message' => "Cette catégorie n'existe pas !",
            ], 422);
        }

        $category->update([
            'name' => $request->new_name,
            'description' => $request->new_description,
        ]);

        return response()->json([
            'status' => 200,
            'message' => "La catégorie a été bien mise à jour",
        ], 200);
    }

    
    /**
     * @param $id
     * supprimer une catégorie
     */
    public function deleteCategory($id)
    {

       /** @var User $authUser */
       $authUser = Auth::user();
       if (!$authUser->hasRole('admin')) {
           return response()->json([
               'status' => 403,
               'error' => true,
               'message' => "Vous n'êtes pas autorisé à afficher cette catégorie.",
           ], 403);
       }

        $category = Category::find($id);

        if (is_null($category)) {
            return response()->json([
                'status' => 422,
                'message' => "Cette catégorie n'existe pas, vous ne pouvez la supprimer !",
            ], 422);
        }

        $category->delete();

        return response()->json([
            'status' => 200,
            'message' => "La catégorie a été supprimée avec succès.",
        ], 200);
    }
}

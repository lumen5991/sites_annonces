<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    //
    public function addCategory(Request $request)
    {
        $data = $request->all();

        $request->validate([
            "name" => ["string", "min:2", "required"],
            "description" => ["string"]
        ]);

        $Category = Category::where('name', $data['name'])->first();

        if (!is_null($Category)) {
            return response()->json([
                'status' => 422,
                'message' => 'Une catégorie avec ce nom existe déjà.',
            ], 422);
        }

        $category = Category::create([
            "name" => $data["name"],
            "description" => $data["description"]
        ]);

        return response()->json([
            "status" => 200,
            "message" => "Catégorie ajoutée avec succès",
            "category" => $category
        ], 200);
    }

    public function getAllCategories()
    {


        $categories = Category::all();
        return response()->json([
            'status' => 200,
            'categories' => $categories,
        ], 200);
    }
    public function getCategory($id)
    {
        $category = Category::find($id);
        return response()->json([
            'category' => $category,
        ], 200);
    }
    public function editCategory(Request $request, $id)
    {
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

      /*    if ($category->author !== Auth::user()->id) {
            return response()->json([
                'status' => 403,
                'message' => "Vous n'êtes pas autorisé à modifier cette catégorie.",
            ], 403);
        } */

        $category->update([
            'name' => $request->new_name,
            'description' => $request->new_description,
        ]);

        return response()->json([
            'status' => 200,
            'message' => "La catégorie a été bien mise à jour",
        ], 200);
    }

    public function deleteCategory($id)
    {

        $category = Category::find($id);

        if (is_null($category)) {
            return response()->json([
                'status' => 422,
                'message' => "Cette catégorie n'existe pas, vous ne pouvez la supprimer !",
            ], 422);
        }

      /*   if ($category->author !== Auth::user()->id) {
            return response()->json([
                'status' => 403,
                'message' => "Vous n'êtes pas autorisé à supprimer cette catégorie.",
            ], 403);
        } */

        $category->delete();

        return response()->json([
            'status' => 200,
            'message' => "La catégorie a été supprimée avec succès.",
        ], 200);
    }

}

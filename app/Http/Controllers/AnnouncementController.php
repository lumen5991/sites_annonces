<?php
namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function addAnnounce(Request $request)
    {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return response()->json([
                "status" => 401,
                "message" => "Veuillez vous connecter avant d'ajouter une annonce.",
            ], 401);
        }

        $data = $request->validate([
            "title" => ["string", "required", "min:2"],
            "body" => ["string"],
            "category" => ["string", "exists:categories,id"],
            "pictures.*" => ["image|mimes:jpeg,png,jpg,gif,svg"],
        ]);

        $announcement = Announcement::create([
            "title" => $data["title"],
            "body" => $data["body"],
            "category" => $data["category"],
            "added_at" => now(),
            "author" => Auth::user()->id,
        ]);

        $imagePaths = [];

if ($request->hasFile('pictures')) {
    foreach ($request->file('pictures') as $picture) {
        $path = $picture->store('announce_pictures');

        $announcement->pictures()->create([
            'path' => $path,
            'announcement' => $announcement->id,
        ]);

        
        $imagePaths[] = asset('storage/' . $path);
    }
}


        $announcement->load('category', 'author', 'pictures');

        return response()->json([
            "status" => 200,
            "message" => "Annonce publiée avec succès",
            "annonce" => $announcement,
            "images" => $imagePaths,
        ], 200);
    }
}

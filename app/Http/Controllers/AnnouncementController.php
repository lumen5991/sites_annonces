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
        // TODO Revoie la validation des fichiers
        $data = $request->validate([
            "title" => ["string", "required", "min:2"],
            "body" => ["string"],
            "category" => ["string", "exists:categories,id"],
            "pictures" => ["array"],
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

                // Débogage : Affiche le chemin temporairement
                // TODO ton return est mal placé. Le code n'atteint pas la création des images dans la table pictures
                // TODO Pour débuguer utilise la fonction "dd() de laravel"
                // TODO tu peux faire dd($path) pour voir ce qu'il en retourne
                // return response()->json(['path' => $path]);

                //TODO Ce code n'est jamais exécuté dû au return de la ligne précédente
                $announcement->pictures()->create([
                    'path' => $path,
                    'announcement' => $announcement->id,
                ]);

                $imagePaths[] = public_path('storage/' . $path);
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

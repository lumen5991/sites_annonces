<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Announcement;


class AnnouncementController extends Controller
{
    //ajouter une annonce
    public function addAnnounce(Request $request)
    {
        // TODO Revoir la validation des fichiers
        $data = $request->validate([
            "title" => ["string", "required", "min:2"],
            "body" => ["string"],
            "category" => ["string", "exists:categories,id"],
            "pictures" => ["array"],
            "pictures.*" => ["image", "mimes:jpeg,png,jpg,gif,svg"],
        ]);

        $announcement = Announcement::create([

            "title" => $data["title"],
            "body" => $data["body"],
            "category" => $data["category"],
            "added_at" => now(),
            "author" => Auth::user()->id

        ]);

        $imagePaths = [];
        if ($request->hasFile('pictures')) {
            $pictures = $request->file('pictures');
            foreach ($pictures as $picture) {

                $path = $picture->store('announce_pictures', 'public');

                $announcement->pictures()->create([

                    'path' => $path,
                    'announcement' => $announcement->id,
                ]);

                $imagePaths[] = $path;

            }


        }

        $announcement->load('category', 'author', 'pictures');

        return response()->json([

            "status" => 200,
            "message" => "Annonce publiée avec succès",
            "annonce" => $announcement,
            "pictures" => $imagePaths,

        ], 200);

    }


    //afficher toutes les annonces
    public function getAllAnnounce()
    {
        $announces = Announcement::all();
        return response()->json([
            'status' => 200,
            'announcements' => $announces,
        ], 200);
    }

    //afficher une annonce
    public function getAnnouncement($id)
    {
        $announcement = Announcement::find($id);
        return response()->json([
            'announcement' => $announcement
        ], 200);
    }

    //mise à jour des annonces (éditer)

    public function editAnnounce(Request $request, $id)
    {

        $data = $request->validate([
            "title" => ["string", "min:2"],
            "body" => ["string"],
            "category" => ["string", "exists:categories,id"],
            "pictures" => ["array"],
            "pictures.*" => ["image", "mimes:jpeg,png,jpg,gif,svg"],
        ]);

        $announcement = Announcement::find($id);

        if (is_null($announcement)) {

            return response()->json([
                'status' => 422,
                'message' => "Nous n'avons pas retrouvé cette annonce !",
            ], 422);
        }

        if ($announcement->author !== Auth::user()->id) {
            return response()->json([
                'status' => 403,
                'message' => "Vous n'êtes pas autorisé à modifier cette annonce.",
            ], 403);
        }

        $announcement->update([
            "title" => $data["title"],
            "body" => $data["body"],
            "category" => $data["category"],
            "added_at" => now(),
            "author" => Auth::user()->id

        ]);

        $imagePaths = [];

        if ($request->hasFile('pictures')) {

            $pictures = $request->file('pictures');

            foreach ($pictures as $picture) {

                $path = $picture->store('announce_pictures', 'public');

                $announcement->pictures()->update([

                    'path' => $path,
                    'announcement' => $announcement->id,
                ]);

                $imagePaths[] = $path;

            }

        }

        $announcement->load('category', 'author', 'pictures');

        return response()->json([

            "status" => 200,
            "message" => "Annonce modifiée avec succès",
            "annonce" => $announcement,
            "pictures" => $imagePaths,

        ], 200);

    }

    //suppression des annonces
    public function deleteAnnounce($id)
    {
        $announcement = Announcement::find($id);

        if (is_null($announcement)) {
            return response()->json([
                'status' => 422,
                'message' => "Cette annonce n'a pas été trouvée.",
            ], 422);
        }

        if ($announcement->author !== Auth::user()->id) {
            return response()->json([
                'status' => 403,
                'message' => "Vous n'êtes pas autorisé à supprimer cette annonce.",
            ], 403);
        }

        $announcement->pictures->each->delete();
        
        $announcement->delete();

        return response()->json([

            "status" => 200,
            "message" => "Annonce supprimée avec succès",

        ], 200);

    }

}
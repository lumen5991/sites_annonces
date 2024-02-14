<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Announcement;
use App\Models\Note;


class AnnouncementController extends Controller
{
    /**
     * @param Request $request
     * ajouter une annonce
     */
    public function addAnnounce(Request $request)
    {
        // TODO Revoir la validation des fichiers

        if (!Auth::user()) {
            return response()->json([
                'status' => 401,
                'message' => "Veuillez vous connectez ou vous inscrire avant d'effectuer cette action.",
            ], 401);
        }

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
                    'path' => asset(Storage::url($path)),
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


     /**
     * @param Request $request, $id
     * mise à jour des annonces (éditer) 
     */

   

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

        $announcement->pictures()->delete();

        $announcement->update([
            "title" => $data["title"],
            "body" => $data["body"],
            "category" => $data["category"],
            "added_at" => now(),
            "author" => Auth::user()->id,
        ]);

        $imagePaths = [];

        if ($request->hasFile('pictures')) {
            $pictures = $request->file('pictures');

            foreach ($pictures as $picture) {
                $path = $picture->store('announce_pictures', 'public');

                $announcement->pictures()->create([
                    'path' => asset(Storage::url($path)),
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


    /**
     * afficher toutes les annonces
     */
    public function getAllAnnounce()
    {
        $announcements = Announcement::with('category', 'author', 'pictures')-> latest()->take(6)->get();

       /*  $posts = Post::latest()->take(10)->get(); */
        $notes = Note::all();

        $moyennes = [];

        foreach ($announcements as $announcement) {
            $notesByAnnounce = $notes->where('announcement', $announcement->id);

            $moyenne = $notesByAnnounce->avg('note');

            $moyennes[$announcement->id] = $moyenne;
        }

        return response()->json([
            'status' => 200,
            'announcements' => $announcements,
            'moyennes' => $moyennes,
        ], 200);
    }



    /**
     * @param $id
     * afficher une annonce
     */
    public function getAnnouncement($id)
    {
        $announcement = Announcement::find($id);

        $announcement->load('category', 'author', 'pictures', );

        $notes = Note::where('announcement', $id)->get();

        $moyenne = $notes->avg('note');

        return response()->json([

            'announcement' => $announcement,

            'notes' => $notes,

            "moyenne" => $moyenne
        ], 200);
    }

    /**
 * Afficher les annonces de l'utilisateur connecté
 */
/**
 * Afficher les annonces de l'utilisateur connecté avec les notes et la moyenne
 */
public function getMyAnnouncements()
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'status' => 401,
            'message' => "Vous devez être authentifié pour effectuer cette action.",
        ], 401);
    }

    $userAnnouncements = Announcement::where('author', $user->id)
        ->with('category', 'pictures')
        ->get();

    $notes = Note::whereIn('announcement', $userAnnouncements->pluck('id'))->get();

    $moyennes = [];

    foreach ($userAnnouncements as $announcement) {
        $notesByAnnounce = $notes->where('announcement', $announcement->id);
        $moyenne = $notesByAnnounce->avg('note');
        $moyennes[$announcement->id] = $moyenne;
    }

    return response()->json([
        'status' => 200,
        'announcements' => $userAnnouncements,
        'notes' => $notes,
        'moyennes' => $moyennes,
    ], 200);
}



    /**
     * @param $id
     * suppression des annonces
     */
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
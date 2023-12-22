<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    //
    public function addNote(Request $request, $announcement)
    {
        $data = $request->validate([
            "note" => ["required", "integer", "min:1", "max:5"],
        ]);

        $user = Auth::user();

        $announcement = Announcement::find($announcement);

        if (!$announcement) {
            return response()->json([
                "status" => 404,
                "message" => "Annonce non trouvée.",
            ], 404);
        }

        $existingNote = Note::where('user', $user->id)
            ->where('announcement', $announcement->id)
            ->first();

        if ($existingNote) {
            return response()->json([
                "status" => 400,
                "message" => "Vous avez déjà noté cette annonce.",
            ], 400);
        }

        $note = Note::create([
            'note' => $data['note'],
            'user' => $user->id,
            'announcement' => $announcement->id,
        ]);

        return response()->json([
            "status" => 200,
            "message" => "Note ajoutée avec succès.",
            "note" => $note,
        ], 200);
    }

    //mettre à jour une note

    public function updateNote(Request $request, $id)
    {
        $data = $request->validate([
            "note" => ["required", "integer", "min:1", "max:5"],
        ]);

        $note = note::find($id);

        if (!$note) {
            return response()->json([
                "status" => 404,
                "message" => "note non trouvée.",
            ], 404);
        }

        if ($note->user !== Auth::user()->id) {
            return response()->json([
                "status" => 403,
                "message" => "Vous n'êtes pas l'auteur de cette note. Vous n'avez pas la permission de la mettre à jour.",
            ], 403);
        }

        $note->update([
            'note' => $data['note'],
        ]);

        return response()->json([
            "status" => 200,
            "message" => "Note mise à jour avec succès.",
            "note" => $note,
        ], 200);
    }

    //supprimer une note
    public function deleteNote($id)
    {

        $note = note::find($id);

        if (!$note) {
            return response()->json([
                "status" => 404,
                "message" => "note non trouvée.",
            ], 404);
        }

        if ($note->user !== Auth::user()->id) {
            return response()->json([
                "status" => 403,
                "message" => "Vous n'êtes pas l'auteur de cette note. Vous n'avez pas la permission de la supprimer.",
            ], 403);
        }

        $note->delete();

        return response()->json([
            "status" => 200,
            "message" => "Note supprimée avec succès.",
            "note" => $note,
        ], 200);

    }


}

<?php

namespace App\Http\Controllers;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    //
    public function addNote(Request $request)
    {
        $data = $request->validate([
            "note" => ["required", "integer", "min:1", "max:5"],
            "announcement" => ["required", "exists:announcements,id"],
        ]);

        $user = Auth::user();

        $existingNote = Note::where('user', $user->id)
                            ->where('announcement', $data['announcement'])
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
            'announcement' => $data['announcement'],
        ]);

        return response()->json([
            "status" => 200,
            "message" => "Note ajoutée avec succès.",
            "note" => $note,
        ], 200);
    }

    

}

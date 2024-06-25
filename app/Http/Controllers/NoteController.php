<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{

    public function index()
    {
        $notes = Note::with("user")->where("note_by", Auth::id())->latest()->get();

        return response()->json(["notes" => $notes, "user" => Auth::id()]);
    }

    public function store(Request $request)
    {

        $fileContent = null;

        $attributes = [
            "title" => $request["title"],
            "content" => $request["content"],
            "file_content" => $fileContent,
            "note_by" => Auth::id()
        ];

        $note = Note::create($attributes);

        return response()->json(["success" => true]);
    }
}

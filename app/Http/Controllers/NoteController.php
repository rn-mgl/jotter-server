<?php

namespace App\Http\Controllers;

use App\Models\Note;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

use function PHPUnit\Framework\isEmpty;

class NoteController extends Controller
{

    public function index(Request $request)
    {
        $valid_search_type = ["title", "content"];
        $search_type = $request["search_type"];
        $search_value = $request["search_value"];

        if (!in_array($search_type, $valid_search_type)) {
            throw ValidationException::withMessages([
                "search_type" => "Invalid Search Type",
            ]);
        }

        $query = Note::with("user")->where("note_by", Auth::id())->where("is_deleted", 0);

        if (!empty($search_value)) {
            $query->where("$search_type", "like", "%$search_value%");
        }

        $notes = $query->latest()->get();

        return response()->json(["notes" => $notes, "user" => Auth::id()]);
    }

    public function store(Request $request)
    {

        $fileContent = null;

        if ($request->hasFile("file_content")) {
            $fileContent = cloudinary()->upload($request->file("file_content")->getRealPath(), ["folder" => "jotter-uploads"])->getSecurePath();
        }

        $title = $request["title"] ?? "";

        $attributes = [
            "title" => $title,
            "content" => $request["content"],
            "file_content" => $fileContent,
            "note_by" => Auth::id()
        ];

        $note = Note::create($attributes);

        return response()->json(["success" => true]);
    }

    public function show(Note $note)
    {
        return response()->json($note);
    }

    public function patch(Note $note, Request $request)
    {

        $fileContent = null;

        if ($request->hasFile("file_content")) {
            $fileContent = cloudinary()->upload($request->file("file_content")->getRealPath(), ["folder" => "jotter-uploads"])->getSecurePath();
        } else if ($request["file_content"]) {
            $fileContent = $request["file_content"];
        }

        $title = $request["title"] ?? "";

        $attributes = [
            "title" => $title,
            "content" => $request["content"],
            "file_content" => $fileContent,
            "updated_at" => now()
        ];

        $updated = $note->update($attributes);

        return response()->json(["success" => $updated]);
    }

    public function destroy(Note $note)
    {
        $deleted = $note->update(["is_deleted" => true]);

        return response()->json(["success" => $deleted]);
    }
}

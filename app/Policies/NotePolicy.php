<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotePolicy
{
    public function edit(User $user, Note $note)
    {
        return $user->is($note->user);
    }
}

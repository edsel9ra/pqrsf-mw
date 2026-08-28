<?php

namespace App\Policies;

use App\Models\PqrsfSubmission;
use App\Models\User;

class PqrsfSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessReadOnlyPanel();
    }

    public function view(User $user, PqrsfSubmission $submission): bool
    {
        return $user->canAccessReadOnlyPanel();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, PqrsfSubmission $submission): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, PqrsfSubmission $submission): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}

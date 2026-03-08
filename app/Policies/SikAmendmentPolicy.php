<?php

namespace App\Policies;

use App\Models\SikAmendment;
use App\Models\User;

class SikAmendmentPolicy
{
    public function process(User $user, SikAmendment $amendment): bool
    {
        return $user->isAdmin() || $user->hasRole('Kemahasiswaan') || $user->hasRole('Staf Kemahasiswaan');
    }
}

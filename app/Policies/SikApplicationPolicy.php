<?php

namespace App\Policies;

use App\Models\SikApplication;
use App\Models\User;

class SikApplicationPolicy
{
    public function view(User $user, SikApplication $sikApplication): bool
    {
        return $user->isAdmin() || $user->ormawas()->where('ormawas.id', $sikApplication->ormawa_id)->exists();
    }

    public function processStep(User $user, SikApplication $sikApplication): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $currentStep = $sikApplication->steps()->where('status_step', 'pending')->orderBy('step_order')->first();
        if (! $currentStep) {
            return false;
        }

        return $user->roles->pluck('title')->map(fn ($role) => strtolower(trim($role)))
            ->contains(strtolower(trim($currentStep->role_target)));
    }

    public function updateAfterRevision(User $user, SikApplication $sikApplication): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->ormawas()->where('ormawas.id', $sikApplication->ormawa_id)->exists()
            && $sikApplication->status_sik === 'need_revision';
    }

    public function requestAmendment(User $user, SikApplication $sikApplication): bool
    {
        return $user->isAdmin() || $user->ormawas()->where('ormawas.id', $sikApplication->ormawa_id)->exists();
    }

    public function toggleAmendmentAccess(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('Kemahasiswaan') || $user->hasRole('Staf Kemahasiswaan');
    }
}

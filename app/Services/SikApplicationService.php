<?php

namespace App\Services;

use App\Models\SikApplication;
use App\Models\User;
use Carbon\Carbon;

class SikApplicationService
{
    /**
     * Validate whether a SIK can be used for booking by a user in requested timeline.
     *
     * @return array{0:bool,1:string|null}
     */
    public function canBeUsedForBooking(SikApplication $sik, User $user, Carbon $start, Carbon $end): array
    {
        if ($sik->status_sik !== 'issued') {
            return [false, 'SIK belum terbit, sehingga belum dapat dipakai untuk peminjaman ruang.'];
        }

        // User must belong to the same ormawa as the SIK owner.
        $hasAccess = $user->ormawas()->where('ormawas.id', $sik->ormawa_id)->exists();
        if (! $hasAccess) {
            return [false, 'Anda tidak memiliki akses menggunakan SIK ini.'];
        }

        $timelineStart = Carbon::parse($sik->timeline_mulai_final)->startOfDay();
        $timelineEnd = Carbon::parse($sik->timeline_selesai_final)->endOfDay();

        if ($start->lt($timelineStart) || $end->gt($timelineEnd)) {
            return [false, 'Waktu peminjaman berada di luar timeline final SIK.'];
        }

        return [true, null];
    }
}


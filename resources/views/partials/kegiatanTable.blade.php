<div class="table-responsive">
    {{-- Tambahkan kelas 'modern-table' dan 'home-kegiatan-table' --}}
    <table class="table table-borderless modern-table home-kegiatan-table">
        <thead>
            <tr>
                <th>Kegiatan</th>
                <th>Ruangan</th>
                <th>Waktu</th>
                <th>Peminjam</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kegiatans as $kegiatan)
                <tr>
                    {{-- Tambahkan data-label pada setiap <td> --}}
                    <td data-label="Kegiatan">
                        <div class="kegiatan-title-cell">{{ $kegiatan->nama_kegiatan }}</div>
                    </td>
                    <td data-label="Ruangan">
                        <span class="badge-ruangan">{{ $kegiatan->ruangan->nama ?? '' }}</span>
                    </td>
                    <td data-label="Waktu">
                        <div class="kegiatan-sub-cell">
                            {{ \Carbon\Carbon::parse($kegiatan->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($kegiatan->waktu_selesai)->format('H:i') }}
                        </div>
                    </td>
                    <td data-label="Peminjam">
                        <div class="kegiatan-sub-cell">
                            <div class="user-avatar"><i class="fas fa-user"></i></div>
                            <span class="kegiatan-sub-cell">{{ $kegiatan->user->name ?? '' }}</span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-4">
                        <p class="mb-0 text-muted">Tidak ada kegiatan yang dijadwalkan.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>


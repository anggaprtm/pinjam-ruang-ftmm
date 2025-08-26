<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Nama Kegiatan</th>
                <th>Peminjam</th>
                <th>Ruangan</th>
                <th>Waktu</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kegiatans as $kegiatan)
                {{-- Hanya tampilkan kegiatan yang waktu selesainya belum lewat --}}
                @if(\Carbon\Carbon::parse($kegiatan->waktu_selesai)->isFuture())
                    <tr>
                        <td><strong>{{ $kegiatan->nama_kegiatan }}</strong></td>
                        <td>{{ $kegiatan->user->name ?? '-' }}</td>
                        <td><span class="badge-ruangan">{{ $kegiatan->ruangan->nama ?? '-' }}</span></td>
                        {{-- Menampilkan rentang waktu mulai dan selesai --}}
                        <td>
                            {{ \Carbon\Carbon::parse($kegiatan->waktu_mulai)->translatedFormat('H:i') }} - {{ \Carbon\Carbon::parse($kegiatan->waktu_selesai)->translatedFormat('H:i') }}
                        </td>
                        <td class="text-center">
                            @php
                                $statusClass = str_replace('_', '-', $kegiatan->status);
                                $statusText = ucwords(str_replace('_', ' ', $kegiatan->status));
                            @endphp
                            <span class="badge-status badge-status-{{ $statusClass }}">{{ $statusText }}</span>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        {{ $empty_message }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

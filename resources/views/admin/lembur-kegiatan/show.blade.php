@extends('layouts.admin')

@section('styles')
<style>
    .detail-card { background:#fff; border-radius:14px; box-shadow:0 4px 20px rgba(0,0,0,.08); border:none; margin-bottom:1.5rem; overflow:hidden; }
    .status-badge { display:inline-flex; align-items:center; gap:5px; border-radius:20px; padding:4px 12px; font-size:.78rem; font-weight:700; }
    .status-valid      { background:#d1fae5; color:#065f46; }
    .status-menunggu   { background:#fef9c3; color:#713f12; }
    .status-tidak      { background:#fee2e2; color:#991b1b; }
    .table thead th { background:#f8fafc; color:#475569; font-weight:700; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; border-bottom:2px solid #e2e8f0; padding:.85rem 1.25rem; }
    .table tbody td { padding:.85rem 1.25rem; vertical-align:middle; border-bottom:1px solid #f1f5f9; }
    .durasi-bar { height:6px; border-radius:3px; background:#e2e8f0; overflow:hidden; margin-top:4px; }
    .durasi-bar-fill { height:100%; border-radius:3px; background:linear-gradient(90deg, #22c55e, #16a34a); }
    .assign-panel { background:#f8fafc; border-radius:12px; padding:1.25rem; border:1px solid #e2e8f0; }
    .quick-assign-item { display:flex; align-items:center; gap:.75rem; padding:.5rem .75rem; border-radius:8px; cursor:pointer; transition:background .15s; }
    .quick-assign-item:hover { background:#e0f2fe; }
    .quick-assign-item input[type=checkbox] { width:1.05em; height:1.05em; }
</style>
@endsection

@section('content')
<div class="content">
    {{-- HEADER --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('admin.lembur-kegiatan.index') }}" class="btn btn-sm btn-light border shadow-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="flex-grow-1">
            <h3 class="h3 mb-0 fw-bold text-gray-800">{{ $lemburKegiatan->nama_kegiatan }}</h3>
            <p class="text-muted small mb-0">
                <i class="fas fa-calendar me-1"></i>
                {{ \Carbon\Carbon::parse($lemburKegiatan->tanggal)->translatedFormat('l, d F Y') }}
                @if(\Carbon\Carbon::parse($lemburKegiatan->tanggal)->isWeekend())
                    <span class="badge bg-warning text-dark ms-1">Weekend</span>
                @else
                    <span class="badge bg-info ms-1">Libur Nasional</span>
                @endif
            </p>
        </div>
        <a href="{{ route('admin.lembur-kegiatan.edit', $lemburKegiatan) }}" class="btn btn-outline-secondary shadow-sm">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
    </div>

    @if(session('message'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- KIRI: Info + Tabel Pegawai --}}
        <div class="col-lg-8">
            {{-- Info Kegiatan --}}
            <div class="detail-card">
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="text-muted small fw-semibold">DESKRIPSI</div>
                            <div class="mt-1">{{ $lemburKegiatan->deskripsi ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small fw-semibold">DIBUAT OLEH</div>
                            <!-- <div class="mt-1 fw-semibold">{{ $lemburKegiatan->dibuatOleh->name ?? '-' }}</div> -->
                             Admin
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small fw-semibold">SURAT TUGAS</div>
                            <div class="mt-1">
                                @if($lemburKegiatan->file_surat_tugas)
                                    <a href="{{ Storage::url($lemburKegiatan->file_surat_tugas) }}" target="_blank"
                                       class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size:.78rem;">
                                        <i class="fas fa-paperclip me-1"></i>Lihat File
                                    </a>
                                @else
                                    <span class="text-muted small">Tidak ada</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Statistik Cepat --}}
                    @php
                        $totalValid    = $assignments->where('status_validasi', 'valid')->count();
                        $totalMenunggu = $assignments->where('status_validasi', 'menunggu')->count();
                        $totalTidak    = $assignments->where('status_validasi', 'tidak_valid')->count();
                        $total         = $assignments->count();
                    @endphp
                    <div class="d-flex gap-3 flex-wrap">
                        <div class="text-center px-3 py-2 rounded-3" style="background:#d1fae5;">
                            <div class="fw-bold text-success fs-5">{{ $totalValid }}</div>
                            <div class="text-muted small">Valid</div>
                        </div>
                        <div class="text-center px-3 py-2 rounded-3" style="background:#fef9c3;">
                            <div class="fw-bold text-warning fs-5">{{ $totalMenunggu }}</div>
                            <div class="text-muted small">Tidak Face Recognition</div>
                        </div>
                        <div class="text-center px-3 py-2 rounded-3" style="background:#fee2e2;">
                            <div class="fw-bold text-danger fs-5">{{ $totalTidak }}</div>
                            <div class="text-muted small">Tidak Valid (≤ 4 Jam)</div>
                        </div>
                        <div class="text-center px-3 py-2 rounded-3" style="background:#f1f5f9;">
                            <div class="fw-bold text-secondary fs-5">{{ $total }}</div>
                            <div class="text-muted small">Total Pegawai</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel Pegawai --}}
            <div class="detail-card">
                <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-success"><i class="fas fa-users me-2"></i>Daftar Pegawai</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="pegawaiTable">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width:5%;">#</th>
                                <th style="width:28%;">Nama</th>
                                <th style="width:12%;">Peran</th>
                                <th style="width:18%;">Presensi</th>
                                <th style="width:15%;">Durasi</th>
                                <th style="width:12%;">Status</th>
                                <th style="width:10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="pegawaiTbody">
                            @forelse($assignments as $i => $assign)
                                <tr id="row-{{ $assign->user_id }}">
                                    <td class="ps-4 text-muted fw-bold">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $assign->user->name ?? 'Unknown' }}</div>
                                        <div class="small text-muted">{{ $assign->user->nip ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $assign->peran ?? '—' }}</span>
                                    </td>
                                    <td>
                                        @if($assign->log)
                                            <div class="small">
                                                <i class="fas fa-sign-in-alt text-success me-1"></i>{{ $assign->log->jam_masuk ?? '-' }}
                                            </div>
                                            <div class="small">
                                                <i class="fas fa-sign-out-alt text-danger me-1"></i>{{ $assign->log->jam_keluar ?? '-' }}
                                            </div>
                                        @else
                                            <span class="text-muted small">Belum presensi</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($assign->durasi_menit)
                                            @php $jam = floor($assign->durasi_menit / 60); $menit = $assign->durasi_menit % 60; @endphp
                                            <div class="fw-semibold small">{{ $jam }}j {{ $menit }}m</div>
                                            <div class="durasi-bar">
                                                <div class="durasi-bar-fill" style="width: {{ min(100, ($assign->durasi_menit / 480) * 100) }}%"></div>
                                            </div>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $cls = match($assign->status_validasi) {
                                                'valid'       => 'status-valid',
                                                'tidak_valid' => 'status-tidak',
                                                default       => 'status-menunggu',
                                            };
                                            $lbl = match($assign->status_validasi) {
                                                'valid'       => '<i class="fas fa-check-circle"></i> Valid',
                                                'tidak_valid' => '<i class="fas fa-times-circle"></i> Tidak Face Recognition',
                                                default       => '<i class="fas fa-hourglass-half"></i> ≤ 4 Jam',
                                            };
                                        @endphp
                                        <span class="status-badge {{ $cls }}">{!! $lbl !!}</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger py-1 px-2"
                                                onclick="removePegawai({{ $assign->user_id }}, '{{ addslashes($assign->user->name ?? '') }}')"
                                                title="Hapus dari kegiatan">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyRow">
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fas fa-user-plus fa-2x mb-2 opacity-50 d-block"></i>
                                        Belum ada pegawai yang diassign.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- KANAN: Quick Assign Panel --}}
        <div class="col-lg-4">
            <div class="detail-card">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-success mb-3"><i class="fas fa-user-plus me-2"></i>Tambah Pegawai</h6>
                    <p class="text-muted small mb-3">Assign pegawai ke kegiatan ini. Validasi akan dihitung otomatis berdasarkan data presensi.</p>

                    <div class="mb-2">
                        <input type="text" id="quickSearch" class="form-control form-control-sm" placeholder="Cari nama / NIP...">
                    </div>

                    <div class="assign-panel" style="max-height:320px; overflow-y:auto;" id="quickPegawaiList">
                        {{-- Di-render via AJAX atau PHP di bawah --}}
                        @php
                            $assignedUserIds = $assignments->pluck('user_id')->toArray();
                            $allPegawais = \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('title', ['Pegawai', 'Dosen']))
                                ->whereNotNull('nip')->orderBy('name')->get();
                        @endphp
                        @foreach($allPegawais as $peg)
                            <div class="quick-assign-item" data-name="{{ strtolower($peg->name) }}" data-nip="{{ $peg->nip }}" data-uid="{{ $peg->id }}">
                                <input type="checkbox" class="quick-check" id="qc_{{ $peg->id }}"
                                       value="{{ $peg->id }}" {{ in_array($peg->id, $assignedUserIds) ? 'checked disabled' : '' }}>
                                <div style="flex:1">
                                    <label for="qc_{{ $peg->id }}" style="cursor:pointer; font-size:.83rem; font-weight:600; margin:0;">
                                        {{ $peg->name }}
                                    </label>
                                    <div class="text-muted" style="font-size:.72rem;">{{ $peg->nip }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-semibold">Peran <span class="text-muted fw-normal">(opsional)</span></label>
                        <input type="text" id="quickPeran" class="form-control form-control-sm" placeholder="cth: Koordinator, Anggota, Petugas">
                    </div>

                    <button class="btn btn-success w-100 mt-3 shadow-sm" id="btnAssign">
                        <i class="fas fa-plus me-1"></i> Tambahkan ke Kegiatan
                    </button>

                    <div id="assignAlert" class="mt-2" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const KEGIATAN_ID = {{ $lemburKegiatan->id }};

// Quick search
document.getElementById('quickSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.quick-assign-item').forEach(el => {
        el.style.display = (el.dataset.name.includes(q) || el.dataset.nip.includes(q)) ? '' : 'none';
    });
});

// Assign
document.getElementById('btnAssign').addEventListener('click', async function () {
    const checked = [...document.querySelectorAll('.quick-check:checked:not(:disabled)')];
    if (!checked.length) {
        showAlert('warning', 'Pilih minimal satu pegawai terlebih dahulu.');
        return;
    }

    const peran = document.getElementById('quickPeran').value.trim();
    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';

    for (const cb of checked) {
        await assignSingle(cb.value, peran);
        cb.disabled = true; // Tandai sudah diassign
    }

    this.disabled = false;
    this.innerHTML = '<i class="fas fa-plus me-1"></i> Tambahkan ke Kegiatan';
    document.getElementById('quickPeran').value = '';
    showAlert('success', 'Berhasil ditambahkan!');
});

async function assignSingle(userId, peran) {
    try {
        const res = await fetch(`/admin/lembur-kegiatan/${KEGIATAN_ID}/assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ user_id: userId, peran: peran })
        });
        const data = await res.json();
        if (data.success) {
            appendRow(data.assignment);
            document.getElementById('emptyRow')?.remove();
        } else {
            showAlert('danger', data.message);
        }
    } catch (e) {
        showAlert('danger', 'Terjadi kesalahan jaringan.');
    }
}

function appendRow(assignment) {
    const tbody = document.getElementById('pegawaiTbody');
    const rowCount = tbody.querySelectorAll('tr:not(#emptyRow)').length + 1;
    const row = document.createElement('tr');
    row.id = `row-${assignment.user_id}`;
    row.innerHTML = `
        <td class="ps-4 text-muted fw-bold">${rowCount}</td>
        <td>
            <div class="fw-bold text-dark">${assignment.user?.name ?? '—'}</div>
            <div class="small text-muted">${assignment.user?.nip ?? '—'}</div>
        </td>
        <td><span class="text-muted small">${assignment.peran ?? '—'}</span></td>
        <td><span class="text-muted small">Belum presensi</span></td>
        <td><span class="text-muted small">—</span></td>
        <td><span class="status-badge status-menunggu"><i class="fas fa-hourglass-half"></i> Menunggu</span></td>
        <td>
            <button class="btn btn-sm btn-outline-danger py-1 px-2"
                    onclick="removePegawai(${assignment.user_id}, '${(assignment.user?.name ?? '').replace(/'/g, "\\'")}')"
                    title="Hapus dari kegiatan">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
}

async function removePegawai(userId, nama) {
    if (!confirm(`Hapus ${nama} dari kegiatan ini?`)) return;
    try {
        const res = await fetch(`/admin/lembur-kegiatan/${KEGIATAN_ID}/remove-pegawai`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ user_id: userId })
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById(`row-${userId}`)?.remove();
            // Re-enable checkbox di quick panel
            const cb = document.getElementById(`qc_${userId}`);
            if (cb) { cb.checked = false; cb.disabled = false; }
        }
    } catch (e) { alert('Gagal menghapus.'); }
}

function showAlert(type, msg) {
    const el = document.getElementById('assignAlert');
    el.innerHTML = `<div class="alert alert-${type} py-2 px-3 small mb-0">${msg}</div>`;
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, 4000);
}
</script>
@endsection
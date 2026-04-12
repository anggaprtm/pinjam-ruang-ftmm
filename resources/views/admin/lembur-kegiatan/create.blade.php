{{-- Dipakai sebagai create.blade.php, untuk edit tinggal ganti nilai default --}}
@extends('layouts.admin')

@section('styles')
<style>
    .form-card { background:#fff; border-radius:14px; box-shadow:0 4px 24px rgba(0,0,0,.08); border:none; padding:2rem; margin-bottom:1.5rem; }
    .section-title { font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#64748b; border-bottom:2px solid #f1f5f9; padding-bottom:.5rem; margin-bottom:1.25rem; }
    .pegawai-search-wrapper { position:relative; }
    .pegawai-search-wrapper input { padding-left:2.5rem; }
    .pegawai-search-wrapper .search-icon { position:absolute; left:.85rem; top:50%; transform:translateY(-50%); color:#94a3b8; }
    .pegawai-list { max-height:340px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:10px; }
    .pegawai-item { display:flex; align-items:center; padding:.65rem 1rem; border-bottom:1px solid #f1f5f9; gap:.75rem; transition:background .15s; }
    .pegawai-item:last-child { border-bottom:none; }
    .pegawai-item:hover { background:#f8fafc; }
    .pegawai-item input[type=checkbox] { width:1.1em; height:1.1em; }
    .pegawai-avatar { width:34px; height:34px; background:#dcfce7; color:#15803d; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.85rem; flex-shrink:0; }
    .peran-input { display:none; margin-top:.4rem; }
    .peran-input.show { display:block; }
</style>
@endsection

@section('content')
<div class="content">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('admin.lembur-kegiatan.index') }}" class="btn btn-sm btn-light border shadow-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h3 class="h3 mb-0 text-gray-800 fw-bold">
                {{ isset($lemburKegiatan) ? 'Edit Kegiatan Lembur' : 'Buat Kegiatan Lembur' }}
            </h3>
            <p class="text-muted small mb-0">
                {{ isset($lemburKegiatan) ? 'Perbarui informasi kegiatan dan assignment pegawai.' : 'Tambahkan kegiatan lembur dan assign pegawai yang terlibat.' }}
            </p>
        </div>
    </div>

    <form action="{{ isset($lemburKegiatan) ? route('admin.lembur-kegiatan.update', $lemburKegiatan) : route('admin.lembur-kegiatan.store') }}"
          method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($lemburKegiatan)) @method('PUT') @endif

        <div class="row g-4">

            {{-- Kolom Kiri: Info Kegiatan --}}
            <div class="col-lg-5">
                <div class="form-card">
                    <div class="section-title"><i class="fas fa-info-circle me-2 text-success"></i>Informasi Kegiatan</div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal Lembur <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
                               value="{{ old('tanggal', isset($lemburKegiatan) ? $lemburKegiatan->tanggal->format('Y-m-d') : $tanggalDefault) }}"
                               required>
                        @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text text-muted small">Pilih tanggal hari libur / akhir pekan.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kegiatan" class="form-control @error('nama_kegiatan') is-invalid @enderror"
                               placeholder="cth: Panitia Parade Wisuda 2025"
                               value="{{ old('nama_kegiatan', $lemburKegiatan->nama_kegiatan ?? '') }}"
                               required>
                        @error('nama_kegiatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"
                                  placeholder="Detail singkat mengenai kegiatan...">{{ old('deskripsi', $lemburKegiatan->deskripsi ?? '') }}</textarea>
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-semibold">Surat Tugas <span class="text-muted fw-normal">(Opsional)</span></label>
                        @if(isset($lemburKegiatan) && $lemburKegiatan->file_surat_tugas)
                            <div class="alert alert-light border py-2 px-3 mb-2 d-flex align-items-center gap-2">
                                <i class="fas fa-file-pdf text-danger"></i>
                                <a href="{{ Storage::url($lemburKegiatan->file_surat_tugas) }}" target="_blank" class="text-decoration-none small">
                                    Lihat surat tugas saat ini
                                </a>
                            </div>
                        @endif
                        <input type="file" name="file_surat_tugas" class="form-control @error('file_surat_tugas') is-invalid @enderror"
                               accept=".pdf,.jpg,.jpeg,.png">
                        @error('file_surat_tugas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text text-muted small">Format: PDF / JPG / PNG. Maks 5MB.</div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Assign Pegawai --}}
            <div class="col-lg-7">
                <div class="form-card">
                    <div class="section-title"><i class="fas fa-users me-2 text-success"></i>Assign Pegawai</div>
                    <p class="text-muted small mb-3">
                        Pilih pegawai yang terlibat di kegiatan ini. Assignment bersifat opsional saat pembuatan dan bisa ditambahkan/diubah nanti.
                        <br><strong>Catatan:</strong> Validasi lembur tetap hanya berlaku jika presensi ≥ 4 jam.
                    </p>

                    {{-- Search --}}
                    <div class="pegawai-search-wrapper mb-2">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="pegawaiSearch" class="form-control" placeholder="Cari nama atau NIP...">
                    </div>

                    {{-- List Pegawai --}}
                    <div class="pegawai-list" id="pegawaiList">
                        @foreach($pegawais as $pegawai)
                            @php
                                $isChecked = isset($assignedIds) && in_array($pegawai->id, $assignedIds);
                                $peranVal  = $assignedPerans[$pegawai->id] ?? '';
                            @endphp
                            <div class="pegawai-item" data-name="{{ strtolower($pegawai->name) }}" data-nip="{{ $pegawai->nip }}">
                                <input type="checkbox" name="pegawai_ids[]" value="{{ $pegawai->id }}"
                                       id="pegawai_{{ $pegawai->id }}" class="pegawai-check"
                                       {{ $isChecked ? 'checked' : '' }}>
                                <div class="pegawai-avatar">{{ strtoupper(substr($pegawai->name, 0, 1)) }}</div>
                                <div class="flex-grow-1">
                                    <label for="pegawai_{{ $pegawai->id }}" class="d-block fw-semibold mb-0" style="cursor:pointer; font-size:.875rem;">
                                        {{ $pegawai->name }}
                                    </label>
                                    <small class="text-muted">{{ $pegawai->nip }}</small>
                                    {{-- Input peran, muncul jika checked --}}
                                    <div class="peran-input {{ $isChecked ? 'show' : '' }}">
                                        <input type="text"
                                               name="perans[{{ $pegawai->id }}]"
                                               class="form-control form-control-sm"
                                               placeholder="Peran (cth: Koordinator, Anggota)"
                                               value="{{ old('perans.' . $pegawai->id, $peranVal) }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-2 text-end">
                        <small class="text-muted" id="selectedCount">0 pegawai dipilih</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="d-flex justify-content-end gap-2 mt-2 mb-4">
            <a href="{{ route('admin.lembur-kegiatan.index') }}" class="btn btn-light border shadow-sm px-4">Batal</a>
            <button type="submit" class="btn btn-success shadow-sm px-5">
                <i class="fas fa-save me-2"></i>{{ isset($lemburKegiatan) ? 'Perbarui Kegiatan' : 'Simpan Kegiatan' }}
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    // Search pegawai
    document.getElementById('pegawaiSearch').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.pegawai-item').forEach(el => {
            const name = el.dataset.name;
            const nip  = el.dataset.nip;
            el.style.display = (name.includes(q) || nip.includes(q)) ? '' : 'none';
        });
    });

    // Toggle peran input & counter
    function updateCounter() {
        const count = document.querySelectorAll('.pegawai-check:checked').length;
        document.getElementById('selectedCount').textContent = count + ' pegawai dipilih';
    }

    document.querySelectorAll('.pegawai-check').forEach(cb => {
        cb.addEventListener('change', function () {
            const peranDiv = this.closest('.pegawai-item').querySelector('.peran-input');
            peranDiv.classList.toggle('show', this.checked);
            if (!this.checked) {
                peranDiv.querySelector('input').value = '';
            }
            updateCounter();
        });
    });

    // Init counter
    updateCounter();
</script>
@endsection
@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="h3 mb-0 text-gray-800 fw-bold">{{ isset($jadwal) ? 'Edit' : 'Tambah' }} Jadwal WFH</h3>
            <p class="text-muted small mb-0">Atur siapa saja yang mendapat jadwal Work From Home dan kapan berlakunya.</p>
        </div>
        <a href="{{ route('admin.jadwal-wfh.index') }}" class="btn btn-light border shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <form action="{{ isset($jadwal) ? route('admin.jadwal-wfh.update', $jadwal->id) : route('admin.jadwal-wfh.store') }}" method="POST">
        @csrf
        @if(isset($jadwal)) @method('PUT') @endif

        <div class="row g-4">
            {{-- Kolom Kiri: Kapan WFH Berlangsung --}}
            <div class="col-lg-6">
                <div class="card shadow border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-success"><i class="fas fa-calendar-alt me-2"></i>Waktu Pelaksanaan</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Keterangan / Nama Jadwal <span class="text-danger">*</span></label>
                            <input type="text" name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" 
                                   value="{{ old('keterangan', $jadwal->keterangan ?? '') }}" placeholder="Contoh: WFH Rutin Rabu / WFH Maintenance Gedung" required>
                            @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold d-block">Tipe Waktu</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input input-tipe-waktu" type="radio" name="tipe_waktu" id="tipe_rutin" value="rutin" {{ (old('tipe_waktu') == 'rutin' || (isset($jadwal) && $jadwal->hari_rutin)) ? 'checked' : 'checked' }}>
                                <label class="form-check-label" for="tipe_rutin">Rutin Mingguan</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input input-tipe-waktu" type="radio" name="tipe_waktu" id="tipe_insidental" value="insidental" {{ (old('tipe_waktu') == 'insidental' || (isset($jadwal) && $jadwal->tanggal)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="tipe_insidental">Insidental (Satu Tanggal)</label>
                            </div>
                        </div>

                        {{-- Form Hari (Rutin) --}}
                        <div class="mb-3" id="form-hari" style="display: none;">
                            <label class="form-label fw-bold text-info">Pilih Hari Rutin <span class="text-danger">*</span></label>
                            <select name="hari_rutin" class="form-select @error('hari_rutin') is-invalid @enderror">
                                <option value="">-- Pilih Hari --</option>
                                <option value="1" {{ old('hari_rutin', $jadwal->hari_rutin ?? '') == 1 ? 'selected' : '' }}>Senin</option>
                                <option value="2" {{ old('hari_rutin', $jadwal->hari_rutin ?? '') == 2 ? 'selected' : '' }}>Selasa</option>
                                <option value="3" {{ old('hari_rutin', $jadwal->hari_rutin ?? '') == 3 ? 'selected' : '' }}>Rabu</option>
                                <option value="4" {{ old('hari_rutin', $jadwal->hari_rutin ?? '') == 4 ? 'selected' : '' }}>Kamis</option>
                                <option value="5" {{ old('hari_rutin', $jadwal->hari_rutin ?? '') == 5 ? 'selected' : '' }}>Jumat</option>
                            </select>
                            @error('hari_rutin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Form Tanggal (Insidental) --}}
                        <div class="mb-3" id="form-tanggal" style="display: none;">
                            <label class="form-label fw-bold text-warning">Pilih Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" 
                                   value="{{ old('tanggal', isset($jadwal) && $jadwal->tanggal ? $jadwal->tanggal : '') }}">
                            @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Siapa yang WFH --}}
            <div class="col-lg-6">
                <div class="card shadow border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-users me-2"></i>Sasaran Pegawai</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold d-block">Berlaku Untuk</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input input-sasaran" type="radio" name="sasaran" id="sasaran_semua" value="semua" {{ (old('sasaran') == 'semua' || (isset($jadwal) && is_null($jadwal->user_id))) ? 'checked' : 'checked' }}>
                                <label class="form-check-label" for="sasaran_semua">
                                    <span class="badge bg-success">Semua Pegawai</span>
                                    <small class="d-block text-muted mt-1">Jadwal berlaku massal untuk seluruh tendik/dosen.</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input input-sasaran" type="radio" name="sasaran" id="sasaran_spesifik" value="spesifik" {{ (old('sasaran') == 'spesifik' || (isset($jadwal) && !is_null($jadwal->user_id))) ? 'checked' : '' }}>
                                <label class="form-check-label" for="sasaran_spesifik">
                                    Pegawai Spesifik
                                    <small class="d-block text-muted mt-1">Hanya berlaku untuk satu pegawai tertentu (misal: ijin khusus).</small>
                                </label>
                            </div>
                        </div>

                        {{-- Pilihan Pegawai Spesifik --}}
                        <div class="mb-3" id="form-pegawai" style="display: none;">
                            <label class="form-label fw-bold">Pilih Pegawai (Bisa Lebih Dari Satu) <span class="text-danger">*</span></label>
                            
                            {{-- Search Box --}}
                            <div class="position-relative mb-2">
                                <i class="fas fa-search position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%);"></i>
                                <input type="text" id="pegawaiSearch" class="form-control" placeholder="Cari nama atau NIP..." style="padding-left: 35px;">
                            </div>

                           {{-- List Checkbox Pegawai --}}
                            <div id="pegawaiList" class="border rounded bg-white" style="max-height: 250px; overflow-y: auto;">
                                @foreach($pegawais as $pegawai)
                                    @php
                                        // Cek apakah pegawai ini sudah terpilih (untuk mode Edit)
                                        $isChecked = isset($jadwal) && $jadwal->users->contains($pegawai->id);
                                    @endphp
                                    <div class="pegawai-item d-flex align-items-center p-2 border-bottom" data-name="{{ strtolower($pegawai->name) }}" data-nip="{{ $pegawai->nip }}">
                                        <input type="checkbox" name="user_ids[]" value="{{ $pegawai->id }}" 
                                               id="pegawai_{{ $pegawai->id }}" class="pegawai-check me-3" style="width: 1.2em; height: 1.2em;"
                                               {{ $isChecked ? 'checked' : '' }}>
                                        <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center fw-bold me-3 flex-shrink-0" style="width: 35px; height: 35px;">
                                            {{ strtoupper(substr($pegawai->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <label for="pegawai_{{ $pegawai->id }}" class="d-block fw-semibold mb-0" style="cursor:pointer; font-size: 0.9rem;">{{ $pegawai->name }}</label>
                                            <small class="text-muted">{{ $pegawai->nip }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            {{-- Counter & Tombol Reset --}}
                            <div class="mt-2 d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" id="btnResetPilihan" style="font-size: 0.8rem;">
                                    <i class="fas fa-times me-1"></i> Reset Pilihan
                                </button>
                                <small class="text-muted fw-bold" id="selectedCount">0 pegawai dipilih</small>
                            </div>
                            @error('user_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mt-4 mb-5">
            <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">
                <i class="fas fa-save me-1"></i> Simpan Jadwal WFH
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
   document.addEventListener('DOMContentLoaded', function() {
        // Toggle Waktu (Rutin vs Tanggal)
        const radiosWaktu = document.querySelectorAll('.input-tipe-waktu');
        const formHari = document.getElementById('form-hari');
        const formTanggal = document.getElementById('form-tanggal');

        function toggleWaktu() {
            let selected = document.querySelector('.input-tipe-waktu:checked').value;
            if (selected === 'rutin') {
                formHari.style.display = 'block';
                formTanggal.style.display = 'none';
            } else {
                formHari.style.display = 'none';
                formTanggal.style.display = 'block';
            }
        }
        
        radiosWaktu.forEach(radio => radio.addEventListener('change', toggleWaktu));
        toggleWaktu(); // Init on load

        // Toggle Sasaran (Semua vs Spesifik)
        const radiosSasaran = document.querySelectorAll('.input-sasaran');
        const formPegawai = document.getElementById('form-pegawai');

        function toggleSasaran() {
            let selected = document.querySelector('.input-sasaran:checked').value;
            if (selected === 'spesifik') {
                formPegawai.style.display = 'block';
            } else {
                formPegawai.style.display = 'none';
            }
        }

        radiosSasaran.forEach(radio => radio.addEventListener('change', toggleSasaran));
        toggleSasaran(); // Init on load

        // --- DEKLARASI VARIABEL PENCARIAN, COUNTER & RESET ---
        const searchInput = document.getElementById('pegawaiSearch');
        const checkboxes = document.querySelectorAll('.pegawai-check');
        const countText = document.getElementById('selectedCount');
        const btnReset = document.getElementById('btnResetPilihan');

        function updateCounter() {
            if (!countText) return; 
            const count = document.querySelectorAll('.pegawai-check:checked').length;
            countText.textContent = count + ' pegawai dipilih';
        }

        // Fix Logika Pencarian agar menembus d-flex Bootstrap
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const q = this.value.toLowerCase();
                document.querySelectorAll('.pegawai-item').forEach(el => {
                    const name = el.dataset.name;
                    const nip = el.dataset.nip;
                    
                    if (name.includes(q) || nip.includes(q)) {
                        el.classList.remove('d-none');
                        el.classList.add('d-flex');
                    } else {
                        el.classList.remove('d-flex');
                        el.classList.add('d-none');
                    }
                });
            });
        }

        // Fungsi Reset Pilihan Checkbox
        if (btnReset) {
            btnReset.addEventListener('click', function() {
                checkboxes.forEach(cb => cb.checked = false);
                updateCounter();
                
                // Opsional: Kosongkan juga kolom pencarian jika sedang memfilter
                if (searchInput.value !== '') {
                    searchInput.value = '';
                    searchInput.dispatchEvent(new Event('input')); // Trigger ulang pencarian agar list tampil semua
                }
            });
        }

        if (checkboxes.length > 0) {
            checkboxes.forEach(cb => cb.addEventListener('change', updateCounter));
            updateCounter(); // Init on load
        }
    });
</script>
@endsection
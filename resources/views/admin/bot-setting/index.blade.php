@extends('layouts.admin')

@section('content')
<div class="content">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="h3 mb-1 text-gray-800 fw-bold"><i class="fas fa-robot me-2"></i>Pengaturan Bot Telegram</h3>
            <p class="text-muted small mb-0">Atur jadwal dan format pesan otomatis untuk pegawai.</p>
        </div>
        
        <button type="submit" form="botForm" class="btn btn-primary shadow-sm fw-bold px-4" style="border-radius: 10px;">
            <i class="fas fa-save me-2"></i>Simpan Perubahan
        </button>
    </div>

    <form action="{{ route('admin.bot-setting.update') }}" method="POST" id="botForm">
        @csrf
        
        <div class="row">
            {{-- KOLOM KIRI: SETTING JADWAL --}}
            <div class="col-lg-8">
                
                {{-- 1. REMINDER PAGI --}}
                <div class="card border-0 shadow-sm mb-4" style="border-left: 5px solid #4e73df;">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-coffee me-2"></i>Reminder Pagi (Sapaan)</h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="pagi_aktif" id="pagi_aktif" {{ $setting->pagi_aktif ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small ms-3" for="pagi_aktif">Aktif</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-bold">Jam Kirim</label>
                            <div class="col-sm-4">
                                <input type="time" name="pagi_jam" class="form-control" value="{{ $setting->pagi_jam ?? '06:30' }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Isi Pesan Pagi (Tendik)</label>
                            <textarea name="pagi_pesan" class="form-control" rows="3">{{ $setting->pagi_pesan ?? 'Selamat Pagi {nama}, jangan lupa absen ya!' }}</textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold">Isi Pesan Pagi (Dosen)</label>
                            <textarea name="pagi_pesan_dosen" class="form-control" rows="3">{{ $setting->pagi_pesan_dosen ?? 'Selamat Pagi Bpk/Ibu {nama}, selamat mengajar hari ini!' }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 2. WARNING BELUM MASUK --}}
                <div class="card border-0 shadow-sm mb-4" style="border-left: 5px solid #f6c23e;">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Peringatan Belum Masuk</h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="masuk_aktif" id="masuk_aktif" {{ $setting->masuk_aktif ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small ms-3" for="masuk_aktif">Aktif</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning py-2 small border-0"><i class="fas fa-info-circle me-1"></i> Bot akan cek data presensi dulu. Jika belum scan, pesan ini dikirim.</div>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-bold">Jam Cek</label>
                            <div class="col-sm-4">
                                <input type="time" name="masuk_jam" class="form-control" value="{{ $setting->masuk_jam ?? '07:50' }}">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold">Isi Pesan</label>
                            <textarea name="masuk_pesan" class="form-control" rows="3">{{ $setting->masuk_pesan }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 3. REMINDER PULANG --}}
                <div class="card border-0 shadow-sm mb-4" style="border-left: 5px solid #1cc88a;">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-success"><i class="fas fa-home me-2"></i>Reminder Pulang</h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="pulang_aktif" id="pulang_aktif" {{ $setting->pulang_aktif ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small ms-3" for="pulang_aktif">Aktif</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-bold">Jam Cek</label>
                            <div class="col-sm-4">
                                <input type="time" name="pulang_jam" class="form-control" value="{{ $setting->pulang_jam ?? '17:00' }}">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold">Isi Pesan</label>
                            <textarea name="pulang_pesan" class="form-control" rows="3">{{ $setting->pulang_pesan }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4" style="border-left: 5px solid #36b9cc;">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-info"><i class="fas fa-chalkboard-teacher me-2"></i>Pengecekan Siang (Khusus Dosen)</h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="siang_dosen_aktif" id="siang_dosen_aktif" {{ $setting->siang_dosen_aktif ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small ms-3" for="siang_dosen_aktif">Aktif</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info py-2 small border-0"><i class="fas fa-info-circle me-1"></i> Bot akan mengecek kehadiran Dosen pada jam ini.</div>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-bold">Jam Cek</label>
                            <div class="col-sm-4">
                                <input type="time" name="siang_dosen_jam" class="form-control" value="{{ $setting->siang_dosen_jam ?? '14:00' }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pesan Jika BELUM Absen</label>
                            <textarea name="siang_dosen_pesan_belum" class="form-control" rows="3">{{ $setting->siang_dosen_pesan_belum ?? '⚠️ Bpk/Ibu {nama}, Anda belum tercatat presensi masuk hingga siang ini.' }}</textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold">Pesan Jika SUDAH Absen</label>
                            <textarea name="siang_dosen_pesan_sudah" class="form-control" rows="3">{{ $setting->siang_dosen_pesan_sudah ?? '✅ Terima kasih Bpk/Ibu {nama}, sistem mencatat Anda sudah presensi pada pukul {jam_masuk}.' }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 4. EVALUASI MALAM --}}
                <div class="card border-0 shadow-sm mb-4" style="border-left: 5px solid #e74a3b;">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-danger"><i class="fas fa-skull me-2"></i>Evaluasi Kedisiplinan (Telat 2x)</h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="evaluasi_aktif" id="evaluasi_aktif" {{ $setting->evaluasi_aktif ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small ms-3" for="evaluasi_aktif">Aktif</label>
                        </div>
                    </div>
                    <div class="card-body">
                         <div class="alert alert-danger py-2 small border-0 bg-soft-danger text-danger"><i class="fas fa-info-circle me-1"></i> Hanya dikirim ke pegawai yang hari ini telat untuk kedua kalinya dalam bulan ini.</div>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-bold">Jam Cek</label>
                            <div class="col-sm-4">
                                <input type="time" name="evaluasi_jam" class="form-control" value="{{ $setting->evaluasi_jam ?? '19:00' }}">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold">Isi Pesan</label>
                            <textarea name="evaluasi_pesan" class="form-control" rows="3">{{ $setting->evaluasi_pesan }}</textarea>
                        </div>
                    </div>
                </div>

            </div>

            {{-- KOLOM KANAN: CHEAT SHEET & INFO --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 20px; z-index: 1;">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-code me-2"></i>Variabel Dinamis</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Anda dapat menyisipkan kode berikut di dalam pesan. Sistem akan menggantinya secara otomatis.</p>
                        
                        <div class="mb-3">
                            <label class="small fw-bold text-dark">Nama Pegawai</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control bg-light" value="{nama}" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{nama}')"><i class="fas fa-copy"></i></button>
                            </div>
                            <small class="text-muted" style="font-size: 0.7rem;">Contoh output: "Angga"</small>
                        </div>

                        <div class="mb-3">
                            <label class="small fw-bold text-dark">Tanggal Hari Ini</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control bg-light" value="{tanggal}" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{tanggal}')"><i class="fas fa-copy"></i></button>
                            </div>
                            <small class="text-muted" style="font-size: 0.7rem;">Contoh output: "16-02-2026"</small>
                        </div>

                        <div class="mb-3">
                            <label class="small fw-bold text-dark">Batas Jam Pulang</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control bg-light" value="{batas_jam}" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{batas_jam}')"><i class="fas fa-copy"></i></button>
                            </div>
                            <small class="text-muted" style="font-size: 0.7rem;">Contoh output: "17:00"</small>
                        </div>

                        <hr>
                        <div class="small text-muted fst-italic">
                            <i class="fas fa-lightbulb text-warning me-1"></i>
                            <strong>Tips:</strong> Gunakan tag HTML seperti <code>&lt;b&gt;tebal&lt;/b&gt;</code> atau <code>&lt;i&gt;miring&lt;/i&gt;</code> untuk mempercantik pesan di Telegram.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text);
        alert('Disalin: ' + text);
    }
</script>
@endsection
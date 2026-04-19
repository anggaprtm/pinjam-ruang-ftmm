@extends('layouts.admin')

@section('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&display=swap" rel="stylesheet">

<style>
:root {
    --brand-maroon:     #741847;
    --brand-maroon-dk:  #5a1238;
    --surface-0:        #ffffff;
    --surface-1:        #f8f7f9;
    --surface-border:   #e8e2ee;
    --text-primary:     #1a1025;
    --text-secondary:   #6b6080;
    --accent-green:     #10b981;
    --radius-md:        12px;
    --shadow-sm:        0 1px 3px rgba(116,24,71,0.06);
}

.cmd-header { background: linear-gradient(135deg, var(--brand-maroon) 0%, #9c2456 50%, #741847 100%); border-radius: 20px; padding: 1.75rem 2rem; margin-bottom: 1.75rem; box-shadow: 0 8px 32px rgba(116,24,71,0.3); color: #fff; }
.cmd-header-title { font-family: 'Montserrat', sans-serif; font-size: 1.55rem; margin: 0; }
.cmd-header-subtitle { font-family: 'Nunito', sans-serif; font-size: 0.875rem; color: rgba(255,255,255,0.7); }

.panel-card { background: var(--surface-0); border-radius: var(--radius-md); border: 1px solid var(--surface-border); box-shadow: var(--shadow-sm); overflow: hidden; }

/* Matrix Table Styling */
.matrix-table { margin: 0; font-family: 'Nunito', sans-serif; font-size: 0.85rem; }
.matrix-table th { background: var(--surface-1); color: var(--text-secondary); font-weight: 800; text-align: center; vertical-align: middle; border-bottom: 2px solid var(--surface-border); }
.matrix-table td { vertical-align: middle; text-align: center; border-color: var(--surface-border); }
.matrix-table .col-sticky { position: sticky; left: 0; background: var(--surface-0); z-index: 2; box-shadow: 2px 0 5px rgba(0,0,0,0.02); }

/* Cell Indicators */
.cell-box { width: 28px; height: 28px; border-radius: 6px; margin: 0 auto; display: flex; align-items: center; justify-content: center; transition: all 0.2s; cursor: pointer; font-size: 0.8rem; }
.cell-empty { background: #f1f5f9; border: 1px solid #cbd5e1; }
.cell-empty:hover { background: #e2e8f0; transform: scale(1.1); }
.cell-done { background: var(--accent-green); color: white; box-shadow: 0 2px 6px rgba(16,185,129,0.4); }
.cell-done:hover { transform: scale(1.1); filter: brightness(1.1); }
.cell-none { color: #cbd5e1; font-size: 0.7rem; }
.cell-disabled { background: #f1f5f9; border: 1px dashed #cbd5e1; cursor: not-allowed; }

.btn-brand { background: var(--brand-maroon); color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.2rem; font-weight: 700; font-family: 'Nunito', sans-serif; transition: 0.2s; }
.btn-brand:hover { background: var(--brand-maroon-dk); color: #fff; }
.month-checkbox-wrapper { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
.month-label { display: flex; align-items: center; gap: 6px; font-size: 0.8rem; background: var(--surface-1); padding: 8px; border-radius: 6px; border: 1px solid var(--surface-border); cursor: pointer; }
.month-label:hover { border-color: var(--brand-maroon); }
</style>
@endsection

@section('content')
<div class="container-fluid p-0">

    {{-- HEADER --}}
    <div class="cmd-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h2 class="cmd-header-title">📋 Monitoring Tugas Rutinan</h2>
            <p class="cmd-header-subtitle mt-1 mb-0">Laporan & Bukti Kinerja Pegawai {{ Auth::user()->isKTU() ? 'Fakultas' : (Auth::user()->isKasubag() ? 'Sub Bagian' : 'Pribadi') }}</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <form method="GET" class="d-flex gap-2 align-items-center">
                {{-- FILTER DROPDOWN UNTUK ATASAN --}}
                @if(Auth::user()->isKTU() || Auth::user()->isKasubag())
                    <select name="user_id" class="form-select form-select-sm" style="border-radius:8px; font-family:'Nunito';" onchange="this.form.submit()">
                        <option value="">Semua Pegawai</option>
                        @foreach($subordinates as $sub)
                            <option value="{{ $sub->id }}" {{ $selectedUserId == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                        @endforeach
                    </select>
                @endif
                <select name="year" class="form-select form-select-sm" style="font-family:'Nunito',sans-serif; width:250px; border-radius:8px;" onchange="this.form.submit()">
                    @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endfor
                </select>
            </form>

            @if(Auth::user()->isKTU() || Auth::user()->isKasubag())
                <button class="btn btn-light btn-sm fw-bold" style="border-radius:8px; font-family:'Nunito',sans-serif;" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                    <i class="fas fa-plus text-primary me-1"></i> Tambah Tugas
                </button>
            @endif
        </div>
    </div>

    {{-- MATRIX PANEL PER PEGAWAI --}}
    @forelse($groupedTasks as $uId => $userTasks)
        @php 
            $emp = $userTasks->first()->user; 
            $monthsLabel = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des']; 
        @endphp
        
        <div class="mb-4">
            @if(Auth::user()->isKTU() || Auth::user()->isKasubag())
                <h6 class="fw-bold text-dark mb-2 ms-2" style="font-family:'Nunito';"><i class="fas fa-user-tie text-primary me-1"></i> {{ $emp->name }} <span class="text-muted fw-normal" style="font-size:0.8rem;">({{ $emp->tendikDetail->nama_jabatan ?? 'Staf' }})</span></h6>
            @endif

            <div class="panel-card table-responsive" style="overflow-x: auto;">
                <table class="table table-hover matrix-table mb-0">
                    <thead>
                        <tr>
                            <th class="text-start col-sticky" style="min-width:280px; padding-left:1.5rem;">Tugas Rutinan</th>
                            @foreach($monthsLabel as $m) <th style="min-width: 45px;">{{ $m }}</th> @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($userTasks as $t)
                            <tr>
                                <td class="text-start col-sticky" style="padding-left:1.5rem;">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <div class="fw-bold" style="color:var(--brand-maroon);">{{ $t->title }}</div>
                                            <div class="text-muted" style="font-size:0.7rem;"><i class="fas fa-arrow-right"></i> Dari: {{ $t->assigner->name }}</div>
                                        </div>
                                        @if(Auth::user()->isKTU() || Auth::user()->isKasubag())
                                            <button class="btn btn-sm text-primary p-0 btn-edit-routine" 
                                                    data-id="{{ $t->id }}" 
                                                    data-title="{{ $t->title }}" 
                                                    data-months="{{ json_encode($t->target_months) }}" 
                                                    title="Edit Tugas">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                @for($i=1; $i<=12; $i++)
                                    <td>
                                        @if(in_array($i, $t->target_months))
                                            @php $log = $t->logs->firstWhere('month', $i); @endphp
                                            @if($log)
                                                <div class="cell-box cell-done btn-view-log" 
                                                     data-logid="{{ $log->id }}"
                                                     data-date="{{ \Carbon\Carbon::parse($log->completed_at)->format('d M Y') }}"
                                                     data-notes="{{ $log->notes }}"
                                                     data-file="{{ asset('storage/' . $log->proof_file_path) }}"
                                                     data-status="{{ $log->status }}"
                                                     title="Lihat Bukti">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                            @else
                                                @if($t->user_id == Auth::id())
                                                    <div class="cell-box cell-empty btn-submit-log" data-id="{{ $t->id }}" data-title="{{ $t->title }}" data-month="{{ $i }}" data-monthname="{{ $monthsLabel[$i-1] }}" title="Klik untuk Lapor"></div>
                                                @else
                                                    <div class="cell-box cell-disabled" title="Belum Dikerjakan"></div>
                                                @endif
                                            @endif
                                        @else
                                            <span class="cell-none"><i class="fas fa-minus"></i></span>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="text-center py-5 text-muted bg-white rounded shadow-sm border">
            <i class="fas fa-clipboard-list fa-3x mb-3 opacity-25"></i>
            <p class="mb-0 font-monospace">Belum ada data tugas rutinan di tahun ini.</p>
        </div>
    @endforelse

</div>

{{-- MODAL TAMBAH TUGAS --}}
@if(Auth::user()->isKTU() || Auth::user()->isKasubag())
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-header" style="background:var(--brand-maroon); color:white;">
                <h5 class="modal-title fw-bold" style="font-family:'Nunito';"><i class="fas fa-plus-circle me-2"></i>Tambah Tugas Rutin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCreateTask">
                <div class="modal-body" style="font-family:'Nunito'; font-size:0.85rem;">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Tugaskan Kepada:</label>
                        <select name="user_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Staf --</option>
                            @foreach($subordinates as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Nama Tugas Rutinan:</label>
                        <input type="text" name="title" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="fw-bold mb-2">Target Bulan Pengerjaan:</label>
                        <div class="month-checkbox-wrapper">
                            @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $idx => $bulan)
                                <label class="month-label"><input type="checkbox" name="target_months[]" value="{{ $idx + 1 }}" class="form-check-input mt-0"> {{ $bulan }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="border-radius:8px;">Batal</button>
                    <button type="submit" class="btn-brand btn-sm"><i class="fas fa-save"></i> Simpan Tugas</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT TUGAS --}}
<div class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-header bg-dark color-white">
                <h5 class="modal-title fw-bold text-white" style="font-family:'Nunito';"><i class="fas fa-edit me-2"></i>Edit Tugas Rutin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditTask">
                <div class="modal-body" style="font-family:'Nunito'; font-size:0.85rem;">
                    <input type="hidden" id="editTaskId">
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Nama Tugas Rutinan:</label>
                        <input type="text" name="title" id="editTaskTitle" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="fw-bold mb-2">Target Bulan Pengerjaan:</label>
                        <div class="month-checkbox-wrapper">
                            @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $idx => $bulan)
                                <label class="month-label"><input type="checkbox" name="target_months[]" value="{{ $idx + 1 }}" class="form-check-input mt-0 edit-month-cb"> {{ $bulan }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="border-radius:8px;">Batal</button>
                    <button type="submit" class="btn-brand btn-sm"><i class="fas fa-save"></i> Update Tugas</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL SUBMIT BUKTI (STAF) --}}
<div class="modal fade" id="submitLogModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-header" style="background:var(--accent-green); color:white;">
                <h5 class="modal-title fw-bold" style="font-family:'Nunito';"><i class="fas fa-upload me-2"></i>Lapor Kinerja</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSubmitLog" enctype="multipart/form-data">
                <input type="hidden" name="routine_task_id" id="submitTaskId">
                <input type="hidden" name="month" id="submitMonth">
                <div class="modal-body" style="font-family:'Nunito'; font-size:0.85rem;">
                    <div class="alert alert-success" style="background:#ecfdf5; border:1px dashed var(--accent-green);">
                        <strong id="submitTaskTitle" class="d-block mb-1 text-dark"></strong>
                        <span class="text-success"><i class="far fa-calendar-alt"></i> Target Laporan: <b id="submitMonthName"></b> {{ $year }}</span>
                    </div>
                    <div class="mb-3"><label class="fw-bold mb-1">Tanggal Diselesaikan:</label><input type="date" name="completed_at" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}"></div>
                    <div class="mb-3"><label class="fw-bold mb-1">Unggah Bukti (PDF / Gambar):</label><input type="file" name="proof_file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" required><small class="text-muted">Maksimal 5MB.</small></div>
                    <div class="mb-2"><label class="fw-bold mb-1">Catatan / Keterangan (Opsional):</label><textarea name="notes" class="form-control form-control-sm" rows="3"></textarea></div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="border-radius:8px;">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold" style="border-radius:8px;"><i class="fas fa-paper-plane"></i> Kirim Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL VIEW BUKTI & VERIFIKASI (ATASAN & STAF) --}}
<div class="modal fade" id="viewLogModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold text-dark" style="font-family:'Nunito';"><i class="fas fa-file-signature text-primary me-2"></i>Detail Bukti Kinerja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="font-family:'Nunito'; font-size:0.9rem;">
                <input type="hidden" id="verifyLogId">
                <table class="table table-borderless mb-0">
                    <tr><td class="text-muted fw-bold" width="130">Diselesaikan Tgl</td><td>: <span id="viewCompletedAt" class="fw-bold text-dark"></span></td></tr>
                    <tr><td class="text-muted fw-bold">Status Verifikasi</td><td>: <span id="viewStatus"></span></td></tr>
                    <tr><td class="text-muted fw-bold">Catatan Staf</td><td>: <span id="viewNotes" class="fst-italic"></span></td></tr>
                </table>
                <hr class="text-muted my-3">
                <div class="text-center mb-3">
                    <a href="#" id="viewFileLink" target="_blank" class="btn btn-outline-primary btn-sm fw-bold" style="border-radius:8px;"><i class="fas fa-download me-1"></i> Unduh File Bukti Dukung</a>
                </div>
                
                {{-- TOMBOL VERIFIKASI (HANYA MUNCUL BUAT ATASAN JIKA STATUS PENDING) --}}
                @if(Auth::user()->isKTU() || Auth::user()->isKasubag())
                <div id="verifyActionBlock" class="d-none bg-light p-3 rounded text-center border">
                    <p class="small fw-bold text-muted mb-2">Verifikasi Laporan Ini?</p>
                    <button type="button" class="btn btn-success btn-sm fw-bold rounded-pill px-3 btn-verify" data-status="approved"><i class="fas fa-check"></i> Setujui</button>
                    <button type="button" class="btn btn-danger btn-sm fw-bold rounded-pill px-3 btn-verify" data-status="rejected"><i class="fas fa-times"></i> Tolak / Revisi</button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // --- CREATE TASK ---
    $('#formCreateTask').submit(function(e) {
        e.preventDefault();
        if ($('input[name="target_months[]"]:checked').length === 0) return Swal.fire('Ops!', 'Pilih minimal 1 bulan target.', 'warning');
        let btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        $.ajax({ url: "{{ route('admin.productivity.routine.store') }}", type: "POST", data: $(this).serialize() })
            .done(() => location.reload())
            .fail(() => { Swal.fire('Gagal!', 'Terjadi kesalahan.', 'error'); btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Tugas'); });
    });

    // --- OPEN EDIT MODAL ---
    $('.btn-edit-routine').click(function() {
        let id = $(this).data('id');
        let title = $(this).data('title');
        let months = $(this).data('months'); // ini array dari json_encode
        
        $('#editTaskId').val(id);
        $('#editTaskTitle').val(title);
        $('.edit-month-cb').prop('checked', false);
        months.forEach(m => { $(`#formEditTask input[value="${m}"]`).prop('checked', true); });
        
        new bootstrap.Modal(document.getElementById('editTaskModal')).show();
    });

    // --- UPDATE TASK ---
    $('#formEditTask').submit(function(e) {
        e.preventDefault();
        let id = $('#editTaskId').val();
        if ($('#formEditTask input[name="target_months[]"]:checked').length === 0) return Swal.fire('Ops!', 'Pilih minimal 1 bulan target.', 'warning');
        let btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        $.ajax({ url: `/admin/productivity/routine/${id}`, type: "PUT", data: $(this).serialize() })
            .done(() => { Swal.fire({ title: 'Berhasil', icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload()); })
            .fail(() => { Swal.fire('Gagal!', 'Terjadi kesalahan.', 'error'); btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update Tugas'); });
    });

    // --- SUBMIT LOG ---
    $('.btn-submit-log').click(function() {
        $('#submitTaskId').val($(this).data('id'));
        $('#submitMonth').val($(this).data('month'));
        $('#submitTaskTitle').text($(this).data('title'));
        $('#submitMonthName').text($(this).data('monthname'));
        $('#formSubmitLog')[0].reset();
        $('#formSubmitLog input[type="date"]').val(new Date().toISOString().split('T')[0]);
        new bootstrap.Modal(document.getElementById('submitLogModal')).show();
    });

    $('#formSubmitLog').submit(function(e) {
        e.preventDefault();
        let btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengirim...');
        $.ajax({ url: `/admin/productivity/routine/${$('#submitTaskId').val()}/submit`, type: "POST", data: new FormData(this), processData: false, contentType: false })
            .done(() => { Swal.fire({ title: 'Berhasil!', icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload()); })
            .fail(() => { Swal.fire('Gagal!', 'File max 5MB.', 'error'); btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Kirim Laporan'); });
    });

    // --- VIEW LOG & VERIFY ---
    $('.btn-view-log').click(function() {
        let status = $(this).data('status');
        $('#viewCompletedAt').text($(this).data('date'));
        $('#viewNotes').text($(this).data('notes') || 'Tidak ada catatan.');
        $('#viewFileLink').attr('href', $(this).data('file'));
        $('#verifyLogId').val($(this).data('logid')); // Set ID Log untuk verifikasi

        let badge = $('#viewStatus');
        if (status === 'approved') badge.removeClass().addClass('badge bg-success rounded-pill px-3').text('Disetujui');
        else if (status === 'rejected') badge.removeClass().addClass('badge bg-danger rounded-pill px-3').text('Ditolak / Revisi');
        else badge.removeClass().addClass('badge bg-warning text-dark rounded-pill px-3').text('Menunggu Verifikasi');

        // Tampilkan tombol verifikasi (hanya jika pending dan atasan login)
        if (status === 'pending_approval' && $('#verifyActionBlock').length) {
            $('#verifyActionBlock').removeClass('d-none');
        } else {
            $('#verifyActionBlock').addClass('d-none');
        }

        new bootstrap.Modal(document.getElementById('viewLogModal')).show();
    });

    // --- VERIFY AJAX ---
    $('.btn-verify').click(function() {
        let logId = $('#verifyLogId').val();
        let status = $(this).data('status');
        let btn = $(this);
        let oriHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
        
        $.ajax({ url: `/admin/productivity/routine/log/${logId}/verify`, type: "PATCH", data: { status: status } })
            .done(() => { Swal.fire({ title: 'Tersimpan!', icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload()); })
            .fail(() => { Swal.fire('Gagal!', 'Terjadi kesalahan.', 'error'); btn.prop('disabled', false).html(oriHtml); });
    });
});
</script>
@endsection
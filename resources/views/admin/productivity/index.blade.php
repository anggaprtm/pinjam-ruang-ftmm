@extends('layouts.admin')

@section('styles')
<style>
    /* Global Workspace Styles */
    .workspace-header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; border-radius: 15px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); }
    .card-modern { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); height: 100%; }
    .card-modern .card-header { background: transparent; border-bottom: 1px solid #f3f4f6; padding: 1.25rem 1.5rem; font-weight: 700; }
    
    /* Super Productivity Task UI */
    .task-item { display: flex; align-items: flex-start; justify-content: space-between; padding: 1rem; border: 1px solid #f3f4f6; border-radius: 10px; margin-bottom: 0.75rem; transition: all 0.2s; background: #fff; gap: 1rem; }
    .task-item:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.05); transform: translateY(-2px); border-left: 3px solid #3b82f6; }
    .task-item.completed { opacity: 0.5; background: #f8fafc; border-left: 3px solid #10b981; }
    .task-item.completed .task-title { text-decoration: line-through; color: #64748b; }
    .custom-checkbox { width: 22px; height: 22px; cursor: pointer; border-radius: 6px; margin-top: 2px; flex-shrink: 0; }
    .task-tag { font-size: 0.7rem; padding: 3px 8px; border-radius: 6px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
    
    /* Sleek Quick Add Modal */
    .modal-quick-add .modal-content { border-radius: 16px; border: none; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
    .input-title-sleek { font-size: 1.25rem; padding: 1rem 1.5rem; border: none; font-weight: 500; border-bottom: 1px solid #f1f5f9; border-radius: 0; }
    .input-title-sleek:focus { box-shadow: none; border-bottom: 1px solid #3b82f6; }
    .quick-toolbar { background: #f8fafc; padding: 1rem 1.5rem; display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; }
    
    /* Date Quick Buttons */
    .date-quick-btn { font-size: 0.75rem; font-weight: 600; padding: 6px 12px; border-radius: 20px; border: 1px solid #cbd5e1; background: #fff; color: #475569; cursor: pointer; transition: all 0.15s; }
    .date-quick-btn:hover, .date-quick-btn.active { background: #3b82f6; border-color: #3b82f6; color: white; }

    /* Habit Styles */
    .habit-item { display: flex; align-items: center; padding: 0.8rem 1rem; border-radius: 10px; margin-bottom: 0.5rem; background: #f8fafc; transition: all 0.2s; border-left: 4px solid transparent; }
    .habit-item.active { background: #ecfdf5; border-left-color: #10b981; }
    .habit-btn { width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #cbd5e1; background: #fff; color: #cbd5e1; cursor: pointer; transition: all 0.2s; flex-shrink: 0; }
    .habit-item.active .habit-btn { background: #10b981; border-color: #10b981; color: #fff; }
    
    /* Sticky Notes Styles */
    .notes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.5rem; }
    .sticky-note { padding: 1.5rem; border-radius: 12px; box-shadow: 3px 4px 15px rgba(0,0,0,0.06); position: relative; transition: transform 0.2s; min-height: 180px; display: flex; flex-direction: column; }
    .sticky-note:hover { transform: rotate(-1deg) scale(1.02); }
    .sticky-note .delete-btn { position: absolute; top: 10px; right: 10px; opacity: 0; transition: opacity 0.2s; color: #ef4444; cursor: pointer; background: transparent; border: none; }
    .sticky-note:hover .delete-btn { opacity: 1; }
    .sticky-note-title { font-weight: bold; margin-bottom: 0.5rem; font-size: 1.05rem; }
    .sticky-note-content { white-space: pre-wrap; font-size: 0.9rem; color: #374151; flex-grow: 1; }
</style>
@endsection

@section('content')
<div class="container-fluid p-0">
    
    {{-- Header --}}
    <div class="workspace-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1 fw-bold text-white">👋 Halo, {{ Auth::user()->name }}!</h3>
            <p class="mb-0 opacity-75"><i class="fas fa-calendar-alt me-2"></i>{{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}</p>
        </div>
        <div class="text-end d-flex align-items-center gap-2">
            <span class="badge bg-white text-dark px-3 py-2 rounded-pill fs-6 shadow-sm d-none d-md-inline-block">
                <i class="fas fa-bolt text-warning me-1"></i> Focus Mode
            </span>
            <button class="btn btn-light text-secondary rounded-pill px-3 shadow-sm fw-bold border-0" data-bs-toggle="modal" data-bs-target="#settingsModal">
                <i class="fas fa-cog"></i> <span class="d-none d-sm-inline ms-1">Pengaturan</span>
            </button>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- KIRI: TASKS & TO-DO (8 Kolom) --}}
        <div class="col-xl-8 col-lg-7">
            <div class="card card-modern border-0 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom">
                    <span class="text-dark fw-bold fs-5"><i class="fas fa-check-square me-2 text-primary"></i> Tugas Saya</span>
                    <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm fw-bold" onclick="openTaskModal()">
                        <i class="fas fa-plus me-1"></i> Tambah Tugas
                    </button>
                </div>
                <div class="card-body p-4 bg-light bg-opacity-50" id="task-container">
                    @forelse($tasks as $task)
                        <div class="task-item {{ $task->status == 'completed' ? 'completed' : '' }}" id="task-{{ $task->id }}">
                            <input class="form-check-input custom-checkbox task-checkbox" type="checkbox" 
                                   data-id="{{ $task->id }}" {{ $task->status == 'completed' ? 'checked' : '' }}>
                            
                            <div class="flex-grow-1">
                                <div class="task-title fw-bold text-dark" style="font-size: 1.05rem;">{{ $task->title }}</div>
                                <div class="d-flex flex-wrap gap-2 mt-2 align-items-center">
                                    @if($task->tag)
                                        <span class="task-tag"><i class="fas fa-tag text-primary"></i> {{ $task->tag }}</span>
                                    @endif
                                    @if($task->deadline_at)
                                        @php 
                                            $deadline = \Carbon\Carbon::parse($task->deadline_at);
                                            $isOverdue = $deadline->isPast() && $task->status != 'completed';
                                        @endphp
                                        <span class="task-tag {{ $isOverdue ? 'bg-danger text-white border-danger' : '' }}">
                                            <i class="far fa-clock"></i> 
                                            {{ $deadline->isToday() ? 'Hari ini, ' . $deadline->format('H:i') : $deadline->format('d M, H:i') }}
                                        </span>
                                    @endif
                                    <span class="task-tag">
                                        @if($task->priority == 'high') <i class="fas fa-arrow-up text-danger"></i> High
                                        @elseif($task->priority == 'medium') <i class="fas fa-minus text-warning"></i> Med
                                        @else <i class="fas fa-arrow-down text-info"></i> Low @endif
                                    </span>
                                </div>
                            </div>
                            
                            <button class="btn btn-sm text-danger btn-delete-task border-0 bg-transparent opacity-50 hover-opacity-100" data-id="{{ $task->id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted" id="empty-task-msg">
                            <i class="fas fa-clipboard-check fa-3x mb-3 opacity-25"></i>
                            <p>Semua tugas selesai. Waktunya bersantai!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- KANAN: HABIT TRACKER (4 Kolom) --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card card-modern border-0 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom">
                    <span class="text-dark fw-bold fs-5"><i class="fas fa-fire me-2 text-warning"></i> Habit Harian</span>
                    <button class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#habitModal">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="card-body p-4" id="habit-container">
                    @forelse($habits as $habit)
                        <div class="habit-item {{ $habit->is_completed_today ? 'active' : '' }}" id="habit-{{ $habit->id }}">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="habit-btn habit-toggle" data-id="{{ $habit->id }}">
                                        <i class="{{ $habit->icon ?? 'fas fa-check' }}"></i>
                                    </div>
                                    <span class="fw-bold text-dark">{{ $habit->name }}</span>
                                </div>
                                <button class="btn btn-sm text-danger btn-delete-habit border-0 bg-transparent" data-id="{{ $habit->id }}"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <p class="small mb-0">Belum ada habit. Mulai bangun kebiasaan baik!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- BAWAH: STICKY NOTES (12 Kolom) --}}
    <div class="card card-modern border-0 shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom">
            <span class="text-dark fw-bold fs-5"><i class="far fa-sticky-note me-2 text-warning"></i> Brain Dump & Notes</span>
            <button class="btn btn-sm btn-warning fw-bold text-dark rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#noteModal">
                <i class="fas fa-pen me-1"></i> Tulis Note
            </button>
        </div>
        <div class="card-body p-4 rounded-bottom" style="background-color: #f8fafc; border-top: 1px dashed #e2e8f0;">
            <div class="notes-grid" id="note-container">
                @forelse($notes as $note)
                    <div class="sticky-note" id="note-{{ $note->id }}" style="background-color: {{ $note->bg_color }};">
                        <button class="delete-btn btn-delete-note" data-id="{{ $note->id }}"><i class="fas fa-trash-alt"></i></button>
                        @if($note->title)<div class="sticky-note-title">{{ $note->title }}</div>@endif
                        <div class="sticky-note-content">{{ $note->content }}</div>
                        <div class="small text-muted mt-3 pt-2 border-top border-dark border-opacity-10" style="font-size: 0.75rem;">
                            <i class="far fa-clock me-1"></i>{{ $note->created_at->diffForHumans() }}
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5" id="empty-note-msg">
                        Papan catatan masih kosong.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ============================== MODALS ============================== --}}

{{-- SLEEK MODAL TASK (Gaya Super Productivity) --}}
<div class="modal fade modal-quick-add" id="taskModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="formAddTask">
                {{-- Input Judul Besar --}}
                <input type="text" name="title" id="taskTitleInput" class="form-control input-title-sleek shadow-none" required placeholder="Apa yang ingin Anda kerjakan?" autocomplete="off">
                
                {{-- Quick Toolbar (Tanggal & Tag) --}}
                <div class="quick-toolbar">
                    <div class="d-flex gap-2 align-items-center border-end pe-3">
                        <button type="button" class="date-quick-btn active" onclick="setQuickDate('today', this)">Hari Ini</button>
                        <button type="button" class="date-quick-btn" onclick="setQuickDate('tomorrow', this)">Besok</button>
                        <input type="datetime-local" name="deadline_at" id="taskDeadlineInput" class="form-control form-control-sm border-0 shadow-none bg-transparent fw-bold text-primary" style="width: auto;">
                    </div>
                    
                    <div class="d-flex gap-2 align-items-center flex-grow-1 ps-2">
                        <i class="fas fa-tags text-muted"></i>
                        <select name="tag" id="taskTagSelect" class="form-select form-select-sm border-0 shadow-none bg-transparent" style="width: 100%;">
                            <option value="">Tambah Tag...</option>
                            <option value="Pekerjaan">Pekerjaan</option>
                            <option value="Pribadi">Pribadi</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2 align-items-center border-start ps-3">
                        <select name="priority" class="form-select form-select-sm border-0 shadow-none bg-transparent fw-bold text-danger">
                            <option value="low">Low Pri</option>
                            <option value="medium" selected>Med Pri</option>
                            <option value="high">High Pri</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 pt-0">
                    <button type="submit" class="btn btn-primary px-4 fw-bold rounded-pill shadow-sm"><i class="fas fa-paper-plane me-2"></i>Simpan Tugas (Enter)</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Note Baru --}}
<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header bg-warning border-0 pb-2"><h5 class="modal-title text-dark fw-bold"><i class="far fa-sticky-note me-2"></i>Catatan Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formAddNote">
                <div class="modal-body p-4 pt-3 bg-warning">
                    <input type="text" name="title" class="form-control border-0 shadow-none bg-transparent text-dark fw-bold fs-5 px-0 mb-2" placeholder="Judul (Opsional)..." style="border-bottom: 1px solid rgba(0,0,0,0.1)!important; border-radius:0;">
                    <textarea name="content" class="form-control border-0 shadow-none bg-transparent text-dark px-0" rows="5" required placeholder="Tulis ide atau catatanmu di sini..."></textarea>
                </div>
                <div class="modal-footer bg-warning border-0"><button type="submit" class="btn btn-dark w-100 fw-bold rounded-pill">Tempel Catatan</button></div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Habit Baru --}}
<div class="modal fade" id="habitModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i class="fas fa-leaf me-2 text-success"></i>Habit Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formAddHabit">
                <div class="modal-body px-4 pb-4 pt-1">
                    <label class="form-label fw-bold text-muted small">Apa yang ingin dijadikan kebiasaan?</label>
                    <input type="text" name="name" class="form-control form-control-lg bg-light border-0" required placeholder="Cth: Minum Air 2L">
                </div>
                <div class="modal-footer border-0 pt-0"><button type="submit" class="btn btn-success w-100 fw-bold rounded-pill">Mulai Habit</button></div>
            </form>
        </div>
    </div>
</div>
{{-- Modal Pengaturan --}}
<div class="modal fade" id="settingsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header bg-light border-0"><h5 class="modal-title fw-bold text-dark"><i class="fas fa-cog me-2 text-secondary"></i>Pengaturan Workspace</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formUpdateSettings">
                <div class="modal-body p-4">
                    <h6 class="fw-bold mb-3"><i class="fab fa-telegram text-primary me-2"></i>Notifikasi Telegram Bot</h6>
                    
                    @if(empty(Auth::user()->telegram_chat_id))
                        <div class="alert alert-warning small">
                            <i class="fas fa-exclamation-triangle me-1"></i> Anda belum menautkan ID Telegram. Silakan hubungi Bot dan masukkan ID di menu Edit Profil agar fitur ini berfungsi.
                        </div>
                    @endif

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="telegram_remind_morning" id="setMorning" style="width: 2.5em; height: 1.3em;" {{ Auth::user()->telegram_remind_morning ? 'checked' : '' }}>
                        <label class="form-check-label ms-2 pt-1 fw-semibold" for="setMorning">Kirim Rekap Pagi (07:00 WIB)</label>
                        <div class="form-text mt-0 ms-2">Menerima ringkasan tugas dan habit yang harus dilakukan hari ini.</div>
                    </div>
                    
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="telegram_remind_deadline" id="setDeadline" style="width: 2.5em; height: 1.3em;" {{ Auth::user()->telegram_remind_deadline ? 'checked' : '' }}>
                        <label class="form-check-label ms-2 pt-1 fw-semibold" for="setDeadline">Peringatan Deadline (H-1 Jam)</label>
                        <div class="form-text mt-0 ms-2">Menerima pesan darurat saat sebuah tugas mendekati tenggat waktu.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light"><button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill">Simpan Pengaturan</button></div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// --- LOGIKA QUICK DATE TASK ---
function openTaskModal() {
    $('#taskModal').modal('show');
    setTimeout(() => $('#taskTitleInput').focus(), 300);
    setQuickDate('today', $('.date-quick-btn')[0]); // Default hari ini jam 16:00
}

function setQuickDate(type, btnElement) {
    $('.date-quick-btn').removeClass('active');
    $(btnElement).addClass('active');

    let date = new Date();
    if (type === 'tomorrow') {
        date.setDate(date.getDate() + 1);
    }
    
    // Default jam 16:00 (Bisa disesuaikan)
    date.setHours(16, 0, 0, 0); 
    
    // Format ke YYYY-MM-DDTHH:mm untuk input datetime-local
    let offset = date.getTimezoneOffset() * 60000;
    let localISOTime = (new Date(date - offset)).toISOString().slice(0, 16);
    
    $('#taskDeadlineInput').val(localISOTime);
}

$(document).ready(function() {
    // Setup CSRF
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // Inisialisasi Tags Select2 di dalam Modal Task
    $('#taskTagSelect').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#taskModal'),
        tags: true, // Memungkinkan user ngetik tag custom
        placeholder: "Ketik atau pilih tag..."
    });

    // AJAX Simpan Settings
    $('#formUpdateSettings').submit(function(e) {
        e.preventDefault();
        let btn = $(this).find('button[type="submit"]');
        btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...').prop('disabled', true);
        
        $.post("{{ route('admin.productivity.settings.update') }}", $(this).serialize())
        .done(function() {
            $('#settingsModal').modal('hide');
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Pengaturan disimpan!', showConfirmButton: false, timer: 2000 });
        })
        .always(function() {
            btn.html('Simpan Pengaturan').prop('disabled', false);
        });
    });

    // 1. AJAX Tambah Task
    $('#formAddTask').submit(function(e) {
        e.preventDefault();
        $.post("{{ route('admin.productivity.tasks.store') }}", $(this).serialize())
        .done(function(res) {
            if(res.success) location.reload(); 
        })
        .fail(function(xhr) {
            Swal.fire('Gagal!', xhr.responseJSON.message || 'Terjadi kesalahan sistem', 'error');
        });
    });

    // 2. AJAX Toggle Status Task
    $('.task-checkbox').change(function() {
        let taskId = $(this).data('id');
        let status = $(this).is(':checked') ? 'completed' : 'pending';
        let itemDiv = $('#task-' + taskId);
        
        if(status === 'completed') itemDiv.addClass('completed');
        else itemDiv.removeClass('completed');

        $.ajax({
            url: `/admin/productivity/tasks/${taskId}/status`, type: 'PATCH', data: { status: status },
            success: function() { 
                const Toast = Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 1500 });
                Toast.fire({ icon: 'success', title: status === 'completed' ? 'Tugas Selesai! 🎉' : 'Tugas Diaktifkan Kembali' });
            }
        });
    });

    // 3. AJAX Delete Task
    $('.btn-delete-task').click(function() {
        let taskId = $(this).data('id');
        Swal.fire({ title: 'Hapus Tugas?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus' }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({ url: `/admin/productivity/tasks/${taskId}`, type: 'DELETE', success: function() { $('#task-' + taskId).slideUp(300, function(){ $(this).remove(); }); }});
            }
        });
    });

    // 4. AJAX Toggle Habit
    $('.habit-toggle').click(function() {
        let habitId = $(this).data('id');
        let itemDiv = $('#habit-' + habitId);
        
        $.post(`/admin/productivity/habits/${habitId}/toggle`, function(res) {
            if(res.is_completed) itemDiv.addClass('active');
            else itemDiv.removeClass('active');
            
            const Toast = Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 1500 });
            Toast.fire({ icon: res.is_completed ? 'success' : 'info', title: res.is_completed ? 'Habit Selesai!' : 'Habit Dibatalkan' });
        });
    });

    // 5. AJAX Tambah & Hapus Note/Habit dengan Error Handling
    $('#formAddNote').submit(function(e) { 
        e.preventDefault(); 
        $.post("{{ route('admin.productivity.notes.store') }}", $(this).serialize())
        .done(function() { location.reload(); })
        .fail(function(xhr) { Swal.fire('Gagal Menyimpan Catatan', xhr.responseJSON.message || 'Cek kembali isian Anda.', 'error'); });
    });
    
    $('#formAddHabit').submit(function(e) { 
        e.preventDefault(); 
        $.post("{{ route('admin.productivity.habits.store') }}", $(this).serialize())
        .done(function() { location.reload(); })
        .fail(function(xhr) { Swal.fire('Gagal Menyimpan Habit', xhr.responseJSON.message, 'error'); });
    });
    
    $('.btn-delete-note').click(function() { 
        let id = $(this).data('id'); 
        $.ajax({ url: `/admin/productivity/notes/${id}`, type: 'DELETE', success: function() { $('#note-' + id).fadeOut(300, function(){ $(this).remove(); }); }}); 
    });
    
    $('.btn-delete-habit').click(function() { 
        let id = $(this).data('id'); 
        $.ajax({ url: `/admin/productivity/habits/${id}`, type: 'DELETE', success: function() { $('#habit-' + id).slideUp(); }}); 
    });
});
</script>
@endsection
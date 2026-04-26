@php
    $isCompleted = $task->status === 'completed';
    // Cek apakah tugas overdue: Jika ada parameter forceOverdue dari loop, atau manual hitung berdasarkan deadline
    $isOverdue = isset($forceOverdue) && $forceOverdue 
        ? true 
        : ($task->deadline_at && \Carbon\Carbon::parse($task->deadline_at)->lt(\Carbon\Carbon::today()->startOfDay()) && !$isCompleted);
    
    // Tentukan class berdasarkan status & prioritas
    $priorityClass  = 'priority-' . $task->priority; // priority-high, priority-medium, priority-low
    $overdueClass   = $isOverdue ? 'task-overdue' : '';
    $completedClass = $isCompleted ? 'completed' : '';
@endphp

<div class="task-item {{ $priorityClass }} {{ $completedClass }} {{ $overdueClass }}" id="task-{{ $task->id }}">
    
    {{-- Checkbox Toggle Status --}}
    <input type="checkbox" 
           class="task-check task-checkbox" 
           data-id="{{ $task->id }}" 
           {{ $isCompleted ? 'checked' : '' }}
           autocomplete="off"
           title="Tandai Selesai / Belum Selesai">

    {{-- Konten Utama Tugas --}}
    <div class="task-content">
        {{-- Judul (Bisa diklik untuk view detail) --}}
        <div class="task-title-text btn-view-task" 
             data-id="{{ $task->id }}"
             data-title="{{ $task->title }}"
             data-desc="{{ $task->description }}"
             data-priority="{{ ucfirst($task->priority) }}"
             data-deadline="{{ $task->deadline_at ? \Carbon\Carbon::parse($task->deadline_at)->translatedFormat('d M Y, H:i') : 'Tanpa Deadline' }}"
             data-subtasks="{{ $task->subTasks->toJson() }}"
             data-attachments="{{ $task->attachments->toJson() }}"
             data-comments="{{ $task->comments->toJson() }}">
            {{ $task->title }}
        </div>

        {{-- Meta / Badges --}}
        <div class="task-meta">
            {{-- Tag --}}
            @if($task->tag)
                <span class="task-badge badge-tag"><i class="fas fa-hashtag"></i> {{ $task->tag }}</span>
            @endif

            {{-- Rekurensi --}}
            @if($task->recurrence !== 'none')
                <span class="task-badge badge-recur"><i class="fas fa-sync-alt"></i> {{ ucfirst($task->recurrence) }}</span>
            @endif

            {{-- Indikator Subtask --}}
            @if($task->subTasks && $task->subTasks->count() > 0)
                @php
                    $subTotal = $task->subTasks->count();
                    $subDone  = $task->subTasks->where('is_completed', true)->count();
                @endphp
                <span class="task-badge badge-tag"><i class="fas fa-tasks"></i> {{ $subDone }}/{{ $subTotal }}</span>
            @endif

            {{-- Indikator Lampiran --}}
            @if($task->attachments && $task->attachments->count() > 0)
                <span class="task-badge badge-tag"><i class="fas fa-paperclip"></i> {{ $task->attachments->count() }}</span>
            @endif

            {{-- Deadline --}}
            @if($task->deadline_at)
                @php
                    $dl = \Carbon\Carbon::parse($task->deadline_at);
                @endphp
                @if($isOverdue)
                    <span class="task-badge badge-overdue"><i class="fas fa-exclamation-circle"></i> {{ $dl->translatedFormat('d M Y, H:i') }}</span>
                @elseif($dl->isToday())
                    <span class="task-badge badge-today"><i class="far fa-clock"></i> Hari ini, {{ $dl->format('H:i') }}</span>
                @else
                    <span class="task-badge badge-deadline"><i class="far fa-calendar-alt"></i> {{ $dl->translatedFormat('d M Y, H:i') }}</span>
                @endif
            @endif

            {{-- Indikator Delegasi --}}
            @if($task->assigned_by && $task->assigned_by !== Auth::id() && $task->user_id === Auth::id())
                <span class="task-badge badge-tag" title="Tugas masuk dari orang lain"><i class="fas fa-user-tie"></i> Delegasi Masuk</span>
            @elseif($task->assigned_by === Auth::id() && $task->user_id !== Auth::id())
                <span class="task-badge" style="background:#e0e7ff; color:#4338ca; border:1px solid #c7d2fe;" title="Tugas yang Anda delegasikan">
                    <i class="fas fa-paper-plane"></i> Ke Pegawai Lain
                </span>
            @endif
        </div>
    </div>

    {{-- Aksi (Edit, View, Arsip, Hapus) --}}
    <div class="task-actions">
        {{-- Edit Button --}}
        <button class="task-action-btn edit btn-edit-task" title="Edit Tugas"
            data-id="{{ $task->id }}"
            data-title="{{ $task->title }}"
            data-desc="{{ $task->description }}"
            data-tag="{{ $task->tag }}"
            data-priority="{{ $task->priority }}"
            data-recurrence="{{ $task->recurrence }}"
            data-assignee="{{ $task->user_id !== Auth::id() ? $task->user_id : '' }}"
            data-deadline="{{ $task->deadline_at ? \Carbon\Carbon::parse($task->deadline_at)->format('Y-m-d\TH:i') : '' }}"
            data-subtasks="{{ $task->subTasks->toJson() }}"
            data-attachments="{{ $task->attachments->toJson() }}">
            <i class="fas fa-pencil-alt"></i>
        </button>

        {{-- View Button --}}
        <button class="task-action-btn btn-view-task" title="Detail / Diskusi"
            data-id="{{ $task->id }}"
            data-title="{{ $task->title }}"
            data-desc="{{ $task->description }}"
            data-priority="{{ ucfirst($task->priority) }}"
            data-deadline="{{ $task->deadline_at ? \Carbon\Carbon::parse($task->deadline_at)->translatedFormat('d M Y, H:i') : 'Tanpa Deadline' }}"
            data-subtasks="{{ $task->subTasks->toJson() }}"
            data-attachments="{{ $task->attachments->toJson() }}"
            data-comments="{{ $task->comments->toJson() }}">
            <i class="fas fa-eye"></i>
        </button>

        {{-- Archive / Unarchive Button --}}
        @if($task->is_archived)
            <button class="task-action-btn archive btn-unarchive-task" title="Pulihkan Arsip" data-id="{{ $task->id }}">
                <i class="fas fa-box-open"></i>
            </button>
        @else
            <button class="task-action-btn archive btn-archive-task" title="Arsipkan Tugas" data-id="{{ $task->id }}">
                <i class="fas fa-archive"></i>
            </button>
        @endif

        {{-- Delete Button --}}
        <button class="task-action-btn danger btn-delete-task" title="Hapus Tugas" data-id="{{ $task->id }}">
            <i class="fas fa-trash-alt"></i>
        </button>
    </div>
</div>
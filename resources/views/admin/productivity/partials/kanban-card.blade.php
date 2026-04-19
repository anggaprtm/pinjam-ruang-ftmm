@php
    $isOverdue = $task->deadline_at && \Carbon\Carbon::parse($task->deadline_at)->isPast() && $task->status != 'completed';
    $isToday   = $task->deadline_at && \Carbon\Carbon::parse($task->deadline_at)->isToday();
@endphp

<div class="task-item kanban-card mb-2 p-3 shadow-sm bg-white" 
     data-id="{{ $task->id }}" 
     style="cursor: grab; flex-direction: column; gap: 0.5rem; border-left: 3px solid {{ $task->priority == 'high' ? 'var(--accent-red)' : ($task->priority == 'medium' ? 'var(--accent-amber)' : 'var(--accent-blue)') }};">
    
    {{-- Row Atas: Tag & Recurrence --}}
    <div class="d-flex justify-content-between align-items-center w-100">
        <div class="d-flex gap-1 flex-wrap">
            @if($task->tag)
                <span class="badge bg-light text-secondary border small" style="font-size:0.6rem;"><i class="fas fa-tag"></i> {{ $task->tag }}</span>
            @endif
            @if($task->recurrence && $task->recurrence != 'none')
                <span class="badge" style="background:#ecfdf5; color:#059669; font-size:0.6rem;"><i class="fas fa-sync-alt"></i></span>
            @endif
        </div>
        
        {{-- Delegasi Indicator (Opsional di pojok) --}}
        @if($task->assigned_by && $task->assigned_by != Auth::id() && $task->user_id == Auth::id())
            <span title="Tugas Masuk" class="text-primary" style="font-size:0.7rem;"><i class="fas fa-arrow-down"></i></span>
        @elseif($task->assigned_by == Auth::id() && $task->user_id != Auth::id())
            <span title="Tugas Keluar" class="text-danger" style="font-size:0.7rem;"><i class="fas fa-arrow-up"></i></span>
        @endif
    </div>

    {{-- Judul (Clickable Modal) --}}
    <div class="fw-bold text-dark btn-view-task w-100" 
         style="font-size: 0.9rem; font-family:'Nunito',sans-serif; cursor:pointer; line-height: 1.3;"
         data-id="{{ $task->id }}"
         data-title="{{ $task->title }}"
         data-desc="{{ $task->description }}"
         data-priority="{{ $task->priority }}"
         data-deadline="{{ $task->deadline_at ? \Carbon\Carbon::parse($task->deadline_at)->format('d M Y, H:i') : 'Tanpa Tenggat' }}"
         data-subtasks='{{ $task->subTasks->toJson() }}'
         data-attachments='{{ $task->attachments->toJson() }}'
         data-comments='{{ $task->comments->toJson() }}'>
        {{ $task->title }}
    </div>
    
    {{-- Row Bawah: Info & Indikator --}}
    <div class="d-flex justify-content-between align-items-end w-100 mt-1">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            {{-- Deadline --}}
            @if($task->deadline_at)
                <span class="badge {{ $isOverdue ? 'bg-danger-subtle text-danger' : ($isToday ? 'bg-warning-subtle text-warning' : 'bg-light text-dark border') }}" style="font-size:0.65rem;">
                    <i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($task->deadline_at)->format('d M, H:i') }}
                </span>
            @endif
        </div>

        {{-- Meta Icons (Subtask, Attachments, Comments) --}}
        <div class="d-flex gap-2 text-muted" style="font-size: 0.75rem;">
            @if($task->subTasks->count() > 0)
                <span title="Checklist"><i class="fas fa-tasks"></i> {{ $task->subTasks->where('is_completed', true)->count() }}/{{ $task->subTasks->count() }}</span>
            @endif
            @if($task->attachments->count() > 0)
                <span title="Lampiran"><i class="fas fa-paperclip"></i> {{ $task->attachments->count() }}</span>
            @endif
            @if($task->comments->count() > 0)
                <span title="Komentar"><i class="far fa-comments"></i> {{ $task->comments->count() }}</span>
            @endif
        </div>
    </div>
</div>
@php
    $isOverdue  = $task->deadline_at && \Carbon\Carbon::parse($task->deadline_at)->isPast() && $task->status != 'completed';
    $isToday    = $task->deadline_at && \Carbon\Carbon::parse($task->deadline_at)->isToday();
    $dlParsed   = $task->deadline_at ? \Carbon\Carbon::parse($task->deadline_at) : null;

    // Label deadline yang ringkas
    $dlLabel = '';
    if ($dlParsed) {
        if ($isOverdue)     $dlLabel = 'Terlambat ' . $dlParsed->diffForHumans();
        elseif ($isToday)   $dlLabel = 'Hari ini ' . $dlParsed->format('H:i');
        else                $dlLabel = $dlParsed->format('d M, H:i');
    }

    // Warna aksen prioritas
    $accentColor = match($task->priority) {
        'high'   => 'var(--accent-red)',
        'medium' => 'var(--accent-amber)',
        default  => 'var(--accent-blue)',
    };

    // Hitung sub-task
    $totalSub     = $task->subTasks->count();
    $completedSub = $task->subTasks->where('is_completed', true)->count();
    $subPct       = $totalSub > 0 ? round(($completedSub / $totalSub) * 100) : 0;

    // BUG FIX #1: Encode JSON dengan benar — hindari single-quote attribute breakage
    $jsonSubtasks     = htmlspecialchars($task->subTasks->toJson(),     ENT_QUOTES, 'UTF-8');
    $jsonAttachments  = htmlspecialchars($task->attachments->toJson(),  ENT_QUOTES, 'UTF-8');
    $jsonComments     = htmlspecialchars($task->comments->toJson(),     ENT_QUOTES, 'UTF-8');
@endphp

<div class="kanban-card"
     data-id="{{ $task->id }}"
     draggable="true">

    {{-- ── Aksen warna prioritas (top bar) ── --}}
    <div class="kc-priority-bar" style="background: {{ $accentColor }};"></div>

    <div class="kc-body">

        {{-- ── Row 1: Badges ── --}}
        <div class="kc-badges">
            @if($task->tag)
                <span class="kc-badge kc-badge-tag">
                    <i class="fas fa-tag"></i> {{ $task->tag }}
                </span>
            @endif
            @if($task->recurrence && $task->recurrence !== 'none')
                <span class="kc-badge kc-badge-green" title="Berulang">
                    <i class="fas fa-sync-alt"></i>
                    {{ ['daily'=>'Harian','weekly'=>'Mingguan','monthly'=>'Bulanan'][$task->recurrence] ?? '' }}
                </span>
            @endif
            {{-- Delegasi incoming --}}
            @if($task->assigned_by && $task->assigned_by != Auth::id() && $task->user_id == Auth::id())
                <span class="kc-badge kc-badge-indigo" title="Delegasi masuk">
                    <i class="fas fa-arrow-down"></i> Masuk
                </span>
            @elseif($task->assigned_by == Auth::id() && $task->user_id != Auth::id())
                <span class="kc-badge kc-badge-rose" title="Delegasi keluar">
                    <i class="fas fa-arrow-up"></i> Keluar
                </span>
            @endif
        </div>

        {{-- ── Row 2: Judul ── --}}
        <div class="kc-title btn-view-task"
             data-id="{{ $task->id }}"
             data-title="{{ $task->title }}"
             data-desc="{{ $task->description }}"
             data-priority="{{ $task->priority }}"
             data-deadline="{{ $task->deadline_at ? \Carbon\Carbon::parse($task->deadline_at)->format('d M Y, H:i') : 'Tanpa Tenggat' }}"
             data-subtasks="{{ $jsonSubtasks }}"
             data-attachments="{{ $jsonAttachments }}"
             data-comments="{{ $jsonComments }}">
            {{ $task->title }}
        </div>

        {{-- ── Row 3: Deskripsi singkat (jika ada) ── --}}
        @if($task->description)
            <div class="kc-desc">{{ Str::limit(strip_tags($task->description), 70) }}</div>
        @endif

        {{-- ── Row 4: Sub-task progress bar ── --}}
        @if($totalSub > 0)
            <div class="kc-subtask-wrap">
                <div class="kc-subtask-bar">
                    <div class="kc-subtask-fill" style="width: {{ $subPct }}%;"></div>
                </div>
                <span class="kc-subtask-label">{{ $completedSub }}/{{ $totalSub }}</span>
            </div>
        @endif

        {{-- ── Row 5: Footer meta ── --}}
        <div class="kc-footer">
            {{-- Deadline --}}
            <div class="kc-footer-left">
                @if($dlParsed)
                    <span class="kc-deadline {{ $isOverdue ? 'kc-overdue' : ($isToday ? 'kc-today' : '') }}">
                        <i class="far fa-clock"></i> {{ $dlLabel }}
                    </span>
                @else
                    <span class="kc-no-deadline"><i class="far fa-clock"></i> Tanpa tenggat</span>
                @endif
            </div>

            {{-- Meta icons (attachments & comments) --}}
            <div class="kc-footer-right">
                @if($task->attachments->count() > 0)
                    <span class="kc-meta-icon" title="{{ $task->attachments->count() }} lampiran">
                        <i class="fas fa-paperclip"></i> {{ $task->attachments->count() }}
                    </span>
                @endif
                @if($task->comments->count() > 0)
                    <span class="kc-meta-icon" title="{{ $task->comments->count() }} komentar">
                        <i class="far fa-comment"></i> {{ $task->comments->count() }}
                    </span>
                @endif
                {{-- Priority dot --}}
                <span class="kc-priority-dot" style="background: {{ $accentColor }};"
                      title="Prioritas: {{ ucfirst($task->priority) }}"></span>
            </div>
        </div>
    </div>
</div>
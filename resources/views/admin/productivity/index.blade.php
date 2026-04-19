@extends('layouts.admin')

@section('styles')
{{-- Hanya Syne untuk display title, body tetap Nunito dari layout --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&display=swap" rel="stylesheet">

<style>
:root {
    --brand-maroon:     #741847;
    --brand-maroon-dk:  #5a1238;
    --brand-maroon-lt:  #f9eef4;
    --accent-blue:      #3b82f6;
    --accent-green:     #10b981;
    --accent-amber:     #f59e0b;
    --accent-red:       #ef4444;

    --surface-0:        #ffffff;
    --surface-1:        #f8f7f9;
    --surface-2:        #f1eef4;
    --surface-border:   #e8e2ee;

    --text-primary:     #1a1025;
    --text-secondary:   #6b6080;
    --text-muted:       #a09ab8;

    --radius-sm:        8px;
    --radius-md:        12px;
    --radius-lg:        16px;
    --radius-xl:        20px;

    --shadow-sm:        0 1px 3px rgba(116,24,71,0.06), 0 1px 2px rgba(0,0,0,0.04);
    --shadow-md:        0 4px 16px rgba(116,24,71,0.08), 0 2px 6px rgba(0,0,0,0.05);
    --shadow-lg:        0 10px 40px rgba(116,24,71,0.12), 0 4px 12px rgba(0,0,0,0.06);

    --transition:       all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ---- HEADER ---- */
.cmd-header {
    background: linear-gradient(135deg, var(--brand-maroon) 0%, #9c2456 50%, #741847 100%);
    border-radius: var(--radius-xl);
    padding: 1.75rem 2rem;
    margin-bottom: 1.75rem;
    box-shadow: 0 8px 32px rgba(116,24,71,0.3), inset 0 1px 0 rgba(255,255,255,0.15);
    position: relative;
    overflow: hidden;
}
.cmd-header::before {
    content: '';
    position: absolute; top: -60px; right: -60px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,0.04); border-radius: 50%;
}
.cmd-header::after {
    content: '';
    position: absolute; bottom: -80px; left: 30%;
    width: 250px; height: 250px;
    background: rgba(255,255,255,0.03); border-radius: 50%;
}
.cmd-header-title {
    font-family: 'Montserrat', sans-serif;
    font-size: 1.55rem;
    color: #fff;
    margin: 0; line-height: 1.2;
}
.cmd-header-subtitle {
    color: rgba(255,255,255,0.65);
    font-size: 0.875rem;
    margin-top: 0.35rem;
    font-family: 'Nunito', sans-serif; /* eksplisit Nunito */
}

/* Stat Pills */
.stat-pill {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.18);
    border-radius: 50px;
    padding: 0.45rem 1rem;
    color: #fff;
    font-size: 0.78rem;
    font-weight: 700;
    font-family: 'Nunito', sans-serif;
    display: inline-flex; align-items: center; gap: 0.4rem;
    backdrop-filter: blur(8px);
    transition: var(--transition);
    white-space: nowrap;
}
.stat-pill:hover { background: rgba(255,255,255,0.2); }
.stat-pill .stat-num { font-family: 'Montserrat', sans-serif; font-size: 1rem; }
.stat-pill.danger  { background: rgba(239,68,68,0.3); border-color: rgba(239,68,68,0.5); }
.stat-pill.success { background: rgba(16,185,129,0.2); border-color: rgba(16,185,129,0.4); }
.stat-pill.warning { background: rgba(245,158,11,0.2); border-color: rgba(245,158,11,0.4); }

/* ---- PANEL CARD ---- */
.panel-card {
    background: var(--surface-0);
    border-radius: var(--radius-lg);
    border: 1px solid var(--surface-border);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    height: 100%;
}
.panel-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--surface-border);
    background: var(--surface-0);
}
.panel-title {
    font-family: 'Nunito', sans-serif; /* Nunito, bukan Syne */
    font-size: 1rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0;
    display: flex; align-items: center; gap: 0.6rem;
}
.panel-title-icon {
    width: 28px; height: 28px;
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem;
}

/* ---- FILTER BAR ---- */
.filter-bar {
    display: flex; gap: 0.4rem; align-items: center; flex-wrap: wrap;
    padding: 0.75rem 1.25rem;
    background: var(--surface-0);
    border-bottom: 1px solid var(--surface-border);
}
.filter-tab {
    padding: 0.3rem 0.85rem;
    border-radius: 50px;
    border: 1px solid var(--surface-border);
    background: var(--surface-1);
    color: var(--text-secondary);
    font-size: 0.78rem;
    font-weight: 700;
    font-family: 'Nunito', sans-serif;
    cursor: pointer;
    text-decoration: none;
    transition: var(--transition);
    display: inline-flex; align-items: center; gap: 0.3rem;
}
.filter-tab:hover { border-color: var(--brand-maroon); color: var(--brand-maroon); }
.filter-tab.active {
    background: var(--brand-maroon); border-color: var(--brand-maroon);
    color: #fff; box-shadow: 0 2px 8px rgba(116,24,71,0.3);
}
.search-input-wrap {
    position: relative; margin-left: auto; flex-shrink: 0;
}
.search-input-wrap i {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    color: var(--text-muted); font-size: 0.78rem; pointer-events: none;
}
.search-input-wrap input {
    padding: 0.3rem 0.85rem 0.3rem 2rem;
    border-radius: 50px;
    border: 1px solid var(--surface-border);
    background: var(--surface-1);
    font-size: 0.8rem;
    font-family: 'Nunito', sans-serif;
    color: var(--text-primary);
    outline: none; width: 180px;
    transition: var(--transition);
}
.search-input-wrap input:focus {
    border-color: var(--brand-maroon);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(116,24,71,0.1);
    width: 220px;
}

/* ---- TASK ITEMS ---- */
.task-list { display: flex; flex-direction: column; gap: 0.55rem; }

.task-item {
    background: var(--surface-0);
    border: 1px solid var(--surface-border);
    border-radius: var(--radius-md);
    padding: 0.85rem 1rem;
    display: flex; align-items: flex-start; gap: 0.8rem;
    transition: var(--transition);
    position: relative; overflow: hidden;
}
.task-item::before {
    content: '';
    position: absolute; left: 0; top: 0; bottom: 0;
    width: 3px; background: transparent;
    transition: var(--transition);
    border-radius: 3px 0 0 3px;
}
.task-item:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); border-color: rgba(116,24,71,0.15); }
.task-item:hover::before { background: var(--brand-maroon); }
.task-item.priority-high::before   { background: var(--accent-red); }
.task-item.priority-medium::before { background: var(--accent-amber); }
.task-item.priority-low::before    { background: var(--accent-blue); }
.task-item.completed { opacity: 0.55; background: var(--surface-1); }
.task-item.completed .task-title-text { text-decoration: line-through; color: var(--text-muted); }
.task-item.completed::before { background: var(--accent-green) !important; }

/* Checkbox */
.task-check {
    width: 20px; height: 20px; border-radius: 6px;
    border: 2px solid #d1c4d9; background: var(--surface-0);
    cursor: pointer; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    transition: var(--transition); margin-top: 1px;
    -webkit-appearance: none; appearance: none;
}
.task-check:checked { background: var(--accent-green); border-color: var(--accent-green); }
.task-check:checked::after {
    content: '✓'; color: white; font-size: 12px; font-weight: 800; line-height: 1;
}

/* Task Content */
.task-content { flex: 1; min-width: 0; }
.task-title-text {
    font-weight: 700;
    font-size: 0.9rem;
    font-family: 'Nunito', sans-serif;
    color: var(--text-primary);
    line-height: 1.4;
    cursor: pointer;
    transition: color 0.15s;
}
.task-title-text:hover { color: var(--brand-maroon); }
.task-description-text {
    font-size: 0.8rem;
    font-family: 'Nunito', sans-serif;
    color: var(--text-secondary);
    margin-top: 0.35rem;
    line-height: 1.55;
    display: none;
}
.task-description-text.visible { display: block; }
.task-meta { display: flex; flex-wrap: wrap; gap: 0.35rem; margin-top: 0.45rem; align-items: center; }

.task-badge {
    font-size: 0.67rem; font-weight: 700;
    font-family: 'Nunito', sans-serif;
    padding: 2px 8px;
    border-radius: 5px;
    display: inline-flex; align-items: center; gap: 4px;
}
.badge-tag     { background: var(--surface-2); color: var(--text-secondary); border: 1px solid var(--surface-border); }
.badge-recur   { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.badge-overdue { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.badge-deadline { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
.badge-today   { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
.badge-priority-high   { background: #fef2f2; color: #dc2626; }
.badge-priority-medium { background: #fffbeb; color: #d97706; }
.badge-priority-low    { background: #eff6ff; color: #2563eb; }

/* Task Actions */
.task-actions {
    display: flex; align-items: center; gap: 0.15rem;
    flex-shrink: 0; opacity: 0; transition: opacity 0.15s;
}
.task-item:hover .task-actions { opacity: 1; }
.task-action-btn {
    width: 28px; height: 28px; border-radius: 7px;
    border: none; background: transparent;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.72rem; cursor: pointer;
    transition: var(--transition); color: var(--text-muted);
}
.task-action-btn:hover         { background: var(--surface-2); color: var(--text-primary); }
.task-action-btn.danger:hover  { background: #fef2f2; color: var(--accent-red); }
.task-action-btn.archive:hover { background: #fffbeb; color: var(--accent-amber); }
.task-action-btn.edit:hover    { background: var(--brand-maroon-lt); color: var(--brand-maroon); }

/* Sort Dropdown */
.sort-select {
    font-size: 0.78rem; font-weight: 700;
    font-family: 'Nunito', sans-serif;
    padding: 0.28rem 0.7rem;
    border-radius: 50px;
    border: 1px solid var(--surface-border);
    background: var(--surface-1);
    color: var(--text-secondary);
    cursor: pointer; outline: none;
}
.sort-select:focus { border-color: var(--brand-maroon); }

/* ---- HABIT TRACKER ---- */
.habit-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.75rem 1rem;
    border-radius: var(--radius-md);
    background: var(--surface-0);
    border: 1px solid var(--surface-border);
    transition: var(--transition);
    margin-bottom: 0.55rem;
}
.habit-item.done { background: #ecfdf5; border-color: #a7f3d0; }
.habit-toggle-btn {
    width: 34px; height: 34px; border-radius: 50%;
    border: 2px solid var(--surface-border);
    background: var(--surface-1); color: var(--text-muted);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.78rem; transition: var(--transition); flex-shrink: 0;
}
.habit-item.done .habit-toggle-btn {
    background: var(--accent-green); border-color: var(--accent-green);
    color: #fff; box-shadow: 0 2px 8px rgba(16,185,129,0.3);
}
.habit-name {
    font-weight: 700; font-size: 0.875rem;
    font-family: 'Nunito', sans-serif; color: var(--text-primary);
}
.habit-streak {
    font-size: 0.7rem; font-weight: 700;
    font-family: 'Nunito', sans-serif; color: var(--accent-amber);
}
.habit-progress-bar {
    height: 5px; background: var(--surface-2);
    border-radius: 5px; margin: 0.5rem 0 1rem; overflow: hidden;
}
.habit-progress-fill {
    height: 100%; border-radius: 5px;
    background: linear-gradient(90deg, var(--accent-green), #34d399);
    transition: width 0.5s ease;
}

/* ---- STICKY NOTES ---- */
.notes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 1.25rem;
}
.sticky-note {
    padding: 1.25rem; border-radius: var(--radius-md);
    box-shadow: 3px 4px 0px rgba(0,0,0,0.08);
    position: relative; transition: all 0.2s ease;
    min-height: 160px; display: flex; flex-direction: column;
    border: 1px solid rgba(0,0,0,0.06);
}
.sticky-note:hover { transform: rotate(-0.8deg) translateY(-3px); box-shadow: 5px 8px 0px rgba(0,0,0,0.1); }
.sticky-note .note-pin {
    position: absolute; top: -8px; left: 50%; transform: translateX(-50%);
    width: 14px; height: 14px; border-radius: 50%;
    background: rgba(0,0,0,0.22); box-shadow: 0 2px 4px rgba(0,0,0,0.18);
}
.sticky-note .note-actions {
    position: absolute; top: 8px; right: 8px;
    display: flex; gap: 4px; opacity: 0; transition: opacity 0.15s;
}
.sticky-note:hover .note-actions { opacity: 1; }
.note-action-btn {
    width: 24px; height: 24px; border-radius: 5px;
    display: flex; align-items: center; justify-content: center;
    background: transparent; border: none;
    cursor: pointer; font-size: 0.72rem;
    color: rgba(0,0,0,0.4); transition: var(--transition);
}
.note-action-btn:hover { background: rgba(0,0,0,0.1); color: rgba(0,0,0,0.7); }
.sticky-note-title {
    font-family: 'Nunito', sans-serif;
    font-size: 0.95rem; font-weight: 800;
    margin-bottom: 0.5rem; color: rgba(0,0,0,0.8);
}
.sticky-note-content {
    white-space: pre-wrap; font-family: 'Nunito', sans-serif;
    font-size: 0.82rem; color: rgba(0,0,0,0.65);
    flex-grow: 1; line-height: 1.6;
}
.sticky-note-content p { margin-bottom: 0.3rem; }
.sticky-note-content ul, .sticky-note-content ol { padding-left: 1.2rem; margin-bottom: 0.3rem; }
.sticky-note-content strong { font-weight: 800; }
.sticky-note-footer {
    font-size: 0.68rem; font-family: 'Nunito', sans-serif;
    color: rgba(0,0,0,0.35); margin-top: 0.75rem;
    padding-top: 0.6rem; border-top: 1px solid rgba(0,0,0,0.08);
}

/* ---- MODALS ---- */
.modal-cmd .modal-content {
    border-radius: var(--radius-xl); border: none;
    box-shadow: var(--shadow-lg), 0 0 0 1px rgba(116,24,71,0.08);
    overflow: hidden;
    font-family: 'Nunito', sans-serif;
}
.modal-cmd .modal-header {
    background: linear-gradient(135deg, var(--brand-maroon), #9c2456);
    color: #fff; border: none; padding: 1.2rem 1.5rem;
}
.modal-cmd .modal-title {
    font-family: 'Nunito', sans-serif;
    font-weight: 800; font-size: 1rem;
}
.modal-cmd .btn-close { filter: invert(1) brightness(2); }
.modal-cmd .modal-body { padding: 1.35rem 1.5rem; background: var(--surface-0); }
.modal-cmd .modal-footer {
    border-top: 1px solid var(--surface-border);
    background: var(--surface-1); padding: 0.9rem 1.5rem;
}
.modal-cmd label {
    font-size: 0.75rem; font-weight: 700;
    color: var(--text-secondary); font-family: 'Nunito', sans-serif;
}
.modal-cmd .form-control, .modal-cmd .form-select {
    font-family: 'Nunito', sans-serif; font-size: 0.875rem;
    border-color: var(--surface-border);
    border-radius: var(--radius-sm);
}
.modal-cmd .form-control:focus, .modal-cmd .form-select:focus {
    border-color: var(--brand-maroon);
    box-shadow: 0 0 0 3px rgba(116,24,71,0.1);
}

/* Quick-Add Task Input */
.task-quick-input {
    font-family: 'Nunito', sans-serif;
    font-size: 1.15rem; font-weight: 700;
    border: none; border-bottom: 2px solid var(--surface-border);
    border-radius: 0; padding: 1rem 1.5rem;
    background: transparent; color: var(--text-primary);
    transition: var(--transition); width: 100%;
}
.task-quick-input:focus {
    outline: none; border-bottom-color: var(--brand-maroon); box-shadow: none;
}
.quick-toolbar {
    padding: 0.8rem 1.5rem;
    background: var(--surface-1);
    display: flex; gap: 0.65rem; align-items: center;
    flex-wrap: wrap; border-bottom: 1px solid var(--surface-border);
}
.quick-toolbar label {
    font-size: 0.72rem; font-weight: 700; color: var(--text-muted); white-space: nowrap;
}
.quick-toolbar select, .quick-toolbar input[type="datetime-local"] {
    font-size: 0.8rem; font-weight: 600; font-family: 'Nunito', sans-serif;
    border: 1px solid var(--surface-border); border-radius: var(--radius-sm);
    padding: 0.3rem 0.65rem;
    background: var(--surface-0); color: var(--text-secondary);
    outline: none; transition: var(--transition);
}
.quick-toolbar select:focus, .quick-toolbar input[type="datetime-local"]:focus {
    border-color: var(--brand-maroon); color: var(--text-primary);
}
.date-quick-btn {
    font-size: 0.72rem; font-weight: 700; font-family: 'Nunito', sans-serif;
    padding: 4px 10px; border-radius: 5px;
    border: 1px solid var(--surface-border);
    background: var(--surface-0); color: var(--text-secondary);
    cursor: pointer; transition: var(--transition);
}
.date-quick-btn:hover, .date-quick-btn.active {
    background: var(--brand-maroon); border-color: var(--brand-maroon); color: #fff;
}
.task-desc-textarea {
    width: 100%; border: 1px solid var(--surface-border);
    border-radius: var(--radius-sm); padding: 0.65rem 1rem;
    font-size: 0.85rem; font-family: 'Nunito', sans-serif;
    color: var(--text-secondary); background: var(--surface-1);
    resize: vertical; min-height: 75px; outline: none;
    transition: var(--transition);
}
.task-desc-textarea:focus { border-color: var(--brand-maroon); background: #fff; }

/* Buttons */
.btn-brand {
    background: var(--brand-maroon); color: #fff; border: none;
    border-radius: var(--radius-sm); font-weight: 700; font-family: 'Nunito', sans-serif;
    padding: 0.5rem 1.2rem; font-size: 0.875rem; cursor: pointer;
    transition: var(--transition); display: inline-flex; align-items: center; gap: 0.45rem;
}
.btn-brand:hover { background: var(--brand-maroon-dk); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(116,24,71,0.3); }
.btn-ghost {
    background: var(--surface-2); color: var(--text-secondary); border: none;
    border-radius: var(--radius-sm); font-weight: 700; font-family: 'Nunito', sans-serif;
    padding: 0.5rem 1rem; font-size: 0.85rem; cursor: pointer;
    transition: var(--transition); display: inline-flex; align-items: center; gap: 0.45rem;
}
.btn-ghost:hover { background: var(--surface-border); color: var(--text-primary); }
.header-btn {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff; border-radius: var(--radius-sm);
    padding: 0.45rem 1rem; font-size: 0.8rem; font-weight: 700;
    font-family: 'Nunito', sans-serif;
    cursor: pointer; transition: var(--transition);
    display: inline-flex; align-items: center; gap: 0.4rem;
    backdrop-filter: blur(8px);
}
.header-btn:hover { background: rgba(255,255,255,0.25); color: #fff; }

/* Color picker */
.color-picker-group { display: flex; gap: 8px; align-items: center; }
.color-circle {
    width: 26px; height: 26px; border-radius: 50%; cursor: pointer;
    border: 2px solid transparent; transition: transform 0.15s, box-shadow 0.15s;
}
.color-circle:hover { transform: scale(1.15); }
.color-radio:checked + .color-circle {
    border-color: #374151; box-shadow: 0 0 0 2px #fff inset; transform: scale(1.15);
}

/* Empty state */
.empty-state { text-align: center; padding: 2.5rem 1rem; color: var(--text-muted); }
.empty-state i { font-size: 2.5rem; opacity: 0.2; margin-bottom: 0.75rem; display: block; }
.empty-state p { font-size: 0.875rem; font-family: 'Nunito', sans-serif; }

/* Task panel scrollable */
.task-panel-body {
    max-height: calc(100vh - 310px);
    overflow-y: auto;
    padding: 1rem 1.25rem;
    scrollbar-width: thin;
    scrollbar-color: var(--surface-border) transparent;
}
.task-panel-body::-webkit-scrollbar { width: 4px; }
.task-panel-body::-webkit-scrollbar-thumb { background: var(--surface-border); border-radius: 4px; }

/* ---- POMODORO ---- */
#pomodoro-widget {
    position: fixed; bottom: 24px; right: 24px;
    width: 265px; border-radius: var(--radius-xl); overflow: hidden;
    z-index: 1050;
    box-shadow: var(--shadow-lg), 0 0 0 1px rgba(116,24,71,0.12);
    transform: translateY(130%) scale(0.95); opacity: 0;
    transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}
#pomodoro-widget.show { transform: translateY(0) scale(1); opacity: 1; }
.pomo-header {
    background: var(--brand-maroon); color: #fff;
    padding: 0.85rem 1.2rem;
    display: flex; justify-content: space-between; align-items: center;
    font-weight: 800; font-size: 0.875rem; font-family: 'Nunito', sans-serif;
}
.pomo-body { padding: 1.4rem; text-align: center; background: #fff; }
.pomo-time {
    font-family: 'Montserrat', sans-serif;
    font-size: 3rem; color: var(--text-primary);
    line-height: 1; letter-spacing: -2px; margin-bottom: 0.3rem;
}
.pomo-label {
    font-size: 0.78rem; color: var(--text-muted);
    font-weight: 600; font-family: 'Nunito', sans-serif;
    margin-bottom: 1.2rem;
}

/* CKEditor overrides — pakai Nunito */
.ck.ck-editor__main > .ck-editor__editable {
    background-color: transparent !important;
    border: none !important;
    box-shadow: none !important;
    color: #111827 !important;
    min-height: 120px;
    padding: 0 !important;
}
.ck.ck-toolbar {
    background-color: rgba(255, 255, 255, 0.4) !important;
    border: none !important;
    border-bottom: 1px dashed rgba(0,0,0,0.1) !important;
    border-radius: 8px !important;
    margin-bottom: 10px;
}
.ck.ck-editor__editable.ck-focused { outline: none !important; border: none !important; }

/* ============================================================
   KANBAN BOARD
   ============================================================ */
 
/* Wrapper horizontal scroll */
.kanban-board-wrap {
    padding: 1rem 1.25rem 1.25rem;
    overflow-x: auto;
    /* Scrollbar tipis */
    scrollbar-width: thin;
    scrollbar-color: var(--surface-border) transparent;
}
.kanban-board-wrap::-webkit-scrollbar { height: 5px; }
.kanban-board-wrap::-webkit-scrollbar-thumb { background: var(--surface-border); border-radius: 5px; }
 
/* Baris kolom */
.kanban-row {
    display: flex;
    gap: 1rem;
    min-width: 760px;                   /* prevent collapse on narrow screens */
    height: calc(100vh - 320px);
    min-height: 400px;
}
 
/* Satu kolom */
.kanban-col {
    flex: 1;
    display: flex;
    flex-direction: column;
    border-radius: var(--radius-md);
    overflow: hidden;
    border: 1px solid var(--surface-border);
    background: var(--surface-1);
    min-width: 220px;
}
 
/* Header kolom */
.kanban-col-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--surface-border);
    background: var(--surface-0);
    flex-shrink: 0;
}
.kanban-col-title {
    font-family: 'Nunito', sans-serif;
    font-size: 0.8rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 0.45rem;
    color: var(--text-primary);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.kanban-col-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
/* BUG FIX #2: Ganti spinner yang berputar terus dengan dot statis */
.dot-pending   { background: #94a3b8; }
.dot-progress  { background: var(--accent-blue); }
.dot-completed { background: var(--accent-green); }
 
.kanban-col-count {
    font-family: 'Nunito', sans-serif;
    font-size: 0.7rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 20px;
    background: var(--surface-2);
    color: var(--text-secondary);
    border: 1px solid var(--surface-border);
    min-width: 22px;
    text-align: center;
    transition: var(--transition);
}
/* Warna count per kolom */
.kanban-col[data-col="pending"]    .kanban-col-count { background:#f1f5f9; color:#64748b; }
.kanban-col[data-col="in_progress"] .kanban-col-count { background:#eff6ff; color:#2563eb; border-color:#bfdbfe; }
.kanban-col[data-col="completed"]  .kanban-col-count { background:#ecfdf5; color:#059669; border-color:#a7f3d0; }
 
/* List area (scrollable) */
.kanban-list {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 0.75rem 0.65rem;
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
    /* Scrollbar tipis */
    scrollbar-width: thin;
    scrollbar-color: var(--surface-border) transparent;
}
.kanban-list::-webkit-scrollbar { width: 4px; }
.kanban-list::-webkit-scrollbar-thumb { background: var(--surface-border); border-radius: 4px; }
 
/* Drop zone highlight */
.kanban-list.drag-over {
    background: rgba(116, 24, 71, 0.04);
    border-radius: var(--radius-sm);
    outline: 2px dashed rgba(116, 24, 71, 0.2);
    outline-offset: -2px;
}
 
/* Empty placeholder */
.kanban-empty {
    text-align: center;
    padding: 2rem 1rem;
    color: var(--text-muted);
    font-size: 0.78rem;
    font-family: 'Nunito', sans-serif;
    font-weight: 600;
    border: 2px dashed var(--surface-border);
    border-radius: var(--radius-md);
    margin: 0.25rem 0;
}
.kanban-empty i { font-size: 1.5rem; opacity: 0.2; display: block; margin-bottom: 0.5rem; }
 
/* ============================================================
   KANBAN CARD
   ============================================================ */
 
.kanban-card {
    background: var(--surface-0);
    border-radius: var(--radius-md);
    border: 1px solid var(--surface-border);
    overflow: hidden;
    cursor: grab;
    transition: box-shadow 0.18s ease, transform 0.18s ease;
    user-select: none;
    position: relative;
}
.kanban-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
    border-color: rgba(116,24,71,0.18);
}
.kanban-card:active { cursor: grabbing; }
 
/* BUG FIX #3: Priority bar di atas kartu (bukan border-left inline) — 
   tidak akan bentrok dengan .task-item::before */
.kc-priority-bar {
    height: 3px;
    width: 100%;
    flex-shrink: 0;
}
 
/* Ghost saat di-drag */
.kanban-card.sortable-ghost {
    opacity: 0.45;
    background: var(--surface-2);
    box-shadow: none;
    transform: none;
}
/* Kartu yang sedang dibawa */
.kanban-card.sortable-drag {
    opacity: 1 !important;
    box-shadow: var(--shadow-lg) !important;
    transform: rotate(1.5deg) scale(1.02) !important;
    border-color: rgba(116,24,71,0.3) !important;
}
 
/* Body kartu */
.kc-body {
    padding: 0.75rem 0.85rem 0.7rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
 
/* Badges baris atas */
.kc-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
}
.kc-badge {
    font-size: 0.62rem;
    font-weight: 800;
    font-family: 'Nunito', sans-serif;
    padding: 1px 7px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    letter-spacing: 0.01em;
    border: 1px solid transparent;
}
.kc-badge-tag    { background: var(--surface-2); color: var(--text-secondary); border-color: var(--surface-border); }
.kc-badge-green  { background: #ecfdf5; color: #059669; border-color: #a7f3d0; }
.kc-badge-indigo { background: #e0e7ff; color: #4338ca; border-color: #c7d2fe; }
.kc-badge-rose   { background: #fce7f3; color: #be185d; border-color: #fbcfe8; }
 
/* Judul */
.kc-title {
    font-family: 'Nunito', sans-serif;
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.35;
    cursor: pointer;
    transition: color 0.15s;
}
.kc-title:hover { color: var(--brand-maroon); }
 
/* Deskripsi singkat */
.kc-desc {
    font-family: 'Nunito', sans-serif;
    font-size: 0.75rem;
    color: var(--text-muted);
    line-height: 1.5;
}
 
/* Sub-task progress */
.kc-subtask-wrap {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.kc-subtask-bar {
    flex: 1;
    height: 4px;
    background: var(--surface-2);
    border-radius: 4px;
    overflow: hidden;
}
.kc-subtask-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--accent-green), #34d399);
    border-radius: 4px;
    transition: width 0.4s ease;
}
.kc-subtask-label {
    font-family: 'Nunito', sans-serif;
    font-size: 0.65rem;
    font-weight: 800;
    color: var(--text-muted);
    white-space: nowrap;
    flex-shrink: 0;
}
 
/* Footer */
.kc-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 0.45rem;
    border-top: 1px solid var(--surface-border);
    margin-top: 0.1rem;
}
.kc-footer-left, .kc-footer-right {
    display: flex;
    align-items: center;
    gap: 0.45rem;
}
.kc-deadline {
    font-family: 'Nunito', sans-serif;
    font-size: 0.65rem;
    font-weight: 700;
    color: var(--text-muted);
    display: flex; align-items: center; gap: 3px;
}
.kc-overdue  { color: #dc2626 !important; }
.kc-today    { color: #d97706 !important; }
.kc-no-deadline { font-family: 'Nunito', sans-serif; font-size: 0.62rem; color: var(--text-muted); opacity: 0.5; display: flex; align-items: center; gap: 3px; }
.kc-meta-icon {
    font-size: 0.65rem;
    font-family: 'Nunito', sans-serif;
    color: var(--text-muted);
    display: flex; align-items: center; gap: 2px;
}
.kc-priority-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}
 
/* Toggle Button custom */
.btn-toggle-kanban {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-family: 'Nunito', sans-serif;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 0.28rem 0.85rem;
    border-radius: 50px;
    border: 1px solid var(--surface-border);
    background: var(--surface-1);
    color: var(--text-secondary);
    cursor: pointer;
    transition: var(--transition);
}
.btn-toggle-kanban:hover,
.btn-toggle-kanban.active {
    background: var(--brand-maroon);
    border-color: var(--brand-maroon);
    color: #fff;
    box-shadow: 0 2px 8px rgba(116,24,71,0.25);
}

/* ---- Entrance animation ---- */
@keyframes fadeSlideUp {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}
.task-item { animation: fadeSlideUp 0.22s ease both; }
.task-item:nth-child(1) { animation-delay: 0.02s; }
.task-item:nth-child(2) { animation-delay: 0.05s; }
.task-item:nth-child(3) { animation-delay: 0.08s; }
.task-item:nth-child(4) { animation-delay: 0.11s; }
.task-item:nth-child(5) { animation-delay: 0.14s; }
</style>
@endsection

@section('content')
<div class="container-fluid p-0">

    {{-- ============================================================
         HEADER
    ============================================================ --}}
    <div class="cmd-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h2 class="cmd-header-title">👋🏻 Halo, {{ Auth::user()->name }}</h2>
            <p class="cmd-header-subtitle mb-0">
                <i class="fas fa-calendar-alt me-1"></i>
                {{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}
            </p>
        </div>

        {{-- BUG FIX #1: Stats pill dengan warna berbeda tiap jenis --}}
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <div class="stat-pill">
                <i class="fas fa-list" style="font-size:0.65rem;opacity:0.75;"></i>
                <span class="stat-num">{{ $statsTotal }}</span> Total
            </div>
            <div class="stat-pill warning">
                <i class="fas fa-spinner" style="font-size:0.65rem;"></i>
                <span class="stat-num">{{ $statsPending }}</span> Aktif
            </div>
            <div class="stat-pill success">
                <i class="fas fa-check" style="font-size:0.65rem;"></i>
                <span class="stat-num">{{ $statsCompleted }}</span> Selesai
            </div>
            <div class="stat-pill" style="background: rgba(59, 130, 246, 0.2); border-color: rgba(59, 130, 246, 0.4);">
                <i class="fas fa-paper-plane" style="font-size:0.65rem;"></i>
                <span class="stat-num">{{ $statsDelegated }}</span> Delegasi
            </div>
            @if($statsOverdue > 0)
            <div class="stat-pill danger">
                <i class="fas fa-exclamation-triangle" style="font-size:0.65rem;"></i>
                <span class="stat-num">{{ $statsOverdue }}</span> Terlambat
            </div>
            @endif

            <div class="d-flex gap-2 ms-2">
                <button onclick="openTaskModal()" class="header-btn">
                    <i class="fas fa-plus"></i> Tambah Tugas
                </button>
                <a href="{{ route('admin.productivity.routine.index') }}" class="header-btn" style="text-decoration: none;">
                    <i class="fas fa-clipboard"></i> Rutinan
                </a>
                <button onclick="togglePomodoro()" class="header-btn">
                    <i class="fas fa-bolt" style="color:#fbbf24;"></i> Focus
                </button>
                <button class="header-btn" data-bs-toggle="modal" data-bs-target="#settingsModal">
                    <i class="fas fa-cog"></i>
                </button>
                <button class="header-btn" data-bs-toggle="modal" data-bs-target="#settingsModal">
                    <i class="fas fa-cog"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MAIN: Tasks (8) + Habits (4)
    ============================================================ --}}
    <div class="row g-4 mb-4">

        {{-- ---- TASKS PANEL ---- --}}
        <div class="col-xl-8 col-lg-7">
            <div class="panel-card">
                <div class="panel-header">
                    <h2 class="panel-title">
                        <span class="panel-title-icon" style="background:var(--brand-maroon-lt);">
                            <i class="fas fa-check-square" style="color:var(--brand-maroon);"></i>
                        </span>
                        Tugas Saya
                        <span style="background:var(--surface-2);color:var(--text-secondary);font-size:0.72rem;font-weight:700;border-radius:20px;padding:2px 9px;font-family:'Nunito',sans-serif;">
                            {{ $tasks->count() }}
                        </span>
                    </h2>
                    <select class="sort-select" id="sortSelect" onchange="applyFilters()">
                        <option value="deadline" {{ $sort=='deadline' ? 'selected':'' }}>↑ Deadline</option>
                        <option value="priority" {{ $sort=='priority' ? 'selected':'' }}>↑ Prioritas</option>
                        <option value="created"  {{ $sort=='created'  ? 'selected':'' }}>↑ Terbaru</option>
                    </select>
                </div>

                {{-- Filter Bar --}}
                <div class="filter-bar">
                    <a href="#" onclick="setFilter('active',event)"    class="filter-tab {{ $filter=='active'    ? 'active':'' }}"><i class="fas fa-circle-notch"></i> Aktif</a>
                    <a href="#" onclick="setFilter('completed',event)" class="filter-tab {{ $filter=='completed' ? 'active':'' }}"><i class="fas fa-check-circle"></i> Selesai</a>
                    <a href="#" onclick="setFilter('all',event)"       class="filter-tab {{ $filter=='all'       ? 'active':'' }}"><i class="fas fa-list"></i> Semua</a>
                    <a href="#" onclick="setFilter('delegated',event)" 
                    class="filter-tab {{ $filter=='delegated' ? 'active':'' }}" 
                    style="{{ $filter=='delegated' ? 'background:#e0e7ff;border-color:#4338ca;color:#4338ca;' : '' }}">
                        <i class="fas fa-paper-plane"></i> 
                        Delegasi
                        @if($statsDelegated > 0)
                            <span class="ms-1 px-2 py-0.5 rounded-pill" 
                                style="font-size: 0.65rem; background: {{ $filter=='delegated' ? '#fff' : 'var(--brand-maroon)' }}; color: {{ $filter=='delegated' ? '#4338ca' : '#fff' }}; font-weight: 800;">
                                {{ $statsDelegated }}
                            </span>
                        @endif
                    </a>
                    <a href="#" onclick="setFilter('archived',event)"  class="filter-tab {{ $filter=='archived'  ? 'active':'' }}"><i class="fas fa-archive"></i> Arsip</a>

                    @if($allTags->count() > 0)
                    <select class="sort-select" id="tagFilter" onchange="applyFilters()" style="border-radius:6px;">
                        <option value="">Semua Tag</option>
                        @foreach($allTags as $t)
                            <option value="{{ $t }}" {{ $tag==$t ? 'selected':'' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                    @endif

                    <button class="btn-toggle-kanban" id="btnViewKanban">
                        <i class="fas fa-columns"></i> Kanban
                    </button>
                    <button class="btn-toggle-kanban ms-1" id="btnViewCalendar">
                        <i class="far fa-calendar-alt"></i> Kalender
                    </button>

                    <div class="search-input-wrap ms-2">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Cari tugas..."
                               value="{{ $search }}"
                               onkeydown="if(event.key==='Enter') applyFilters()">
                    </div>
                </div>

                {{-- Task List --}}
                <div class="task-panel-body" id="task-container">
                    @forelse($tasks as $task)
                        @php
                            $isOverdue = $task->deadline_at && \Carbon\Carbon::parse($task->deadline_at)->isPast() && $task->status != 'completed';
                            $isToday   = $task->deadline_at && \Carbon\Carbon::parse($task->deadline_at)->isToday();
                            $dl = $task->deadline_at ? \Carbon\Carbon::parse($task->deadline_at) : null;
                            $deadlineLabel = '';
                            if ($dl) {
                                if ($isOverdue) $deadlineLabel = 'Terlambat ' . $dl->diffForHumans();
                                elseif ($isToday) $deadlineLabel = 'Hari ini, ' . $dl->format('H:i');
                                else $deadlineLabel = $dl->format('d M, H:i');
                            }
                        @endphp
                        <div class="task-item priority-{{ $task->priority }} {{ $task->status=='completed' ? 'completed':'' }}"
                             id="task-{{ $task->id }}">

                            @if(!$task->is_archived)
                            <input type="checkbox" class="task-check task-checkbox"
                                   data-id="{{ $task->id }}"
                                   {{ $task->status=='completed' ? 'checked':'' }}>
                            @else
                            <div style="width:20px;flex-shrink:0;"></div>
                            @endif

                            <div class="task-content">
                                <div class="task-title-text btn-view-task" 
                                    style="cursor: pointer;"
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
                                @if($task->description)
                                <div class="task-description-text" id="desc-{{ $task->id }}">{{ $task->description }}</div>
                                @endif
                                <div class="task-meta">
                                    @if($task->tag)
                                        <span class="task-badge badge-tag"><i class="fas fa-tag"></i> {{ $task->tag }}</span>
                                    @endif
                                    @if($task->recurrence && $task->recurrence != 'none')
                                        <span class="task-badge badge-recur">
                                            <i class="fas fa-sync-alt"></i>
                                            {{ ['daily'=>'Harian','weekly'=>'Mingguan','monthly'=>'Bulanan'][$task->recurrence] ?? $task->recurrence }}
                                        </span>
                                    @endif
                                    @if($dl)
                                        <span class="task-badge {{ $isOverdue ? 'badge-overdue' : ($isToday ? 'badge-today' : 'badge-deadline') }}">
                                            <i class="far fa-clock"></i> {{ $deadlineLabel }}
                                        </span>
                                    @endif
                                    <span class="task-badge badge-priority-{{ $task->priority }}">
                                        @if($task->priority=='high') <i class="fas fa-arrow-up"></i> High
                                        @elseif($task->priority=='medium') <i class="fas fa-minus"></i> Med
                                        @else <i class="fas fa-arrow-down"></i> Low @endif
                                    </span>
                                    {{-- Indikator Sub-Task --}}
                                    @if($task->subTasks->count() > 0)
                                        @php
                                            $completedSub = $task->subTasks->where('is_completed', true)->count();
                                            $totalSub = $task->subTasks->count();
                                        @endphp
                                        <span class="task-badge" style="background:#f3f4f6; color:#4b5563; border:1px solid #d1d5db;" title="Checklist Sub-task">
                                            <i class="fas fa-tasks"></i> {{ $completedSub }}/{{ $totalSub }}
                                        </span>
                                    @endif

                                    {{-- Indikator Attachment --}}
                                    @if($task->attachments->count() > 0)
                                        <span class="task-badge" style="background:#f3f4f6; color:#4b5563; border:1px solid #d1d5db;" title="Ada Lampiran File">
                                            <i class="fas fa-paperclip"></i> {{ $task->attachments->count() }}
                                        </span>
                                    @endif
                                </div>
                                @if($task->assigned_by && $task->assigned_by != Auth::id() && $task->user_id == Auth::id())
                                    {{-- Skenario 1: Ini tugas masuk (Saya dikasih tugas sama orang) --}}
                                    <span class="task-badge mt-2" style="background:#e0e7ff; color:#4338ca; border:1px solid #c7d2fe;" title="Didelegasikan oleh rekan Anda">
                                        <i class="fas fa-hand-holding-medical"></i> Dari: {{ $task->assigner->name ?? 'Admin' }}
                                    </span>
                                @elseif($task->assigned_by == Auth::id() && $task->user_id != Auth::id())
                                    {{-- Skenario 2: Ini tugas keluar (Saya ngasih tugas ke orang) --}}
                                    <span class="task-badge mt-2" style="background:#fce7f3; color:#be185d; border:1px solid #fbcfe8;" title="Sedang dikerjakan oleh rekan Anda">
                                        <i class="fas fa-user-tag"></i> Kepada: {{ $task->user->name ?? 'Pegawai' }}
                                    </span>
                                @endif
                            </div>

                            <div class="task-actions">
                                @if(!$task->is_archived)
                                    @if(is_null($task->assigned_by) || $task->assigned_by == Auth::id())
                                    <button class="task-action-btn edit btn-edit-task"
                                            data-id="{{ $task->id }}"
                                            data-title="{{ $task->title }}"
                                            data-desc="{{ $task->description }}"
                                            data-tag="{{ $task->tag }}"
                                            data-priority="{{ $task->priority }}"
                                            data-recurrence="{{ $task->recurrence }}"
                                            data-deadline="{{ $dl ? $dl->format('Y-m-d\TH:i') : '' }}"
                                            data-assignee="{{ $task->user_id }}"
                                            data-subtasks="{{ $task->subTasks->toJson() }}"
                                            data-attachments="{{ $task->attachments->toJson() }}"
                                            title="Edit">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    @endif
                                    <button class="task-action-btn archive btn-archive-task" data-id="{{ $task->id }}" title="Arsipkan">
                                        <i class="fas fa-archive"></i>
                                    </button>
                                @else
                                    <button class="task-action-btn edit btn-unarchive-task" data-id="{{ $task->id }}" title="Pulihkan">
                                        <i class="fas fa-inbox"></i>
                                    </button>
                                @endif
                                @if(is_null($task->assigned_by) || $task->assigned_by == Auth::id())
                                <button class="task-action-btn danger btn-delete-task" data-id="{{ $task->id }}" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="fas fa-clipboard-check"></i>
                            <p>
                                @if($filter==='archived') Belum ada tugas yang diarsipkan.
                                @elseif($filter==='completed') Belum ada tugas selesai.
                                @else Tidak ada tugas aktif. Waktunya bersantai! ✨
                                @endif
                            </p>
                        </div>
                    @endforelse
                </div>
                <div class="kanban-board-wrap d-none" id="kanban-container">
                    <div class="kanban-row">
                
                        {{-- ── Kolom PENDING ── --}}
                        <div class="kanban-col" data-col="pending">
                            <div class="kanban-col-header">
                                <div class="kanban-col-title">
                                    <span class="kanban-col-dot dot-pending"></span>
                                    Pending
                                </div>
                                {{-- BUG FIX #4: Badge count pakai ID agar bisa diupdate JS --}}
                                <span class="kanban-col-count" id="kc-count-pending">
                                    {{ $tasks->where('status','pending')->count() }}
                                </span>
                            </div>
                            <div class="kanban-list" data-status="pending" id="kanban-list-pending">
                                @forelse($tasks->where('status','pending') as $task)
                                    @include('admin.productivity.partials.kanban-card', ['task' => $task])
                                @empty
                                    <div class="kanban-empty"><i class="far fa-clipboard"></i> Tidak ada tugas pending</div>
                                @endforelse
                            </div>
                        </div>
                
                        {{-- ── Kolom IN PROGRESS ── --}}
                        <div class="kanban-col" data-col="in_progress">
                            <div class="kanban-col-header">
                                <div class="kanban-col-title">
                                    <span class="kanban-col-dot dot-progress"></span>
                                    In Progress
                                </div>
                                <span class="kanban-col-count" id="kc-count-in_progress">
                                    {{ $tasks->where('status','in_progress')->count() }}
                                </span>
                            </div>
                            <div class="kanban-list" data-status="in_progress" id="kanban-list-in_progress">
                                @forelse($tasks->where('status','in_progress') as $task)
                                    @include('admin.productivity.partials.kanban-card', ['task' => $task])
                                @empty
                                    <div class="kanban-empty"><i class="fas fa-hourglass-half"></i> Tidak ada tugas berjalan</div>
                                @endforelse
                            </div>
                        </div>
                
                        {{-- ── Kolom COMPLETED ── --}}
                        <div class="kanban-col" data-col="completed">
                            <div class="kanban-col-header">
                                <div class="kanban-col-title">
                                    <span class="kanban-col-dot dot-completed"></span>
                                    Selesai
                                </div>
                                <span class="kanban-col-count" id="kc-count-completed">
                                    {{ $tasks->where('status','completed')->count() }}
                                </span>
                            </div>
                            <div class="kanban-list" data-status="completed" id="kanban-list-completed">
                                @forelse($tasks->where('status','completed') as $task)
                                    @include('admin.productivity.partials.kanban-card', ['task' => $task])
                                @empty
                                    <div class="kanban-empty"><i class="fas fa-check-circle"></i> Belum ada tugas selesai</div>
                                @endforelse
                            </div>
                        </div>
                
                    </div>
                </div>
                <div class="d-none" id="calendar-container" style="padding: 1rem 1.25rem; min-height: 60vh; background: var(--surface-0);">
                    <div id="calendar" style="font-family: 'Nunito', sans-serif;"></div>
                </div>
            </div>
        </div>

        {{-- ---- HABIT TRACKER ---- --}}
        <div class="col-xl-4 col-lg-5">
            <div class="panel-card">
                <div class="panel-header">
                    <h2 class="panel-title">
                        <span class="panel-title-icon" style="background:#fffbeb;">
                            <i class="fas fa-fire" style="color:#f59e0b;"></i>
                        </span>
                        Habit Harian
                    </h2>
                    <button class="btn-ghost" style="padding:0.28rem 0.75rem;font-size:0.78rem;"
                            data-bs-toggle="modal" data-bs-target="#habitModal">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </div>
                <div style="padding:1rem 1.25rem;" id="habit-container">
                    @php
                        $habitTotal     = $habits->count();
                        $habitCompleted = $habits->where('is_completed_today', true)->count();
                        $habitProgress  = $habitTotal > 0 ? round(($habitCompleted / $habitTotal) * 100) : 0;
                    @endphp

                    @if($habitTotal > 0)
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span style="font-size:0.73rem;font-weight:700;color:var(--text-secondary);font-family:'Nunito',sans-serif;">Progress Hari Ini</span>
                        {{-- BUG FIX #3: ID untuk update real-time --}}
                        <span id="habit-progress-label" style="font-size:0.73rem;font-weight:700;color:var(--accent-green);font-family:'Nunito',sans-serif;">
                            {{ $habitCompleted }}/{{ $habitTotal }}
                        </span>
                    </div>
                    <div class="habit-progress-bar">
                        <div class="habit-progress-fill" id="habit-progress-fill" style="width:{{ $habitProgress }}%;"></div>
                    </div>
                    @endif

                    @forelse($habits as $habit)
                    <div class="habit-item {{ $habit->is_completed_today ? 'done':'' }}" id="habit-{{ $habit->id }}">
                        <div class="d-flex align-items-center gap-3">
                            <button class="habit-toggle-btn habit-toggle" data-id="{{ $habit->id }}">
                                <i class="{{ $habit->icon ?? 'fas fa-check' }}"></i>
                            </button>
                            <div>
                                <div class="habit-name">{{ $habit->name }}</div>
                                @if(isset($habit->streak) && $habit->streak > 0)
                                    <div class="habit-streak"><i class="fas fa-fire"></i> {{ $habit->streak }} hari beruntun</div>
                                @else
                                    <div class="habit-streak" style="color:var(--text-muted);">Mulai kebiasaan baru</div>
                                @endif
                            </div>
                        </div>
                        <button class="task-action-btn danger btn-delete-habit" data-id="{{ $habit->id }}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    @empty
                    <div class="empty-state">
                        <i class="fas fa-seedling"></i>
                        <p>Belum ada habit. Mulai bangun kebiasaan baik! 🌱</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         STICKY NOTES
    ============================================================ --}}
    <div class="panel-card mb-4" style="height:auto;">
        <div class="panel-header">
            <h2 class="panel-title">
                <span class="panel-title-icon" style="background:#fffbeb;">
                    <i class="far fa-sticky-note" style="color:#f59e0b;"></i>
                </span>
                Brain Dump & Catatan
            </h2>
            <button class="btn-brand" data-bs-toggle="modal" data-bs-target="#noteModal">
                <i class="fas fa-pen"></i> Tulis Catatan
            </button>
        </div>
        <div style="padding:1.5rem;background:var(--surface-1);">
            <div class="notes-grid" id="note-container">
                @forelse($notes as $note)
                    <div class="sticky-note" id="note-{{ $note->id }}" style="background-color:{{ $note->bg_color }};">
                        <div class="note-pin"></div>
                        {{-- BUG FIX #2: Tombol Edit + Hapus --}}
                        <div class="note-actions">
                            <button class="note-action-btn btn-edit-note"
                                    data-id="{{ $note->id }}"
                                    data-title="{{ $note->title }}"
                                    data-content="{{ htmlspecialchars($note->content) }}"
                                    data-color="{{ $note->bg_color }}"
                                    title="Edit Catatan">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="note-action-btn btn-delete-note" data-id="{{ $note->id }}" title="Hapus">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @if($note->title)
                            <div class="sticky-note-title">{{ $note->title }}</div>
                        @endif
                        <div class="sticky-note-content">{!! $note->content !!}</div>
                        <div class="sticky-note-footer">
                            <i class="far fa-clock me-1"></i>{{ $note->created_at->diffForHumans() }}
                        </div>
                    </div>
                @empty
                    <div style="grid-column:1/-1;" class="empty-state">
                        <i class="fas fa-feather-alt"></i>
                        <p>Papan catatan masih kosong. Tuangkan pikiranmu! ✍️</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

{{-- ============================================================
     MODALS
============================================================ --}}

{{-- MODAL: TAMBAH TASK --}}
<div class="modal fade modal-cmd" id="taskModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Tambah Tugas Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAddTask">
                <input type="text" name="title" id="taskTitleInput" class="task-quick-input"
                       required placeholder="Apa yang ingin Anda kerjakan?" autocomplete="off">
                <div class="quick-toolbar">
                    <label>Deadline:</label>
                    <button type="button" class="date-quick-btn active" onclick="setQuickDate('none',this)">Tanpa</button>
                    <button type="button" class="date-quick-btn" onclick="setQuickDate('today',this)">Hari Ini</button>
                    <button type="button" class="date-quick-btn" onclick="setQuickDate('tomorrow',this)">Besok</button>
                    <input type="datetime-local" name="deadline_at" id="taskDeadlineInput" style="width:auto;max-width:195px;">
                </div>
                <div class="quick-toolbar" style="border-top:none;padding-top:0.5rem;">
                    <div class="d-flex gap-3 flex-wrap align-items-center w-100">
                        <div class="d-flex align-items-center gap-2">
                            <label><i class="fas fa-tag"></i> Tag:</label>
                            <select name="tag" id="taskTagSelect" class="form-select form-select-sm" style="min-width:150px;font-family:'Nunito',sans-serif;">
                                <option value="">Tanpa Tag</option>
                                <option value="Pekerjaan">Pekerjaan</option>
                                <option value="Pribadi">Pribadi</option>
                                <option value="Urgent">Urgent</option>
                                <option value="Rapat">Rapat</option>
                                <option value="UTS">UTS</option>
                                <option value="Dari Telegram">Dari Telegram</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label>Prioritas:</label>
                            <select name="priority" class="form-select form-select-sm" style="width:105px;font-family:'Nunito',sans-serif;">
                                <option value="low">🔵 Low</option>
                                <option value="medium" selected>🟡 Medium</option>
                                <option value="high">🔴 High</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label><i class="fas fa-sync-alt" style="color:var(--accent-green);"></i> Ulang:</label>
                            <select name="recurrence" class="form-select form-select-sm" style="width:115px;font-family:'Nunito',sans-serif;">
                                <option value="none" selected>Sekali</option>
                                <option value="daily">Harian</option>
                                <option value="weekly">Mingguan</option>
                                <option value="monthly">Bulanan</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-2 border-start ps-3" style="min-width: 200px;">
                            <label><i class="fas fa-user-friends text-primary"></i> Tugaskan:</label>
                            <select name="assigned_to" id="taskAssigneeSelect" class="form-select form-select-sm w-100" style="font-family:'Nunito',sans-serif;">
                                <option value="{{ Auth::id() }}">Diri Sendiri</option> 
                                @foreach($coworkers as $cw)
                                    <option value="{{ $cw->id }}">{{ $cw->name }}</option> 
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-body" style="padding-top:0.75rem;">
                    <label class="mb-1">Deskripsi (opsional):</label>
                    <textarea name="description" class="task-desc-textarea"
                              placeholder="Catatan tambahan untuk tugas ini..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-brand"><i class="fas fa-paper-plane"></i> Simpan Tugas</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL: EDIT TASK --}}
<div class="modal fade modal-cmd" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#1a1025,#3a1a4a);">
                <h5 class="modal-title"><i class="fas fa-pencil-alt me-2"></i>Edit Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditTask">
                <input type="hidden" id="editTaskId">
                <input type="text" name="title" id="editTaskTitle" class="task-quick-input"
                       required placeholder="Judul tugas...">
                <div class="quick-toolbar">
                    <label>Deadline:</label>
                    <button type="button" class="date-quick-btn" id="editBtnNone" onclick="setEditQuickDate('none',this)">Tanpa</button>
                    <button type="button" class="date-quick-btn" onclick="setEditQuickDate('today',this)">Hari Ini</button>
                    <button type="button" class="date-quick-btn" onclick="setEditQuickDate('tomorrow',this)">Besok</button>
                    <input type="datetime-local" name="deadline_at" id="editTaskDeadline" style="width:auto;max-width:195px;">
                </div>
                <div class="quick-toolbar" style="border-top:none;padding-top:0.5rem;">
                    <div class="d-flex gap-3 flex-wrap align-items-center w-100">
                        <div class="d-flex align-items-center gap-2">
                            <label><i class="fas fa-tag"></i> Tag:</label>
                            <select name="tag" id="editTaskTag" class="form-select form-select-sm" style="min-width:150px;font-family:'Nunito',sans-serif;">
                                <option value="">Tanpa Tag</option>
                                <option value="Pekerjaan">Pekerjaan</option>
                                <option value="Pribadi">Pribadi</option>
                                <option value="Urgent">Urgent</option>
                                <option value="Rapat">Rapat</option>
                                <option value="UTS">UTS</option>
                                <option value="Dari Telegram">Dari Telegram</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label>Prioritas:</label>
                            <select name="priority" id="editTaskPriority" class="form-select form-select-sm" style="width:105px;font-family:'Nunito',sans-serif;">
                                <option value="low">🔵 Low</option>
                                <option value="medium">🟡 Medium</option>
                                <option value="high">🔴 High</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label><i class="fas fa-sync-alt" style="color:var(--accent-green);"></i> Ulang:</label>
                            <select name="recurrence" id="editTaskRecurrence" class="form-select form-select-sm" style="width:115px;font-family:'Nunito',sans-serif;">
                                <option value="none">Sekali</option>
                                <option value="daily">Harian</option>
                                <option value="weekly">Mingguan</option>
                                <option value="monthly">Bulanan</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-2 border-start ps-3">
                            <label><i class="fas fa-user-friends text-primary"></i> Tugaskan:</label>
                            <select name="assigned_to" id="editTaskAssignee" class="form-select form-select-sm" style="width:140px; font-family:'Nunito',sans-serif;">
                                <option value="">Diri Sendiri</option>
                                @foreach($coworkers as $cw)
                                    <option value="{{ $cw->id }}">{{ $cw->name }}</option>
                                @endforeach
                            </select>
                        </div>    
                    </div>
                </div>
                <div class="modal-body" style="padding-top:0.75rem;">
                    <label class="mb-1">Deskripsi:</label>
                    <textarea name="description" id="editTaskDesc" class="task-desc-textarea mb-3"
                            placeholder="Catatan tambahan..."></textarea>
                    
                    <div class="row border-top pt-3 mt-2">
                        {{-- KOLOM SUB-TASK --}}
                        <div class="col-md-6 border-end">
                            <label class="mb-2 text-primary"><i class="fas fa-tasks"></i> Sub-Task (Checklist)</label>
                            <div class="d-flex gap-2 mb-2">
                                <input type="text" id="newSubTaskTitle" class="form-control form-control-sm" placeholder="Tambah item baru..." style="font-family:'Nunito',sans-serif;">
                                <button type="button" class="btn btn-sm btn-brand" id="btnAddSubTask"><i class="fas fa-plus"></i></button>
                            </div>
                            <div id="subTaskList" class="d-flex flex-column gap-1" style="max-height: 150px; overflow-y: auto;">
                                </div>
                        </div>

                        {{-- KOLOM LAMPIRAN --}}
                        <div class="col-md-6">
                            <label class="mb-2 text-primary"><i class="fas fa-paperclip"></i> Lampiran File</label>
                            <div class="d-flex gap-2 mb-2">
                                <input type="file" id="newAttachmentFile" class="form-control form-control-sm" style="font-family:'Nunito',sans-serif;">
                                <button type="button" class="btn btn-sm btn-brand" id="btnUploadAttachment"><i class="fas fa-upload"></i></button>
                            </div>
                            <div id="attachmentList" class="d-flex flex-column gap-1" style="max-height: 150px; overflow-y: auto;">
                                </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-brand"><i class="fas fa-save"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade modal-cmd" id="viewTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #4b5563, #1f2937);">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i> Detail Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <h3 id="viewTaskTitle" class="fw-bold mb-1" style="font-family:'Montserrat',sans-serif; color:var(--text-primary);"></h3>
                    <div id="viewTaskMeta" class="d-flex flex-wrap gap-2"></div>
                </div>

                <div class="mb-4">
                    <label class="text-muted fw-bold small text-uppercase mb-2 d-block">Deskripsi</label>
                    <div id="viewTaskDesc" class="p-3 rounded bg-light border" style="font-family:'Nunito',sans-serif; font-size:0.9rem; min-height:60px; white-space: pre-wrap;"></div>
                </div>

                <div class="row">
                    {{-- Sub-Task Section --}}
                    <div class="col-md-6 border-end">
                        <label class="text-muted fw-bold small text-uppercase mb-2 d-block">Checklist Progres</label>
                        <div id="viewSubTaskList" class="d-flex flex-column gap-2"></div>
                    </div>

                    {{-- Attachment Section --}}
                    <div class="col-md-6">
                        <label class="text-muted fw-bold small text-uppercase mb-2 d-block">Lampiran File</label>
                        <div id="viewAttachmentList" class="d-flex flex-column gap-2"></div>
                    </div>
                </div>
                <hr class="my-4 text-muted">
                {{-- Comment Section --}}
                <div class="mb-2">
                    <label class="text-muted fw-bold small text-uppercase mb-2 d-block"><i class="far fa-comments"></i> Diskusi / Catatan</label>
                    <div id="viewCommentList" class="d-flex flex-column gap-3 mb-3" style="max-height: 250px; overflow-y: auto;">
                        </div>

                    {{-- Form Tambah Komentar --}}
                    <div class="d-flex gap-2">
                        <input type="text" id="newCommentText" class="form-control form-control-sm" placeholder="Tulis komentar/laporan..." style="font-family:'Nunito',sans-serif;">
                        <button type="button" class="btn btn-sm btn-brand" id="btnSubmitComment"><i class="fas fa-paper-plane"></i> Kirim</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn-ghost" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: TAMBAH HABIT --}}
<div class="modal fade modal-cmd" id="habitModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#059669,#10b981);">
                <h5 class="modal-title"><i class="fas fa-seedling me-2"></i>Tambah Habit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAddHabit">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="mb-1">Nama Habit</label>
                        <input type="text" name="name" class="form-control" required
                               placeholder="contoh: Olahraga 30 menit"
                               style="font-family:'Nunito',sans-serif;">
                    </div>
                    <div>
                        <label class="mb-1">Ikon</label>
                        <select name="icon" class="form-select" style="font-family:'Nunito',sans-serif;">
                            <option value="fas fa-dumbbell">💪 Olahraga</option>
                            <option value="fas fa-book-open">📖 Membaca</option>
                            <option value="fas fa-glass-water">💧 Minum Air</option>
                            <option value="fas fa-praying-hands">🧘 Meditasi</option>
                            <option value="fas fa-pen-nib">✍️ Menulis</option>
                            <option value="fas fa-apple-alt">🍎 Makan Sehat</option>
                            <option value="fas fa-moon">🌙 Tidur Cukup</option>
                            <option value="fas fa-check-circle" selected>✅ Umum</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-brand" style="background:var(--accent-green);">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL: TULIS NOTE (BUG FIX #2 — CKEditor) --}}
<div class="modal fade modal-cmd" id="noteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#d97706,#f59e0b);">
                <h5 class="modal-title"><i class="fas fa-sticky-note me-2"></i>Tulis Catatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAddNote">
                <div class="modal-body" id="noteModalContainer" style="background:#fef08a;transition:background 0.3s;">
                    <div class="mb-3">
                        <input type="text" name="title" id="noteTitle" class="form-control border-0 shadow-none"
                               placeholder="Judul catatan (opsional)"
                               style="background:transparent;font-weight:800;font-size:1rem;font-family:'Nunito',sans-serif;color:#1a1025;">
                    </div>
                    {{-- CKEditor: sudah ada di admin layout, langsung pakai --}}
                    <div class="mb-3">
                        <textarea name="content" id="noteContent"></textarea>
                    </div>
                    <div class="border-top border-dark border-opacity-10 pt-3">
                        <input type="hidden" name="bg_color" id="selectedColor" value="#fef08a">
                        <div style="font-size:0.7rem;font-weight:700;color:#6b7280;font-family:'Nunito',sans-serif;margin-bottom:6px;">
                            WARNA CATATAN:
                        </div>
                        <div class="color-picker-group">
                            @foreach(['#fef08a'=>'Kuning','#bbf7d0'=>'Hijau','#bfdbfe'=>'Biru','#fbcfe8'=>'Pink','#fed7aa'=>'Oranye','#e9d5ff'=>'Ungu','#f1f5f9'=>'Abu'] as $hex => $label)
                                <label title="{{ $label }}" style="cursor:pointer;">
                                    <input type="radio" name="_color_radio" class="color-radio d-none"
                                           value="{{ $hex }}" {{ $hex=='#fef08a' ? 'checked':'' }}
                                           onchange="changeModalColor('{{ $hex }}')">
                                    <div class="color-circle" style="background:{{ $hex }};border:2px solid rgba(0,0,0,0.1);"></div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-brand" style="background:#d97706;">
                        <i class="fas fa-thumbtack"></i> Tempel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL: EDIT NOTE (BUG FIX #2) --}}
<div class="modal fade modal-cmd" id="editNoteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#d97706,#f59e0b);">
                <h5 class="modal-title"><i class="fas fa-pencil-alt me-2"></i>Edit Catatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditNote">
                <input type="hidden" id="editNoteId">
                <div class="modal-body" id="editNoteModalContainer" style="background:#fef08a;transition:background 0.3s;">
                    <div class="mb-3">
                        <input type="text" name="title" id="editNoteTitle" class="form-control border-0 shadow-none"
                               placeholder="Judul catatan (opsional)"
                               style="background:transparent;font-weight:800;font-size:1rem;font-family:'Nunito',sans-serif;color:#1a1025;">
                    </div>
                    <div class="mb-3">
                        <textarea name="content" id="editNoteContent"></textarea>
                    </div>
                    <div class="border-top border-dark border-opacity-10 pt-3">
                        <input type="hidden" name="bg_color" id="editSelectedColor" value="#fef08a">
                        <div style="font-size:0.7rem;font-weight:700;color:#6b7280;font-family:'Nunito',sans-serif;margin-bottom:6px;">
                            WARNA CATATAN:
                        </div>
                        <div class="color-picker-group" id="editColorGroup">
                            @foreach(['#fef08a'=>'Kuning','#bbf7d0'=>'Hijau','#bfdbfe'=>'Biru','#fbcfe8'=>'Pink','#fed7aa'=>'Oranye','#e9d5ff'=>'Ungu','#f1f5f9'=>'Abu'] as $hex => $label)
                                <label title="{{ $label }}" style="cursor:pointer;">
                                    <input type="radio" name="_edit_color_radio" class="edit-color-radio d-none"
                                           value="{{ $hex }}"
                                           onchange="changeEditModalColor('{{ $hex }}')">
                                    <div class="color-circle" style="background:{{ $hex }};border:2px solid rgba(0,0,0,0.1);"></div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-brand" style="background:#d97706;">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL: SETTINGS --}}
<div class="modal fade modal-cmd" id="settingsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-cog me-2"></i>Pengaturan Notifikasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUpdateSettings">
                <div class="modal-body">
                    <p style="font-size:0.82rem;color:var(--text-secondary);" class="mb-3">
                        <i class="fab fa-telegram me-1" style="color:#2CA5E0;"></i>
                        Aktifkan pengingat via Telegram Bot. Pastikan ID Telegram sudah terhubung di profil.
                    </p>
                    <div class="d-flex flex-column gap-3">
                        <label class="d-flex align-items-center gap-3 p-3 rounded-3"
                               style="background:var(--surface-1);cursor:pointer;border:1px solid var(--surface-border);">
                            <input type="checkbox" name="telegram_remind_morning" class="form-check-input m-0" style="width:20px;height:20px;"
                                   {{ Auth::user()->telegram_remind_morning ? 'checked':'' }}>
                            <div>
                                <div class="fw-bold" style="font-size:0.875rem;font-family:'Nunito',sans-serif;">🌅 Pengingat Pagi</div>
                                <div style="font-size:0.75rem;color:var(--text-muted);font-family:'Nunito',sans-serif;">Kirim ringkasan tugas aktif setiap pagi jam 07.00</div>
                            </div>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3 rounded-3"
                               style="background:var(--surface-1);cursor:pointer;border:1px solid var(--surface-border);">
                            <input type="checkbox" name="telegram_remind_deadline" class="form-check-input m-0" style="width:20px;height:20px;"
                                   {{ Auth::user()->telegram_remind_deadline ? 'checked':'' }}>
                            <div>
                                <div class="fw-bold" style="font-size:0.875rem;font-family:'Nunito',sans-serif;">⏰ Pengingat Deadline</div>
                                <div style="font-size:0.75rem;color:var(--text-muted);font-family:'Nunito',sans-serif;">Notifikasi H-1 sebelum tugas jatuh tempo</div>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-brand"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- POMODORO WIDGET --}}
<div id="pomodoro-widget">
    <div class="pomo-header">
        <span><i class="fas fa-bolt" style="color:#fbbf24;" class="me-2"></i> Focus Timer</span>
        <button type="button" class="btn-close btn-close-white btn-sm" onclick="togglePomodoro()"></button>
    </div>
    <div class="pomo-body">
        <div class="pomo-time" id="pomodoro-time">25:00</div>
        <div class="pomo-label" id="pomodoro-label">Siap untuk fokus!</div>
        <div class="d-flex justify-content-center gap-2 mb-2">
            <button id="btn-pomo-start" class="btn-brand"><i class="fas fa-play"></i> Mulai</button>
            <button id="btn-pomo-reset" class="btn-ghost"><i class="fas fa-redo"></i></button>
        </div>
        <div class="d-flex justify-content-center gap-2">
            <button class="date-quick-btn" onclick="setPomoTime(25)">25m</button>
            <button class="date-quick-btn" onclick="setPomoTime(15)">15m</button>
            <button class="date-quick-btn" onclick="setPomoTime(5)">5m Break</button>
        </div>
    </div>
</div>
@endsection


@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/id.global.min.js'></script>
<script>
// ============================================================
// STATE
// ============================================================
let currentFilter = '{{ $filter }}';

// ============================================================
// FILTER & SORT
// ============================================================
function setFilter(f, event) {
    if (event) event.preventDefault();
    currentFilter = f;
    applyFilters();
}

function applyFilters() {
    const url = new URL(window.location.href);
    url.searchParams.set('filter', currentFilter);
    url.searchParams.set('sort',   document.getElementById('sortSelect').value);
    url.searchParams.set('tag',    document.getElementById('tagFilter') ? document.getElementById('tagFilter').value : '');
    url.searchParams.set('search', document.getElementById('searchInput').value);
    window.location.href = url.toString();
}

// Debounce search
let searchTimer = null;
function debounceSearch(val) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => applyFilters(), 500);
}

// ============================================================
// TASK MODAL HELPERS
// ============================================================
function openTaskModal() {
    document.getElementById('formAddTask').reset();
    setQuickDate('none', document.querySelector('#taskModal .date-quick-btn'));
    new bootstrap.Modal(document.getElementById('taskModal')).show();
    setTimeout(() => document.getElementById('taskTitleInput').focus(), 350);
}

function toLocalISO(date) {
    return new Date(date - date.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
}
function setQuickDate(type, btnEl) {
    document.querySelectorAll('#taskModal .date-quick-btn').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');
    if (type === 'none') { document.getElementById('taskDeadlineInput').value = ''; return; }
    let d = new Date();
    if (type === 'tomorrow') d.setDate(d.getDate() + 1);
    d.setHours(23, 59, 0, 0);
    document.getElementById('taskDeadlineInput').value = toLocalISO(d);
}
function setEditQuickDate(type, btnEl) {
    document.querySelectorAll('#editTaskModal .date-quick-btn').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');
    if (type === 'none') { document.getElementById('editTaskDeadline').value = ''; return; }
    let d = new Date();
    if (type === 'tomorrow') d.setDate(d.getDate() + 1);
    d.setHours(23, 59, 0, 0);
    document.getElementById('editTaskDeadline').value = toLocalISO(d);
}

function toggleTaskDesc(id) {
    const desc = document.getElementById('desc-' + id);
    const chev = document.getElementById('chevron-' + id);
    if (!desc) return;
    desc.classList.toggle('visible');
    if (chev) chev.style.transform = desc.classList.contains('visible') ? 'rotate(180deg)' : '';
}

window.changeModalColor = function(color) {
    document.getElementById('noteModalContainer').style.backgroundColor = color;
    document.getElementById('selectedColor').value = color;
};
window.changeEditModalColor = function(color) {
    document.getElementById('editNoteModalContainer').style.backgroundColor = color;
    document.getElementById('editSelectedColor').value = color;
};

// ============================================================
// POMODORO GLOBAL LOGIC
// ============================================================
let pomoInterval;
let pomoTime = 25 * 60;
let isPomoRunning = false;

function updatePomoDisplay() {
    let m = Math.floor(pomoTime / 60), s = pomoTime % 60;
    document.getElementById('pomodoro-time').textContent = (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
}

window.togglePomodoro = function() {
    document.getElementById('pomodoro-widget').classList.toggle('show');
};

window.setPomoTime = function(minutes) {
    clearInterval(pomoInterval);
    isPomoRunning = false;
    pomoTime = minutes * 60;
    updatePomoDisplay();
    document.getElementById('pomodoro-label').textContent = 'Siap untuk fokus!';
    document.getElementById('btn-pomo-start').innerHTML = '<i class="fas fa-play"></i> Mulai';
};

// ============================================================
// CKEDITOR INSTANCES
// ============================================================
let editorAdd, editorEdit;

document.addEventListener('DOMContentLoaded', function() {
    if (typeof ClassicEditor !== 'undefined') {
        ClassicEditor.create(document.querySelector('#noteContent'), {
            toolbar: ['bold', 'italic', 'underline', '|', 'bulletedList', 'numberedList', '|', 'blockQuote', 'undo', 'redo'],
            placeholder: 'Tuangkan pikiranmu di sini...'
        }).then(editor => { editorAdd = editor; }).catch(console.error);

        ClassicEditor.create(document.querySelector('#editNoteContent'), {
            toolbar: ['bold', 'italic', 'underline', '|', 'bulletedList', 'numberedList', '|', 'blockQuote', 'undo', 'redo'],
            placeholder: 'Edit catatan...'
        }).then(editor => { editorEdit = editor; }).catch(console.error);
    }
});

document.getElementById('noteModal').addEventListener('show.bs.modal', function() {
    changeModalColor('#fef08a');
    document.querySelector('input[name="_color_radio"][value="#fef08a"]').checked = true;
    if (editorAdd) editorAdd.setData('');
    document.getElementById('noteTitle').value = '';
});

// ============================================================
// DOCUMENT READY — SEMUA EVENT HANDLER JQUERY DI SINI
// ============================================================
$(document).ready(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // =========================================================
    // TASKS: ADD
    // =========================================================
    $('#formAddTask').off('submit').on('submit', function(e) {
        e.preventDefault();
        let btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');
        $.post("{{ route('admin.productivity.tasks.store') }}", $(this).serialize())
            .done(res => { if (res.success) location.reload(); })
            .fail(xhr => {
                Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Simpan Tugas');
            });
    });

    // =========================================================
    // TASKS: TOGGLE STATUS
    // =========================================================
    $(document).on('change', '.task-checkbox', function() {
        let id     = $(this).data('id');
        let status = $(this).is(':checked') ? 'completed' : 'pending';
        let item   = $('#task-' + id);
        if (status === 'completed') item.addClass('completed');
        else item.removeClass('completed');

        $.ajax({ url: `/admin/productivity/tasks/${id}/status`, type: 'PATCH', data: { status } })
            .done(() => {
                const T = Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 2000 });
                T.fire({ icon: status === 'completed' ? 'success' : 'info',
                         title: status === 'completed' ? 'Tugas Selesai! 🎉' : 'Tugas Diaktifkan' });
                if (status === 'completed') setTimeout(() => location.reload(), 2200);
            });
    });

    // =========================================================
    // TASKS: EDIT — Open & Prefill
    // =========================================================
    $(document).on('click', '.btn-edit-task', function() {
        let btn    = $(this);
        let taskId = btn.data('id');
        $('#editTaskId').val(taskId);

        let subTasks    = btn.data('subtasks')    || [];
        let attachments = btn.data('attachments') || [];
        renderSubTasks(taskId, subTasks);
        renderAttachments(taskId, attachments);

        $('#editTaskTitle').val(btn.data('title'));
        $('#editTaskDesc').val(btn.data('desc'));
        $('#editTaskTag').val(btn.data('tag'));
        $('#editTaskPriority').val(btn.data('priority'));
        $('#editTaskRecurrence').val(btn.data('recurrence'));
        $('#editTaskAssignee').val(btn.data('assignee')).trigger('change');

        let dl = btn.data('deadline');
        $('#editTaskDeadline').val(dl || '');
        $('#editTaskModal .date-quick-btn').removeClass('active');
        if (!dl) $('#editBtnNone').addClass('active');

        new bootstrap.Modal(document.getElementById('editTaskModal')).show();
        setTimeout(() => document.getElementById('editTaskTitle').focus(), 350);
    });

    // =========================================================
    // TASKS: VIEW MODAL
    // =========================================================
    let currentViewTaskId = null;

    $(document).on('click', '.btn-view-task', function() {
        let btn = $(this);
        currentViewTaskId = btn.data('id');
        let taskId = btn.data('id');

        $('#viewTaskTitle').text(btn.data('title'));
        $('#viewTaskDesc').text(btn.data('desc') || 'Tidak ada deskripsi tambahan.');

        let metaHtml = `
            <span class="badge bg-primary rounded-pill small"><i class="far fa-clock me-1"></i> ${btn.data('deadline')}</span>
            <span class="badge bg-secondary rounded-pill small"><i class="fas fa-flag me-1"></i> Prioritas: ${btn.data('priority')}</span>
        `;
        $('#viewTaskMeta').html(metaHtml);

        let subTasks = btn.data('subtasks') || [];
        let subHtml = '';
        subTasks.forEach(st => {
            let checked   = st.is_completed ? 'checked' : '';
            let textStyle = st.is_completed ? 'text-decoration: line-through; color: #9ca3af;' : '';
            subHtml += `
                <div class="d-flex align-items-center gap-2 p-2 rounded border bg-white shadow-sm">
                    <input type="checkbox" class="form-check-input toggle-subtask" data-task="${taskId}" data-id="${st.id}" ${checked} style="cursor:pointer; width:18px; height:18px;">
                    <span class="small" style="${textStyle}">${st.title}</span>
                </div>
            `;
        });
        $('#viewSubTaskList').html(subHtml || '<p class="text-muted small">Tidak ada checklist.</p>');

        let attachments = btn.data('attachments') || [];
        let attHtml = '';
        attachments.forEach(att => {
            attHtml += `
                <a href="/storage/${att.file_path}" target="_blank" class="d-flex align-items-center justify-content-between p-2 rounded border bg-white shadow-sm text-decoration-none">
                    <span class="small text-dark text-truncate" style="max-width: 85%;"><i class="far fa-file-alt me-2 text-primary"></i> ${att.file_name}</span>
                    <i class="fas fa-download text-muted small"></i>
                </a>
            `;
        });
        $('#viewAttachmentList').html(attHtml || '<p class="text-muted small">Tidak ada lampiran.</p>');

        let comments = btn.data('comments') || [];
        renderComments(comments);

        new bootstrap.Modal(document.getElementById('viewTaskModal')).show();
    });

    function renderComments(comments) {
        let html = '';
        let currentUserId = {{ Auth::id() }};
        comments.forEach(c => {
            let isMe   = c.user_id === currentUserId;
            let align  = isMe ? 'text-end' : 'text-start';
            let bg     = isMe ? 'bg-primary-subtle text-primary-emphasis' : 'bg-light border';
            let dateObj = new Date(c.created_at);
            let timeStr = dateObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            html += `
                <div class="d-flex flex-column ${align}">
                    <small class="text-muted" style="font-size:0.7rem; font-weight:700;">${c.user.name} &bull; ${timeStr}</small>
                    <div class="p-2 rounded d-inline-block mt-1 ${bg}" style="max-width:85%; align-self: ${isMe ? 'flex-end' : 'flex-start'}; font-family:'Nunito',sans-serif; font-size:0.85rem;">
                        ${c.comment}
                    </div>
                </div>
            `;
        });
        if (comments.length === 0) html = '<div class="text-center text-muted small my-2">Belum ada diskusi.</div>';
        $('#viewCommentList').html(html);
        setTimeout(() => {
            let el = document.getElementById('viewCommentList');
            if (el) el.scrollTop = el.scrollHeight;
        }, 100);
    }

    $('#btnSubmitComment').click(function() {
        let comment = $('#newCommentText').val().trim();
        if (!comment || !currentViewTaskId) return;
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.post(`/admin/productivity/tasks/${currentViewTaskId}/comments`, { comment })
            .done(() => { $('#newCommentText').val(''); location.reload(); })
            .fail(() => Swal.fire('Error', 'Gagal mengirim komentar.', 'error'))
            .always(() => btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Kirim'));
    });

    $('#newCommentText').on('keypress', function(e) {
        if (e.which === 13) $('#btnSubmitComment').click();
    });

    function renderSubTasks(taskId, subTasks) {
        let html = '';
        subTasks.forEach(st => {
            let checked   = st.is_completed ? 'checked' : '';
            let textStyle = st.is_completed ? 'text-decoration: line-through; color: #9ca3af;' : 'color: #374151;';
            html += `
                <div class="d-flex align-items-center justify-content-between p-1 border rounded bg-light" id="subtask-${st.id}">
                    <div class="d-flex align-items-center gap-2" style="font-size: 0.8rem; font-family:'Nunito',sans-serif;">
                        <input type="checkbox" class="form-check-input mt-0 toggle-subtask" data-task="${taskId}" data-id="${st.id}" ${checked} style="cursor:pointer;">
                        <span style="${textStyle}">${st.title}</span>
                    </div>
                    <button type="button" class="btn btn-sm text-danger p-0 delete-subtask" data-task="${taskId}" data-id="${st.id}"><i class="fas fa-times"></i></button>
                </div>
            `;
        });
        if (subTasks.length === 0) html = '<div class="text-muted" style="font-size:0.75rem;">Belum ada sub-task.</div>';
        $('#subTaskList').html(html);
    }

    function renderAttachments(taskId, attachments) {
        let html = '';
        attachments.forEach(att => {
            html += `
                <div class="d-flex align-items-center justify-content-between p-1 border rounded bg-light" id="att-${att.id}">
                    <a href="/storage/${att.file_path}" target="_blank" class="text-truncate" style="font-size: 0.8rem; font-family:'Nunito',sans-serif; text-decoration:none; max-width:80%;">
                        <i class="far fa-file-alt me-1 text-primary"></i> ${att.file_name}
                    </a>
                    <button type="button" class="btn btn-sm text-danger p-0 delete-attachment" data-task="${taskId}" data-id="${att.id}"><i class="fas fa-times"></i></button>
                </div>
            `;
        });
        if (attachments.length === 0) html = '<div class="text-muted" style="font-size:0.75rem;">Belum ada lampiran.</div>';
        $('#attachmentList').html(html);
    }

    $('#btnAddSubTask').click(function() {
        let taskId = $('#editTaskId').val();
        let title  = $('#newSubTaskTitle').val();
        if (!title) return;
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.post(`/admin/productivity/tasks/${taskId}/subtasks`, { title })
            .done(() => location.reload())
            .fail(() => Swal.fire('Error', 'Gagal menambah sub-task', 'error'))
            .always(() => btn.prop('disabled', false).html('<i class="fas fa-plus"></i>'));
    });

    $(document).on('change', '.toggle-subtask', function() {
        let taskId = $(this).data('task');
        let subId  = $(this).data('id');
        $.ajax({ url: `/admin/productivity/tasks/${taskId}/subtasks/${subId}/toggle`, type: 'PATCH' })
            .done(() => location.reload());
    });

    $(document).on('click', '.delete-subtask', function() {
        let taskId = $(this).data('task');
        let subId  = $(this).data('id');
        $.ajax({ url: `/admin/productivity/tasks/${taskId}/subtasks/${subId}`, type: 'DELETE' })
            .done(() => $('#subtask-' + subId).remove());
    });

    $('#btnUploadAttachment').click(function() {
        let taskId    = $('#editTaskId').val();
        let fileInput = $('#newAttachmentFile')[0];
        if (fileInput.files.length === 0) return Swal.fire('Ops!', 'Pilih file dulu', 'warning');
        let formData = new FormData();
        formData.append('file', fileInput.files[0]);
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.ajax({
            url: `/admin/productivity/tasks/${taskId}/attachments`,
            type: 'POST', data: formData, processData: false, contentType: false,
            success: () => location.reload(),
            error: xhr => {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Gagal upload', 'error');
                btn.prop('disabled', false).html('<i class="fas fa-upload"></i>');
            }
        });
    });

    $(document).on('click', '.delete-attachment', function() {
        let taskId = $(this).data('task');
        let attId  = $(this).data('id');
        $.ajax({ url: `/admin/productivity/tasks/${taskId}/attachments/${attId}`, type: 'DELETE' })
            .done(() => $('#att-' + attId).remove());
    });

    $('#formEditTask').submit(function(e) {
        e.preventDefault();
        let id  = $('#editTaskId').val();
        let btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>...');
        $.ajax({ url: `/admin/productivity/tasks/${id}`, type: 'PATCH', data: $(this).serialize() })
            .done(res => {
                if (res.success) {
                    $('#editTaskModal').modal('hide');
                    Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 2000 })
                        .fire({ icon: 'success', title: 'Tugas berhasil diperbarui!' });
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .fail(xhr => {
                Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Perubahan');
            });
    });

    // =========================================================
    // TASKS: ARCHIVE / UNARCHIVE / DELETE
    // =========================================================
    $(document).on('click', '.btn-archive-task', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Arsipkan Tugas?',
            text: 'Tugas dipindahkan ke arsip, tidak muncul di daftar aktif.',
            icon: 'question', showCancelButton: true,
            confirmButtonText: '<i class="fas fa-archive me-1"></i> Arsipkan',
            cancelButtonText: 'Batal', confirmButtonColor: '#f59e0b'
        }).then(r => {
            if (r.isConfirmed) {
                $.ajax({ url: `/admin/productivity/tasks/${id}/archive`, type: 'PATCH' })
                    .done(() => {
                        $('#task-' + id).slideUp(350, function() { $(this).remove(); });
                        Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 2000 })
                            .fire({ icon: 'info', title: 'Tugas diarsipkan.' });
                    });
            }
        });
    });

    $(document).on('click', '.btn-unarchive-task', function() {
        let id = $(this).data('id');
        $.ajax({ url: `/admin/productivity/tasks/${id}/unarchive`, type: 'PATCH' })
            .done(() => {
                Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 2000 })
                    .fire({ icon: 'success', title: 'Tugas dipulihkan!' });
                setTimeout(() => location.reload(), 1200);
            });
    });

    $(document).on('click', '.btn-delete-task', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Permanen?', text: 'Tindakan ini tidak dapat dibatalkan.',
            icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#ef4444'
        }).then(r => {
            if (r.isConfirmed) {
                $.ajax({ url: `/admin/productivity/tasks/${id}`, type: 'DELETE' })
                    .done(() => { $('#task-' + id).slideUp(300, function() { $(this).remove(); }); });
            }
        });
    });

    // =========================================================
    // HABITS
    // =========================================================
    const habitTotal = {{ $habitTotal ?? 0 }};
    let habitDoneCount = {{ $habitCompleted ?? 0 }};

    $(document).on('click', '.habit-toggle', function() {
        let id     = $(this).data('id');
        let item   = $('#habit-' + id);
        let wasDone = item.hasClass('done');
        $.post(`/admin/productivity/habits/${id}/toggle`, function(res) {
            item.toggleClass('done', res.is_completed);
            if (res.is_completed && !wasDone)  habitDoneCount = Math.min(habitTotal, habitDoneCount + 1);
            else if (!res.is_completed && wasDone) habitDoneCount = Math.max(0, habitDoneCount - 1);
            const pct = habitTotal > 0 ? Math.round((habitDoneCount / habitTotal) * 100) : 0;
            $('#habit-progress-fill').css('width', pct + '%');
            $('#habit-progress-label').text(habitDoneCount + '/' + habitTotal);
            Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 1500 })
                .fire({ icon: res.is_completed ? 'success' : 'info',
                        title: res.is_completed ? '✅ Habit selesai!' : 'Habit dibatalkan' });
        });
    });

    $('#formAddHabit').submit(function(e) {
        e.preventDefault();
        $.post("{{ route('admin.productivity.habits.store') }}", $(this).serialize())
            .done(() => location.reload())
            .fail(xhr => Swal.fire('Gagal', xhr.responseJSON?.message, 'error'));
    });

    $(document).on('click', '.btn-delete-habit', function() {
        let id = $(this).data('id');
        $.ajax({ url: `/admin/productivity/habits/${id}`, type: 'DELETE' })
            .done(() => $('#habit-' + id).slideUp());
    });

    // =========================================================
    // NOTES
    // =========================================================
    $('#formAddNote').submit(function(e) {
        e.preventDefault();
        if (editorAdd) $('#noteContent').val(editorAdd.getData());
        let btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>...');
        $.post("{{ route('admin.productivity.notes.store') }}", $(this).serialize())
            .done(() => location.reload())
            .fail(xhr => {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Cek isian Anda.', 'error');
                btn.prop('disabled', false).html('<i class="fas fa-thumbtack"></i> Tempel');
            });
    });

    $(document).on('click', '.btn-edit-note', function() {
        let btn   = $(this);
        let color = btn.data('color') || '#fef08a';
        $('#editNoteId').val(btn.data('id'));
        $('#editNoteTitle').val(btn.data('title'));
        changeEditModalColor(color);
        $('input[name="_edit_color_radio"][value="' + color + '"]').prop('checked', true);
        if (editorEdit) editorEdit.setData(btn.data('content'));
        else $('#editNoteContent').val(btn.data('content'));
        new bootstrap.Modal(document.getElementById('editNoteModal')).show();
    });

    $('#formEditNote').submit(function(e) {
        e.preventDefault();
        let id  = $('#editNoteId').val();
        if (editorEdit) $('#editNoteContent').val(editorEdit.getData());
        let btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>...');
        $.ajax({ url: `/admin/productivity/notes/${id}`, type: 'PATCH', data: $(this).serialize() })
            .done(() => {
                $('#editNoteModal').modal('hide');
                Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 2000 })
                    .fire({ icon: 'success', title: 'Catatan diperbarui!' });
                setTimeout(() => location.reload(), 1500);
            })
            .fail(xhr => {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Cek isian Anda.', 'error');
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Perubahan');
            });
    });

    $(document).on('click', '.btn-delete-note', function() {
        let id = $(this).data('id');
        $.ajax({ url: `/admin/productivity/notes/${id}`, type: 'DELETE' })
            .done(() => { $('#note-' + id).fadeOut(300, function() { $(this).remove(); }); });
    });

    // =========================================================
    // SETTINGS
    // =========================================================
    $('#formUpdateSettings').submit(function(e) {
        e.preventDefault();
        let btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>...');
        $.post("{{ route('admin.productivity.settings.update') }}", $(this).serialize())
            .done(() => {
                $('#settingsModal').modal('hide');
                Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })
                    .fire({ icon: 'success', title: 'Pengaturan disimpan!' });
            })
            .always(() => btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan'));
    });

    // =========================================================
    // POMODORO BUTTON ACTIONS
    // =========================================================
    $('#btn-pomo-start').click(function() {
        if (isPomoRunning) {
            clearInterval(pomoInterval);
            isPomoRunning = false;
            $(this).html('<i class="fas fa-play"></i> Lanjut');
            document.getElementById('pomodoro-label').textContent = 'Dijeda...';
        } else {
            isPomoRunning = true;
            $(this).html('<i class="fas fa-pause"></i> Jeda');
            document.getElementById('pomodoro-label').textContent = 'Fokus bekerja... 🔥';
            pomoInterval = setInterval(() => {
                if (pomoTime > 0) { pomoTime--; updatePomoDisplay(); }
                else {
                    clearInterval(pomoInterval);
                    isPomoRunning = false;
                    Swal.fire({ title: 'Waktu Fokus Habis! 🎉', text: 'Kerja bagus! Istirahatkan matamu 5 menit.', icon: 'success' });
                    window.setPomoTime(25);
                }
            }, 1000);
        }
    });

    $('#btn-pomo-reset').click(function() { window.setPomoTime(25); });

    $('#taskDeadlineInput').on('change', function() {
        $('#taskModal .date-quick-btn').removeClass('active');
        if (!$(this).val()) $('#taskModal .date-quick-btn:first').addClass('active');
    });

    // Select2
    if ($.fn.select2) {
        $('#taskTagSelect').select2({ theme: 'bootstrap-5', dropdownParent: $('#taskModal'), tags: true, placeholder: 'Ketik atau pilih tag...' });
        $('#editTaskTag').select2({ theme: 'bootstrap-5', dropdownParent: $('#editTaskModal'), tags: true, placeholder: 'Ketik atau pilih tag...' });
        $('#taskAssigneeSelect').select2({ theme: 'bootstrap-5', dropdownParent: $('#taskModal'), placeholder: 'Cari pegawai...', width: '100%' });
        if ($('#editTaskAssignee').length) {
            $('#editTaskAssignee').select2({ theme: 'bootstrap-5', dropdownParent: $('#editTaskModal'), placeholder: 'Cari pegawai...', width: '100%' });
        }
    }

    // ============================================================
    // VIEW SWITCHER & CALENDAR LOGIC
    // ============================================================
    const $taskContainer     = $('#task-container');
    const $kanbanContainer   = $('#kanban-container');
    const $calendarContainer = $('#calendar-container');
    const $btnKanban         = $('#btnViewKanban');
    const $btnCalendar       = $('#btnViewCalendar');
    let calendarInstance     = null;

    // Fungsi reset semua view
    function resetViews() {
        $taskContainer.addClass('d-none');
        $kanbanContainer.addClass('d-none');
        $calendarContainer.addClass('d-none');
        $btnKanban.removeClass('active');
        $btnCalendar.removeClass('active');
    }

    // Toggle Kanban
    $btnKanban.on('click', function() {
        if ($(this).hasClass('active')) {
            // Kembali ke List View
            resetViews();
            $taskContainer.removeClass('d-none');
        } else {
            // Buka Kanban
            resetViews();
            $kanbanContainer.removeClass('d-none');
            $(this).addClass('active');
        }
    });

    // Toggle Kalender
    $btnCalendar.on('click', function() {
        if ($(this).hasClass('active')) {
            // Kembali ke List View
            resetViews();
            $taskContainer.removeClass('d-none');
        } else {
            // Buka Kalender
            resetViews();
            $calendarContainer.removeClass('d-none');
            $(this).addClass('active');

            // Render kalender hanya saat pertama kali dibuka (lazy load)
            if (!calendarInstance) {
                let calendarEl = document.getElementById('calendar');
                calendarInstance = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    
                    // --- 1. BAHASA INDONESIA ---
                    locale: 'id',
                    buttonText: {
                        today: 'Hari Ini',
                        month: 'Bulan',
                        week: 'Minggu',
                        list: 'Agenda'
                    },
                    
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listWeek'
                    },
                    height: 'auto',

                    // --- 2. FORMAT WAKTU (Mencegah 11:59p / 00:00) ---
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false // Paksa format 24 jam ala Indonesia (misal: 23:59)
                    },
                    
                    // --- 3. MENCEGAH MELUBER KE BESOK ---
                    displayEventEnd: false, 
                    defaultTimedEventDuration: '00:01:00', // Set durasi cuma 1 menit (23:59 + 1 mnt = 00:00)
                    nextDayThreshold: '00:00:00', // Jika selesai pas jam 00:00, jangan tampilkan di hari besoknya
                    
                    events: [
                        @foreach($tasks as $t)
                            @if($t->deadline_at)
                            {
                                id: '{{ $t->id }}',
                                title: '{!! addslashes($t->title) !!}',
                                
                                // Paksa detik menjadi "00" agar FullCalendar tidak membulatkan 23:59:59 menjadi 00:00 besoknya
                                start: '{{ \Carbon\Carbon::parse($t->deadline_at)->format("Y-m-d\TH:i:00") }}',
                                allDay: false, 
                                
                                backgroundColor: '{{ $t->priority == "high" ? "var(--accent-red)" : ($t->priority == "medium" ? "var(--accent-amber)" : "var(--accent-blue)") }}',
                                className: '{{ $t->status == "completed" ? "opacity-50" : "" }}'
                            },
                            @endif
                        @endforeach
                    ],
                    eventClick: function(info) {
                        let taskId = info.event.id;
                        $('.btn-view-task[data-id="'+taskId+'"]').first().click();
                    }
                });
                calendarInstance.render();
            } else {
                calendarInstance.updateSize(); 
            }
        }
    });

    // --- Helper: update badge count header kolom ---
    function updateKanbanCounts() {
        ['pending', 'in_progress', 'completed'].forEach(function(status) {
            const list  = document.getElementById('kanban-list-' + status);
            const badge = document.getElementById('kc-count-' + status);
            if (list && badge) {
                badge.textContent = list.querySelectorAll('.kanban-card').length;
            }
        });
    }

    // --- Helper: tampilkan/sembunyikan empty state per kolom ---
    function syncEmptyState(list) {
        const hasCards = list.querySelectorAll('.kanban-card').length > 0;
        let emptyEl    = list.querySelector('.kanban-empty');
        if (!hasCards && !emptyEl) {
            emptyEl = document.createElement('div');
            emptyEl.className = 'kanban-empty';
            emptyEl.innerHTML = '<i class="far fa-clipboard"></i> Tidak ada tugas di sini';
            list.appendChild(emptyEl);
        } else if (hasCards && emptyEl) {
            emptyEl.remove();
        }
    }

    // --- Sortable.js ---
    document.querySelectorAll('.kanban-list').forEach(function(listEl) {
        new Sortable(listEl, {
            group:             'kanban-tasks',
            animation:         180,
            ghostClass:        'sortable-ghost',
            dragClass:         'sortable-drag',
            handle:            '.kanban-card',
            scroll:            true,
            scrollSensitivity: 60,
            scrollSpeed:       10,

            onStart: function() {
                document.querySelectorAll('.kanban-list').forEach(function(l) { l.classList.add('drag-over'); });
            },

            onEnd: function(evt) {
                document.querySelectorAll('.kanban-list').forEach(function(l) { l.classList.remove('drag-over'); });

                const cardEl    = evt.item;
                const taskId    = cardEl.dataset.id;
                const newStatus = evt.to.dataset.status;
                const oldStatus = evt.from.dataset.status;

                syncEmptyState(evt.from);
                syncEmptyState(evt.to);
                updateKanbanCounts();

                if (newStatus === oldStatus) return;

                $.ajax({
                    url:  `/admin/productivity/tasks/${taskId}/status`,
                    type: 'PATCH',
                    data: { status: newStatus }
                })
                .done(function() {
                    const listItem = document.getElementById('task-' + taskId);
                    if (listItem) {
                        const checkbox = listItem.querySelector('.task-checkbox');
                        if (newStatus === 'completed') {
                            listItem.classList.add('completed');
                            if (checkbox) checkbox.checked = true;
                        } else {
                            listItem.classList.remove('completed');
                            if (checkbox) checkbox.checked = false;
                        }
                    }
                    Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 1500 })
                        .fire({ icon: newStatus === 'completed' ? 'success' : 'info', title: 'Status tugas diperbarui!' });
                })
                .fail(function() {
                    evt.from.insertBefore(cardEl, evt.from.children[evt.oldIndex] || null);
                    syncEmptyState(evt.from);
                    syncEmptyState(evt.to);
                    updateKanbanCounts();
                    Swal.fire('Gagal!', 'Terjadi kesalahan saat memindah tugas.', 'error');
                });
            }
        });
    });

    // Inisialisasi count saat load
    updateKanbanCounts();

});
</script>
@endsection
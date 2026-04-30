@extends('layouts.admin')

@section('styles')
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
    --accent-indigo:    #6366f1;

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

/* ══════════════════════════════════════
   HEADER — Compact Command Center
══════════════════════════════════════ */
.cmd-header {
    background: linear-gradient(135deg, var(--brand-maroon) 0%, #9c2456 55%, var(--brand-maroon-dk) 100%);
    border-radius: 20px;
    padding: 0.85rem 1.25rem;          /* was: 1rem 1.5rem */
    margin-bottom: 1.5rem;
    box-shadow: 0 12px 32px rgba(116,24,71,0.25), inset 0 1px 0 rgba(255,255,255,0.15);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-wrap: wrap;
    gap: 1.25rem;                       /* was: 1.5rem */
    justify-content: space-between;
    align-items: center;
}
.cmd-header::before {
    content: ''; position: absolute; top: -70px; right: -70px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,0.045); border-radius: 50%;
}
.cmd-header::after {
    content: ''; position: absolute; bottom: -90px; left: 32%;
    width: 260px; height: 260px;
    background: rgba(255,255,255,0.03); border-radius: 50%;
}
 
/* ── Kiri: Profil ── */
.header-profile-section {
    display: flex;
    flex-direction: column;
    position: relative;
    z-index: 1;
}
.greeting-wrapper {
    display: flex;
    align-items: center;
    margin-bottom: 0.2rem;             /* was: 0.35rem */
}
.greeting-icon {
    width: 32px;                        /* was: 36px */
    font-size: 1.35rem;                 /* was: 1.6rem */
    text-align: left;
    line-height: 1;
}
.cmd-header-title {
    font-family: 'Montserrat', sans-serif;
    font-size: 1.35rem;                 /* was: 1.65rem */
    color: #ffffff;
    font-weight: 800;
    margin: 0;
    letter-spacing: -0.3px;
}
.subtitle-wrapper {
    display: flex;
    align-items: center;
    color: rgba(255,255,255,0.75);
    font-size: 0.8rem;                  /* was: 0.9rem */
    font-family: 'Nunito', sans-serif;
}
.subtitle-icon {
    width: 32px;                        /* was: 36px — harus sama dengan .greeting-icon */
    font-size: 0.9rem;
    text-align: left;
    padding-left: 2px;
}
 
/* ── Kiri: Bento Stats ── */
.header-stats-grid {
    display: flex;
    gap: 0.65rem;                       /* was: 1rem */
    position: relative;
    z-index: 1;
    flex-wrap: wrap;
}
.stat-bento {
    background: rgba(255,255,255,0.06);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;                /* was: 16px */
    padding: 0.55rem 0.9rem;            /* was: 1rem 1.25rem — ini perubahan terbesar */
    min-width: 90px;                    /* was: 130px */
    display: flex;
    flex-direction: column;
    justify-content: center;
    transition: transform 0.2s ease, background 0.2s ease;
}
.stat-bento:hover {
    transform: translateY(-2px);
    background: rgba(255,255,255,0.1);
}
.stat-bento-value {
    font-family: 'Montserrat', sans-serif;
    font-size: 1.2rem;                  /* was: 1.5rem */
    font-weight: 800;
    color: #fff;
    line-height: 1;
    margin-bottom: 0.25rem;             /* was: 0.4rem */
}
.stat-bento-label {
    font-family: 'Nunito', sans-serif;
    font-size: 0.68rem;                 /* was: 0.75rem */
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.stat-bento.danger .stat-bento-label  { color: #fca5a5; }
.stat-bento.warning .stat-bento-label { color: #fcd34d; }
.stat-bento.success .stat-bento-label { color: #6ee7b7; }
.stat-bento.primary .stat-bento-label { color: #a5b4fc; }
 
/* ── Kanan: Progress Bar ── */
.daily-progress-wrap {
    background: rgba(0,0,0,0.2);
    border-radius: 12px;                /* was: 16px */
    padding: 0.75rem 1rem;              /* was: 1.25rem — ini perubahan terbesar */
    min-width: 240px;                   /* was: 260px */
    display: flex;
    flex-direction: column;
    justify-content: center;
    border: 1px solid rgba(255,255,255,0.05);
}
.daily-progress-count {
    font-family: 'Nunito', sans-serif;
    font-size: 0.78rem;                 /* was: 0.85rem */
    color: rgba(255,255,255,0.9);
    margin-bottom: 0.5rem;              /* was: 0.75rem */
}
.daily-progress-bar {
    height: 6px;                        /* was: 8px */
    background: rgba(255,255,255,0.1);
    border-radius: 12px;
    overflow: hidden;
}
.daily-progress-fill {
    height: 100%;
    border-radius: 12px;
    background: linear-gradient(90deg, #34d399, #10b981);
    box-shadow: 0 0 10px rgba(16,185,129,0.4);
}
 
/* ── Kanan: Toolbar ── */
.header-toolbar {
    display: flex;
    gap: 0.5rem;
    width: 100%;
    justify-content: flex-end;
    flex-wrap: wrap;
    padding-top: 0.5rem;
    border-top: 1px solid rgba(255,255,255,0.1);
}
.header-btn {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.15);
    color: #fff;
    border-radius: 10px;                /* was: 12px */
    padding: 0.45rem 1rem;              /* was: 0.6rem 1.2rem */
    font-size: 0.8rem;                  /* was: 0.85rem */
    font-weight: 700;
    font-family: 'Nunito', sans-serif;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    text-decoration: none;
}
.header-btn:hover {
    background: #fff;
    color: var(--brand-maroon-dk);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.header-btn i { font-size: 0.9rem; }


/* ══════════════════════════════════════
   RESPONSIVE — Mobile & Tablet
══════════════════════════════════════ */

/* Tablet (768px - 1024px) */
@media (max-width: 1024px) {
    .cmd-header {
        gap: 0.85rem;
    }

    .cmd-header > .d-flex.flex-column.align-items-end {
        align-items: flex-start !important;
        width: 100%;
    }
    .daily-progress-wrap {
        min-width: 200px;
    }

    .header-toolbar {
        justify-content: flex-start;
    }
    .header-stats-grid {
        gap: 0.5rem;
    }
    .stat-bento {
        min-width: 78px;
    }

    .task-panel-body {
        max-height: calc(100vh - 260px);
    }
}

/* Mobile (< 768px) */
@media (max-width: 767px) {
    .cmd-header {
        border-radius: 14px;
        padding: 0.75rem 1rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }

    /* Kolom kanan: align-items-end → flex-start di mobile */
    .cmd-header > .d-flex.flex-column.align-items-end {
        align-items: flex-start !important;
        width: 100%;
    }
    .daily-progress-wrap {
        width: 100%;
        min-width: unset;
    }
    .header-toolbar {
        justify-content: flex-start;
    }

    .cmd-header-title {
        font-size: 1.1rem;
    }
    .greeting-icon {
        font-size: 1.1rem;
    }
    .header-stats-grid {
        gap: 0.4rem;
    }
    .stat-bento {
        min-width: 62px;
        padding: 0.45rem 0.6rem;
        border-radius: 10px;
    }
    .stat-bento-value {
        font-size: 1rem;
    }
    .stat-bento-label {
        font-size: 0.58rem;
    }
    .daily-progress-wrap {
        padding: 0.65rem 0.85rem;
        border-radius: 10px;
    }
    .header-btn {
        padding: 0.4rem 0.7rem;
        font-size: 0.72rem;
    }
    .header-btn span.d-none-mobile {
        display: none; /* sembunyikan label teks, sisakan ikon */
    }

    /* Filter bar scroll horizontal di mobile */
    .filter-bar-prod {
        flex-wrap: nowrap;
        overflow-x: auto;
        padding: 0.6rem 0.9rem;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        gap: 0.3rem;
    }
    .filter-bar-prod::-webkit-scrollbar { display: none; }
    .filter-tab {
        flex-shrink: 0;
        white-space: nowrap;
    }
    .search-input-wrap {
        flex-shrink: 0;
        margin-left: 0.25rem;
    }
    .search-input-wrap input {
        width: 140px;
    }
    .search-input-wrap input:focus {
        width: 160px;
    }

    /* Panel header stack di mobile */
    .panel-header {
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
    }
    .panel-header .d-flex {
        flex-wrap: wrap;
        gap: 0.4rem;
    }

    /* Task item compact di mobile */
    .task-actions {
        opacity: 1; /* Selalu tampil di mobile (tidak ada hover) */
    }
    .task-action-btn {
        width: 26px;
        height: 26px;
    }

    /* Kanban scroll horizontal di mobile */
    .kanban-row {
        min-width: 600px;
        height: auto;
        min-height: 400px;
    }

    /* Task panel body full height di mobile */
    .task-panel-body {
        max-height: 65vh;
    }

    /* Layout kolom mobile */
    .col-xl-8, .col-xl-4,
    .col-lg-7, .col-lg-5 {
        /* Bootstrap handles ini, tapi tambahan safeguard: */
    }

    /* Notes grid 1 kolom di mobile */
    .notes-grid {
        grid-template-columns: 1fr !important;
    }

    /* Modal full width di mobile */
    .modal-dialog {
        margin: 0.5rem;
    }
}

/* Very small mobile (< 480px) */
@media (max-width: 480px) {
    .cmd-header-title {
        font-size: 1rem;
    }
    .stat-bento-value {
        font-size: 0.95rem;
    }
    .header-btn {
        padding: 0.38rem 0.6rem;
        font-size: 0.7rem;
        border-radius: 8px;
    }
    /* Sembunyikan teks di tombol header, sisakan ikon */
    .header-btn .btn-label { display: none; }
}

/* ══════════════════════════════════════
   PANEL CARD
══════════════════════════════════════ */
.panel-card {
    background: var(--surface-0);
    border-radius: var(--radius-lg);
    border: 1px solid var(--surface-border);
    box-shadow: var(--shadow-sm);
    overflow: hidden; height: 100%;
}
.panel-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--surface-border);
    background: var(--surface-0);
}
.panel-title {
    font-family: 'Nunito', sans-serif; font-size: 1rem;
    font-weight: 800; color: var(--text-primary);
    margin: 0; display: flex; align-items: center; gap: 0.6rem;
}
.panel-title-icon {
    width: 28px; height: 28px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem;
}
.panel-count-badge {
    background: var(--surface-2); color: var(--text-secondary);
    font-size: 0.72rem; font-weight: 700; border-radius: 20px;
    padding: 2px 9px; font-family: 'Nunito', sans-serif;
}

/* ══════════════════════════════════════
   TEMPORAL FILTER BAR (Hari Ini / Mendatang / dll)
══════════════════════════════════════ */
.filter-bar-prod {
    display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap;
    padding: 0.7rem 1.25rem;
    background: var(--surface-0);
    border-bottom: 1px solid var(--surface-border);
}
.filter-tab {
    padding: 0.3rem 0.85rem;
    border-radius: 50px;
    border: 1px solid var(--surface-border);
    background: var(--surface-1);
    color: var(--text-secondary);
    font-size: 0.78rem; font-weight: 700;
    font-family: 'Nunito', sans-serif;
    cursor: pointer; text-decoration: none;
    transition: var(--transition);
    display: inline-flex; align-items: center; gap: 0.3rem;
}
.filter-tab:hover { border-color: var(--brand-maroon); color: var(--brand-maroon); }
.filter-tab.active {
    background: var(--brand-maroon); border-color: var(--brand-maroon);
    color: #fff; box-shadow: 0 2px 8px rgba(116,24,71,0.3);
}
.filter-tab.tab-today.active {
    background: linear-gradient(135deg, var(--brand-maroon), #9c2456);
}
.filter-tab.tab-upcoming.active { background: var(--accent-blue); border-color: var(--accent-blue); }
.filter-tab.tab-delegated.active { background: var(--accent-indigo); border-color: var(--accent-indigo); }

.search-input-wrap { position: relative; margin-left: auto; flex-shrink: 0; }
.search-input-wrap i {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    color: var(--text-muted); font-size: 0.78rem; pointer-events: none;
}
.search-input-wrap input {
    padding: 0.3rem 0.85rem 0.3rem 2rem;
    border-radius: 50px; border: 1px solid var(--surface-border);
    background: var(--surface-1); font-size: 0.8rem;
    font-family: 'Nunito', sans-serif; color: var(--text-primary);
    outline: none; width: 180px; transition: var(--transition);
}
.search-input-wrap input:focus {
    border-color: var(--brand-maroon); background: #fff;
    box-shadow: 0 0 0 3px rgba(116,24,71,0.1); width: 220px;
}

/* ══════════════════════════════════════
   SECTION DIVIDERS (Overdue / Hari Ini / Lainnya)
══════════════════════════════════════ */
.section-divider {
    display: flex; align-items: center; gap: 0.65rem;
    padding: 0.5rem 0 0.35rem;
    margin-bottom: 0.5rem; margin-top: 0.25rem;
}
.section-divider-dot {
    width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0;
}
.section-divider-label {
    font-family: 'Nunito', sans-serif; font-size: 0.7rem;
    font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em;
    white-space: nowrap;
}
.section-divider-line {
    flex: 1; height: 1px; background: var(--surface-border);
}
.section-divider-count {
    font-family: 'Nunito', sans-serif; font-size: 0.65rem;
    font-weight: 700; padding: 1px 7px; border-radius: 20px;
    white-space: nowrap;
}
/* overdue */
.divider-overdue .section-divider-dot  { background: var(--accent-red); }
.divider-overdue .section-divider-label { color: var(--accent-red); }
.divider-overdue .section-divider-count { background: #fef2f2; color: var(--accent-red); border: 1px solid #fecaca; }
/* today */
.divider-today .section-divider-dot  { background: var(--brand-maroon); }
.divider-today .section-divider-label { color: var(--brand-maroon); }
.divider-today .section-divider-count { background: var(--brand-maroon-lt); color: var(--brand-maroon); border: 1px solid #e9b8cf; }
/* other */
.divider-other .section-divider-dot  { background: var(--text-muted); }
.divider-other .section-divider-label { color: var(--text-secondary); }
.divider-other .section-divider-count { background: var(--surface-2); color: var(--text-secondary); border: 1px solid var(--surface-border); }

/* ══════════════════════════════════════
   TASK LIST & ITEMS
══════════════════════════════════════ */
.task-panel-body {
    max-height: calc(100vh - 300px);
    overflow-y: auto; padding: 0.25rem 1.25rem 1rem !important;
    scrollbar-width: thin;
    scrollbar-color: var(--surface-border) transparent;
}
.task-panel-body::-webkit-scrollbar { width: 4px; }
.task-panel-body::-webkit-scrollbar-thumb { background: var(--surface-border); border-radius: 4px; }
.section-divider:first-child {
    margin-top: 0;
    padding-top: 0.3rem;
}
.btn-toggle-kanban i { font-size: 0.85rem; } /* Sesuaikan ukuran ikon header */

.task-list { display: flex; flex-direction: column; gap: 0.5rem; }

.task-item {
    background: var(--surface-0);
    border: 1px solid var(--surface-border);
    border-radius: var(--radius-md);
    padding: 0.8rem 1rem;
    display: flex; align-items: flex-start; gap: 0.8rem;
    transition: var(--transition);
    position: relative; overflow: hidden;
}
.task-item::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0;
    width: 3px; background: transparent; transition: var(--transition);
    border-radius: 3px 0 0 3px;
}
.task-item:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); border-color: rgba(116,24,71,0.15); }
.task-item:hover::before { background: var(--brand-maroon); }
.task-item.priority-high::before   { background: var(--accent-red); }
.task-item.priority-medium::before { background: var(--accent-amber); }
.task-item.priority-low::before    { background: var(--accent-blue); }
.task-item.completed { opacity: 0.52; background: var(--surface-1); }
.task-item.completed .task-title-text { text-decoration: line-through; color: var(--text-muted); }
.task-item.completed::before { background: var(--accent-green) !important; }
.task-item.task-overdue { border-color: #fecaca; background: #fffafa; }
.task-item.task-overdue::before { background: var(--accent-red) !important; }

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
.task-check:checked::after { content: '✓'; color: white; font-size: 12px; font-weight: 800; line-height: 1; }

/* Task Content */
.task-content { flex: 1; min-width: 0; }
.task-title-text {
    font-weight: 700; font-size: 0.9rem;
    font-family: 'Nunito', sans-serif;
    color: var(--text-primary); line-height: 1.4;
    cursor: pointer; transition: color 0.15s;
}
.task-title-text:hover { color: var(--brand-maroon); }
.task-meta { display: flex; flex-wrap: wrap; gap: 0.35rem; margin-top: 0.4rem; align-items: center; }

.task-badge {
    font-size: 0.67rem; font-weight: 700;
    font-family: 'Nunito', sans-serif;
    padding: 2px 8px; border-radius: 5px;
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
    padding: 0.28rem 0.7rem; border-radius: 50px;
    border: 1px solid var(--surface-border);
    background: var(--surface-1); color: var(--text-secondary);
    cursor: pointer; outline: none;
}
.sort-select:focus { border-color: var(--brand-maroon); }

/* Buttons */
.btn-brand {
    background: var(--brand-maroon); color: #fff;
    border: none; border-radius: var(--radius-sm);
    padding: 0.45rem 1rem; font-size: 0.82rem; font-weight: 700;
    font-family: 'Nunito', sans-serif; cursor: pointer;
    transition: var(--transition);
    display: inline-flex; align-items: center; gap: 0.4rem;
}
.btn-brand:hover { background: var(--brand-maroon-dk); color: #fff; }
.btn-ghost {
    background: transparent; border: 1px solid var(--surface-border);
    color: var(--text-secondary); border-radius: var(--radius-sm);
    padding: 0.45rem 1rem; font-size: 0.82rem; cursor: pointer;
    transition: var(--transition);
    display: inline-flex; align-items: center; gap: 0.45rem;
    font-family: 'Nunito', sans-serif; font-weight: 700;
}
.btn-ghost:hover { background: var(--surface-border); color: var(--text-primary); }

/* Toggle Kanban/Calendar buttons */
.btn-toggle-kanban {
    display: inline-flex; align-items: center; gap: 0.4rem;
    font-family: 'Nunito', sans-serif; font-size: 0.78rem; font-weight: 700;
    padding: 0.28rem 0.85rem; border-radius: 50px;
    border: 1px solid var(--surface-border);
    background: var(--surface-1); color: var(--text-secondary);
    cursor: pointer; transition: var(--transition);
}
.btn-toggle-kanban:hover, .btn-toggle-kanban.active {
    background: var(--brand-maroon); border-color: var(--brand-maroon);
    color: #fff; box-shadow: 0 2px 8px rgba(116,24,71,0.25);
}

/* Empty state */
.empty-state { text-align: center; padding: 2.5rem 1rem; color: var(--text-muted); }
.empty-state i { font-size: 2.5rem; opacity: 0.2; margin-bottom: 0.75rem; display: block; }
.empty-state p { font-size: 0.875rem; font-family: 'Nunito', sans-serif; }

/* ══════════════════════════════════════
   HABIT TRACKER
══════════════════════════════════════ */
.habit-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.75rem 1rem; border-radius: var(--radius-md);
    background: var(--surface-0); border: 1px solid var(--surface-border);
    transition: var(--transition); margin-bottom: 0.5rem;
}
.habit-item.done { background: #ecfdf5; border-color: #a7f3d0; }
.habit-toggle-btn {
    width: 34px; height: 34px; border-radius: 50%;
    border: 2px solid var(--surface-border);
    background: var(--surface-1); color: var(--text-muted);
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-size: 0.78rem; transition: var(--transition); flex-shrink: 0;
}
.habit-item.done .habit-toggle-btn {
    background: var(--accent-green); border-color: var(--accent-green);
    color: #fff; box-shadow: 0 2px 8px rgba(16,185,129,0.3);
}
.habit-name { font-weight: 700; font-size: 0.875rem; font-family: 'Nunito', sans-serif; color: var(--text-primary); }
.habit-streak { font-size: 0.7rem; font-weight: 700; font-family: 'Nunito', sans-serif; color: var(--accent-amber); }
.habit-progress-bar { height: 5px; background: var(--surface-2); border-radius: 5px; margin: 0.5rem 0 1rem; overflow: hidden; }
.habit-progress-fill { height: 100%; border-radius: 5px; background: linear-gradient(90deg, var(--accent-green), #34d399); transition: width 0.5s ease; }

/* ══════════════════════════════════════
   STICKY NOTES
══════════════════════════════════════ */
.notes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 1.25rem; }
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
.sticky-note .note-actions { position: absolute; top: 8px; right: 8px; display: flex; gap: 4px; opacity: 0; transition: opacity 0.15s; }
.sticky-note:hover .note-actions { opacity: 1; }
.note-action-btn { width: 24px; height: 24px; border-radius: 5px; display: flex; align-items: center; justify-content: center; background: transparent; border: none; cursor: pointer; font-size: 0.72rem; color: rgba(0,0,0,0.4); transition: var(--transition); }
.note-action-btn:hover { background: rgba(0,0,0,0.1); color: rgba(0,0,0,0.7); }
.sticky-note-title { font-family: 'Nunito', sans-serif; font-size: 0.95rem; font-weight: 800; margin-bottom: 0.5rem; color: rgba(0,0,0,0.8); }
.sticky-note-content { white-space: pre-wrap; font-family: 'Nunito', sans-serif; font-size: 0.82rem; color: rgba(0,0,0,0.65); flex-grow: 1; line-height: 1.6; }
.sticky-note-content p { margin-bottom: 0.3rem; }
.sticky-note-content ul, .sticky-note-content ol { padding-left: 1.2rem; margin-bottom: 0.3rem; }
.sticky-note-content strong { font-weight: 800; }
.sticky-note-footer { font-size: 0.68rem; font-family: 'Nunito', sans-serif; color: rgba(0,0,0,0.35); margin-top: 0.75rem; padding-top: 0.6rem; border-top: 1px solid rgba(0,0,0,0.08); }

/* ══════════════════════════════════════
   MODALS
══════════════════════════════════════ */
.modal-cmd .modal-content { border-radius: var(--radius-xl); border: none; box-shadow: var(--shadow-lg), 0 0 0 1px rgba(116,24,71,0.08); overflow: hidden; font-family: 'Nunito', sans-serif; }
.modal-cmd .modal-header { background: linear-gradient(135deg, var(--brand-maroon), #9c2456); color: #fff; border: none; padding: 1.2rem 1.5rem; }
.modal-cmd .modal-title { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 1rem; }
.modal-cmd .btn-close { filter: invert(1) brightness(2); }
.modal-cmd .modal-body { padding: 1.35rem 1.5rem; background: var(--surface-0); }
.modal-cmd .modal-footer { border-top: 1px solid var(--surface-border); background: var(--surface-1); padding: 0.9rem 1.5rem; }
.modal-cmd label { font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); font-family: 'Nunito', sans-serif; }
.modal-cmd .form-control, .modal-cmd .form-select { font-family: 'Nunito', sans-serif; font-size: 0.875rem; border-color: var(--surface-border); border-radius: var(--radius-sm); }
.modal-cmd .form-control:focus, .modal-cmd .form-select:focus { border-color: var(--brand-maroon); box-shadow: 0 0 0 3px rgba(116,24,71,0.1); }

/* Quick Add Input */
.task-quick-input { font-family: 'Nunito', sans-serif; font-size: 1.15rem; font-weight: 700; border: none; border-bottom: 2px solid var(--surface-border); border-radius: 0; padding: 1rem 1.5rem; background: transparent; color: var(--text-primary); transition: var(--transition); width: 100%; }
.task-quick-input:focus { outline: none; border-bottom-color: var(--brand-maroon); box-shadow: none; }
.quick-toolbar { padding: 0.8rem 1.5rem; background: var(--surface-1); display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap; border-bottom: 1px solid var(--surface-border); }
.quick-toolbar label { font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); font-family: 'Nunito', sans-serif; margin: 0; }
.quick-select { font-family: 'Nunito', sans-serif; font-size: 0.8rem; padding: 0.28rem 0.6rem; border-radius: var(--radius-sm); border: 1px solid var(--surface-border); background: var(--surface-0); color: var(--text-primary); cursor: pointer; }
.quick-select:focus { border-color: var(--brand-maroon); outline: none; }
.date-quick-btn { font-family: 'Nunito', sans-serif; font-size: 0.75rem; font-weight: 700; padding: 0.28rem 0.7rem; border-radius: 50px; border: 1px solid var(--surface-border); background: var(--surface-0); color: var(--text-secondary); cursor: pointer; transition: var(--transition); }
.date-quick-btn:hover { border-color: var(--brand-maroon); color: var(--brand-maroon); }
.date-quick-btn.active { background: var(--brand-maroon); border-color: var(--brand-maroon); color: #fff; }
.task-desc-textarea { font-family: 'Nunito', sans-serif; font-size: 0.875rem; border: 1px solid var(--surface-border); border-radius: var(--radius-sm); padding: 0.6rem 0.9rem; width: 100%; resize: vertical; min-height: 80px; outline: none; transition: var(--transition); }
.task-desc-textarea:focus { border-color: var(--brand-maroon); box-shadow: 0 0 0 3px rgba(116,24,71,0.1); }

/* Color picker */
.color-picker-group { display: flex; gap: 8px; align-items: center; }
.color-circle { width: 26px; height: 26px; border-radius: 50%; cursor: pointer; border: 2px solid transparent; transition: transform 0.15s, box-shadow 0.15s; }
.color-circle:hover { transform: scale(1.15); }
.color-radio:checked + .color-circle { border-color: #374151; box-shadow: 0 0 0 2px #fff inset; transform: scale(1.15); }

/* ══════════════════════════════════════
   KANBAN BOARD
══════════════════════════════════════ */
.kanban-board-wrap { padding: 1rem 1.25rem 1.25rem; overflow-x: auto; scrollbar-width: thin; scrollbar-color: var(--surface-border) transparent; }
.kanban-board-wrap::-webkit-scrollbar { height: 5px; }
.kanban-board-wrap::-webkit-scrollbar-thumb { background: var(--surface-border); border-radius: 5px; }
.kanban-row { display: flex; gap: 1rem; min-width: 760px; height: calc(100vh - 320px); min-height: 400px; }
.kanban-col { flex: 1; display: flex; flex-direction: column; border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--surface-border); background: var(--surface-1); min-width: 220px; }
.kanban-col-header { display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; border-bottom: 1px solid var(--surface-border); background: var(--surface-0); flex-shrink: 0; }
.kanban-col-title { font-family: 'Nunito', sans-serif; font-size: 0.8rem; font-weight: 800; display: flex; align-items: center; gap: 0.45rem; color: var(--text-primary); text-transform: uppercase; letter-spacing: 0.04em; }
.kanban-col-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.dot-pending   { background: #94a3b8; }
.dot-progress  { background: var(--accent-blue); }
.dot-completed { background: var(--accent-green); }
.kanban-col-count { font-family: 'Nunito', sans-serif; font-size: 0.7rem; font-weight: 800; padding: 2px 8px; border-radius: 20px; background: var(--surface-2); color: var(--text-secondary); border: 1px solid var(--surface-border); min-width: 22px; text-align: center; transition: var(--transition); }
.kanban-col[data-col="pending"]     .kanban-col-count { background:#f1f5f9; color:#64748b; }
.kanban-col[data-col="in_progress"] .kanban-col-count { background:#eff6ff; color:#2563eb; border-color:#bfdbfe; }
.kanban-col[data-col="completed"]   .kanban-col-count { background:#ecfdf5; color:#059669; border-color:#a7f3d0; }
.kanban-list { flex: 1; overflow-y: auto; overflow-x: hidden; padding: 0.75rem 0.65rem; display: flex; flex-direction: column; gap: 0.55rem; scrollbar-width: thin; scrollbar-color: var(--surface-border) transparent; }
.kanban-list::-webkit-scrollbar { width: 4px; }
.kanban-list::-webkit-scrollbar-thumb { background: var(--surface-border); border-radius: 4px; }
.kanban-list.drag-over { background: rgba(116,24,71,0.04); border-radius: var(--radius-sm); outline: 2px dashed rgba(116,24,71,0.2); outline-offset: -2px; }
.kanban-empty { text-align: center; padding: 2rem 1rem; color: var(--text-muted); font-size: 0.78rem; font-family: 'Nunito', sans-serif; font-weight: 600; border: 2px dashed var(--surface-border); border-radius: var(--radius-md); margin: 0.25rem 0; }
.kanban-empty i { font-size: 1.5rem; opacity: 0.2; display: block; margin-bottom: 0.5rem; }

/* ══════════════════════════════════════
   KANBAN CARD
══════════════════════════════════════ */
.kanban-card { background: var(--surface-0); border-radius: var(--radius-md); border: 1px solid var(--surface-border); overflow: hidden; cursor: grab; transition: box-shadow 0.18s ease, transform 0.18s ease; user-select: none; }
.kanban-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
.kanban-card.sortable-ghost { opacity: 0.4; }
.kanban-card.sortable-drag { box-shadow: var(--shadow-lg); cursor: grabbing; }
.kc-priority-bar { height: 3px; width: 100%; }
.kc-body { padding: 0.75rem 0.85rem; }
.kanban-list[data-status="completed"] .kc-title {
    text-decoration: line-through;
    color: var(--text-muted);
}
.kc-badges { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 0.5rem; }
.kc-badge { font-size: 0.62rem; font-weight: 700; font-family: 'Nunito', sans-serif; padding: 2px 7px; border-radius: 5px; display: inline-flex; align-items: center; gap: 3px; }
.kc-badge-tag     { background: var(--surface-2); color: var(--text-secondary); border: 1px solid var(--surface-border); }
.kc-badge-green   { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.kc-badge-indigo  { background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
.kc-badge-rose    { background: #fce7f3; color: #be185d; border: 1px solid #fbcfe8; }
.kc-title { font-family: 'Nunito', sans-serif; font-size: 0.85rem; font-weight: 800; color: var(--text-primary); line-height: 1.4; cursor: pointer; margin-bottom: 0.3rem; }
.kc-title:hover { color: var(--brand-maroon); }
.kc-desc { font-size: 0.75rem; font-family: 'Nunito', sans-serif; color: var(--text-secondary); line-height: 1.5; margin-bottom: 0.5rem; }
.kc-subtask-wrap { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; }
.kc-subtask-bar { flex: 1; height: 4px; background: var(--surface-2); border-radius: 4px; overflow: hidden; }
.kc-subtask-fill { height: 100%; background: linear-gradient(90deg, var(--accent-green), #34d399); border-radius: 4px; transition: width 0.4s; }
.kc-subtask-label { font-size: 0.65rem; font-weight: 700; font-family: 'Nunito', sans-serif; color: var(--text-muted); white-space: nowrap; }
.kc-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 0.35rem; }
.kc-footer-left { min-width: 0; }
.kc-footer-right { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
.kc-deadline { font-size: 0.68rem; font-weight: 700; font-family: 'Nunito', sans-serif; color: var(--text-secondary); display: flex; align-items: center; gap: 3px; }
.kc-overdue { color: var(--accent-red); }
.kc-today   { color: var(--accent-amber); }
.kc-no-deadline { font-size: 0.68rem; font-family: 'Nunito', sans-serif; color: var(--text-muted); display: flex; align-items: center; gap: 2px; }
.kc-meta-icon { font-size: 0.68rem; font-weight: 700; font-family: 'Nunito', sans-serif; color: var(--text-muted); display: flex; align-items: center; gap: 2px; }
.kc-priority-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

/* ══════════════════════════════════════
   POMODORO
══════════════════════════════════════ */
#pomodoro-widget { position: fixed; bottom: 24px; right: 24px; width: 265px; border-radius: var(--radius-xl); overflow: hidden; z-index: 1050; box-shadow: var(--shadow-lg), 0 0 0 1px rgba(116,24,71,0.12); transform: translateY(130%) scale(0.95); opacity: 0; transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); }
#pomodoro-widget.show { transform: translateY(0) scale(1); opacity: 1; }
.pomo-header { background: var(--brand-maroon); color: #fff; padding: 0.85rem 1.2rem; display: flex; justify-content: space-between; align-items: center; font-weight: 800; font-size: 0.875rem; font-family: 'Nunito', sans-serif; }
.pomo-body { padding: 1.4rem; text-align: center; background: #fff; }
.pomo-time { font-family: 'Montserrat', sans-serif; font-size: 3rem; color: var(--text-primary); line-height: 1; letter-spacing: -2px; margin-bottom: 0.3rem; }
.pomo-label { font-size: 0.78rem; color: var(--text-muted); font-weight: 600; font-family: 'Nunito', sans-serif; margin-bottom: 1.2rem; }

/* CKEditor */
.ck.ck-editor__main > .ck-editor__editable { background-color: transparent !important; border: none !important; box-shadow: none !important; color: #111827 !important; min-height: 120px; padding: 0 !important; }
.ck.ck-toolbar { background-color: rgba(255,255,255,0.4) !important; border: none !important; border-bottom: 1px dashed rgba(0,0,0,0.1) !important; border-radius: 8px !important; margin-bottom: 10px; }
.ck.ck-editor__editable.ck-focused { outline: none !important; border: none !important; }

/* Animations */
@keyframes fadeSlideUp {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.task-item { animation: fadeSlideUp 0.2s ease both; }
.task-item:nth-child(1) { animation-delay: 0.02s; }
.task-item:nth-child(2) { animation-delay: 0.05s; }
.task-item:nth-child(3) { animation-delay: 0.08s; }
.task-item:nth-child(4) { animation-delay: 0.11s; }
.task-item:nth-child(5) { animation-delay: 0.14s; }
</style>
@endsection

@section('content')
@php
    $hour = (int) now()->format('H');
    if ($hour < 11)       $greeting = '🌅 Selamat Pagi';
    elseif ($hour < 15)   $greeting = '☀️ Selamat Siang';
    elseif ($hour < 18)   $greeting = '🌤️ Selamat Sore';
    else                  $greeting = '🌙 Selamat Malam';

    $absenText  = $isLibur ? 'Libur' : 'Belum Absen';
    $absenColor = 'rgba(255,255,255,0.15)';
    $absenIcon  = $isLibur ? 'fas fa-calendar-day' : 'fas fa-fingerprint';
    $batasPulang = now()->isFriday() ? '15:00' : '15:30';

    if (isset($absensiHariIni)) {
        $checkIn  = $absensiHariIni->jam_masuk;
        $checkOut = $absensiHariIni->jam_keluar;
        
        if ($isLibur) {
            // --- LOGIKA HARI LIBUR (LEMBUR) ---
            if ($checkIn && (!$checkOut || $checkOut == '-')) {
                $absenText  = 'Lembur Aktif';
                $absenColor = '#f59e0b'; // Amber
                $absenIcon  = 'fas fa-stopwatch';
            } elseif ($checkIn && $checkOut && $checkOut != '-') {
                // Hitung durasi lembur
                $start = \Carbon\Carbon::parse($checkIn);
                $end   = \Carbon\Carbon::parse($checkOut);
                $durasi = $start->diffInHours($end);

                if ($durasi >= 4) {
                    $absenText  = 'Lembur Selesai (' . $durasi . ' Jam)';
                    $absenColor = '#6366f1'; // Indigo
                } else {
                    $absenText  = 'Presensi Libur (< 4 Jam)';
                    $absenColor = '#94a3b8'; // Grey
                }
                $absenIcon = 'fas fa-check-double';
            }
        } else {
            // --- LOGIKA HARI KERJA ---
            if ($checkIn && ($checkOut == $checkIn)) {
                $absenText  = 'Masuk: ' . substr($checkIn, 0, 5);
                $absenColor = $absensiHariIni->status == 'terlambat' ? '#ef4444' : '#10b981';
                $absenIcon  = 'fas fa-sign-in-alt';
            } elseif ($checkIn && $checkOut && $checkOut != '-') {
                if ($checkOut < $batasPulang) {
                    $absenText  = 'Pulang Awal: ' . substr($checkOut, 0, 5);
                    $absenColor = '#f59e0b'; // Warning
                    $absenIcon  = 'fas fa-door-open';
                } else {
                    $absenText  = 'Selesai: ' . substr($checkIn, 0, 5) . "-" . substr($checkOut, 0, 5);
                    $absenColor = '#3b82f6';
                    $absenIcon  = 'fas fa-user-check';
                }
            }
           
        }
    }
    $dailyPct = $statsTodayTotal > 0 ? round(($statsTodayDone / $statsTodayTotal) * 100) : 0;
    $remainingToday = $statsTodayTotal - $statsTodayDone;
@endphp

<div class="container-fluid p-0">

    @php
        $hour = (int) now()->format('H');
        if ($hour < 11) { $greeting = 'Selamat Pagi'; $emoji = '🌅'; }
        elseif ($hour < 15) { $greeting = 'Selamat Siang'; $emoji = '☀️'; }
        elseif ($hour < 18) { $greeting = 'Selamat Sore'; $emoji = '🌤️'; }
        else { $greeting = 'Selamat Malam'; $emoji = '🌙'; }
    @endphp

    {{-- ══════════════════════════════════
         HEADER — Daily Command Center
    ══════════════════════════════════ --}}
    <div class="cmd-header">

        {{-- Kiri: Profil (atas) + Stats (bawah) --}}
        <div class="d-flex flex-column gap-2" style="z-index:1;position:relative;">
            <div class="header-profile-section">
                <div class="greeting-wrapper">
                    <span class="greeting-icon">{{ $emoji }}</span>
                    <h2 class="cmd-header-title">{{ $greeting }}, {{ Auth::user()->name }}</h2>
                </div>
                <div class="subtitle-wrapper d-flex align-items-center flex-wrap" style="gap: 8px;">
                    <div class="d-flex align-items-center">
                        <span class="subtitle-icon"></span>
                        <span>{{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}</span>
                    </div>
                    
                    {{-- Dot Separator --}}
                    <div style="width: 4px; height: 4px; background: rgba(255,255,255,0.4); border-radius: 50%;"></div>
                    
                    {{-- Badge Status Absen --}}
                    <span style="background: {{ $absenColor }}; color: #ffffff; padding: 2px 12px; border-radius: 20px; font-weight: 800; font-size: 0.65rem; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                        <i class="{{ $absenIcon }}"></i> 
                        {{ $absenText }}
                        
                        {{-- Tambahan info jika sedang lembur --}}
                        @if($isLibur && isset($absensiHariIni) && (!$absensiHariIni->jam_keluar || $absensiHariIni->jam_keluar == '-'))
                            <small style="opacity: 0.8; font-weight: 400; border-left: 1px solid rgba(255,255,255,0.3); padding-left: 5px; margin-left: 2px;">
                                Sejak {{ substr($absensiHariIni->jam_masuk, 0, 5) }}
                            </small>
                        @endif
                    </span>
                </div>
            </div>

            <div class="header-stats-grid">
                @if($statsOverdue > 0)
                <div class="stat-bento danger">
                    <div class="stat-bento-value">{{ $statsOverdue }}</div>
                    <div class="stat-bento-label"><i class="fas fa-exclamation-circle me-1"></i>Terlambat</div>
                </div>
                @endif
                <div class="stat-bento warning">
                    <div class="stat-bento-value">{{ $statsPending }}</div>
                    <div class="stat-bento-label"><i class="fas fa-clock me-1"></i>Aktif</div>
                </div>
                <div class="stat-bento success">
                    <div class="stat-bento-value">{{ $statsCompleted }}</div>
                    <div class="stat-bento-label"><i class="fas fa-check-double me-1"></i>Selesai</div>
                </div>
            </div>
        </div>

        {{-- Kanan: Progress (atas) + Toolbar (bawah) --}}
        <div class="d-flex flex-column align-items-end gap-2" style="z-index:1;">
            <div class="daily-progress-wrap">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="stat-bento-label" style="color:rgba(255,255,255,0.75);">Progress Harian</div>
                    <div id="daily-progress-pct" style="font-family:'Montserrat',sans-serif;font-weight:800;font-size:0.9rem;color:#fff;">{{ $dailyPct }}%</div>
                </div>
                <div class="daily-progress-bar">
                    <div class="daily-progress-fill" id="daily-progress-fill" style="width: {{ $dailyPct }}%;"></div>
                </div>
                <div class="daily-progress-count mt-2 mb-0" id="daily-progress-text">
                    @if($dailyPct === 100 && $statsTodayTotal > 0) 🎉 Kerja bagus! Semua selesai.
                    @elseif($remainingToday > 0) <i class="fas fa-bullseye text-warning me-1"></i> {{ $remainingToday }} tugas tersisa hari ini.
                    @else Belum ada target hari ini.
                    @endif
                </div>
            </div>

            <div class="header-toolbar">
                <button onclick="openTaskModal()" class="header-btn">
                    <i class="fas fa-plus"></i> Tugas Baru
                </button>
                <a href="{{ route('admin.productivity.routine.index') }}" class="header-btn">
                    <i class="fas fa-clipboard-list"></i> Rutinan
                </a>
                <button onclick="togglePomodoro()" class="header-btn">
                    <i class="fas fa-bolt text-warning"></i> Focus
                </button>
                <button class="header-btn px-3" data-bs-toggle="modal" data-bs-target="#settingsModal">
                    <i class="fas fa-cog"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════
         MAIN CONTENT — 2 column layout
    ══════════════════════════════════ --}}
    <div class="row g-4 mb-4">

        {{-- ── TASK PANEL (left, wider) ── --}}
        <div class="col-xl-8 col-lg-7">
            <div class="panel-card">
                <div class="panel-header">
                    <h2 class="panel-title">
                        <span class="panel-title-icon" style="background:var(--brand-maroon-lt);">
                            <i class="fas fa-check-square" style="color:var(--brand-maroon);"></i>
                        </span>
                        @if($filter === 'today') Tugas Hari Ini
                        @elseif($filter === 'upcoming') Tugas Mendatang
                        @elseif($filter === 'delegated') Tugas Delegasi
                        @elseif($filter === 'archived') Arsip Tugas
                        @else Semua Tugas
                        @endif
                        <span class="panel-count-badge">{{ $tasks->count() }}</span>
                    </h2>
                    
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex bg-light rounded-pill border p-1" style="gap:2px;">
                            <button class="btn-toggle-kanban active" id="btnViewList" title="Tampilan Daftar" style="margin:0; border:none;">
                                <i class="fas fa-list"></i>
                            </button>
                            <button class="btn-toggle-kanban" id="btnViewKanban" title="Tampilan Kanban" style="margin:0; border:none;">
                                <i class="fas fa-columns"></i>
                            </button>
                            <button class="btn-toggle-kanban" id="btnViewCalendar" title="Tampilan Kalender" style="margin:0; border:none;">
                                <i class="far fa-calendar-alt"></i>
                            </button>
                        </div>

                        <div style="width:1px; height:24px; background:var(--surface-border); margin:0 4px;"></div>

                        <select class="sort-select" id="sortSelect" onchange="applyFilters()">
                            <option value="deadline" {{ $sort=='deadline' ? 'selected':'' }}>↑ Deadline</option>
                            <option value="priority" {{ $sort=='priority' ? 'selected':'' }}>↑ Prioritas</option>
                            <option value="created"  {{ $sort=='created'  ? 'selected':'' }}>↑ Terbaru</option>
                        </select>
                    </div>
                </div>

                {{-- ── Temporal Filter Bar ── --}}
                <div class="filter-bar-prod">
                    <a href="#" onclick="setFilter('today',event)"
                       class="filter-tab tab-today {{ $filter=='today' ? 'active':'' }}">
                        <i class="fas fa-sun"></i> Hari Ini
                        @if($statsOverdue > 0 && $filter !== 'today')
                            <span style="background:#ef4444;color:#fff;border-radius:50%;width:16px;height:16px;display:inline-flex;align-items:center;justify-content:center;font-size:0.6rem;font-weight:800;">{{ $statsOverdue }}</span>
                        @endif
                    </a>
                    <a href="#" onclick="setFilter('upcoming',event)"
                       class="filter-tab tab-upcoming {{ $filter=='upcoming' ? 'active':'' }}">
                        <i class="fas fa-calendar-week"></i> Mendatang
                    </a>
                    <a href="#" onclick="setFilter('all',event)"
                       class="filter-tab {{ $filter=='all' ? 'active':'' }}">
                        <i class="fas fa-list"></i> Semua
                    </a>
                    <a href="#" onclick="setFilter('delegated',event)"
                       class="filter-tab tab-delegated {{ $filter=='delegated' ? 'active':'' }}">
                        <i class="fas fa-paper-plane"></i> Delegasi
                        @if($statsDelegated > 0)
                            <span class="ms-1 px-2 rounded-pill" style="font-size:0.65rem;background:{{ $filter=='delegated' ? '#fff' : 'var(--accent-indigo)' }};color:{{ $filter=='delegated' ? '#4338ca' : '#fff' }};font-weight:800;">{{ $statsDelegated }}</span>
                        @endif
                    </a>
                    <a href="#" onclick="setFilter('archived',event)"
                       class="filter-tab {{ $filter=='archived' ? 'active':'' }}">
                        <i class="fas fa-archive"></i> Arsip
                    </a>

                    @if($allTags->count() > 0)
                    <select class="sort-select" id="tagFilter" onchange="applyFilters()" style="border-radius:6px;">
                        <option value="">Semua Tag</option>
                        @foreach($allTags as $t)
                            <option value="{{ $t }}" {{ $tag==$t ? 'selected':'' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                    @endif

                    <div class="search-input-wrap ms-2">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Cari tugas..."
                               value="{{ $search }}"
                               onkeydown="if(event.key==='Enter') applyFilters()">
                    </div>
                </div>

                {{-- ══ TASK LIST VIEW ══ --}}
                <div class="task-panel-body" id="task-container">

                    @if($filter === 'today')
                        {{-- ── Section: OVERDUE ── --}}
                        @if($tasksOverdueGroup->count() > 0)
                        <div class="section-divider divider-overdue">
                            <div class="section-divider-dot"></div>
                            <div class="section-divider-label"><i class="fas fa-exclamation-circle me-1"></i>Terlambat</div>
                            <div class="section-divider-line"></div>
                            <div class="section-divider-count">{{ $tasksOverdueGroup->count() }} tugas</div>
                        </div>
                        <div class="task-list mb-3">
                            @foreach($tasksOverdueGroup as $task)
                                @include('admin.productivity.partials.task-item', ['task' => $task, 'forceOverdue' => true])
                            @endforeach
                        </div>
                        @endif

                        {{-- ── Section: HARI INI ── --}}
                        @if($tasksTodayGroup->count() > 0)
                        <div class="section-divider divider-today">
                            <div class="section-divider-dot"></div>
                            <div class="section-divider-label"><i class="fas fa-sun me-1"></i>Hari Ini</div>
                            <div class="section-divider-line"></div>
                            <div class="section-divider-count">{{ $tasksTodayGroup->count() }} tugas</div>
                        </div>
                        <div class="task-list mb-3">
                            @foreach($tasksTodayGroup as $task)
                                @include('admin.productivity.partials.task-item', ['task' => $task])
                            @endforeach
                        </div>
                        @endif

                        {{-- ── Section: IN PROGRESS (tanpa deadline) ── --}}
                        @if($tasksOtherGroup->count() > 0)
                        <div class="section-divider divider-other">
                            <div class="section-divider-dot"></div>
                            <div class="section-divider-label">Sedang Berjalan</div>
                            <div class="section-divider-line"></div>
                            <div class="section-divider-count">{{ $tasksOtherGroup->count() }} tugas</div>
                        </div>
                        <div class="task-list mb-3">
                            @foreach($tasksOtherGroup as $task)
                                @include('admin.productivity.partials.task-item', ['task' => $task])
                            @endforeach
                        </div>
                        @endif

                        @if($tasksOverdueGroup->count() === 0 && $tasksTodayGroup->count() === 0 && $tasksOtherGroup->count() === 0)
                        <div class="empty-state">
                            <i class="fas fa-check-double" style="opacity:0.15;font-size:3rem;"></i>
                            <p style="font-weight:800;font-size:1rem;color:var(--text-secondary);">Tidak ada tugas untuk hari ini! 🎉</p>
                            <p style="font-size:0.8rem;">Hari yang produktif. Cek tab <strong>Mendatang</strong> untuk persiapan besok.</p>
                        </div>
                        @endif

                    @else
                        {{-- ── View Non-Today: flat list ── --}}
                        <div class="task-list">
                            @forelse($tasks as $task)
                                @include('admin.productivity.partials.task-item', ['task' => $task])
                            @empty
                                <div class="empty-state">
                                    <i class="fas fa-clipboard-check"></i>
                                    <p>
                                        @if($filter==='archived') Belum ada tugas yang diarsipkan.
                                        @elseif($filter==='delegated') Belum ada tugas delegasi aktif.
                                        @elseif($filter==='upcoming') Tidak ada tugas mendatang. Santai dulu! 😎
                                        @else Tidak ada tugas di sini.
                                        @endif
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    @endif
                </div>

                {{-- ══ KANBAN VIEW ══ --}}
                <div class="kanban-board-wrap d-none" id="kanban-container">
                    <div class="kanban-row">
                        <div class="kanban-col" data-col="pending">
                            <div class="kanban-col-header">
                                <div class="kanban-col-title">
                                    <span class="kanban-col-dot dot-pending"></span> Pending
                                </div>
                                <span class="kanban-col-count" id="kc-count-pending">{{ $tasks->where('status','pending')->count() }}</span>
                            </div>
                            <div class="kanban-list" data-status="pending" id="kanban-list-pending">
                                @forelse($tasks->where('status','pending') as $task)
                                    @include('admin.productivity.partials.kanban-card', ['task' => $task])
                                @empty
                                    <div class="kanban-empty"><i class="far fa-clipboard"></i> Tidak ada tugas pending</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="kanban-col" data-col="in_progress">
                            <div class="kanban-col-header">
                                <div class="kanban-col-title">
                                    <span class="kanban-col-dot dot-progress"></span> In Progress
                                </div>
                                <span class="kanban-col-count" id="kc-count-in_progress">{{ $tasks->where('status','in_progress')->count() }}</span>
                            </div>
                            <div class="kanban-list" data-status="in_progress" id="kanban-list-in_progress">
                                @forelse($tasks->where('status','in_progress') as $task)
                                    @include('admin.productivity.partials.kanban-card', ['task' => $task])
                                @empty
                                    <div class="kanban-empty"><i class="fas fa-hourglass-half"></i> Tidak ada tugas berjalan</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="kanban-col" data-col="completed">
                            <div class="kanban-col-header">
                                <div class="kanban-col-title">
                                    <span class="kanban-col-dot dot-completed"></span> Selesai
                                </div>
                                <span class="kanban-col-count" id="kc-count-completed">{{ $tasks->where('status','completed')->count() }}</span>
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

                {{-- ══ CALENDAR VIEW ══ --}}
                <div class="d-none" id="calendar-container" style="padding: 1rem 1.25rem; min-height: 60vh; background: var(--surface-0);">
                    <div id="calendar" style="font-family: 'Nunito', sans-serif;"></div>
                </div>
            </div>
        </div>

        {{-- ── HABIT TRACKER (right) ── --}}
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

    {{-- ══════════════════════════════════
         STICKY NOTES
    ══════════════════════════════════ --}}
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

{{-- ══════════════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════════════ --}}

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
                <div class="quick-toolbar d-block">
                    <div class="row g-3">
                        <div class="col-md-6 d-flex align-items-center gap-2">
                            <label style="width: 70px; margin:0;">Prioritas:</label>
                            <select name="priority" class="quick-select flex-grow-1">
                                <option value="low">🔵 Rendah</option>
                                <option value="medium" selected>🟡 Sedang</option>
                                <option value="high">🔴 Tinggi</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-center gap-2">
                            <label style="width: 40px; margin:0;">Tag:</label>
                            <div class="flex-grow-1" style="min-width:0;">
                                <select name="tag" id="taskTagSelect" class="form-select form-select-sm w-100">
                                    <option value="">Tanpa Tag</option>
                                    @foreach($allTags as $t)
                                        <option value="{{ $t }}">{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 d-flex align-items-center flex-wrap gap-2">
                            <label style="width: 70px; margin:0;">Deadline:</label>
                            <button type="button" class="date-quick-btn active" id="taskBtnNone"
                                    onclick="setQuickDate('', '#taskDeadlineInput', '#taskModal')">Tanpa</button>
                            <button type="button" class="date-quick-btn"
                                    onclick="setQuickDate('today', '#taskDeadlineInput', '#taskModal')">Hari Ini</button>
                            <button type="button" class="date-quick-btn"
                                    onclick="setQuickDate('tomorrow', '#taskDeadlineInput', '#taskModal')">Besok</button>
                            <input type="datetime-local" name="deadline_at" id="taskDeadlineInput" class="quick-select flex-grow-1" style="font-size:0.78rem; min-width:150px;">
                        </div>

                        <div class="col-md-6 d-flex align-items-center gap-2">
                            <label style="width: 70px; margin:0;">Berulang:</label>
                            <select name="recurrence" class="quick-select flex-grow-1">
                                <option value="none">Sekali</option>
                                <option value="daily">Harian</option>
                                <option value="weekly">Mingguan</option>
                                <option value="monthly">Bulanan</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-center gap-2">
                            <label style="width: 65px; margin:0;">Delegasi:</label>
                            <select name="assigned_to" id="taskAssigneeSelect" class="quick-select flex-grow-1">
                                <option value="{{ Auth::id() }}">Diri Sendiri</option>
                                @foreach($coworkers as $cw)
                                    <option value="{{ $cw->id }}">{{ $cw->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <label class="mb-1">Deskripsi (opsional):</label>
                    <textarea name="description" class="task-desc-textarea" placeholder="Catatan tambahan..."></textarea>
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
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-pencil-alt me-2"></i>Edit Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditTask">
                <input type="hidden" id="editTaskId">
                <input type="text" name="title" id="editTaskTitle" class="task-quick-input" required placeholder="Judul tugas...">
                <div class="quick-toolbar d-block">
                    <div class="row g-3">
                        <div class="col-md-6 d-flex align-items-center gap-2">
                            <label style="width: 70px; margin:0;">Prioritas:</label>
                            <select name="priority" id="editTaskPriority" class="quick-select flex-grow-1">
                                <option value="low">🔵 Low</option>
                                <option value="medium">🟡 Medium</option>
                                <option value="high">🔴 High</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-center gap-2">
                            <label style="width: 40px; margin:0;">Tag:</label>
                            <div class="flex-grow-1" style="min-width:0;">
                                <select name="tag" id="editTaskTag" class="form-select form-select-sm w-100">
                                    <option value="">Tanpa Tag</option>
                                    @foreach($allTags as $t)
                                        <option value="{{ $t }}">{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 d-flex align-items-center flex-wrap gap-2">
                            <label style="width: 70px; margin:0;">Deadline:</label>
                            <button type="button" class="date-quick-btn" id="editBtnNone"
                                    onclick="setQuickDate('', '#editTaskDeadline', '#editTaskModal')">Tanpa</button>
                            <button type="button" class="date-quick-btn"
                                    onclick="setQuickDate('today', '#editTaskDeadline', '#editTaskModal')">Hari Ini</button>
                            <button type="button" class="date-quick-btn"
                                    onclick="setQuickDate('tomorrow', '#editTaskDeadline', '#editTaskModal')">Besok</button>
                            <input type="datetime-local" name="deadline_at" id="editTaskDeadline" class="quick-select flex-grow-1" style="font-size:0.78rem; min-width:150px;">
                        </div>

                        <div class="col-md-6 d-flex align-items-center gap-2">
                            <label style="width: 70px; margin:0;">Berulang:</label>
                            <select name="recurrence" id="editTaskRecurrence" class="quick-select flex-grow-1">
                                <option value="none">Sekali</option>
                                <option value="daily">Harian</option>
                                <option value="weekly">Mingguan</option>
                                <option value="monthly">Bulanan</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-center gap-2">
                            <label style="width: 65px; margin:0;">Delegasi:</label>
                            <select name="assigned_to" id="editTaskAssignee" class="quick-select flex-grow-1">
                                <option value="{{ Auth::id() }}">Diri Sendiri</option>
                                @foreach($coworkers as $cw)
                                    <option value="{{ $cw->id }}">{{ $cw->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-body" style="padding-top:0.75rem;">
                    <label class="mb-1">Deskripsi:</label>
                    <textarea name="description" id="editTaskDesc" class="task-desc-textarea mb-3" placeholder="Catatan tambahan..."></textarea>

                    <div class="row border-top pt-3 mt-2">
                        <div class="col-md-6 border-end">
                            <label class="mb-2 text-primary"><i class="fas fa-tasks"></i> Sub-Task (Checklist)</label>
                            <div class="d-flex gap-2 mb-2">
                                <input type="text" id="newSubTaskTitle" class="form-control form-control-sm" placeholder="Tambah item baru..." style="font-family:'Nunito',sans-serif;">
                                <button type="button" class="btn btn-sm btn-brand" id="btnAddSubTask"><i class="fas fa-plus"></i></button>
                            </div>
                            <div id="subTaskList" class="d-flex flex-column gap-1" style="max-height:150px;overflow-y:auto;"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="mb-2 text-primary"><i class="fas fa-paperclip"></i> Lampiran File</label>
                            <div class="d-flex gap-2 mb-2">
                                <input type="file" id="newAttachmentFile" class="form-control form-control-sm" style="font-family:'Nunito',sans-serif;">
                                <button type="button" class="btn btn-sm btn-brand" id="btnUploadAttachment"><i class="fas fa-upload"></i></button>
                            </div>
                            <div id="attachmentList" class="d-flex flex-column gap-1" style="max-height:150px;overflow-y:auto;"></div>
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

{{-- MODAL: VIEW TASK --}}
<div class="modal fade modal-cmd" id="viewTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#4b5563,#1f2937);">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i> Detail Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <h3 id="viewTaskTitle" class="fw-bold mb-1" style="font-family:'Montserrat',sans-serif;color:var(--text-primary);"></h3>
                    <div id="viewTaskMeta" class="d-flex flex-wrap gap-2"></div>
                </div>
                <div class="mb-4">
                    <label class="text-muted fw-bold small text-uppercase mb-2 d-block">Deskripsi</label>
                    <div id="viewTaskDesc" class="p-3 rounded bg-light border" style="font-family:'Nunito',sans-serif;font-size:0.9rem;min-height:60px;white-space:pre-wrap;"></div>
                </div>
                <div class="row">
                    <div class="col-md-6 border-end">
                        <label class="text-muted fw-bold small text-uppercase mb-2 d-block">Checklist Progres</label>
                        <div id="viewSubTaskList" class="d-flex flex-column gap-2"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted fw-bold small text-uppercase mb-2 d-block">Lampiran File</label>
                        <div id="viewAttachmentList" class="d-flex flex-column gap-2"></div>
                    </div>
                </div>
                <hr class="my-4 text-muted">
                <div class="mb-2">
                    <label class="text-muted fw-bold small text-uppercase mb-2 d-block"><i class="far fa-comments"></i> Diskusi / Catatan</label>
                    <div id="viewCommentList" class="d-flex flex-column gap-3 mb-3" style="max-height:250px;overflow-y:auto;"></div>
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
                        <input type="text" name="name" class="form-control" required placeholder="contoh: Olahraga 30 menit" style="font-family:'Nunito',sans-serif;">
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

{{-- MODAL: TULIS NOTE --}}
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
                    <div class="mb-3">
                        <textarea name="content" id="noteContent"></textarea>
                    </div>
                    <div class="border-top border-dark border-opacity-10 pt-3">
                        <input type="hidden" name="bg_color" id="selectedColor" value="#fef08a">
                        <div style="font-size:0.7rem;font-weight:700;color:#6b7280;font-family:'Nunito',sans-serif;margin-bottom:6px;">WARNA CATATAN:</div>
                        <div class="color-picker-group">
                            @foreach(['#fef08a'=>'Kuning','#bbf7d0'=>'Hijau','#bfdbfe'=>'Biru','#fbcfe8'=>'Pink','#fed7aa'=>'Oranye','#e9d5ff'=>'Ungu','#f1f5f9'=>'Abu'] as $hex => $label)
                                <label title="{{ $label }}" style="cursor:pointer;">
                                    <input type="radio" name="_color_radio" class="color-radio d-none" value="{{ $hex }}" {{ $hex=='#fef08a' ? 'checked':'' }} onchange="changeModalColor('{{ $hex }}')">
                                    <div class="color-circle" style="background:{{ $hex }};border:2px solid rgba(0,0,0,0.1);"></div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-brand" style="background:#d97706;"><i class="fas fa-thumbtack"></i> Tempel</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL: EDIT NOTE --}}
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
                        <div style="font-size:0.7rem;font-weight:700;color:#6b7280;font-family:'Nunito',sans-serif;margin-bottom:6px;">WARNA CATATAN:</div>
                        <div class="color-picker-group" id="editColorGroup">
                            @foreach(['#fef08a'=>'Kuning','#bbf7d0'=>'Hijau','#bfdbfe'=>'Biru','#fbcfe8'=>'Pink','#fed7aa'=>'Oranye','#e9d5ff'=>'Ungu','#f1f5f9'=>'Abu'] as $hex => $label)
                                <label title="{{ $label }}" style="cursor:pointer;">
                                    <input type="radio" name="_edit_color_radio" class="edit-color-radio d-none" value="{{ $hex }}" onchange="changeEditModalColor('{{ $hex }}')">
                                    <div class="color-circle" style="background:{{ $hex }};border:2px solid rgba(0,0,0,0.1);"></div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-brand" style="background:#d97706;"><i class="fas fa-save"></i> Simpan Perubahan</button>
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
                        <label class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:var(--surface-1);cursor:pointer;border:1px solid var(--surface-border);">
                            <input type="checkbox" name="telegram_remind_morning" class="form-check-input m-0" style="width:20px;height:20px;" {{ Auth::user()->telegram_remind_morning ? 'checked':'' }}>
                            <div>
                                <div class="fw-bold" style="font-size:0.875rem;font-family:'Nunito',sans-serif;">🌅 Pengingat Pagi</div>
                                <div style="font-size:0.75rem;color:var(--text-muted);font-family:'Nunito',sans-serif;">Kirim ringkasan tugas aktif setiap pagi jam 07.00</div>
                            </div>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:var(--surface-1);cursor:pointer;border:1px solid var(--surface-border);">
                            <input type="checkbox" name="telegram_remind_deadline" class="form-check-input m-0" style="width:20px;height:20px;" {{ Auth::user()->telegram_remind_deadline ? 'checked':'' }}>
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
        <div class="pomo-time" id="pomodoro-display">25:00</div>
        <div class="pomo-label" id="pomodoro-label">Siap untuk fokus!</div>
        <div class="d-flex gap-2 justify-content-center">
            <button id="btn-pomo-start" class="btn-brand" style="font-size:0.82rem;padding:0.5rem 1.2rem;">
                <i class="fas fa-play"></i> Mulai
            </button>
            <button id="btn-pomo-reset" class="btn-ghost" style="font-size:0.82rem;padding:0.5rem 1rem;">
                <i class="fas fa-redo"></i>
            </button>
        </div>
        <div class="d-flex gap-2 justify-content-center mt-3 flex-wrap">
            @foreach([5,10,15,25,50] as $m)
            <button class="date-quick-btn" onclick="setPomoTime({{ $m }})">{{ $m }}m</button>
            @endforeach
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
function setFilter(val, e) {
    if (e) e.preventDefault();
    document.getElementById('filterInput') && (document.getElementById('filterInput').value = val);
    const url = new URL(window.location.href);
    url.searchParams.set('filter', val);
    url.searchParams.set('sort', document.getElementById('sortSelect')?.value || 'deadline');
    url.searchParams.set('tag', document.getElementById('tagFilter')?.value || '');
    url.searchParams.set('search', document.getElementById('searchInput')?.value || '');
    window.location.href = url.toString();
}

function applyFilters() {
    const url = new URL(window.location.href);
    url.searchParams.set('filter', '{{ $filter }}');
    url.searchParams.set('sort', document.getElementById('sortSelect')?.value || 'deadline');
    url.searchParams.set('tag', document.getElementById('tagFilter')?.value || '');
    url.searchParams.set('search', document.getElementById('searchInput')?.value || '');
    window.location.href = url.toString();
}

function openTaskModal() {
    new bootstrap.Modal(document.getElementById('taskModal')).show();
}
$('#taskModal').on('shown.bs.modal', function () { $('#taskTitleInput').focus(); });
$('#editTaskModal').on('shown.bs.modal', function () { $('#editTaskTitle').focus(); });

// ==========================================
// 2. PERBAIKAN SEAMLESS PROGRESS BAR
// ==========================================
function syncDailyProgress(stats) {
    if (!stats) return;
    $('#daily-progress-pct').text(stats.pct + '%');
    $('#daily-progress-fill').css('width', stats.pct + '%');

    let countText = '';
    if (stats.pct === 100 && stats.total > 0) {
        countText = '🎉 Kerja bagus! Semua selesai.';
    } else if (stats.remaining > 0) {
        countText = `<i class="fas fa-bullseye text-warning me-1"></i> ${stats.remaining} tugas tersisa hari ini.`;
    } else {
        countText = 'Belum ada target hari ini.';
    }
    $('#daily-progress-text').html(countText);
}

// Quick date buttons
function setQuickDate(type, inputSelector, modalSelector) {
    const input = document.querySelector(inputSelector);
    const modal = document.querySelector(modalSelector);
    if (!input) return;

    modal.querySelectorAll('.date-quick-btn').forEach(b => b.classList.remove('active'));

    let date = null;
    if (type === 'today') {
        date = new Date();
        date.setHours(23, 59, 0, 0);
        event.target.classList.add('active');
    } else if (type === 'tomorrow') {
        date = new Date();
        date.setDate(date.getDate() + 1);
        date.setHours(23, 59, 0, 0);
        event.target.classList.add('active');
    } else {
        // none
        modal.querySelector('.date-quick-btn:first-of-type') &&
            modal.querySelector('.date-quick-btn').classList.add('active');
    }

    if (date) {
        const pad = n => String(n).padStart(2, '0');
        input.value = `${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
    } else {
        input.value = '';
    }
}

// Color modal helpers
function changeModalColor(hex) {
    document.getElementById('selectedColor').value = hex;
    document.getElementById('noteModalContainer').style.background = hex;
}
function changeEditModalColor(hex) {
    document.getElementById('editSelectedColor').value = hex;
    document.getElementById('editNoteModalContainer').style.background = hex;
}

// ══════════════════════════════════════════════════════════
//  POMODORO
// ══════════════════════════════════════════════════════════
let pomoInterval, isPomoRunning = false;
let pomoTime = 25 * 60;

function updatePomoDisplay() {
    const m = String(Math.floor(pomoTime / 60)).padStart(2, '0');
    const s = String(pomoTime % 60).padStart(2, '0');
    document.getElementById('pomodoro-display').textContent = `${m}:${s}`;
}
function togglePomodoro() {
    const w = document.getElementById('pomodoro-widget');
    w.classList.toggle('show');
}
window.setPomoTime = function(minutes) {
    clearInterval(pomoInterval);
    isPomoRunning = false;
    pomoTime = minutes * 60;
    updatePomoDisplay();
    document.getElementById('pomodoro-label').textContent = 'Siap untuk fokus!';
    document.getElementById('btn-pomo-start').innerHTML = '<i class="fas fa-play"></i> Mulai';
};

// ══════════════════════════════════════════════════════════
//  CKEDITOR
// ══════════════════════════════════════════════════════════
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

// ══════════════════════════════════════════════════════════
//  JQUERY — ALL EVENT HANDLERS
// ══════════════════════════════════════════════════════════
$(document).ready(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // TASK: ADD
    $('#formAddTask').off('submit').on('submit', function(e) {
        e.preventDefault();
        let $form = $(this);
        let btn   = $form.find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');
        
        $.post("{{ route('admin.productivity.tasks.store') }}", $form.serialize())
            .done(res => {
                if (res.success && res.task) {
                    if (res.stats) syncDailyProgress(res.stats);
                    $('#taskModal').modal('hide');
                    $form[0].reset();
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Simpan Tugas');

                    // Inject task baru ke atas list tanpa reload
                    const t = res.task;
                    const priorityClass = 'priority-' + t.priority;
                    const newHtml = `
                    <div class="task-item ${priorityClass}" id="task-${t.id}" style="animation:fadeSlideUp 0.25s ease both;">
                        <input type="checkbox" class="task-check task-checkbox" data-id="${t.id}" autocomplete="off">
                        <div class="task-content">
                            <div class="task-title-text btn-view-task" data-id="${t.id}" data-title="${t.title}"
                                data-desc="${t.description||''}" data-priority="${t.priority}"
                                data-deadline="${t.formatted_deadline}"
                                data-subtasks="[]" data-attachments="[]" data-comments="[]">
                                ${t.title}
                            </div>
                            <div class="task-meta">
                                ${t.tag ? `<span class="task-badge badge-tag"><i class="fas fa-hashtag"></i> ${t.tag}</span>` : ''}
                                ${t.deadline_badge_html}
                            </div>
                        </div>
                        <div class="task-actions">
                            <button class="task-action-btn edit btn-edit-task" data-id="${t.id}"
                                data-title="${t.title}" data-desc="${t.description||''}"
                                data-tag="${t.tag||''}" data-priority="${t.priority}"
                                data-recurrence="${t.recurrence||'none'}" data-assignee=""
                                data-deadline="${t.deadline_at||''}" data-subtasks="[]" data-attachments="[]">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="task-action-btn danger btn-delete-task" data-id="${t.id}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>`;

                    // Tambah ke task list yang terlihat
                    let $taskList = $('#task-container .task-list').first();
                    if ($taskList.length) {
                        $taskList.prepend(newHtml);
                        // Update counter badge
                        let $badge = $('.panel-count-badge');
                        $badge.text(parseInt($badge.text() || 0) + 1);
                    } else {
                        // Kalau list kosong (empty state), reload sekali
                        location.reload();
                        return;
                    }

                    Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 2000 })
                        .fire({ icon: 'success', title: '✅ Tugas berhasil ditambahkan!' });
                }
            })
            .fail(xhr => {
                Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Simpan Tugas');
            });
    });

    // ========================================================
    // PERBAIKAN BUG: Fungsi untuk Update Header Secara Real-Time
    // ========================================================
    function updateDailyProgress(isCompleted) {
        let $countEl = $('.daily-progress-count');
        let $labelEl = $('.daily-progress-label span:last-child');
        let $fillEl  = $('.daily-progress-fill');

        let currentLabel = $labelEl.text().split('/'); 
        if(currentLabel.length !== 2) return;

        let done  = parseInt(currentLabel[0]);
        let total = parseInt(currentLabel[1]);

        if (isCompleted) {
            done++;
            // Jika kita kerjakan tugas tanpa deadline / dari "Semua Tugas", maka total produktivitas bertambah!
            if (done > total) total = done; 
        } else {
            done = Math.max(done - 1, 0);
        }

        let pct = total > 0 ? Math.round((done / total) * 100) : 0;

        $labelEl.text(done + '/' + total);
        $fillEl.css('width', pct + '%');

        let remaining = total - done;
        if (pct === 100 && total > 0) $countEl.text('🎉 Semua selesai!');
        else if (remaining > 0)       $countEl.text(remaining + ' tugas tersisa hari ini');
        else                          $countEl.text('Tidak ada tugas hari ini');
    }

    // ========================================================
    // REVISI TASK: TOGGLE STATUS (checkbox)
    // ========================================================
    $(document).on('change', '.task-checkbox', function() {
        let checkbox = $(this); // Amankan context 'this' dari awal
        let id       = checkbox.data('id');
        let status   = checkbox.is(':checked') ? 'completed' : 'pending';
        let item     = $('#task-' + id);

        // Optimistic UI Update untuk List View
        if (status === 'completed') item.addClass('completed');
        else item.removeClass('completed');

        $.ajax({ url: `/admin/productivity/tasks/${id}/status`, type: 'PATCH', data: { status } })
            .done((res) => {
                Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 1500 })
                    .fire({ icon: status === 'completed' ? 'success' : 'info',
                            title: status === 'completed' ? 'Tugas Selesai! 🎉' : 'Tugas Diaktifkan' });
                
                            if (res.stats) syncDailyProgress(res.stats);
                
                // PERBAIKAN PROGRESS BAR: Cek yang benar apakah ada di list "Hari Ini"
                if (item.closest('.task-list').prevAll('.divider-today').length > 0 || $('.tab-today').hasClass('active')) {
                    updateDailyProgress(status === 'completed');
                }

                // SINKRONISASI KANBAN VIEW (Agar dipindah otomatis & teks ikut tercoret)
                let kanbanCard = $('.kanban-card[data-id="' + id + '"]');
                if (kanbanCard.length) {
                    let targetList = status === 'completed' ? $('#kanban-list-completed') : $('#kanban-list-pending');
                    let originList = kanbanCard.closest('.kanban-list');
                    
                    targetList.append(kanbanCard); // Pindah DOM Card
                    
                    if(typeof updateKanbanCounts === 'function') updateKanbanCounts();
                    if(typeof syncEmptyState === 'function') {
                        syncEmptyState(targetList[0]);
                        syncEmptyState(originList[0]);
                    }
                }
            })
            .fail((xhr) => {
                // Revert UI dengan aman jika terjadi kegagalan server
                checkbox.prop('checked', status !== 'completed'); // Paksa kembalikan centang
                if (status === 'completed') item.removeClass('completed');
                else item.addClass('completed');
                
                let errorMsg = xhr.responseJSON?.message || 'Gagal mengubah status tugas.';
                Swal.fire('Ops!', errorMsg, 'error');
            });
    });

    // ========================================================
    // REVISI TASK: DELETE BUTTON
    // ========================================================
    $(document).off('click', '.btn-delete-task').on('click', '.btn-delete-task', function() {
        let btn = $(this);
        let id = btn.data('id');
        let isDelegated = btn.data('delegated');

        // CEGAT DISINI: Jika ini tugas delegasi masuk, langsung tampilkan alert penolakan
        if (isDelegated === true || isDelegated === 'true') {
            Swal.fire({
                icon: 'warning',
                title: 'Akses Ditolak',
                text: 'Task yang didelegasikan ke Anda tidak bisa dihapus, hanya bisa dihapus pemberi delegasi.',
                confirmButtonColor: '#741847'
            });
            return; // Hentikan eksekusi, alert konfirmasi hapus tidak akan muncul
        }

        // Jika bukan tugas delegasi, lanjut ke konfirmasi hapus normal
        let $taskItem = $('#task-' + id);
        let $kanbanCard = $('.kanban-card[data-id="' + id + '"]');

        Swal.fire({
            title: 'Hapus Tugas?', 
            text: 'Tindakan ini tidak dapat dibatalkan.',
            icon: 'warning', 
            showCancelButton: true,
            confirmButtonColor: '#741847', 
            cancelButtonText: 'Batal', 
            confirmButtonText: 'Ya, Hapus!'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({ url: `/admin/productivity/tasks/${id}`, type: 'DELETE' })
                    .done(res => {
                        if (res.success) {
                            $taskItem.fadeOut(300, function() { $(this).remove(); });
                            $kanbanCard.fadeOut(300, function() { $(this).remove(); if(typeof updateKanbanCounts === 'function') updateKanbanCounts(); });
                            if (res.stats) syncDailyProgress(res.stats);
                            
                            Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 1500 })
                                .fire({ icon: 'success', title: 'Tugas dihapus!' });
                        }
                    })
                    .fail(xhr => {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menghapus tugas.', 'error');
                    });
            }
        });
    });

    // TASK: EDIT — Open & Prefill
    $(document).on('click', '.btn-edit-task', function() {
        let btn = $(this);
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
        $('#editTaskAssignee').val(btn.data('assignee'));

        let dl = btn.data('deadline');
        $('#editTaskDeadline').val(dl || '');
        $('#editTaskModal .date-quick-btn').removeClass('active');
        if (!dl) $('#editBtnNone').addClass('active');

        new bootstrap.Modal(document.getElementById('editTaskModal')).show();
        setTimeout(() => document.getElementById('editTaskTitle').focus(), 350);
    });

    // TASK: VIEW MODAL
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
            let textStyle = st.is_completed ? 'text-decoration:line-through;color:#9ca3af;' : '';
            subHtml += `
                <div class="d-flex align-items-center gap-2 p-2 rounded border bg-white shadow-sm">
                    <input type="checkbox" class="form-check-input toggle-subtask" data-task="${taskId}" data-id="${st.id}" ${checked} style="cursor:pointer;width:18px;height:18px;">
                    <span class="small" style="${textStyle}">${st.title}</span>
                </div>`;
        });
        $('#viewSubTaskList').html(subHtml || '<p class="text-muted small">Tidak ada checklist.</p>');

        let attachments = btn.data('attachments') || [];
        let attHtml = '';
        attachments.forEach(att => {
            attHtml += `
                <a href="/storage/${att.file_path}" target="_blank" class="d-flex align-items-center justify-content-between p-2 rounded border bg-white shadow-sm text-decoration-none">
                    <span class="small text-dark text-truncate" style="max-width:85%;"><i class="far fa-file-alt me-2 text-primary"></i> ${att.file_name}</span>
                    <i class="fas fa-download text-muted small"></i>
                </a>`;
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
                    <small class="text-muted" style="font-size:0.7rem;font-weight:700;">${c.user.name} &bull; ${timeStr}</small>
                    <div class="p-2 rounded d-inline-block mt-1 ${bg}" style="max-width:85%;align-self:${isMe?'flex-end':'flex-start'};font-family:'Nunito',sans-serif;font-size:0.85rem;">
                        ${c.comment}
                    </div>
                </div>`;
        });
        if (comments.length === 0) html = '<div class="text-center text-muted small my-2">Belum ada diskusi.</div>';
        $('#viewCommentList').html(html);
        setTimeout(() => { let el = document.getElementById('viewCommentList'); if (el) el.scrollTop = el.scrollHeight; }, 100);
    }

    $('#btnSubmitComment').click(function() {
        let comment = $('#newCommentText').val().trim();
        if (!comment || !currentViewTaskId) return;
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.post(`/admin/productivity/tasks/${currentViewTaskId}/comments`, { comment })
            .done(res => {
                $('#newCommentText').val('');
                // Append komentar baru ke DOM
                let now = new Date();
                let timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                let newComment = `
                <div class="d-flex flex-column text-end">
                    <small class="text-muted" style="font-size:0.7rem;font-weight:700;">Anda &bull; ${timeStr}</small>
                    <div class="p-2 rounded d-inline-block mt-1 bg-primary-subtle text-primary-emphasis"
                        style="max-width:85%;align-self:flex-end;font-family:'Nunito',sans-serif;font-size:0.85rem;">
                        ${comment}
                    </div>
                </div>`;
                $('#viewCommentList').append(newComment);
                let el = document.getElementById('viewCommentList');
                if (el) el.scrollTop = el.scrollHeight;
            })
            .fail(() => Swal.fire('Error', 'Gagal mengirim komentar.', 'error'))
            .always(() => btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Kirim'));
    });
    
    $('#newCommentText').on('keypress', function(e) { if (e.which === 13) $('#btnSubmitComment').click(); });

    function renderSubTasks(taskId, subTasks) {
        let html = '';
        subTasks.forEach(st => {
            let checked   = st.is_completed ? 'checked' : '';
            let textStyle = st.is_completed ? 'text-decoration:line-through;color:#9ca3af;' : 'color:#374151;';
            html += `
                <div class="d-flex align-items-center justify-content-between p-1 border rounded bg-light" id="subtask-${st.id}">
                    <div class="d-flex align-items-center gap-2" style="font-size:0.8rem;font-family:'Nunito',sans-serif;">
                        <input type="checkbox" class="form-check-input mt-0 toggle-subtask" data-task="${taskId}" data-id="${st.id}" ${checked} style="cursor:pointer;">
                        <span style="${textStyle}">${st.title}</span>
                    </div>
                    <button type="button" class="btn btn-sm text-danger p-0 delete-subtask" data-task="${taskId}" data-id="${st.id}"><i class="fas fa-times"></i></button>
                </div>`;
        });
        if (subTasks.length === 0) html = '<div class="text-muted" style="font-size:0.75rem;">Belum ada sub-task.</div>';
        $('#subTaskList').html(html);
    }

    function renderAttachments(taskId, attachments) {
        let html = '';
        attachments.forEach(att => {
            html += `
                <div class="d-flex align-items-center justify-content-between p-1 border rounded bg-light" id="att-${att.id}">
                    <a href="/storage/${att.file_path}" target="_blank" class="text-truncate" style="font-size:0.8rem;font-family:'Nunito',sans-serif;text-decoration:none;max-width:80%;">
                        <i class="far fa-file-alt me-1 text-primary"></i> ${att.file_name}
                    </a>
                    <button type="button" class="btn btn-sm text-danger p-0 delete-attachment" data-task="${taskId}" data-id="${att.id}"><i class="fas fa-times"></i></button>
                </div>`;
        });
        if (attachments.length === 0) html = '<div class="text-muted" style="font-size:0.75rem;">Belum ada lampiran.</div>';
        $('#attachmentList').html(html);
    }

    $('#btnAddSubTask').click(function() {
        let taskId = $('#editTaskId').val();
        let title  = $('#newSubTaskTitle').val().trim();
        if (!title) return;
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.post(`/admin/productivity/tasks/${taskId}/subtasks`, { title })
            .done(res => {
                if (res.success && res.subTask) {
                    let st = res.subTask;
                    let newRow = `
                    <div class="d-flex align-items-center justify-content-between p-1 border rounded bg-light" id="subtask-${st.id}">
                        <div class="d-flex align-items-center gap-2" style="font-size:0.8rem;font-family:'Nunito',sans-serif;">
                            <input type="checkbox" class="form-check-input mt-0 toggle-subtask" data-task="${taskId}" data-id="${st.id}" style="cursor:pointer;">
                            <span style="color:#374151;">${st.title}</span>
                        </div>
                        <button type="button" class="btn btn-sm text-danger p-0 delete-subtask" data-task="${taskId}" data-id="${st.id}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>`;
                    $('#subTaskList .text-muted').remove(); // hapus placeholder
                    $('#subTaskList').append(newRow);
                    $('#newSubTaskTitle').val('').focus();
                }
            })
            .fail(() => Swal.fire('Error', 'Gagal menambah sub-task', 'error'))
            .always(() => btn.prop('disabled', false).html('<i class="fas fa-plus"></i>'));
    });

    $(document).on('change', '.toggle-subtask', function() {
        let taskId = $(this).data('task');
        let subId  = $(this).data('id');
        let $span  = $(this).siblings('span');
        let isDone = $(this).is(':checked');
        
        // Optimistic UI
        $span.css(isDone ? {'text-decoration':'line-through','color':'#9ca3af'} : {'text-decoration':'none','color':'#374151'});
        
        $.ajax({ url: `/admin/productivity/tasks/${taskId}/subtasks/${subId}/toggle`, type: 'PATCH' })
            .fail(() => {
                // Revert
                $span.css(isDone ? {'text-decoration':'none','color':'#374151'} : {'text-decoration':'line-through','color':'#9ca3af'});
                $(this).prop('checked', !isDone);
            });
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
        let formData  = new FormData();
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

    // TASK: EDIT SUBMIT
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
                    
                    // Update judul di list view secara DOM
                    if (res.task) {
                        let t = res.task;
                        let $item = $('#task-' + id);
                        $item.find('.task-title-text').text(t.title)
                            .data('title', t.title)
                            .data('desc', t.description || '')
                            .data('priority', t.priority)
                            .data('deadline', t.deadline_at || 'Tanpa Deadline');
                        $item.find('.btn-edit-task')
                            .data('title', t.title)
                            .data('desc', t.description || '')
                            .data('priority', t.priority)
                            .data('tag', t.tag || '')
                            .data('deadline', t.deadline_at || '');
                        // Update priority class
                        $item.removeClass('priority-high priority-medium priority-low')
                            .addClass('priority-' + t.priority);
                        // Update kanban card title jika ada
                        $('.kanban-card[data-id="' + id + '"] .kc-title').text(t.title);
                    } else {
                        // Fallback reload jika server tidak return task
                        setTimeout(() => location.reload(), 800);
                    }
                }
            })
            .fail(xhr => {
                Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Perubahan');
            });
    });

    // TASK: ARCHIVE / UNARCHIVE / DELETE
    $(document).on('click', '.btn-archive-task', function() {
        let id = $(this).data('id');
        let $item = $('#task-' + id);
        $.ajax({ url: `/admin/productivity/tasks/${id}/archive`, type: 'PATCH' })
            .done(() => {
                $item.fadeOut(250, function() { $(this).remove(); });
                Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 1500 })
                    .fire({ icon: 'info', title: 'Tugas diarsipkan' });
            });
    });

    $(document).on('click', '.btn-unarchive-task', function() {
        let id = $(this).data('id');
        let $item = $('#task-' + id);
        $.ajax({ url: `/admin/productivity/tasks/${id}/unarchive`, type: 'PATCH' })
            .done(() => {
                $item.fadeOut(250, function() { $(this).remove(); });
                Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 1500 })
                    .fire({ icon: 'success', title: 'Tugas dipulihkan!' });
            });
    });

    // HABITS
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
        let btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>');
        $.post("{{ route('admin.productivity.habits.store') }}", $(this).serialize())
            .done(() => {
                $('#habitModal').modal('hide');
                location.reload();
            })
            .fail(xhr => {
                Swal.fire('Gagal', xhr.responseJSON?.message, 'error');
                btn.prop('disabled', false).html('<i class="fas fa-plus"></i> Tambah');
            });
    });

    $(document).on('click', '.btn-delete-habit', function() {
        let id = $(this).data('id');
        $.ajax({ url: `/admin/productivity/habits/${id}`, type: 'DELETE' })
            .done(() => $('#habit-' + id).slideUp());
    });

    // NOTES
    $('#formAddNote').submit(function(e) {
        e.preventDefault();
        if (editorAdd) $('#noteContent').val(editorAdd.getData());
        let btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>...');
        $.post("{{ route('admin.productivity.notes.store') }}", $(this).serialize())
            .done(() => {
                $('#noteModal').modal('hide');
                Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 1000 })
                    .fire({ icon: 'success', title: 'Catatan disimpan!' });
                setTimeout(() => location.reload(), 800); // reload cepat setelah modal tutup
            })
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
        let id = $('#editNoteId').val();
        if (editorEdit) $('#editNoteContent').val(editorEdit.getData());
        let btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>...');
        $.ajax({ url: `/admin/productivity/notes/${id}`, type: 'PATCH', data: $(this).serialize() })
            .done(() => {
                $('#editNoteModal').modal('hide');
                Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 1000 })
                    .fire({ icon: 'success', title: 'Catatan diperbarui!' });
                setTimeout(() => location.reload(), 800);
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

    // SETTINGS
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

    // POMODORO BUTTONS
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

    // ══════════════════════════════════════════════════════════
    //  VIEW SWITCHER (List / Kanban / Calendar)
    // ══════════════════════════════════════════════════════════
    const $taskContainer     = $('#task-container');
    const $kanbanContainer   = $('#kanban-container');
    const $calendarContainer = $('#calendar-container');
    const $btnList           = $('#btnViewList');
    const $btnKanban         = $('#btnViewKanban');
    const $btnCalendar       = $('#btnViewCalendar');
    let calendarInstance     = null;

    function resetViews() {
        $taskContainer.addClass('d-none');
        $kanbanContainer.addClass('d-none');
        $calendarContainer.addClass('d-none');
        $btnList.removeClass('active').css({background:'transparent', color:'var(--text-secondary)'});
        $btnKanban.removeClass('active').css({background:'transparent', color:'var(--text-secondary)'});
        $btnCalendar.removeClass('active').css({background:'transparent', color:'var(--text-secondary)'});
    }

    $btnList.on('click', function() {
        resetViews(); 
        $taskContainer.removeClass('d-none'); 
        $(this).addClass('active').css({background:'var(--brand-maroon)', color:'#fff'});
    });

    $btnKanban.on('click', function() {
        resetViews(); 
        $kanbanContainer.removeClass('d-none'); 
        $(this).addClass('active').css({background:'var(--brand-maroon)', color:'#fff'});
    });

    $btnCalendar.on('click', function() {
        resetViews(); 
        $calendarContainer.removeClass('d-none'); 
        $(this).addClass('active').css({background:'var(--brand-maroon)', color:'#fff'});
        
        setTimeout(() => {
            // FIX: Cek apakah library FullCalendar sudah ter-load
            if (typeof FullCalendar === 'undefined') {
                console.error("FullCalendar library is missing!");
                Swal.fire('Ops!', 'Library Kalender belum termuat sempurna. Coba refresh halaman.', 'warning');
                return;
            }

            if (!calendarInstance) {
                let calendarEl = document.getElementById('calendar');
                calendarInstance = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'id',
                    buttonText: { today: 'Hari Ini', month: 'Bulan', week: 'Minggu', list: 'Agenda' },
                    headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
                    height: 'auto',
                    eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                    displayEventEnd: false, 
                    defaultTimedEventDuration: '00:01:00',
                    nextDayThreshold: '00:00:00',
                    events: [
                        @foreach($tasks as $t)
                            @if($t->deadline_at)
                            {
                                id: '{{ $t->id }}',
                                // FIX: Gunakan json_encode agar aman dari enter / petik ganda
                                title: {!! json_encode($t->title) !!},
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
        }, 50);
    });

    // ══════════════════════════════════════════════════════════
    //  KANBAN SORTABLE
    // ══════════════════════════════════════════════════════════
    function updateKanbanCounts() {
        ['pending', 'in_progress', 'completed'].forEach(function(status) {
            const list  = document.getElementById('kanban-list-' + status);
            const badge = document.getElementById('kc-count-' + status);
            if (list && badge) badge.textContent = list.querySelectorAll('.kanban-card').length;
        });
    }

    function syncEmptyState(list) {
        const hasCards = list.querySelectorAll('.kanban-card').length > 0;
        let emptyEl    = list.querySelector('.kanban-empty');
        if (!hasCards && !emptyEl) {
            emptyEl = document.createElement('div');
            emptyEl.className = 'kanban-empty';
            emptyEl.innerHTML = '<i class="far fa-clipboard"></i> Tidak ada tugas di sini';
            list.appendChild(emptyEl);
        } else if (hasCards && emptyEl) { emptyEl.remove(); }
    }

    if (typeof Sortable !== 'undefined') {
        document.querySelectorAll('.kanban-list').forEach(function(listEl) {
            new Sortable(listEl, {
                group: 'kanban-tasks', animation: 180,
                ghostClass: 'sortable-ghost', dragClass: 'sortable-drag',
                handle: '.kanban-card', scroll: true, scrollSensitivity: 60, scrollSpeed: 10,
                onStart: function() { document.querySelectorAll('.kanban-list').forEach(l => l.classList.add('drag-over')); },
                onEnd: function(evt) {
                    document.querySelectorAll('.kanban-list').forEach(l => l.classList.remove('drag-over'));
                    const cardEl    = evt.item;
                    const taskId    = cardEl.dataset.id;
                    const newStatus = evt.to.dataset.status;
                    const oldStatus = evt.from.dataset.status;
                    syncEmptyState(evt.from);
                    syncEmptyState(evt.to);
                    updateKanbanCounts();
                    if (newStatus === oldStatus) return;
                    $.ajax({ url: `/admin/productivity/tasks/${taskId}/status`, type: 'PATCH', data: { status: newStatus } })
                        .done(function() {
                            Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 1500 })
                                .fire({ icon: newStatus === 'completed' ? 'success' : 'info', title: 'Status tugas diperbarui!' });
                        })
                        .fail(function() {
                            evt.from.insertBefore(cardEl, evt.from.children[evt.oldIndex] || null);
                            syncEmptyState(evt.from); syncEmptyState(evt.to); updateKanbanCounts();
                            Swal.fire('Gagal!', 'Terjadi kesalahan saat memindah tugas.', 'error');
                        });
                }
            });
        });
    } else {
        console.warn("Library SortableJS belum ter-load!");
    }

    updateKanbanCounts();

    // Select2 init
    if ($.fn.select2) {
        $('#taskTagSelect').select2({ theme: 'bootstrap-5', dropdownParent: $('#taskModal'), tags: true, placeholder: 'Ketik atau pilih tag...', width: '100%' });
        $('#editTaskTag').select2({ theme: 'bootstrap-5', dropdownParent: $('#editTaskModal'), tags: true, placeholder: 'Ketik atau pilih tag...', width: '100%' });
        $('#taskAssigneeSelect').select2({ theme: 'bootstrap-5', dropdownParent: $('#taskModal'), placeholder: 'Cari pegawai...', width: '100%' });
        if ($('#editTaskAssignee').length) {
            $('#editTaskAssignee').select2({ theme: 'bootstrap-5', dropdownParent: $('#editTaskModal'), placeholder: 'Cari pegawai...', width: '100%' });
        }
    }
});
// ══════════════════════════════════════════════════════════
//  KEYBOARD SHORTCUTS (Aman dari jQuery Crash)
// ══════════════════════════════════════════════════════════
document.addEventListener('keydown', function(e) {
    // Jangan eksekusi shortcut jika user sedang mengetik
    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
        if (e.key === 'Escape') e.target.blur(); 
        return;
    }

    // [ ALT + N ] -> Tambah Tugas Baru
    if (e.altKey && e.key.toLowerCase() === 'n') {
        e.preventDefault();
        if (typeof openTaskModal === 'function') openTaskModal();
    }
    
    // [ ALT + F ] -> Buka Pomodoro Focus Timer
    if (e.altKey && e.key.toLowerCase() === 'f') {
        e.preventDefault();
        if (typeof togglePomodoro === 'function') togglePomodoro();
    }

    // [ ALT + C ] -> Tulis Catatan Baru
    if (e.altKey && e.key.toLowerCase() === 'c') {
        e.preventDefault();
        let noteModal = document.getElementById('noteModal');
        if (noteModal && typeof bootstrap !== 'undefined') {
            new bootstrap.Modal(noteModal).show();
        }
    }

    // [ / ] -> Fokus ke Search Bar
    if (e.key === '/') {
        e.preventDefault();
        let searchInput = document.getElementById('searchInput');
        if (searchInput) searchInput.focus();
    }
});

// Toast Notifikasi Tips Shortcut
if (!localStorage.getItem('shortcut_hint_shown')) {
    setTimeout(() => {
        if (typeof Swal !== 'undefined') {
            Swal.mixin({ toast: true, position: 'bottom-start', showConfirmButton: false, timer: 5000 })
                .fire({ icon: 'info', title: '💡 Tips', text: 'Tekan ALT+N untuk tugas baru, atau ( / ) untuk mencari.' });
            localStorage.setItem('shortcut_hint_shown', 'true');
        }
    }, 2000);
}
</script>
@endsection
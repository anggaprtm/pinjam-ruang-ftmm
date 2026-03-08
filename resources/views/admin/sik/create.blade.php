@extends('layouts.admin')
@section('content')
<div class="d-flex align-items-center mb-3">
    <h3 class="mb-0">Ajukan SIK</h3>
    <div class="ms-auto">
        <a href="{{ route('admin.sik.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.sik.create') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Filter Tahun Proker</label>
                <select name="tahun" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua Tahun</option>
                    @foreach($availableYears as $yr)
                        <option value="{{ $yr }}" @selected((int)$selectedYear === (int)$yr)>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    @forelse($activeProgramItems as $item)
        <div class="col-md-4">
            <div class="card h-100 shadow-sm proker-card" style="cursor:pointer"
                 data-id="{{ $item->id }}"
                 data-title="{{ e($item->nama_rencana) }}"
                 data-ormawa="{{ e($item->plan->ormawa->nama ?? '-') }}"
                 data-timeline="{{ optional($item->timeline_mulai_rencana)->format('d M Y') }} - {{ optional($item->timeline_selesai_rencana)->format('d M Y') }}"
                 onclick="selectProker(this)">
                <div class="card-body">
                    <div class="small text-muted mb-1">{{ $item->plan->ormawa->nama ?? '-' }} • {{ $item->plan->tahun ?? '-' }}</div>
                    <h5 class="mb-2">{{ $item->nama_rencana }}</h5>
                    <div class="text-muted">Timeline rencana:</div>
                    <div>{{ optional($item->timeline_mulai_rencana)->format('d M Y') }} - {{ optional($item->timeline_selesai_rencana)->format('d M Y') }}</div>
                </div>
                <div class="card-footer bg-white">
                    <button type="button" class="btn btn-sm btn-primary">Pilih Proker Ini</button>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info mb-0">Belum ada proker published yang tersedia untuk diajukan SIK.</div>
        </div>
    @endforelse
</div>

<div class="card shadow-sm" id="sik-form-card" style="display:none;">
    <div class="card-header"><strong>Form Pengajuan SIK</strong></div>
    <div class="card-body">
        <div class="alert alert-light border" id="selected-proker-info"></div>
        <form action="{{ route('admin.sik.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="program_item_id" id="program_item_id" required>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Judul Final Kegiatan</label>
                    <input type="text" name="judul_final_kegiatan" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Rencana Tempat</label>
                    <input type="text" name="rencana_tempat" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Timeline Mulai Final</label>
                    <input type="date" name="timeline_mulai_final" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Timeline Selesai Final</label>
                    <input type="date" name="timeline_selesai_final" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Proposal</label>
                    <input type="file" name="proposal" class="form-control" accept=".pdf,.doc,.docx">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Surat Permohonan</label>
                    <input type="file" name="surat_permohonan" class="form-control" accept=".pdf,.doc,.docx">
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">Ajukan SIK</button>
            </div>
        </form>
    </div>
</div>

<script>
function selectProker(card) {
    document.querySelectorAll('.proker-card').forEach((el) => el.classList.remove('border-primary'));
    card.classList.add('border-primary');

    const id = card.dataset.id;
    const title = card.dataset.title;
    const ormawa = card.dataset.ormawa;
    const timeline = card.dataset.timeline;

    document.getElementById('program_item_id').value = id;
    document.getElementById('selected-proker-info').innerHTML = `<strong>Proker terpilih:</strong> ${title}<br><strong>Ormawa:</strong> ${ormawa}<br><strong>Timeline rencana:</strong> ${timeline}`;
    document.getElementById('sik-form-card').style.display = 'block';
    document.getElementById('sik-form-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>
@endsection

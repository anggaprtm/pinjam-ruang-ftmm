@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Jenis Ormawa</label>
        <select name="jenis_ormawa_id" class="form-control" required>
            <option value="">-- Pilih Jenis --</option>
            @foreach($jenisOrmawas as $jenis)
                <option value="{{ $jenis->id }}" @selected(old('jenis_ormawa_id', $sikFlow->jenis_ormawa_id ?? null) == $jenis->id)>{{ $jenis->nama_jenis }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Nama Flow</label>
        <input type="text" class="form-control" name="nama_flow" value="{{ old('nama_flow', $sikFlow->nama_flow ?? '') }}" required>
    </div>
    <div class="col-md-2">
        <label class="form-label d-block">Status</label>
        <div class="form-check mt-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" class="form-check-input" name="is_active" value="1" id="is_active" @checked(old('is_active', $sikFlow->is_active ?? false))>
            <label class="form-check-label" for="is_active">Aktif</label>
        </div>
    </div>
</div>

<hr>
<div class="d-flex align-items-center mb-2">
    <h5 class="mb-0">Langkah Verifikasi</h5>
    <button class="btn btn-sm btn-primary ms-auto" type="button" onclick="addStep()">+ Tambah Step</button>
</div>
<div id="steps-wrapper" class="d-flex flex-column gap-2"></div>

<div class="mt-4">
    <button type="submit" class="btn btn-success">Simpan Flow</button>
</div>

@php
$oldSteps = old('steps');
$initialSteps = collect($oldSteps ?: (($sikFlow->steps ?? collect())->map(fn($step) => [
    'label_step' => $step->label_step,
    'role_target' => $step->role_target,
    'action_type' => $step->action_type,
    'sla_days' => $step->sla_days,
])->values()->all()));
@endphp

<script>
    const initialSteps = @json($initialSteps);

    function stepTemplate(index, value = {}) {
        return `
            <div class="card p-3 step-item" data-step-index="${index}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-1">
                        <label class="form-label">Urut</label>
                        <input class="form-control" value="${index + 1}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Label Step</label>
                        <input class="form-control" name="steps[${index}][label_step]" value="${value.label_step ?? ''}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Role Target</label>
                        <input class="form-control" name="steps[${index}][role_target]" value="${value.role_target ?? ''}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Aksi</label>
                        <select class="form-control" name="steps[${index}][action_type]" required>
                            <option value="verify" ${value.action_type === 'verify' ? 'selected' : ''}>Verify</option>
                            <option value="approve" ${value.action_type === 'approve' ? 'selected' : ''}>Approve</option>
                            <option value="issue" ${value.action_type === 'issue' ? 'selected' : ''}>Issue</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">SLA</label>
                        <input type="number" class="form-control" min="0" name="steps[${index}][sla_days]" value="${value.sla_days ?? ''}">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeStep(this)">Hapus</button>
                    </div>
                </div>
            </div>
        `;
    }

    function renderSteps() {
        const wrapper = document.getElementById('steps-wrapper');
        const steps = [...wrapper.querySelectorAll('.step-item')].map((item) => {
            const idx = item.dataset.stepIndex;
            return {
                label_step: item.querySelector(`[name=\"steps[${idx}][label_step]\"]`)?.value ?? '',
                role_target: item.querySelector(`[name=\"steps[${idx}][role_target]\"]`)?.value ?? '',
                action_type: item.querySelector(`[name=\"steps[${idx}][action_type]\"]`)?.value ?? 'verify',
                sla_days: item.querySelector(`[name=\"steps[${idx}][sla_days]\"]`)?.value ?? '',
            }
        });

        wrapper.innerHTML = '';
        steps.forEach((step, index) => {
            wrapper.insertAdjacentHTML('beforeend', stepTemplate(index, step));
        });
    }

    function addStep() {
        const wrapper = document.getElementById('steps-wrapper');
        const index = wrapper.querySelectorAll('.step-item').length;
        wrapper.insertAdjacentHTML('beforeend', stepTemplate(index));
    }

    function removeStep(button) {
        const wrapper = document.getElementById('steps-wrapper');
        if (wrapper.querySelectorAll('.step-item').length <= 1) {
            alert('Minimal harus ada 1 step.');
            return;
        }
        button.closest('.step-item').remove();
        renderSteps();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const wrapper = document.getElementById('steps-wrapper');
        const seedSteps = initialSteps.length > 0 ? initialSteps : [{ action_type: 'verify' }];

        seedSteps.forEach((step, index) => {
            wrapper.insertAdjacentHTML('beforeend', stepTemplate(index, step));
        });
    });
</script>

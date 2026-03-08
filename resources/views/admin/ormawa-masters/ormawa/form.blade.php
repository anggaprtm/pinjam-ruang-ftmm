@csrf
<div class="card"><div class="card-body row g-3">
<div class="col-md-4"><label>Jenis Ormawa</label><select name="jenis_ormawa_id" class="form-control" required><option value="">--pilih--</option>@foreach($jenisOrmawas as $jenis)<option value="{{ $jenis->id }}" @selected(old('jenis_ormawa_id', $item->jenis_ormawa_id ?? null) == $jenis->id)>{{ $jenis->nama_jenis }}</option>@endforeach</select></div>
<div class="col-md-4"><label>Nama Ormawa</label><input class="form-control" name="nama" value="{{ old('nama', $item->nama ?? '') }}" required></div>
<div class="col-md-2"><label>Kode</label><input class="form-control" name="kode" value="{{ old('kode', $item->kode ?? '') }}"></div>
<div class="col-md-2"><label>Status</label><div><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))> Aktif</div></div>
<div class="col-md-12"><label>User Operator Ormawa</label><select name="user_ids[]" class="form-control" multiple size="8">@foreach($users as $user)<option value="{{ $user->id }}" @selected(in_array($user->id, old('user_ids', isset($item) ? $item->users->pluck('id')->all() : [])))>{{ $user->name }} ({{ $user->email }})</option>@endforeach</select></div>
</div></div>
<button class="btn btn-primary mt-3">Simpan</button>

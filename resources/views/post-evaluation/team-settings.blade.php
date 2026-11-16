<section class="card card-body pasca-settings">
<div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3"><div><h5 class="mb-1">Tim evaluator</h5><p class="text-muted mb-0">Buat nama tim, tambahkan evaluator, lalu bagi peserta secara otomatis.</p></div><button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#createTeamModal"><i class="bx bx-plus me-1"></i>Buat Nama Tim</button></div>
<div class="d-flex flex-wrap gap-2 mb-4"><span class="badge bg-label-primary">1. Buat nama tim</span><span class="badge bg-label-info">2. Tambah evaluator</span><span class="badge bg-label-success">3. Bagi peserta otomatis</span></div>
<div class="d-flex flex-wrap gap-2 mb-4"><button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addEvaluatorModal" @disabled($teams->isEmpty())>Tambah Evaluator dari User</button><button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createEvaluatorModal" @disabled($teams->isEmpty())>Buat Akun Evaluator</button><form action="{{ route('post-evaluation.assign.auto',$training) }}" method="POST" onsubmit="return confirm('Bagi otomatis peserta yang belum memiliki tim? Pembagian yang sudah ada dan nilai wawancara tetap dipertahankan.')">
@csrf
<button class="btn btn-success" @disabled($teams->isEmpty())>Bagi Peserta Otomatis</button></form></div>
<p class="small text-muted">Peserta yang belum ditugaskan dibagi merata ke tim yang memiliki evaluator, mulai dari tim dengan peserta paling sedikit. Pembagian lama, dokumen, dan nilai wawancara tetap tersimpan.</p><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Nama tim</th><th>Evaluator</th><th>Peserta</th><th class="text-end">Aksi</th></tr></thead><tbody>
@forelse($teams as $team)
<tr><td class="fw-bold">{{ $team->name }}</td><td>
@forelse($members->get($team->id,collect()) as $member)
<div class="d-flex flex-wrap align-items-center gap-2 mb-2"><span class="badge bg-label-primary text-wrap">{{ $member->name }}</span><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#moveEvaluatorModal" data-user="{{ $member->user_id }}" data-name="{{ $member->name }}" data-team="{{ $team->id }}">Kelola</button></div>
@empty
<small class="text-muted">Belum ada evaluator</small>
@endforelse
</td><td>{{ collect($people)->filter(fn($p)=>$p['interview']?->team_id==$team->id)->count() }} peserta</td><td><div class="d-flex flex-wrap justify-content-end gap-2"><button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addEvaluatorModal" data-team="{{ $team->id }}">Tambah Evaluator</button><form action="{{ route('post-evaluation.teams.delete',[$training,$team->id]) }}" method="POST" onsubmit="return confirm('Hapus tim ini? Akun evaluator dan nilai wawancara tetap tersimpan.')">
@csrf
@method('DELETE')
<button class="btn btn-sm btn-outline-danger">Hapus Tim</button></form></div></td></tr>
@empty
<tr><td colspan="4" class="text-center text-muted py-5">Belum ada tim. Klik <strong>Buat Nama Tim</strong> untuk memulai.</td></tr>
@endforelse
</tbody></table></div>
</section>

<div class="modal fade" id="createTeamModal" tabindex="-1" aria-labelledby="createTeamTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><form class="modal-content" action="{{ route('post-evaluation.team',$training) }}" method="POST">
@csrf
<input type="hidden" name="_settings_form" value="create_team"><div class="modal-header"><h5 class="modal-title" id="createTeamTitle">Buat Nama Tim</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><label class="form-label" for="newTeamName">Nama tim</label><input id="newTeamName" name="name" class="form-control" maxlength="150" required value="{{ old('_settings_form')==='create_team' ? old('name') : '' }}" placeholder="Contoh: Tim Evaluasi 1"><small class="text-muted d-block mt-2">Evaluator ditambahkan setelah nama tim tersimpan.</small></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan Nama Tim</button></div></form></div></div>

<div class="modal fade" id="addEvaluatorModal" tabindex="-1" aria-labelledby="addEvaluatorTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg"><form class="modal-content" id="existingEvaluatorsForm" action="{{ route('post-evaluation.evaluator.add',$training) }}" method="POST">
@csrf
<input type="hidden" name="_settings_form" value="add_members"><div class="modal-header"><h5 class="modal-title" id="addEvaluatorTitle">Tambah Evaluator dari User</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><label class="form-label" for="existingEvaluatorTeam">Nama tim</label><select id="existingEvaluatorTeam" name="team_id" class="form-select mb-3" required><option value="">Pilih tim</option>
@foreach($teams as $team)
<option value="{{ $team->id }}" @selected(old('team_id')==$team->id)>{{ $team->name }}</option>
@endforeach
</select><label class="form-label" for="evaluatorSearch">Cari nama user</label><input id="evaluatorSearch" type="search" class="form-control mb-3" placeholder="Cari nama atau username"><div class="border rounded p-2" style="max-height:300px;overflow-y:auto">
@forelse($users as $user)
<label class="d-flex align-items-start gap-2 p-2 rounded evaluator-user-row" data-search="{{ mb_strtolower($user->name.' '.$user->username) }}"><input type="checkbox" class="form-check-input mt-1" name="users[]" value="{{ $user->id }}" @checked(in_array($user->id,old('users',[])))><span><strong class="d-block">{{ $user->name }}</strong><small class="text-muted">{{ $user->username }} - {{ $user->role }}</small></span></label>
@empty
<p class="text-muted mb-0">Belum ada user yang dapat dipilih.</p>
@endforelse
</div><small class="text-muted d-block mt-2">Centang satu atau beberapa user. Akun dan password yang sudah ada tidak diubah.</small><p id="chooseEvaluatorError" class="text-danger small mt-2 d-none" role="alert">Pilih minimal satu evaluator.</p></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Tambahkan ke Tim</button></div></form></div></div>

<div class="modal fade" id="createEvaluatorModal" tabindex="-1" aria-labelledby="createEvaluatorTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><form class="modal-content" action="{{ route('post-evaluation.evaluator.store',$training) }}" method="POST" autocomplete="off">
@csrf
<input type="hidden" name="_settings_form" value="create_evaluator"><div class="modal-header"><h5 class="modal-title" id="createEvaluatorTitle">Tambah evaluator baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><label class="form-label" for="newEvaluatorTeam">Nama tim</label><select id="newEvaluatorTeam" name="team_id" class="form-select mb-3" required><option value="">Pilih tim</option>
@foreach($teams as $team)
<option value="{{ $team->id }}" @selected(old('team_id')==$team->id)>{{ $team->name }}</option>
@endforeach
</select><label class="form-label" for="evaluatorName">Nama evaluator</label><input id="evaluatorName" class="form-control mb-3" name="name" value="{{ old('_settings_form')==='create_evaluator' ? old('name') : '' }}" maxlength="255" required><label class="form-label" for="evaluatorEmail">Email login</label><input id="evaluatorEmail" class="form-control mb-3" name="email" type="email" value="{{ old('email') }}" maxlength="255" required><label class="form-label" for="evaluatorPassword">Password</label><input id="evaluatorPassword" class="form-control mb-3" name="password" type="password" minlength="8" required autocomplete="new-password"><label class="form-label" for="evaluatorPasswordConfirmation">Konfirmasi password</label><input id="evaluatorPasswordConfirmation" class="form-control" name="password_confirmation" type="password" minlength="8" required autocomplete="new-password"><small class="text-muted d-block mt-2">Minimal 8 karakter. Akun baru langsung ditambahkan ke tim yang dipilih.</small></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Buat Akun & Tambahkan</button></div></form></div></div>

<div class="modal fade" id="moveEvaluatorModal" tabindex="-1" aria-labelledby="moveEvaluatorTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><form class="modal-content" action="{{ route('post-evaluation.evaluator.move',$training) }}" method="POST" onsubmit="return confirm('Ubah penugasan evaluator ini? Peserta, dokumen dan nilai wawancara tetap pada timnya.')">
@csrf
<input type="hidden" name="_settings_form" value="move_evaluator"><input type="hidden" name="user_id" value="{{ old('user_id') }}"><input type="hidden" name="from_team_id" value="{{ old('from_team_id') }}"><div class="modal-header"><h5 class="modal-title" id="moveEvaluatorTitle">Kelola Evaluator</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><p class="fw-bold" id="movingEvaluatorName">Evaluator yang dipilih</p><label class="form-label" for="moveEvaluatorTarget">Pindahkan ke tim</label><select id="moveEvaluatorTarget" name="to_team_id" class="form-select"><option value="">Keluarkan dari tim saat ini</option>
@foreach($teams as $team)
<option value="{{ $team->id }}" @selected(old('to_team_id')==$team->id)>{{ $team->name }}</option>
@endforeach
</select><p class="small text-muted mt-3 mb-0">Untuk mengganti evaluator, keluarkan evaluator lama lalu tambahkan evaluator pengganti dari daftar user. Pemindahan hanya mengubah keanggotaan tim, bukan memindahkan peserta atau menghapus akun dan hasil wawancara.</p></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div>

@push('js')
<script>
(()=>{
 document.getElementById('moveEvaluatorModal')?.addEventListener('show.bs.modal',event=>{const button=event.relatedTarget,modal=event.target;if(button){modal.querySelector('[name="user_id"]').value=button.dataset.user;modal.querySelector('[name="from_team_id"]').value=button.dataset.team;document.getElementById('movingEvaluatorName').textContent=button.dataset.name;modal.querySelector('[name="to_team_id"]').value='';}const source=modal.querySelector('[name="from_team_id"]').value;modal.querySelectorAll('[name="to_team_id"] option').forEach(option=>option.disabled=option.value===source);});
 for(const id of ['addEvaluatorModal','createEvaluatorModal']) document.getElementById(id)?.addEventListener('show.bs.modal',event=>{const team=event.relatedTarget?.dataset.team;if(team)event.target.querySelector('[name="team_id"]').value=team;});
 document.getElementById('evaluatorSearch')?.addEventListener('input',function(){const search=this.value.toLocaleLowerCase('id');document.querySelectorAll('.evaluator-user-row').forEach(row=>{row.style.setProperty('display',row.dataset.search.includes(search)?'flex':'none','important');});});
 document.getElementById('existingEvaluatorsForm')?.addEventListener('submit',event=>{const missing=!event.target.querySelector('[name="users[]"]:checked');document.getElementById('chooseEvaluatorError').classList.toggle('d-none',!missing);if(missing)event.preventDefault();});
})();
</script>
@if($errors->any())
<script>document.addEventListener('DOMContentLoaded',()=>{const modalId=@js(['create_team'=>'createTeamModal','add_members'=>'addEvaluatorModal','create_evaluator'=>'createEvaluatorModal','move_evaluator'=>'moveEvaluatorModal'][old('_settings_form')] ?? null);if(modalId)bootstrap.Modal.getOrCreateInstance(document.getElementById(modalId)).show();});</script>
@endif
@endpush

<div class="modal fade" id="modalUserRoles" tabindex="-1" aria-labelledby="userRolesTitle" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered"><form id="userRolesForm" class="modal-content" method="POST">@csrf @method('PUT')
<div class="modal-header"><div><h5 id="userRolesTitle" class="modal-title">Kelola Peran Pengguna</h5><small id="userRolesName" class="text-muted"></small></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
<div class="modal-body"><p class="text-muted">Pilih satu atau beberapa peran tambahan. Identitas peserta/pengajar dan riwayatnya tetap tersimpan. Pengguna dapat membuka menu setiap peran melalui Akses Peran.</p><input type="hidden" name="roles[]" id="primaryRoleValue">
<div class="row g-3">
@foreach(\App\Support\UserRoles::LABELS as $role=>$label)
<div class="col-md-6"><div class="border rounded p-3 h-100"><div class="form-check"><input class="form-check-input user-role-option" type="checkbox" name="roles[]" value="{{ $role }}" id="assignedRole_{{ $role }}"><label class="form-check-label" for="assignedRole_{{ $role }}">{{ $label }} <small class="primary-role-note text-muted d-none">(peran utama)</small></label></div>
@if(in_array($role,\App\Support\UserRoles::SCOPED,true))<div class="role-scope mt-2 d-none" data-role="{{ $role }}"><label class="form-label small" for="roleBidang_{{ $role }}">Bidang untuk {{ $label }}</label><select class="form-select form-select-sm" name="role_bidang[{{ $role }}]" id="roleBidang_{{ $role }}" disabled><option value="">Pilih bidang</option>@foreach($listBidang as $bidang)<option value="{{ $bidang }}">{{ $bidang }}</option>@endforeach</select></div>@endif
</div></div>
@endforeach
</div><div class="alert alert-warning small mt-3 mb-0">Superadmin memberikan akses penuh termasuk pengaturan user dan peran. Peran lainnya mengikuti kewenangan dan bidang yang dipilih.</div></div>
<div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan Peran</button></div></form></div></div>
@push('js')
<script>
function syncRoleScopes(){document.querySelectorAll('#modalUserRoles .role-scope').forEach(scope=>{const option=document.getElementById('assignedRole_'+scope.dataset.role),select=scope.querySelector('select');scope.classList.toggle('d-none',!option.checked);select.disabled=!option.checked||option.disabled;select.required=option.checked&&!option.disabled;});}
document.querySelectorAll('.user-role-option').forEach(option=>option.addEventListener('change',syncRoleScopes));
function manageUserRoles(user){
 document.getElementById('userRolesForm').action=@json(url('users'))+'/'+user.id+'/roles';
 document.getElementById('userRolesName').textContent=user.name;
 document.getElementById('primaryRoleValue').value=user.role;
 const assignments=user.role_assignments||[];
 document.querySelectorAll('.user-role-option').forEach(option=>{option.disabled=option.value===user.role;option.checked=option.disabled||assignments.some(item=>item.role===option.value);option.closest('.form-check').querySelector('.primary-role-note').classList.toggle('d-none',!option.disabled);});
 document.querySelectorAll('#modalUserRoles .role-scope').forEach(scope=>{scope.querySelector('select').value=scope.dataset.role===user.role?(user.bidang||''):(assignments.find(item=>item.role===scope.dataset.role)?.bidang||'');});
 syncRoleScopes();
}
</script>
@endpush

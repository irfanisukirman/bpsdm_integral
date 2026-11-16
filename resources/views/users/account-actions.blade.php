<div class="modal fade" id="modalUserActions" tabindex="-1" aria-labelledby="userActionsTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
<div class="modal-header"><div><h5 class="modal-title" id="userActionsTitle">Tindakan Akun</h5><small id="userActionsName" class="text-muted"></small></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
<div class="modal-body">
<form id="userApproveForm" method="POST" class="border rounded p-3 mb-3 d-none">@csrf @method('PUT')<h6 class="mb-1">Persetujuan kategori akun</h6><p id="userApproveDescription" class="small text-muted mb-3"></p><button type="submit" class="btn btn-sm btn-success"><i class="bx bx-user-check me-1"></i>Setujui Pengajuan</button></form>
<form id="userResetForm" method="POST" class="border rounded p-3 mb-3">@csrf @method('PUT')<h6 class="mb-1">Reset password</h6><p class="small text-muted mb-3">Password akun ini akan diganti menjadi <code>password123</code>. Pemilik akun perlu menggantinya setelah login.</p><button type="submit" class="btn btn-sm btn-outline-primary"><i class="bx bx-reset me-1"></i>Reset Password</button></form>
<form id="userDeleteForm" method="POST" class="border border-danger rounded p-3">@csrf @method('DELETE')<h6 class="text-danger mb-1">Hapus akun peserta</h6><p id="userDeleteDescription" class="small text-muted mb-3">Akun peserta beserta seluruh datanya akan dihapus. Tindakan ini tidak dapat dibatalkan.</p><div id="userDeleteConfirmation" class="form-check mb-3"><input class="form-check-input" type="checkbox" id="userDeleteConfirmed" required><label class="form-check-label small" for="userDeleteConfirmed">Saya memahami akun dan seluruh datanya akan dihapus.</label></div><button id="userDeleteButton" type="submit" class="btn btn-sm btn-outline-danger"><i class="bx bx-trash me-1"></i>Hapus Akun</button></form>
</div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button></div>
</div></div></div>
@push('js')
<script>
function openUserActions(user){
 const base=@json(url('users'))+'/'+user.id;
 document.getElementById('userActionsName').textContent=user.name;
 document.getElementById('userResetForm').action=base+'/reset-password';
 document.getElementById('userDeleteForm').action=base;
 const canDelete=user.role==='participant'&&!(user.role_assignments||[]).length&&user.id!==@json(auth()->id());
 document.getElementById('userDeleteButton').disabled=!canDelete;
 document.getElementById('userDeleteConfirmed').checked=false;
 document.getElementById('userDeleteConfirmed').disabled=!canDelete;
 document.getElementById('userDeleteConfirmation').classList.toggle('d-none',!canDelete);
 document.getElementById('userDeleteDescription').textContent=canDelete?'Akun peserta beserta seluruh datanya akan dihapus. Tindakan ini tidak dapat dibatalkan.':(user.id===@json(auth()->id())?'Akun yang sedang dipakai tidak dapat dihapus.':'Penghapusan menyeluruh tersedia untuk akun peserta tanpa peran tambahan. Kelola tanggung jawab akun terlebih dahulu.');
 const pending=user.user_type_status==='pending'&&['narasumber','mitra'].includes(user.user_type);
 document.getElementById('userApproveForm').classList.toggle('d-none',!pending);
 document.getElementById('userApproveForm').action=base+'/approve-type';
 document.getElementById('userApproveDescription').textContent=pending?'Setujui pengajuan sebagai '+(user.user_type==='narasumber'?'Narasumber':'Mitra')+'.':'';
}
</script>
@endpush

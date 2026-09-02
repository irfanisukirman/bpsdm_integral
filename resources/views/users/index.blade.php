@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Sistem /</span> Kelola Pengguna
        </h4>

        <div class="d-flex flex-wrap gap-2">
            <!-- FORM PENCARIAN USER -->
            <form action="{{ route('users.index') }}" method="GET" class="d-flex gap-2 flex-grow-1" style="min-width: 300px;">
                <select name="category" class="form-select shadow-sm" style="max-width: 230px;" onchange="this.form.submit()" aria-label="Filter kategori akun">
                    <option value="">Semua kategori</option>
                    <option value="admin" @selected(($category ?? '') === 'admin')>Admin & Superadmin</option>
                    <option value="peserta" @selected(($category ?? '') === 'peserta')>Peserta</option>
                    <option value="narasumber" @selected(($category ?? '') === 'narasumber')>Narasumber</option>
                    <option value="mitra" @selected(($category ?? '') === 'mitra')>Mitra</option>
                </select>
                <div class="input-group input-group-merge shadow-sm">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Cari Nama, NIP, atau Bidang..." 
                           value="{{ $search ?? '' }}">
                    @if($search || $category)
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary px-2" title="Hapus filter">
                            <i class="bx bx-x"></i>
                        </a>
                    @endif
                </div>
            </form>

            <!-- TOMBOL TAMBAH PENGGUNA -->
            <button type="button" class="btn btn-info shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddPengajar">
                <i class="bx bx-user-plus me-1"></i> Tambah Pengguna
            </button>

            <!-- TOMBOL TAMBAH USER (ADMIN/SUPERADMIN) -->
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddUser">
                <i class="bx bx-user-plus me-1"></i> Tambah Admin
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible" role="alert">
            <i class="bx bx-x-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($search)
        <div class="mb-3 animate__animated animate__fadeIn">
            <small class="text-muted">Hasil pencarian untuk: <span class="fw-bold text-primary">"{{ $search }}"</span></small>
        </div>
    @endif

    <div class="row g-3 mb-4">
        @foreach([
            ['key' => '', 'label' => 'Semua Pengguna', 'value' => $stats['all'], 'icon' => 'bx-group', 'color' => 'primary'],
            ['key' => 'admin', 'label' => 'Admin & Superadmin', 'value' => $stats['admin'], 'icon' => 'bx-shield-quarter', 'color' => 'danger'],
            ['key' => 'peserta', 'label' => 'Peserta', 'value' => $stats['peserta'], 'icon' => 'bx-user', 'color' => 'success'],
            ['key' => 'narasumber', 'label' => 'Narasumber', 'value' => $stats['narasumber'], 'icon' => 'bx-chalkboard', 'color' => 'info'],
            ['key' => 'mitra', 'label' => 'Mitra', 'value' => $stats['mitra'], 'icon' => 'bx-handshake', 'color' => 'warning'],
        ] as $item)
            <div class="col-6 col-xl">
                <a href="{{ route('users.index', array_filter(['category' => $item['key'], 'search' => $search])) }}" class="card border-0 shadow-sm h-100 text-decoration-none {{ ($category ?? '') === $item['key'] ? 'border border-2 border-'.$item['color'] : '' }}">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <span class="avatar-initial rounded bg-label-{{ $item['color'] }} p-2"><i class="bx {{ $item['icon'] }} fs-4"></i></span>
                        <div>
                            <div class="h5 mb-0 text-dark">{{ number_format($item['value']) }}</div>
                            <small class="text-muted">{{ $item['label'] }}</small>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
    <div class="card shadow-sm border-0">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nama & Role</th>
                        <th>Kategori & Bidang</th>
                        <th>Username / NIP</th>
                        <th>WhatsApp</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex justify-content-start align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-{{ $user->role == 'superadmin' ? 'danger' : ($user->role == 'admin_bidang' ? 'primary' : ($user->role == 'pengajar' ? 'info' : 'success')) }}">
                                        {{ substr($user->name, 0, 1) }}
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark">{{ $user->name }}</span>
                                    <small class="text-muted" style="font-size: 10px;">
                                        {{ in_array($user->role, ['superadmin', 'admin_bidang', 'admin_aset'], true)
                                            ? strtoupper(str_replace('_', ' ', $user->role))
                                            : strtoupper(match($user->user_type) { 'narasumber' => 'Narasumber', 'mitra' => 'Mitra', default => 'Peserta' }) }}
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td class="text-wrap">
                            @php
                                $isAdmin = in_array($user->role, ['superadmin', 'admin_bidang', 'admin_aset'], true);
                                $typeLabel = match($user->user_type) { 'narasumber' => 'Narasumber', 'mitra' => 'Mitra', default => 'Peserta' };
                                $scopeLabel = $isAdmin ? ($user->role === 'superadmin' ? 'Superadmin' : ($user->role === 'admin_aset' ? 'Admin Pengelola Aset' : 'Admin Bidang')) : $typeLabel;
                                $scopeColor = $isAdmin ? 'danger' : match($user->user_type) { 'narasumber' => 'info', 'mitra' => 'warning', default => 'success' };
                            @endphp
                            <span class="badge bg-label-{{ $scopeColor }} mb-1">{{ $scopeLabel }}</span>
                            @if(!$isAdmin && $user->user_type_status === 'pending')
                                <span class="badge bg-label-warning mb-1">Menunggu Persetujuan</span>
                            @endif
                            <small class="text-muted d-block" style="font-size: 11px; line-height: 1.35; max-width: 280px;">
                                {{ $isAdmin ? ($user->bidang ?: 'Akses lintas bidang') : ($user->instansi ?: 'Data instansi belum diisi') }}
                            </small>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <code class="text-primary fw-bold" style="font-size: 12px;">{{ $user->username }}</code>
                                @if($user->nip_nik)
                                    <small class="text-danger" style="font-size: 10px;">NIP: {{ $user->nip_nik }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <a href="https://wa.me/{{ $user->whatsapp }}" target="_blank" class="text-body small">
                                <i class="bx bxl-whatsapp text-success me-1"></i>{{ $user->whatsapp }}
                            </a>
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="editUser({{ json_encode($user) }})" data-bs-toggle="modal" data-bs-target="#modalEditUser">
                                        <i class="bx bx-edit-alt me-1"></i> Edit User
                                    </a>                                    @if($user->user_type_status === 'pending' && in_array($user->user_type, ['narasumber', 'mitra'], true))
                                        <form action="{{ route('users.approve-type', $user) }}" method="POST" onsubmit="return confirm('Setujui {{ $user->name }} sebagai {{ ucfirst($user->user_type) }}?')">
                                            @csrf @method('PUT')
                                            <button class="dropdown-item text-success"><i class="bx bx-user-check me-1"></i> Setujui sebagai {{ ucfirst($user->user_type) }}</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('users.reset-password', $user->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <button class="dropdown-item" onclick="return confirm('Reset password menjadi password123?')"><i class="bx bx-refresh me-1"></i> Reset Password</button>
                                    </form>
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus user ini beserta seluruh datanya?')">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger"><i class="bx bx-trash me-1"></i> Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bx bx-user-x h1 d-block mb-2"></i>
                            User tidak ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($users->hasPages())
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
            <small class="text-muted">
                Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} pengguna
            </small>
            {{ $users->onEachSide(1)->links() }}
        </div>
    @endif
</div>

<!-- ==============================================
     MODAL KHUSUS TAMBAH PENGAJAR 
     ============================================== -->
<div class="modal fade" id="modalAddPengajar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white">Tambah Peserta, Narasumber, atau Mitra</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-4">
                        <i class="bx bx-info-circle me-1"></i> Pilih jenis akun pengguna. Role administratif hanya tersedia melalui tombol Tambah Admin.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Pengguna <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="participant">Peserta</option>
                            <option value="pengajar">Narasumber</option>
                            <option value="mitra">Mitra</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap (Beserta Gelar) <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required />
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIP / NIK <span class="text-danger">*</span></label>
                            <input type="text" name="nip_nik" class="form-control" required />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nomor WA <span class="text-danger">*</span></label>
                            <input type="number" name="whatsapp" class="form-control" placeholder="628..." required />
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Bidang <small>(Opsional)</small></label>
                        <select name="bidang" class="form-select">
                            <option value="">Tanpa bidang</option>
                            @foreach($listBidang as $b)
                                <option value="{{ $b }}">{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <hr class="my-4">
                    <h6 class="fw-bold text-info"><i class="bx bx-lock-alt me-1"></i> Data Login Default</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="text" name="password" class="form-control" value="password123" required />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info text-white w-100">Simpan Akun Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==============================================
     MODAL TAMBAH USER (ADMIN/SUPERADMIN) 
     ============================================== -->
<div class="modal fade" id="modalAddUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Admin / Superadmin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap PIC</label>
                        <input type="text" name="name" class="form-control" required />
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" id="create_role" class="form-select" required>
                                <option value="admin_bidang">Admin Bidang</option>
                                <option value="admin_aset">Admin Pengelola Aset</option>
                                <option value="superadmin">Superadmin</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nomor WA</label>
                            <input type="number" name="whatsapp" class="form-control" placeholder="628..." required />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Penyelenggara / Bidang</label>
                        <select name="bidang" id="create_bidang" class="form-select" required>
                            <option value="Pengelola Aset" data-asset-only>Pengelola Aset</option>
                            @foreach($listBidang as $b)
                                <option value="{{ $b }}">{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==============================================
     MODAL EDIT USER (GLOBAL)
     ============================================== -->
<div class="modal fade" id="modalEditUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="formEditUser" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Edit Data Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">NAMA LENGKAP</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required />
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">USERNAME</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">NIP / NIK</label>
                        <input type="text" name="nip_nik" id="edit_nip_nik" class="form-control" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">ROLE / HAK AKSES</label>
                        <select name="role" id="edit_role" class="form-select" required>
                            <option value="participant">Peserta</option>
                            <option value="pengajar">Narasumber</option>
                            <option value="mitra">Mitra</option>
                            <option value="admin_bidang">Admin Bidang</option>
                            <option value="admin_aset">Admin Pengelola Aset</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">NOMOR WA</label>
                        <input type="number" name="whatsapp" id="edit_whatsapp" class="form-control" required />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">PENYELENGGARA / BIDANG</label>
                        <select name="bidang" id="edit_bidang" class="form-select">
                            <option value="">Tanpa bidang</option>
                            <option value="Pengelola Aset" data-asset-only>Pengelola Aset</option>
                        @foreach($listBidang as $b)
                            <option value="{{ $b }}">{{ $b }}</option>
@endforeach
                    </select>
                </div>
                <div class="alert alert-info py-2 mb-0">
                    <small><i class="bx bx-info-circle me-1"></i> Gunakan fitur tombol Dropdown -> <strong>Reset Password</strong> pada tabel jika pengguna lupa passwordnya.</small>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Update Data User</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    function syncBidangByRole(roleSelector, bidangSelector) {
        const role = $(roleSelector).val();
        const bidang = $(bidangSelector);
        const assetOption = bidang.find('option[value="Pengelola Aset"]');
        const regularOptions = bidang.find('option').not(assetOption);

        if (role === 'admin_aset') {
            regularOptions.prop('disabled', true).prop('hidden', true);
            assetOption.prop('disabled', false).prop('hidden', false);
            bidang.val('Pengelola Aset');
        } else {
            assetOption.prop('disabled', true).prop('hidden', true);
            regularOptions.prop('disabled', false).prop('hidden', false);
            if (bidang.val() === 'Pengelola Aset') {
                bidang.val(role === 'admin_bidang' ? bidang.find('option:not([data-asset-only])').first().val() : '');
            }
        }
    }

    $(function () {
        syncBidangByRole('#create_role', '#create_bidang');
        $('#create_role').on('change', function () {
            syncBidangByRole('#create_role', '#create_bidang');
        });
        $('#edit_role').on('change', function () {
            syncBidangByRole('#edit_role', '#edit_bidang');
        });
    });

    /**
     * Fungsi untuk mengisi data ke Modal Edit secara Dinamis
     */
    function editUser(data) {
        // Set URL Form Action untuk metode PUT
        const url = "{{ url('users') }}/" + data.id;
        $('#formEditUser').attr('action', url);

        // Isi field input dengan data dari database
        $('#edit_name').val(data.name);
        $('#edit_username').val(data.username);
        $('#edit_nip_nik').val(data.nip_nik);
        const administrativeRoles = ['superadmin', 'admin_bidang', 'admin_aset'];
        const editableRole = administrativeRoles.includes(data.role)
            ? data.role
            : (data.user_type === 'narasumber' ? 'pengajar' : (data.user_type === 'mitra' ? 'mitra' : 'participant'));
        $('#edit_role').val(editableRole);
        syncBidangByRole('#edit_role', '#edit_bidang');
        $('#edit_whatsapp').val(data.whatsapp);
        
        // Handle select bidang (jika null, pilih yang value-nya kosong)
        if(data.bidang) {
            $('#edit_bidang').val(data.bidang);
        } else {
            $('#edit_bidang').val('');
        }
    }
</script>
@endpush

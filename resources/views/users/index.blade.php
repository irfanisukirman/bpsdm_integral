@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Sistem /</span> Kelola Pengguna</h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddUser">
            <i class="bx bx-user-plus me-1"></i> Tambah User
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nama & Role</th>
                        <th>Bidang / Penyelenggara</th>
                        <th>Username</th>
                        <th>WhatsApp</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex justify-content-start align-items-center">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">{{ $user->name }}</span>
                                    <small class="text-muted">
                                        <span class="badge {{ $user->role == 'superadmin' ? 'bg-label-danger' : 'bg-label-primary' }} btn-xs">
                                            {{ strtoupper($user->role) }}
                                        </span>
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <small class="text-wrap" style="width: 250px; display: block; line-height: 1.2">
                                {{ $user->bidang }}
                            </small>
                        </td>
                        <td><code>{{ $user->username }}</code></td>
                        <td>{{ $user->whatsapp }}</td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <form action="{{ route('users.reset-password', $user->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <button class="dropdown-item"><i class="bx bx-refresh me-1"></i> Reset Pass</button>
                                    </form>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger"><i class="bx bx-trash me-1"></i> Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="modalAddUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengguna Baru</h5>
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
                            <select name="role" class="form-select" required>
                                <option value="admin_bidang">Admin Bidang</option>
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
                        <select name="bidang" class="form-select" required>
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
@endsection
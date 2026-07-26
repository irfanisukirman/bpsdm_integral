@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Akun /</span> Pengaturan Profil</h4>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <h5 class="card-header">Detail Profil</h5>
                <!-- Form Update Profil -->
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="card-body">
                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                            {{-- Preview Foto --}}
                            @if($user->profile_photo)
                                <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="user-avatar" class="d-block rounded" height="100" width="100" id="uploadedAvatar" />
                            @else
                                <div class="avatar avatar-xl me-2">
                                    <span class="avatar-initial rounded bg-label-primary" style="font-size: 40px;">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif

                            <div class="button-wrapper">
                                <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                                    <span class="d-none d-sm-block">Unggah Foto Baru</span>
                                    <i class="bx bx-upload d-block d-sm-none"></i>
                                    <input type="file" id="upload" name="profile_photo" class="account-file-input" hidden accept="image/png, image/jpeg" />
                                </label>
                                <p class="text-muted mb-0">Hanya JPG atau PNG. Maksimal 2MB.</p>
                            </div>
                        </div>
                    </div>
                    <hr class="my-0" />
                    <div class="card-body">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input class="form-control" type="text" name="name" value="{{ $user->name }}" required />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">Username</label>
                                <input class="form-control" type="text" value="{{ $user->username }}" disabled />
                                <small class="text-muted">Username tidak dapat diubah</small>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">Nomor WhatsApp</label>
                                <input class="form-control" type="text" name="whatsapp" value="{{ $user->whatsapp }}" required />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">Bidang / Unit Kerja</label>
                                <input class="form-control" type="text" value="{{ $user->bidang }}" disabled />
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Card Ganti Password -->
            <div class="card">
                <h5 class="card-header">Keamanan Akun (Ganti Password)</h5>
                <div class="card-body">
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf @method('PUT')
                        <div class="row">
                            <div class="mb-3 col-md-4">
                                <label class="form-label">Password Sekarang</label>
                                <input class="form-control" type="password" name="current_password" required />
                            </div>
                            <div class="mb-3 col-md-4">
                                <label class="form-label">Password Baru</label>
                                <input class="form-control" type="password" name="new_password" required />
                            </div>
                            <div class="mb-3 col-md-4">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input class="form-control" type="password" name="new_password_confirmation" required />
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning">Ubah Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
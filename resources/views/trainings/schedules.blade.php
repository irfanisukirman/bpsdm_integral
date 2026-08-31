@extends('layouts.master')

@section('title', 'Jadwal Pelatihan')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Pelatihan /</span> Jadwal: {{ $training->nama_pelatihan }}
    </h4>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('trainings.manage', $training->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
        <button type="button" class="btn btn-success btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportSchedule">
            <i class="bx bx-file me-1"></i> Import Excel
        </button>
        <a href="{{ route('schedules.pdf', $training->id) }}" class="btn btn-danger btn-sm shadow-sm" target="_blank">
            <i class="bx bxs-file-pdf me-1"></i> Unduh PDF
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm alert-dismissible" role="alert">
        <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm alert-dismissible" role="alert">
        <i class="bx bx-error-circle me-1"></i> <strong>Gagal:</strong> {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <!-- KOLOM KIRI: FORM TAMBAH MANUAL -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0">
            <h5 class="card-header border-bottom">Tambah Sesi Jadwal</h5>
            <div class="card-body pt-3">
                <form action="{{ route('schedules.store', $training->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label fw-bold">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" id="create_start" class="form-control" value="{{ old('start_time') }}" required>
                        </div>
                        <div class="col-3">
                            <label class="form-label fw-bold">Jumlah JP <span class="text-danger">*</span></label>
                            <input type="number" name="jp" id="create_jp" class="form-control" value="{{ old('jp', 1) }}" min="1" max="24" required>
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-bold">Jam Selesai</label>
                            <input type="time" id="create_end" class="form-control bg-light" readonly tabindex="-1">
                        </div>
                        <div class="col-12"><small class="text-muted"><i class="bx bx-calculator me-1"></i>Otomatis: 1 JP = 45 menit.</small></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Materi / Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" name="activity" class="form-control" value="{{ old('activity') }}" placeholder="Contoh: Pengantar Digitalisasi" required>
                    </div>
                    
                    <!-- INPUT PILIH PENGAJAR -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-info"><i class="bx bx-chalkboard me-1"></i>Tenaga Pengajar / Fasilitator</label>
                        <select name="pengajar_id" class="form-select select2">
                            <option value="">-- Tanpa Pengajar / Sesi Mandiri --</option>
                            @foreach($pengajars as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->name }} {{ $p->nip_nik ? "({$p->nip_nik})" : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small">Menampilkan akun non-administratif yang tidak terikat bidang, termasuk pengajar atau narasumber dari luar.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tempat / Ruangan</label>
                        <select name="venue_type" id="create_venue_type" class="form-select mb-2">
                            <option value="internal">Internal BPSDM</option>
                            <option value="external">Eksternal</option>
                        </select>
                        <div id="create_internal">
                            <select name="asset_ids[]" class="form-select select2-assets" multiple>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}">{{ $asset->name }} — {{ $asset->location }} ({{ $asset->capacity ?: '-' }} orang)</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Bisa memilih beberapa ruangan/aset. Bentrok diperiksa saat disimpan.</small>
                        </div>
                        <div id="create_external" class="d-none">
                            <input name="external_place" class="form-control mb-2" value="{{ old('external_place') }}" placeholder="Nama/alamat tempat eksternal (opsional jika menggunakan Zoom)">
                            <div class="input-group">
                                <span class="input-group-text text-success"><i class="bx bx-video"></i></span>
                                <input type="url" name="link_zoom" class="form-control" value="{{ old('link_zoom') }}" placeholder="https://zoom.us/j/...">
                            </div>
                            <small class="text-muted">Isi tempat eksternal, tautan Zoom, atau keduanya.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-primary">Penanggung Jawab (PIC) <span class="text-danger">*</span></label>
                        <input type="text" name="pic" class="form-control" placeholder="Nama PIC Kelas / Panitia" value="{{ old('pic', auth()->user()->name) }}" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 shadow-sm">
                        <i class="bx bx-plus me-1"></i> Simpan Jadwal
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: TABEL DAFTAR JADWAL -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu & JP</th>
                            <th>Materi, Pengajar & Link</th> 
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($schedules as $s)
                        <tr>
                            <td>
                                <span class="badge bg-label-secondary">
                                    {{ \Carbon\Carbon::parse($s->date)->translatedFormat('d M Y') }}
                                </span><br>
                                <small class="fw-bold text-dark">{{ $s->start_time }} - {{ $s->end_time }}</small>
                                @if($s->jp)
                                    <span class="badge bg-label-primary ms-1">{{ $s->jp }} JP</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-1">{{ $s->activity }}</div>
                                <div class="d-flex flex-column gap-1">
                                    @if($s->pengajar)
                                        <small class="text-info fw-semibold">
                                            <i class="bx bx-chalkboard me-1"></i>Pengajar: {{ $s->pengajar->name }}
                                        </small>
                                    @else
                                        <small class="text-muted"><i class="bx bx-minus me-1"></i>Tanpa Pengajar Khusus</small>
                                    @endif
                                    
                                    <div class="d-flex align-items-center gap-2">
                                        <small class="text-muted">
                                            <i class="bx bx-user me-1"></i>PIC: {{ $s->pic }}
                                        </small>
                                        @if($s->link_zoom)
                                            <a href="{{ $s->link_zoom }}" target="_blank" class="badge bg-label-success text-decoration-none">
                                                <i class="bx bx-video me-1"></i> Buka Zoom
                                            </a>
                                        @endif
                                    </div>
                                    <small class="text-primary">
                                        <i class="bx bx-map me-1"></i>
                                        @if($s->venue_type === 'internal')
                                            {{ $s->bookings->pluck('asset.name')->filter()->join(', ') ?: 'Aset internal belum dipilih' }}
                                        @else
                                            {{ $s->external_place ?: 'Lokasi eksternal belum diisi' }}
                                        @endif
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <!-- TOMBOL EDIT -->
                                    <button class="btn btn-sm btn-icon btn-outline-warning" 
                                        onclick="editSchedule({{ json_encode($s) }})"
                                        data-bs-toggle="modal" data-bs-target="#modalEditSchedule" title="Edit Jadwal">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>

                                    <!-- TOMBOL HAPUS -->
                                    <form action="{{ route('schedules.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus sesi jadwal ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus Jadwal">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="bx bx-calendar-x fs-1 d-block mb-2"></i>
                                Belum ada sesi jadwal untuk pelatihan ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL IMPORT JADWAL EXCEL -->
<div class="modal fade" id="modalImportSchedule" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('schedules.import', $training->id) }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white"><i class="bx bx-file me-1"></i> Import Jadwal dari Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info py-2 small mb-3">
                    <i class="bx bx-info-circle me-1"></i> Template kini mendukung pengisian <strong>Link Zoom</strong> dan pencocokan otomatis <strong>Tenaga Pengajar</strong>.
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih File Excel (.xlsx / .xls) <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control" accept=".xlsx, .xls" required>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2">
                    <span class="small text-muted">Belum punya formatnya?</span>
                    <a href="{{ route('schedules.template') }}" class="btn btn-sm btn-outline-success">
                        <i class="bx bx-download me-1"></i> Unduh Template Excel
                    </a>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success">Mulai Import</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT JADWAL -->
<div class="modal fade" id="modalEditSchedule" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="formEditSchedule" class="modal-content">
            @csrf 
            @method('PUT')
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Edit Sesi Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="edit_date" class="form-control" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <label class="form-label fw-bold">Jam Mulai <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" id="edit_start" class="form-control" required>
                    </div>
                    <div class="col-3">
                        <label class="form-label fw-bold">Jumlah JP <span class="text-danger">*</span></label>
                        <input type="number" name="jp" id="edit_jp" class="form-control" min="1" max="24" required>
                    </div>
                    <div class="col-5">
                        <label class="form-label fw-bold">Jam Selesai</label>
                        <input type="time" id="edit_end" class="form-control bg-light" readonly tabindex="-1">
                    </div>
                    <div class="col-12"><small class="text-muted"><i class="bx bx-calculator me-1"></i>Otomatis: 1 JP = 45 menit.</small></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Materi / Kegiatan <span class="text-danger">*</span></label>
                    <input type="text" name="activity" id="edit_activity" class="form-control" required>
                </div>
                
                <!-- DROPDOWN PENGAJAR DI MODAL EDIT -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-info"><i class="bx bx-chalkboard me-1"></i>Tenaga Pengajar / Fasilitator</label>
                    <select name="pengajar_id" id="edit_pengajar_id" class="form-select select2-modal">
                        <option value="">-- Tanpa Pengajar / Sesi Mandiri --</option>
                        @foreach($pengajars as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->name }} {{ $p->nip_nik ? "({$p->nip_nik})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Tempat / Ruangan</label>
                    <select name="venue_type" id="edit_venue_type" class="form-select mb-2">
                        <option value="internal">Internal BPSDM</option>
                        <option value="external">Eksternal</option>
                    </select>
                    <div id="edit_internal">
                        <select name="asset_ids[]" id="edit_asset_ids" class="form-select select2-edit-assets" multiple>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}">{{ $asset->name }} — {{ $asset->location }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="edit_external" class="d-none">
                        <input name="external_place" id="edit_external_place" class="form-control mb-2" placeholder="Nama/alamat tempat eksternal (opsional jika menggunakan Zoom)">
                        <div class="input-group">
                            <span class="input-group-text text-success"><i class="bx bx-video"></i></span>
                            <input type="url" name="link_zoom" id="edit_link_zoom" class="form-control" placeholder="https://zoom.us/j/...">
                        </div>
                        <small class="text-muted">Isi tempat eksternal, tautan Zoom, atau keduanya.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-primary">Penanggung Jawab (PIC) <span class="text-danger">*</span></label>
                    <input type="text" name="pic" id="edit_pic" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Pilih Pengajar --',
            allowClear: true,
            width: '100%'
        });

        $('.select2-modal').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalEditSchedule'),
            placeholder: '-- Pilih Pengajar --',
            allowClear: true,
            width: '100%'
        });
        $('.select2-assets').select2({ theme: 'bootstrap-5', placeholder: 'Pilih aset/ruangan', width: '100%' });
        $('.select2-edit-assets').select2({ theme: 'bootstrap-5', dropdownParent: $('#modalEditSchedule'), placeholder: 'Pilih aset/ruangan', width: '100%' });

        function calculateEndTime(prefix) {
            const start = $('#' + prefix + '_start').val();
            const jp = parseInt($('#' + prefix + '_jp').val(), 10);
            if (!start || !jp || jp < 1) {
                $('#' + prefix + '_end').val('');
                return;
            }
            const [hours, minutes] = start.split(':').map(Number);
            const totalMinutes = (hours * 60 + minutes + (jp * 45)) % (24 * 60);
            const endHours = String(Math.floor(totalMinutes / 60)).padStart(2, '0');
            const endMinutes = String(totalMinutes % 60).padStart(2, '0');
            $('#' + prefix + '_end').val(`${endHours}:${endMinutes}`);
        }

        $('#create_start, #create_jp').on('input change', () => calculateEndTime('create'));
        $('#edit_start, #edit_jp').on('input change', () => calculateEndTime('edit'));
        calculateEndTime('create');

        function toggleVenue(prefix) {
            const internal = $('#' + prefix + '_venue_type').val() === 'internal';
            $('#' + prefix + '_internal').toggleClass('d-none', !internal);
            $('#' + prefix + '_external').toggleClass('d-none', internal);
        }
        $('#create_venue_type').on('change', () => toggleVenue('create'));
        $('#edit_venue_type').on('change', () => toggleVenue('edit'));
        toggleVenue('create');
    });

    function editSchedule(data) {
        const url = "{{ url('schedules') }}/" + data.id;
        $('#formEditSchedule').attr('action', url);

        $('#edit_date').val(data.date);
        $('#edit_start').val(data.start_time);
        $('#edit_end').val(data.end_time);
        $('#edit_activity').val(data.activity);
        $('#edit_jp').val(data.jp);
        $('#edit_link_zoom').val(data.link_zoom || '');
        const jp = parseInt(data.jp, 10);
        if (data.start_time && jp) {
            const [hours, minutes] = data.start_time.substring(0, 5).split(':').map(Number);
            const totalMinutes = (hours * 60 + minutes + (jp * 45)) % (24 * 60);
            $('#edit_end').val(`${String(Math.floor(totalMinutes / 60)).padStart(2, '0')}:${String(totalMinutes % 60).padStart(2, '0')}`);
        }
        $('#edit_pic').val(data.pic);
        $('#edit_pengajar_id').val(data.pengajar_id).trigger('change');
        $('#edit_venue_type').val(data.venue_type || 'external');
        $('#edit_external_place').val(data.external_place || '');
        $('#edit_asset_ids').val((data.bookings || []).map(item => String(item.asset_id))).trigger('change');
        const internal = $('#edit_venue_type').val() === 'internal';
        $('#edit_internal').toggleClass('d-none', !internal);
        $('#edit_external').toggleClass('d-none', internal);
    }
</script>
@endpush

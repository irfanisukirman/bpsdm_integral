@extends('layouts.master')
@section('title', 'Pengaturan Soal Level 2')

@section('content')
<div class="container-xxl container-p-y">
    <a href="{{ route('evall2.index', $training) }}" class="btn btn-outline-secondary btn-sm mb-2"><i class="bx bx-arrow-back me-1" aria-hidden="true"></i>Kembali ke Evaluasi Level 2</a>
    <h4 class="fw-bold mb-1 mt-2">Pengaturan Soal Level 2</h4>
    <p class="text-muted mb-4">{{ $training->nama_pelatihan }}</p>

    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif



    <div class="modal fade" id="quizImportModal" tabindex="-1" aria-labelledby="quizImportTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="quizImportTitle">Impor Soal Excel</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted mb-3">Isi lembar <strong>Soal</strong> pada template Excel. Contoh pengisian tersedia di lembar <strong>Contoh</strong>. Impor menambahkan soal ke daftar yang ada.</p>
            @error('question_file')<div class="alert alert-danger" role="alert">{{ $message }}</div>@enderror
            <a class="btn btn-outline-primary" href="{{ route('evall2.quiz.questions.template', $training) }}">
                <i class="bx bx-download me-1" aria-hidden="true"></i>Unduh Template Excel
            </a>
            <form method="POST" class="mt-3" enctype="multipart/form-data" action="{{ route('evall2.quiz.questions.import', $training) }}">
                @csrf
                <fieldset @disabled(!$editable) aria-describedby="quizImportHelp">
                    <label class="form-label" for="quizQuestionFile">Unggah File Soal (.xlsx)</label>
                    <input class="form-control" id="quizQuestionFile" name="question_file" type="file" accept=".xlsx" required>
                    <button class="btn btn-primary mt-3" type="submit">Impor Soal</button>
                </fieldset>
            </form>
            @if(!$editable)
                <p class="alert alert-warning mb-0 mt-3" id="quizImportHelp">
                    {{ $hasAttempts ? 'Bank soal terkunci karena sudah ada jawaban peserta.' : 'Untuk mengunggah soal, nonaktifkan akses akun peserta dan tautan publik di pengaturan Pretest dan Posttest, lalu simpan.' }}
                </p>
            @else
                <p class="form-text mb-0 mt-2" id="quizImportHelp">Pilih file Excel yang sudah diisi, lalu klik Impor Soal.</p>
            @endif
          </div>
        </div>
      </div>
    </div>

        <section class="card mb-4" aria-labelledby="quizQuestionsTitle">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h5 class="mb-0" id="quizQuestionsTitle">Daftar Soal <span class="badge bg-label-primary">{{ count($questions) }}</span></h5>
                    <small class="text-muted">Pretest dan Posttest memakai daftar soal yang sama.</small>
                    @if($hasAttempts)
                        <small class="d-block text-muted">Soal terkunci karena sudah ada jawaban peserta.</small>
                    @elseif(!$editable)
                        <small class="d-block text-muted">Nonaktifkan akses Pretest dan Posttest sebelum mengubah soal.</small>
                    @endif
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#quizImportModal"><i class="bx bx-spreadsheet me-1" aria-hidden="true"></i>Impor Soal Excel</button>
                    <button type="button" class="btn btn-primary" @disabled(!$editable) data-bs-toggle="modal" data-bs-target="#quizQuestionModal" data-question-id="new"
                            data-question-action="{{ route('evall2.quiz.questions.store', $training) }}"><i class="bx bx-plus me-1" aria-hidden="true"></i>Tambah Soal</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th scope="col">No.</th><th scope="col">Pertanyaan</th><th scope="col">Rumpun</th><th scope="col">Jenis Soal</th><th scope="col">Kunci Jawaban</th><th scope="col">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($questions as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <th scope="row" class="text-break">{{ $item['prompt'] }}</th>
                                <td>{{ $item['group'] ?? '—' }}</td>
                                <td><span class="badge bg-label-secondary">{{ $item['type'] === 'multiple' ? 'Pilihan Ganda' : 'Benar / Salah' }}</span></td>
                                <td>{{ $item['options'][$item['correct']] }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#quizQuestionModal"
                                                data-question-id="{{ $item['id'] }}" data-question="{{ json_encode($item, JSON_THROW_ON_ERROR) }}"
                                                data-question-action="{{ route('evall2.quiz.questions.update', [$training, $item['id']]) }}">{{ $editable ? 'Edit' : 'Lihat' }}</button>
                                        @if($editable)
                                            <form method="POST" action="{{ route('evall2.quiz.questions.destroy', [$training, $item['id']]) }}" onsubmit="return confirm('Hapus soal ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada soal.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="modal fade" id="quizQuestionModal" tabindex="-1" aria-labelledby="quizQuestionTitle" aria-hidden="true" data-editable="{{ (int) $editable }}">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <form method="POST" class="modal-content">
                    @csrf
                    <input type="hidden" name="_method" value="PUT" disabled>
                    <input type="hidden" name="question_form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="quizQuestionTitle">Soal Kuis</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        @if(old('question_form') && $errors->any())
                            <p class="alert alert-danger" data-question-error hidden>{{ $errors->first() }}</p>
                        @endif
                        <fieldset @disabled(!$editable)>
                            @include('evaluasi.partials.l2-quiz-question-fields')
                        </fieldset>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ $editable ? 'Batal' : 'Tutup' }}</button>
                        @if($editable)<button type="submit" class="btn btn-primary">Simpan Soal</button>@endif
                    </div>
                </form>
            </div>
        </div>

    @php
        $quizLabel = $isPretest ? 'Pretest' : 'Posttest';
        $quizRoutes = $isPretest ? 'evall2.pretest.' : 'evall2.quiz.';
        $settingValue = fn ($key, $default) => old('settings_kind') === $quizLabel ? old($key, $default) : $default;
    @endphp
    <section class="card mb-4" id="pengaturan-kuis">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <h5 class="mb-0">Pengaturan Kuis</h5>
                <span class="badge bg-label-{{ $quiz?->is_visible ? 'success' : 'secondary' }}">{{ $quiz?->is_visible ? 'Tampil di akun peserta' : 'Tersembunyi' }}</span>
            </div>
            <form method="GET" action="{{ route('evall2.quiz.manage', $training) }}#pengaturan-kuis" class="mb-3" id="quizKindForm">
                <label class="form-label" for="quizKind">Jenis Kuis</label>
                <select class="form-select w-auto" name="jenis" id="quizKind" aria-describedby="quizKindHelp">
                    <option value="pretest" @selected($isPretest)>Pretest</option>
                    <option value="posttest" @selected(!$isPretest)>Posttest</option>
                </select>
                <p class="form-text mb-0" id="quizKindHelp">Jawaban dari kuis ini masuk ke kolom nilai {{ $quizLabel }}.</p>
                <noscript><button class="btn btn-outline-primary btn-sm mt-2" type="submit">Tampilkan Pengaturan</button></noscript>
            </form>
            <form method="POST" action="{{ route($quizRoutes.'settings', $training) }}" class="row g-3 align-items-end">
                @csrf @method('PUT')
                <input type="hidden" name="settings_kind" value="{{ $quizLabel }}">
                <div class="col-lg-5">
                    <label class="form-label" for="quizTitle-{{ $quizLabel }}">Judul Kuis</label>
                    <input class="form-control" id="quizTitle-{{ $quizLabel }}" name="title" value="{{ $settingValue('title', $quiz?->title ?? $quizLabel.' Pelatihan') }}" maxlength="255" required>
                </div>
                @unless($isPretest)
                <div class="col-sm-5 col-lg-3">
                    <label class="form-label" for="quizPassScore-{{ $quizLabel }}">Standar Kelulusan (0–100)</label>
                    <input type="number" class="form-control" id="quizPassScore-{{ $quizLabel }}" name="pass_score" min="0" max="100" value="{{ $settingValue('pass_score', $quiz?->pass_score ?? 70) }}" @readonly($hasQuizAttempts) required>
                </div>
                @endunless
                <div class="col-sm-7 col-lg-auto mb-2">
                    <input type="hidden" name="is_visible" value="0">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="quizVisibility-{{ $quizLabel }}" name="is_visible" value="1" @checked($settingValue('is_visible', $quiz?->is_visible))>
                        <label class="form-check-label" for="quizVisibility-{{ $quizLabel }}">Tampilkan di akun peserta</label>
                    </div>
                </div>
                <div class="col-12 border-top pt-3">
                    <h6 class="mb-2">Akses Publik</h6>
                    <input type="hidden" name="is_public" value="0">
                    <div class="form-check form-switch mb-2">
                        <input type="checkbox" class="form-check-input" id="quizPublic-{{ $quizLabel }}" name="is_public" value="1" @checked($settingValue('is_public', $quiz?->is_public))>
                        <label class="form-check-label" for="quizPublic-{{ $quizLabel }}">Aktifkan tautan publik</label>
                    </div>
                    <p class="form-text mb-2">Peserta mengisi NIP/NIK dan token pribadi untuk membuka soal tanpa masuk akun. Gunakan tombol Lihat Token di tabel Nilai Peserta.</p>
                    @if($training->level2PublicToken())
                        <label for="quizPublicUrl-{{ $quizLabel }}" class="form-label">Tautan Publik Pretest dan Posttest</label>
                        <div class="input-group">
                            <input id="quizPublicUrl-{{ $quizLabel }}" class="form-control" value="{{ route('public.final-quiz.show', $training->level2PublicToken()) }}" readonly>
                            <button class="btn btn-outline-primary" id="copyQuizPublicUrl-{{ $quizLabel }}" type="button">Salin Tautan</button>
                        </div>
                        <span id="copyQuizStatus-{{ $quizLabel }}" class="form-text" role="status"></span>
                    @else
                        <p class="form-text mb-0">Simpan pengaturan untuk membuat tautan.</p>
                    @endif
                </div>
                <div class="col-12">
                    <h6 class="mb-1">Jadwal Pengisian (WIB)</h6>
                    <p class="form-text mb-0">Berlaku untuk tautan publik dan akun peserta. Kosongkan waktu buka agar langsung dibuka, atau waktu tutup agar pengisian tetap terbuka.</p>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <label class="form-label" for="quizOpensAt-{{ $quizLabel }}">Buka pada</label>
                    <input type="datetime-local" class="form-control" id="quizOpensAt-{{ $quizLabel }}" name="opens_at" value="{{ $settingValue('opens_at', $quiz?->opens_at?->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-sm-6 col-lg-4">
                    <label class="form-label" for="quizClosesAt-{{ $quizLabel }}">Tutup pada</label>
                    <input type="datetime-local" class="form-control" id="quizClosesAt-{{ $quizLabel }}" name="closes_at" value="{{ $settingValue('closes_at', $quiz?->closes_at?->format('Y-m-d\TH:i')) }}">
                </div>
                <fieldset class="col-12 border-top pt-3">
                    <legend class="h6 mb-3">Pengacakan Kuis</legend>
                    <div class="row g-3">
                        @foreach([
                            'shuffle_questions' => ['Acak Soal', 'Acak urutan soal di dalam tiap rumpun.'],
                            'shuffle_answers' => ['Acak Jawaban', 'Acak posisi pilihan jawaban di tiap soal.'],
                            'shuffle_groups' => ['Acak Rumpun', 'Acak urutan blok rumpun. Soal dalam rumpun tetap berdekatan.'],
                        ] as $setting => [$label, $description])
                            <div class="col-md-4">
                                <input type="hidden" name="{{ $setting }}" value="0">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="{{ $setting }}" name="{{ $setting }}" value="1" role="switch"
                                           aria-describedby="{{ $setting }}-help" @checked($settingValue($setting, $quiz?->{$setting}))>
                                    <label class="form-check-label fw-semibold" for="{{ $setting }}">{{ $label }}</label>
                                    <div class="form-text" id="{{ $setting }}-help">{{ $description }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
                <div class="col-12"><button type="submit" class="btn btn-primary">Simpan Pengaturan</button></div>
            </form>
            @if($hasQuizAttempts)
                <p class="form-text mb-0 mt-2">Pengaturan ini sudah menerima jawaban peserta.</p>
            @endif
        </div>
    </section>
</div>
@endsection

@push('js')
<script>
(() => {
    const quizKind = document.getElementById('quizKind');
    quizKind?.addEventListener('change', () => quizKind.form.requestSubmit());
    document.querySelectorAll('[id^="copyQuizPublicUrl-"]').forEach(copy => copy.addEventListener('click', async () => {
        const card = copy.closest('.card');
        const url = card.querySelector('[id^="quizPublicUrl-"]');
        const status = card.querySelector('[id^="copyQuizStatus-"]');
        try {
            await navigator.clipboard.writeText(url.value);
            status.textContent = 'Tautan disalin.';
        } catch {
            url.focus();
            url.select();
            status.textContent = document.execCommand('copy') ? 'Tautan disalin.' : 'Salin tautan yang sudah dipilih.';
        }
    }));

    @if($errors->has('question_file'))
        bootstrap.Modal.getOrCreateInstance(document.getElementById('quizImportModal')).show();
    @endif

    const modal = document.getElementById('quizQuestionModal');
    if (!modal) return;
    const form = modal.querySelector('form');
    const title = modal.querySelector('.modal-title');
    const optionGroup = modal.querySelector('[data-question-options]');
    const options = [...optionGroup.querySelectorAll('input')];
    const types = [...form.querySelectorAll('[name="type"]')];
    const keys = [...modal.querySelectorAll('[data-key-option]')];
    const editable = modal.dataset.editable === '1';
    const retry = {{ \Illuminate\Support\Js::from(old('question_form')) }};
    const oldQuestion = {{ \Illuminate\Support\Js::from(\Illuminate\Support\Arr::only(request()->session()->getOldInput(), ['prompt', 'group', 'type', 'options', 'correct'])) }};

    function setKeyChoices(type, selected) {
        const multiple = type === 'multiple';
        optionGroup.hidden = !multiple;
        options.forEach(input => { input.disabled = !multiple; });
        const labels = multiple ? ['Pilihan A', 'Pilihan B', 'Pilihan C', 'Pilihan D'] : ['Benar', 'Salah'];
        keys.forEach((wrap, index) => {
            const input = wrap.querySelector('input');
            wrap.hidden = index >= labels.length;
            input.disabled = wrap.hidden;
            input.checked = !wrap.hidden && String(selected ?? '') === input.value;
            wrap.querySelector('label').textContent = labels[index] ?? '';
        });
    }

    types.forEach(input => input.addEventListener('change', () => setKeyChoices(input.value, null)));
    modal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        if (!button) return;
        const id = button.dataset.questionId;
        const question = id === retry ? oldQuestion : JSON.parse(button.dataset.question ?? '{}');
        title.textContent = id === 'new' ? 'Tambah Soal' : (editable ? 'Edit Soal' : 'Lihat Soal');
        form.action = button.dataset.questionAction;
        form.elements.namedItem('_method').disabled = id === 'new';
        form.elements.namedItem('question_form').value = id;
        form.elements.namedItem('prompt').value = question.prompt ?? '';
        form.elements.namedItem('group').value = question.group ?? '';
        const type = question.type === 'boolean' ? 'boolean' : 'multiple';
        types.forEach(input => { input.checked = input.value === type; });
        options.forEach((input, index) => { input.value = type === 'multiple' ? (question.options?.[index] ?? '') : ''; });
        setKeyChoices(type, question.correct);
        const error = modal.querySelector('[data-question-error]');
        if (error) error.hidden = id !== retry;
    });
    if (editable) {
        modal.addEventListener('shown.bs.modal', () => form.elements.namedItem('prompt').focus());
        const retryButton = [...document.querySelectorAll('[data-question-id]')].find(button => button.dataset.questionId === retry);
        if (retryButton) bootstrap.Modal.getOrCreateInstance(modal).show(retryButton);
    }
})();
</script>
@endpush

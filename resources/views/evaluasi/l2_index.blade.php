@extends('layouts.master')
@section('title', 'Evaluasi Level 2')

@section('content')
<div class="container-xxl container-p-y">
    <a href="{{ route('trainings.manage', $training) }}" class="small"><i class="bx bx-arrow-back me-1"></i>Kembali ke Kelola Pelatihan</a>
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mt-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Evaluasi Level 2</h4>
            <p class="text-muted mb-1">{{ $training->nama_pelatihan }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('evall2.quiz.manage', $training) }}" class="btn btn-primary"><i class="bx bx-edit me-1"></i>Pengaturan Soal</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif

    @php
        $tokenQuizzes = array_filter(['Pretest' => $pretest, 'Posttest' => $quiz]);
    @endphp
    <section class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="mb-0">Nilai Peserta</h5>
            <div class="d-flex flex-wrap gap-2">
                @if($participants->total())
                    @foreach($tokenQuizzes as $label => $tokenQuiz)
                        <form method="POST" action="{{ route($tokenQuiz->isPretest() ? 'evall2.pretest.tokens.download' : 'evall2.quiz.tokens.download', $training) }}">
                            @csrf
                            <button class="btn btn-outline-primary btn-sm" type="submit">Unduh Token {{ $label }}</button>
                        </form>
                    @endforeach
                @endif
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Peserta <span class="d-block text-muted small">NIP/NIK</span></th>
                        <th scope="col">Instansi / Perangkat Daerah</th>
                        <th scope="col" class="text-end">Pretest</th>
                        <th scope="col" class="text-end">Posttest</th>
                        <th scope="col" class="text-end">Selisih</th>
                        <th scope="col" class="text-nowrap">Status Posttest</th>
                        @if($tokenQuizzes)<th scope="col" class="text-nowrap">Token &amp; Pengisian</th>@endif
                        @if($canCorrect)<th scope="col">Koreksi</th>@endif
                    </tr>
                </thead>
                <tbody>
                @forelse($participants as $participant)
                    @php
                        $attempt = $attempts->get($participant->id);
                        $result = $participant->evaluationL2;
                        $preScore = $result?->pretest;
                        $postScore = $result?->postest;
                        $passed = $attempt && $postScore !== null && $attempt->passes((float) $postScore);
                        $retryCorrection = (string) old('correction_participant') === (string) $participant->id;
                    @endphp
                    <tr>
                        <th scope="row" style="min-width: 11rem">
                            {{ $participant->name }}
                            <small class="d-block fw-normal text-muted text-nowrap">{{ $participant->user?->nip ?: $participant->nip_nik ?: $participant->user?->nip_nik ?: '—' }}</small>
                        </th>
                        <td>{{ $participant->instansi ?: $participant->user?->instansi ?: '—' }}</td>
                        <td class="text-end text-nowrap">{{ $preScore !== null ? number_format((float) $preScore, 2, ',', '.') : '—' }}</td>
                        <td class="text-end text-nowrap">{{ $postScore !== null ? number_format((float) $postScore, 2, ',', '.') : '—' }}</td>
                        <td class="text-end text-nowrap">{{ $preScore !== null && $postScore !== null ? number_format($postScore - $preScore, 2, ',', '.') : '—' }}</td>
                        <td>
                            @if($attempt && $postScore !== null)
                                <span class="badge bg-label-{{ $passed ? 'success' : 'warning' }}">{{ $passed ? 'Lulus' : 'Belum Lulus' }}</span>
                            @elseif($postScore !== null)
                                <span class="text-muted">Nilai historis</span>
                            @else
                                <span class="text-muted">Belum mengerjakan</span>
                            @endif
                        </td>
                        @if($tokenQuizzes)
                        <td>
                            <details>
                                <summary class="text-primary text-nowrap">Kelola Kuis</summary>
                        @foreach($tokenQuizzes as $label => $tokenQuiz)
                            @php
                                $tokenRoutes = $tokenQuiz->isPretest() ? 'evall2.pretest.' : 'evall2.quiz.';
                                $submissionId = $tokenQuiz->isPretest() ? $pretestAttempts->get($participant->id) : $attempt?->id;
                            @endphp
                            <div class="mt-2 pt-2 border-top">
                                <div class="fw-semibold small mb-1">{{ $label }}</div>
                                <form method="POST" data-quiz-token-form data-participant="{{ $participant->name }} · {{ $label }}"
                                      action="{{ route($tokenRoutes.'token.show', [$training, $participant]) }}">
                                    @csrf
                                    <button class="btn btn-outline-primary btn-sm text-nowrap" type="submit">Lihat Token</button>
                                </form>
                                <form method="POST" class="mt-1" action="{{ route($tokenRoutes.'token.rotate', [$training, $participant]) }}"
                                      onsubmit="return confirm('Buat token baru? Token lama peserta ini akan berhenti berlaku.')">
                                    @csrf @method('PUT')
                                    <button class="btn btn-outline-secondary btn-sm text-nowrap" type="submit">Buat Token Baru</button>
                                </form>
                                @if($canCorrect && $submissionId)
                                    <form method="POST" class="mt-1" action="{{ route($tokenRoutes.'attempt.reset', [$training, $participant, $submissionId]) }}"
                                          onsubmit="return confirm(this.dataset.confirm)"
                                          data-confirm="Reset pengisian {{ $label }} untuk {{ $participant->name }}? Jawaban, nilai, dan token {{ $label }} akan dihapus.">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm text-nowrap" type="submit" aria-label="Reset Pengisian {{ $label }} untuk {{ $participant->name }}">Reset Pengisian</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                            </details>
                        </td>
                        @endif
                        @if($canCorrect)
                            <td>
                                @if($postScore !== null)
                                    <details @if($retryCorrection) open @endif>
                                        <summary class="text-primary">Koreksi Nilai</summary>
                                        <form method="POST" action="{{ route('evall2.quiz.score', [$training, $participant]) }}" class="mt-2">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="correction_participant" value="{{ $participant->id }}">
                                            <label class="form-label" for="score-{{ $participant->id }}">Nilai Baru</label>
                                            <input class="form-control form-control-sm mb-2" type="number" min="0" max="100" step="0.01"
                                                   id="score-{{ $participant->id }}" name="score" value="{{ $retryCorrection ? old('score', $result->postest) : $result->postest }}" required>
                                            <label class="form-label" for="reason-{{ $participant->id }}">Alasan Koreksi</label>
                                            <textarea class="form-control form-control-sm mb-2" rows="2" maxlength="500" name="reason"
                                                      id="reason-{{ $participant->id }}" required>{{ $retryCorrection ? old('reason') : '' }}</textarea>
                                            <button type="submit" class="btn btn-primary btn-sm">Simpan Koreksi</button>
                                        </form>
                                        @if($correction = $result->latestScoreCorrection)
                                            <div class="small text-muted mt-2">Riwayat koreksi terakhir: {{ $correction->reason }}</div>
                                        @endif
                                    </details>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ 6 + (int) $canCorrect + (int) !empty($tokenQuizzes) }}" class="text-center text-muted">Belum ada peserta yang disetujui.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($participants->hasPages())<div class="card-footer">{{ $participants->links() }}</div>@endif
    </section>

    @if($tokenQuizzes && $participants->count())
        <dialog id="quizTokenDialog" class="border-0 rounded shadow p-4" style="width: min(26rem, 90vw)" aria-labelledby="quizTokenTitle">
            <h5 id="quizTokenTitle" class="mb-1">Token Pribadi</h5>
            <p data-token-participant class="text-muted"></p>
            <p data-token-status role="status"></p>
            <div data-token-field hidden>
                <label for="quizTokenValue" class="form-label">Token</label>
                <input id="quizTokenValue" class="form-control font-monospace" readonly autocomplete="off">
            </div>
            <form method="dialog" class="mt-3 text-end">
                <button type="submit" class="btn btn-secondary btn-sm">Tutup</button>
            </form>
        </dialog>
    @endif
</div>
@endsection

@push('js')
<script>
(() => {
    const dialog = document.getElementById('quizTokenDialog');
    if (!dialog) return;

    const name = dialog.querySelector('[data-token-participant]');
    const status = dialog.querySelector('[data-token-status]');
    const field = dialog.querySelector('[data-token-field]');
    const input = document.getElementById('quizTokenValue');
    let pending;

    dialog.addEventListener('close', () => {
        pending?.abort();
        input.value = '';
        field.hidden = true;
    });
    input.addEventListener('focus', () => input.select());

    document.querySelectorAll('[data-quiz-token-form]').forEach(form => {
        form.addEventListener('submit', async event => {
            event.preventDefault();
            name.textContent = form.dataset.participant;
            status.textContent = 'Memuat token…';
            const request = new AbortController();
            pending = request;
            dialog.showModal();

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {Accept: 'application/json'},
                    body: new FormData(form),
                    signal: request.signal,
                });
                if (!response.ok) throw new Error();
                const {token} = await response.json();
                if (request.signal.aborted) return;
                input.value = token;
                field.hidden = false;
                status.textContent = '';
            } catch {
                if (!request.signal.aborted) status.textContent = 'Token gagal dimuat. Tutup dan coba lagi.';
            }
        });
    });
})();
</script>
@endpush

    @if($scheduleMessage)
        <div class="alert alert-info" role="status">{{ $scheduleMessage }}</div>
    @elseif($attempt)
        <div class="card card-body p-4">
            @unless($quiz->isPretest())
                <div><span class="badge bg-label-{{ $attempt->passes($finalScore) ? 'success' : 'warning' }}">{{ $attempt->passes($finalScore) ? 'Lulus' : 'Belum Lulus' }}</span></div>
            @endunless
            <h2 class="my-2">{{ number_format($finalScore, 2, ',', '.') }} <small class="text-muted">/ 100</small></h2>
            <p class="mb-0">@unless($quiz->isPretest()) Standar kelulusan {{ $attempt->pass_score }}/100. @endunless {{ $attempt->correct_count }} dari {{ $attempt->questions_count }} jawaban benar.</p>
            @if($finalScore !== (float) $attempt->score)
                <p class="small text-muted mt-2 mb-0">Nilai kuis sebelum koreksi {{ number_format((float) $attempt->score, 2, ',', '.') }}/100.</p>
            @endif
        </div>
    @else
        @push('styles')
            <link rel="stylesheet" href="{{ asset('assets/css/final-quiz.css') }}?v=2">
        @endpush
        <p class="text-muted">@unless($quiz->isPretest()) Standar kelulusan {{ $quiz->pass_score }}/100. @endunless Jawaban hanya bisa dikirim sekali.</p>
        @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ $submitUrl }}" data-final-quiz>
            @csrf
            <input type="hidden" name="revision" value="{{ $quiz->revision }}">
            @if($publicAccess['confirmed'] ?? false)
                <input type="hidden" name="quiz_access" value="{{ $publicAccess['nonce'] }}">
            @endif
            <div class="quiz-workspace">
                <div class="quiz-workspace-main">
                    @foreach($questions as $item)
                        <fieldset class="card quiz-question mb-3" data-quiz-question id="quiz-question-{{ $loop->iteration }}">
                            <legend class="quiz-legend mb-0" tabindex="-1">
                                <span class="d-block small text-primary fw-semibold mb-2">
                                    Soal {{ $loop->iteration }} dari {{ count($questions) }}
                                    @if(filled($item['group'] ?? null)) · {{ $item['group'] }} @endif
                                </span>
                                {{ $item['prompt'] }}
                            </legend>
                            <div class="card-body pt-2 d-grid gap-2">
                                @foreach($item['options'] as $index => $option)
                                    <label class="quiz-option" for="answer-{{ $item['id'] }}-{{ $index }}">
                                        <input class="form-check-input mt-1 flex-shrink-0" type="radio" id="answer-{{ $item['id'] }}-{{ $index }}"
                                               name="answers[{{ $item['id'] }}]" value="{{ $index }}"
                                               @checked((string) old('answers.'.$item['id'], '') === (string) $index) required>
                                        <span>{{ $option }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                    <div class="d-flex flex-wrap gap-2 mb-3" data-quiz-controls hidden>
                        <button class="btn btn-outline-secondary" type="button" data-quiz-prev>Sebelumnya</button>
                        <button class="btn btn-primary" type="button" data-quiz-next>Berikutnya</button>
                    </div>
                    <div class="d-lg-none mb-3" data-quiz-mobile-navigation hidden>
                        <a href="#quiz-navigator">Lihat nomor soal</a>
                    </div>
                    <div class="alert alert-warning" role="alert" data-quiz-message hidden></div>
                    <button class="btn btn-success" type="submit" data-quiz-review-button>Kirim Jawaban Akhir</button>
                    <dialog class="quiz-review-dialog rounded shadow" data-quiz-review aria-labelledby="quiz-review-title" aria-describedby="quiz-review-description">
                        <div class="card-body">
                            <h2 id="quiz-review-title" class="h5 mb-2" tabindex="-1">Kirim jawaban akhir?</h2>
                            <p id="quiz-review-description" class="mb-3">Setelah dikirim, jawaban tidak bisa diubah.</p>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-outline-secondary" type="button" data-quiz-review-cancel autofocus>Kembali ke Soal</button>
                                <button class="btn btn-success" type="submit" data-quiz-review-confirm>Ya, Kirim Jawaban</button>
                            </div>
                        </div>
                    </dialog>
                </div>
                <aside id="quiz-navigator" class="card card-body quiz-navigator" aria-labelledby="quiz-navigator-title" data-quiz-navigator hidden>
                    <h2 class="h6 mb-2" id="quiz-navigator-title">Nomor Soal</h2>
                    <p class="small text-muted mb-2" data-quiz-progress-text aria-live="polite"></p>
                    <progress class="quiz-progress mb-3" data-quiz-progress value="0" max="{{ count($questions) }}" aria-label="Soal terjawab"></progress>
                    <div class="quiz-numbers">
                        @foreach($questions as $item)
                            <button type="button" class="quiz-number" data-quiz-number aria-controls="quiz-question-{{ $loop->iteration }}">{{ $loop->iteration }}</button>
                        @endforeach
                    </div>
                    <div class="small text-muted d-flex flex-wrap gap-3 mt-3">
                        <span><span class="quiz-status-swatch quiz-status-swatch-answered" aria-hidden="true"></span>Terjawab</span>
                        <span><span class="quiz-status-swatch" aria-hidden="true"></span>Belum dijawab</span>
                    </div>
                </aside>
            </div>
        </form>
        <script src="{{ asset('assets/js/final-quiz.js') }}?v=4" defer></script>
    @endif

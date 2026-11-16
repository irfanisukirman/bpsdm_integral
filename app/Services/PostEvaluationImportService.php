<?php

namespace App\Services;

use App\Models\EvaluationResultL34;
use App\Models\Participant;
use App\Models\Question;
use App\Models\Training;
use App\Support\EvaluationProgram;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PostEvaluationImportService
{
    public function __construct(private PostEvaluationService $service) {}

    public function normalize(string $text): string
    {
        $text = html_entity_decode(preg_replace('/<[^>]+>/', ' ', $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/^\s*\d+\s*[.)]\s*/u', '', $text);
        $text = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $text)));
        $text = preg_replace('/\s+([,.;:!?])/u', '$1', $text);

        return str_starts_with($text, 'metode pelaksanaan') ? 'metode pelaksanaan' : $text;
    }

    private function nip(string $text): ?string
    {
        if (preg_match('/(?<!\d)(\d{18}|\d{16})(?!\d)/u', $text, $m)) {
            return $m[1];
        }

        return null;
    }

    private function name(string $text): string
    {
        return $this->normalize(trim(preg_replace('/\|.*$/u', '', $text)));
    }

    private function value($sheet, int $column, int $row): string
    {
        $cell = $sheet->getCell([$column, $row]);
        $value = $cell->isFormula() ? $cell->getOldCalculatedValue() : $cell->getValue();

        return trim((string) $value);
    }

    private function answer(Question $q, string $value, Training $training): array
    {
        if (mb_strlen($value) > 20000) {
            throw new \RuntimeException('Jawaban melebihi 20.000 karakter.');
        }
        if ($q->type === 'slider') {
            if (! is_numeric($value) || (float) $value < 0 || (float) $value > 100 || floor((float) $value) !== (float) $value) {
                throw new \RuntimeException('Nilai slider harus bilangan bulat 0-100.');
            }

            return ['score' => (float) $value, 'note' => null];
        }
        if ($q->type === 'dropdown' || $q->type === 'checkbox') {
            $options = collect($q->options ?? [])->mapWithKeys(fn ($o) => [$this->normalize((string) $o) => (string) $o]);
            if ($q->type === 'dropdown' && $this->service->supportsFourPoint($training) && $this->service->fourPointQuestion($q)) {
                $labels = [1 => 'Tidak Sesuai', 2 => 'Kurang Sesuai', 3 => 'Sesuai', 4 => 'Sangat Sesuai'];
                $ordinal = array_search($this->normalize($value), array_map(fn ($label) => $this->normalize($label), $labels), true);
                if ($ordinal === false && is_numeric($value) && in_array((float) $value, [1.0, 2.0, 3.0, 4.0], true)) {
                    $ordinal = (int) $value;
                }
                if ($ordinal !== false) {
                    $value = $options->has((string) $ordinal) ? (string) $ordinal : $labels[$ordinal];
                }
            }
            $values = [$value];
            if ($q->type === 'checkbox') {
                $json = json_decode($value, true);
                if (is_array($json)) {
                    $values = $json;
                } elseif (! $options->has($this->normalize($value))) {
                    $values = preg_split('/[;|\r\n]+/u', $value);
                }
            }
            $selected = [];
            foreach ($values as $item) {
                if (! is_scalar($item) || ! $options->has($this->normalize((string) $item))) {
                    throw new \RuntimeException('Jawaban tidak sesuai pilihan pada bank soal.');
                }
                $selected[] = $options[$this->normalize((string) $item)];
            }
            if (! $selected) {
                throw new \RuntimeException('Pilihan jawaban kosong.');
            }

            return ['score' => null, 'note' => $q->type === 'checkbox' ? json_encode(array_values(array_unique($selected)), JSON_UNESCAPED_UNICODE) : $selected[0]];
        }
        if ($q->type !== 'text') {
            throw new \RuntimeException('Tipe pertanyaan belum didukung import.');
        }

        return ['score' => null, 'note' => $value];
    }

    public function preview(Training $training, string $path): array
    {
        // Limit decompression and worksheet dimensions before loading untrusted workbooks.
        $zip = new \ZipArchive;
        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages(['workbook' => 'File XLSX tidak valid.']);
        }
        $bytes = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $bytes += ($zip->statIndex($i)['size'] ?? 0);
        }
        $entries = $zip->numFiles;
        $zip->close();
        if ($bytes > 100 * 1024 * 1024 || $entries > 2000) {
            throw ValidationException::withMessages(['workbook' => 'Ukuran isi workbook terlalu besar.']);
        }
        $reader = IOFactory::createReader('Xlsx');
        $sheets = [];
        $cells = 0;
        foreach ($reader->listWorksheetInfo($path) as $info) {
            $key = mb_strtolower(trim($info['worksheetName']));
            if (! in_array($key, ['jawaban', 'data peserta', 'informasi umum'], true)) {
                continue;
            }
            if ($info['totalRows'] > 5002 || $info['totalColumns'] > 300) {
                throw ValidationException::withMessages(['workbook' => 'Batas workbook: 5.000 peserta dan 300 kolom per sheet.']);
            }
            $cells += $info['totalRows'] * $info['totalColumns'];
            if ($cells > 250000) {
                throw ValidationException::withMessages(['workbook' => 'Workbook terlalu besar. Maksimal 250.000 sel pada sheet sumber.']);
            }
            $sheets[$key] = $info['worksheetName'];
        }
        if (! isset($sheets['jawaban'],$sheets['data peserta'])) {
            throw ValidationException::withMessages(['workbook' => 'Gunakan workbook Pasca dengan sheet Jawaban dan Data Peserta.']);
        }
        $reader->setReadDataOnly(true)->setLoadSheetsOnly(array_values($sheets));
        $book = $reader->load($path);
        try {
            $answers = $book->getSheetByName($sheets['jawaban']);
            $roster = $book->getSheetByName($sheets['data peserta']);
            $info = isset($sheets['informasi umum']) ? $book->getSheetByName($sheets['informasi umum']) : null;
            $title = $info ? $this->value($info, 3, 2) : '';
            $program = null;
            if (preg_match('/\b(PKP|PKA|PKN|LATSAR|CPNS|PKTI\s*\/\s*PKTU|TEKNIS)\b/iu', $title, $m)) {
                $program = EvaluationProgram::normalize(preg_replace('/\s+/', '', $m[1]));
            }
            $errors = [];
            $warnings = [];
            if ($program !== null && $program !== EvaluationProgram::normalize($training->program_evaluasi)) {
                $errors[] = 'Program file '.$program.' berbeda dengan program pelatihan '.$training->program_evaluasi.'. Pilih pelatihan tujuan yang sesuai.';
            }
            if ($title === '') {
                $warnings[] = 'Identitas pelatihan pada Informasi Umum tidak tersedia; pastikan pelatihan tujuan benar.';
            }
            $people = [];
            $names = [];
            $rosterNips = [];
            for ($r = 2; $r <= $roster->getHighestDataRow(); $r++) {
                $label = $this->value($roster, 2, $r);
                if ($label === '') {
                    continue;
                }
                $nip = $this->nip($label);
                if (! $nip) {
                    $errors[] = 'Data Peserta baris '.$r.': NIP/NIK 16 atau 18 digit tidak ditemukan pada kolom Nama Peserta.';

                    continue;
                }
                if (isset($rosterNips[$nip])) {
                    $errors[] = 'Data Peserta baris '.$r.': NIP/NIK ganda dengan baris '.$rosterNips[$nip].'.';

                    continue;
                }
                $rosterNips[$nip] = $r;
                $names[$this->name($label)][] = $nip;
                $people[$nip] = ['name' => trim(explode('|', $label)[0]), 'nip' => $nip, 'row' => $r, 'participant_id' => null, 'status' => 'Belum terdaftar', 'roles' => []];
            }
            if (! $people) {
                $errors[] = 'Tidak ditemukan peserta dengan NIP/NIK yang valid.';
            }
            $registered = Participant::where('training_id', $training->id)->orderBy('id')->get()->groupBy(fn ($p) => trim((string) $p->nip_nik));
            foreach ($people as $nip => &$person) {
                $matches = $registered->get((string) $nip, collect());
                if ($matches->count() !== 1) {
                    $errors[] = 'Data Peserta baris '.$person['row'].': '.($matches->isEmpty() ? 'peserta belum terdaftar di pelatihan tujuan.' : 'NIP/NIK ganda di daftar peserta pelatihan.');

                    continue;
                }
                $participant = $matches->first();
                if ($participant->registration_status !== 'approved') {
                    $errors[] = 'Data Peserta baris '.$person['row'].': peserta belum disetujui.';
                    $person['status'] = 'Belum disetujui';

                    continue;
                }
                $person['participant_id'] = $participant->id;
                $person['status'] = 'Cocok';
            }unset($person);
            $questions = $this->service->questions($training);
            $index = [];
            foreach ($questions as $q) {
                $index[str_replace('l34_', '', $q->category)][$this->normalize($q->question_text)][] = $q;
            }
            $columns = [];
            $mapping = [];
            $identityColumns = [];
            $evaluatorColumns = [];
            $mappedQuestions = [];
            $role = 'mandiri';
            $last = Coordinate::columnIndexFromString($answers->getHighestDataColumn());
            for ($c = 3; $c <= $last; $c++) {
                $group = $this->normalize($this->value($answers, $c, 1));
                $header = $this->value($answers, $c, 2);
                $key = $this->normalize($header);
                if (in_array($group, ['atasan alumni', 'atasan langsung', 'atasan'], true)) {
                    $role = 'atasan';
                }
                if (in_array($group, ['rekan kerja', 'rekan'], true)) {
                    $role = 'rekan';
                }
                if ($key === 'nama responden') {
                    $evaluatorColumns[$role] = $c;

                    continue;
                }
                if (str_starts_with($key, 'saya memberikan penilaian pada alumni')) {
                    $identityColumns[$role] = $c;

                    continue;
                }
                if ($key === '' || in_array($key, ['nama instansi responden', 'jabatan responden'], true)) {
                    continue;
                }
                $used = false;
                for ($r = 3; $r <= $answers->getHighestDataRow(); $r++) {
                    if ($this->value($answers, $c, $r) !== '') {
                        $used = true;
                        break;
                    }
                }
                if (! $used) {
                    continue;
                }
                $candidates = $index[$role][$key] ?? [];
                // Legacy workbooks shorten this alumni profile field to "Nama Instansi".
                if (! $candidates && $role === 'mandiri' && $key === 'nama instansi') {
                    $candidates = array_values(array_filter($index[$role]['nama instansi alumni'] ?? [],
                        fn ($q) => $q->type === 'text' && $this->normalize($q->sub_category ?? '') === 'data diri alumni'));
                }

                $catalogCandidates = array_values(array_filter($candidates, fn ($q) => $q->is_active && filled($q->source_column)));
                if ($catalogCandidates) {
                    $columnMatch = array_values(array_filter($catalogCandidates, fn ($q) => $q->source_column === Coordinate::stringFromColumnIndex($c)));
                    $occurrences = collect($catalogCandidates)->pluck('source_occurrence')->unique();
                    if ($occurrences->count() > 1) {
                        $candidates = $columnMatch;
                    } else {
                        $candidates = $catalogCandidates;
                    }
                }
                $shapes = collect($candidates)->map(fn ($q) => json_encode([$q->type, $q->options]))->unique();
                $question = count($candidates) && $shapes->count() === 1 ? collect($candidates)->sortByDesc('id')->first() : null;
                $letter = Coordinate::stringFromColumnIndex($c);
                $mapping[] = ['column' => $letter, 'role' => $role, 'header' => strip_tags($header), 'question_id' => $question?->id, 'question' => $question?->question_text, 'type' => $question?->type, 'options' => $question?->options];
                if (! $question) {
                    $errors[] = 'Kolom '.$letter.' ('.$role.'): pertanyaan tidak ditemukan atau ambigu pada bank soal pelatihan.';

                    continue;
                }
                if (isset($mappedQuestions[$role][$question->id])) {
                    if ($training->program_evaluasi === 'LATSAR' && filled($question->source_column) && $key === 'menggerakkan pemanfaatan berbagai sumber daya untuk tujuan bersama') {
                        $columns[$role][$c] = $question;
                        $warnings[] = 'Kolom '.Coordinate::stringFromColumnIndex($mappedQuestions[$role][$question->id]).' dan '.$letter.' ('.$role.') digabung menjadi satu pertanyaan. Jawaban terisi yang berbeda harus diperbaiki terlebih dahulu.';
                    } else {
                        $errors[] = 'Kolom '.$letter.': pertanyaan yang sama muncul lebih dari satu kali untuk '.$role.'.';
                    }

                    continue;
                }
                $mappedQuestions[$role][$question->id] = $c;
                $columns[$role][$c] = $question;
            }
            $existing = EvaluationResultL34::with('question')->where('training_id', $training->id)->orderBy('id')->get()->groupBy(fn ($a) => $a->participant_id.'|'.$a->evaluator_role.'|'.$this->normalize($a->question?->question_text ?? '').'|'.($a->question?->source_occurrence ?? 1));
            $rows = [];
            $seen = [];
            $new = 0;
            $same = 0;
            $conflicts = 0;
            $roleCounts = [];
            for ($r = 3; $r <= $answers->getHighestDataRow(); $r++) {
                foreach ($columns as $role => $roleColumns) {
                    $filled = array_filter($roleColumns, fn ($q, $c) => $this->value($answers, (int) $c, $r) !== '', ARRAY_FILTER_USE_BOTH);
                    if (! $filled) {
                        continue;
                    }
                    if (! in_array($role, $training->evaluationRoles(), true)) {
                        $errors[] = 'Jawaban baris '.$r.': peran '.$role.' tidak berlaku untuk pelatihan.';

                        continue;
                    }
                    $label = isset($identityColumns[$role]) ? $this->value($answers, $identityColumns[$role], $r) : $this->value($answers, 2, $r);
                    $nip = $this->nip($label);
                    if (! $nip) {
                        $keys = $names[$this->name($label)] ?? [];
                        if (count($keys) === 1) {
                            $nip = $keys[0];
                        }
                    }
                    if (! $nip || ! isset($people[$nip])) {
                        $errors[] = 'Jawaban baris '.$r.' ('.$role.'): identitas tidak cocok dengan NIP pada Data Peserta.';

                        continue;
                    }
                    if (isset($seen[$role][$nip])) {
                        $errors[] = 'Jawaban baris '.$r.' ('.$role.'): NIP/NIK ganda dengan baris '.$seen[$role][$nip].'.';

                        continue;
                    }
                    $seen[$role][$nip] = $r;
                    $people[$nip]['roles'][] = $role;
                    $roleCounts[$role] = ($roleCounts[$role] ?? 0) + 1;
                    if (! $people[$nip]['participant_id']) {
                        continue;
                    }
                    $rowAnswers = [];
                    foreach ($filled as $c => $q) {
                        try {
                            $answer = $this->answer($q, $this->value($answers, $c, $r), $training);
                        } catch (\RuntimeException $e) {
                            $errors[] = 'Sel '.Coordinate::stringFromColumnIndex($c).$r.': '.$e->getMessage();

                            continue;
                        }
                        if (isset($rowAnswers[$q->id])) {
                            if ($rowAnswers[$q->id]['answer'] !== $answer) {
                                $errors[] = 'Sel '.Coordinate::stringFromColumnIndex($c).$r.' ('.$role.'): jawaban berbeda dengan '.Coordinate::stringFromColumnIndex($rowAnswers[$q->id]['column']).$r.' untuk soal LATSAR yang sudah digabung. Samakan nilai kedua kolom sebelum import.';
                            }

                            continue;
                        }
                        $rowAnswers[$q->id] = ['answer' => $answer, 'column' => $c];
                        $pid = $people[$nip]['participant_id'];
                        $key = $pid.'|'.$role.'|'.$this->normalize($q->question_text).'|'.($q->source_occurrence ?? 1);
                        $old = $existing->get($key, collect())->last();
                        $occupied = $old && ($old->score !== null || filled($old->note));
                        $equal = $occupied && (string) $old->score === (string) $answer['score'] && (string) $old->note === (string) $answer['note'];
                        $action = $occupied ? ($equal ? 'same' : 'preserve') : 'insert';
                        if ($action === 'insert') {
                            $new++;
                        } elseif ($action === 'same') {
                            $same++;
                        } else {
                            $conflicts++;
                        }
                        $rows[] = ['participant_id' => $pid, 'question_id' => $q->id, 'role' => $role, 'evaluator_name' => $role === 'mandiri' ? 'Diri Sendiri' : mb_substr(isset($evaluatorColumns[$role]) ? $this->value($answers, $evaluatorColumns[$role], $r) : 'Penilai (import historis)', 0, 255),
                            'score' => $answer['score'], 'note' => $answer['note'], 'action' => $action, 'existing_id' => $old?->id, 'existing_score' => $old?->score, 'existing_note' => $old?->note];
                    }
                }
            }
            if (! $rows && ! $errors) {
                $warnings[] = 'Tidak ada jawaban terisi yang dapat disimpan.';
            }
            if ($conflicts) {
                $warnings[] = $conflicts.' jawaban berbeda dengan database akan dilewati; jawaban yang sudah terisi tetap dipertahankan.';
            }

            return ['title' => $title, 'program' => $program, 'target' => [$training->id, $training->program_evaluasi, $training->bidang, $training->metode],
                'people' => array_values($people), 'mapping' => $mapping, 'rows' => $rows, 'errors' => array_values(array_unique($errors)), 'warnings' => $warnings,
                'counts' => ['participants' => count($people), 'matched' => count(array_filter($people, fn ($p) => $p['participant_id'] !== null)), 'roles' => $roleCounts, 'new' => $new, 'same' => $same, 'conflicts' => $conflicts]];
        } finally {
            $book->disconnectWorksheets();
        }
    }

    public function fingerprint(array $plan): string
    {
        return hash('sha256', json_encode($plan, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }
}

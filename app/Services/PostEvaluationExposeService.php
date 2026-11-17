<?php

namespace App\Services;

use App\Models\AiGeneration;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PostEvaluationExposeService
{
    public const BANDS = ['0', '1-25', '26-50', '51-75', '76-100'];

    public const FEATURE = 'post_evaluation_expose';

    public function payload(array $summary): array
    {
        return ['narrative_version' => $summary['narrative_version'] ?? 1, 'evaluator_notes_hash' => $summary['evaluator_notes_hash'] ?? null,
            'evaluator_note_count' => $summary['evaluator_note_count'] ?? 0, 'interview_coverage' => $summary['interview_coverage'] ?? [],
            'program' => $summary['program'], 'total' => $summary['total'], 'completed' => $summary['completed'], 'partial' => $summary['partial'],
            'roles' => $summary['roles'], 'survey' => $summary['survey'], 'interview' => $summary['interview'],
            'slides' => array_map(fn ($slide) => ['title' => $slide['title'], 'series' => $slide['series']], $summary['slides']),
            'bands' => self::BANDS, 'calculation' => 'survey_average and interview_average are averages of filled long-term, objectives and benefits indicators, not the weighted five-criterion score; missing is never zero; results include drafts.'];
    }

    public function fingerprint(array $summary): string
    {
        return hash('sha256', json_encode($this->payload($summary), JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR));
    }

    public function decorate(Training $training, array $summary): array
    {
        $summary['meta'] = ['name' => $training->nama_pelatihan, 'organizer' => $training->bidang ?: 'Penyelenggara belum diisi', 'location' => $training->lokasi ?: 'Lokasi belum diisi',
            'date' => 'Periode pelatihan: '.Carbon::parse($training->tgl_mulai)->translatedFormat('d F Y').' - '.Carbon::parse($training->tgl_selesai)->translatedFormat('d F Y')];
        $summary['team_count'] = DB::table('post_evaluation_teams')->where('training_id', $training->id)->count();
        $summary = $this->forView($summary);
        $generation = AiGeneration::where('training_id', $training->id)->where('feature', self::FEATURE)->where('status', 'completed')->where('source_hash', $this->fingerprint($summary))->latest('id')->first();
        $summary['ai_status'] = $generation ? 'current' : (AiGeneration::where('training_id', $training->id)->where('feature', self::FEATURE)->where('status', 'completed')->exists() ? 'stale' : 'none');
        if ($generation) {
            $content = $generation->generated_content;
            foreach ($summary['slides'] as $i => &$slide) {
                $slide['discussion'] = $this->paragraphs($content['discussion_'.$i]);
            }
            unset($slide);
            $summary['conclusions'] = $this->paragraphs($content['conclusion']);
            $summary['recommendations'] = array_map(fn ($i) => $content['recommendation_'.$i], range(1, 3));
            $summary['ai_generated_at'] = $generation->generated_at?->toIso8601String();
            $summary['ai_model'] = $generation->model;
        }

        return $summary;
    }

    public function paragraphs(string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R+/u', $text))));
    }

    public function forView(array $summary): array
    {
        $summary['meta'] ??= ['name' => $summary['training'], 'organizer' => '', 'location' => '', 'date' => ''];
        $summary['team_count'] ??= 0;
        $summary['draft'] = max(0, $summary['total'] - $summary['completed']);
        $summary['page_count'] = 9;
        $summary['ai_status'] ??= 'none';
        foreach ($summary['slides'] as &$slide) {
            if (isset($slide['discussion'])) {
                continue;
            }
            $parts = [];
            foreach ($slide['series'] as $series) {
                $d = $series['data'];
                $avg = $d['average'] === null ? 'belum tersedia' : number_format($d['average'], 1, ',', '.');
                $parts[] = $series['label'].': '.$d['valid'].' dari '.$summary['total'].' alumni memiliki nilai; '.$d['missing'].' belum memiliki nilai. Rerata '.$avg.'.';
            }
            $parts[] = 'Perbedaan hasil perlu dikonfirmasi melalui evidence dan konteks penerapan. Cakupan kedua sumber dapat berbeda; data kosong tidak dianggap nol.';
            $slide['discussion'] = $parts;
        }
        unset($slide);

        return $summary;
    }

    public function chart(array $slide, array $summary): string
    {
        $bands = isset($slide['series'][0]['data']['bands']);
        $labels = $bands ? self::BANDS : $summary['categories'];
        $series = $slide['series'];
        $values = array_map(fn ($s) => $s['data'][$bands ? 'bands' : 'counts'], $series);
        $max = max(1, ...array_merge(...$values));
        $ceiling = max(5, (int) ceil($max / 5) * 5);
        $colors = ['#ed7d31', '#4472c4'];
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="560" height="340" viewBox="0 0 560 340"><rect width="560" height="340" fill="white"/>';
        for ($i = 0; $i <= 5; $i++) {
            $x = 145 + $i * 72;
            $svg .= '<line x1="'.$x.'" y1="15" x2="'.$x.'" y2="282" stroke="#e2e5e8"/><text x="'.$x.'" y="301" text-anchor="middle" font-family="DejaVu Sans" font-size="11" fill="#666">'.($ceiling * $i / 5).'</text>';
        }
        foreach (array_reverse($labels, true) as $position => $label) {
            $row = count($labels) - 1 - $position;
            $y = 28 + $row * (240 / count($labels));
            $svg .= '<text x="132" y="'.($y + 17).'" text-anchor="end" font-family="DejaVu Sans" font-size="12" fill="#444">'.htmlspecialchars($label, ENT_XML1).'</text>';
            foreach ($series as $i => $item) {
                $count = $values[$i][$position];
                $width = $count / $ceiling * 360;
                $barY = $y + $i * 17;
                if ($count) {
                    $svg .= '<rect x="145" y="'.$barY.'" width="'.$width.'" height="14" fill="'.$colors[$i % 2].'"/>';
                }
                $svg .= '<text x="'.(150 + $width).'" y="'.($barY + 11).'" font-family="DejaVu Sans" font-size="11" fill="#444">'.$count.'</text>';
            }
        }
        foreach ($series as $i => $item) {
            $x = 110 + $i * 205;
            $svg .= '<rect x="'.$x.'" y="322" width="14" height="10" fill="'.$colors[$i % 2].'"/><text x="'.($x + 21).'" y="331" font-family="DejaVu Sans" font-size="12" fill="#444">'.htmlspecialchars($item['label'], ENT_XML1).'</text>';
        }

        return 'data:image/svg+xml;base64,'.base64_encode($svg.'</svg>');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AgendaSchedule;
use App\Models\Asset;
use App\Models\Schedule;
use App\Support\PublicScheduleAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicDailyScheduleController extends Controller
{
    public function index(Request $request, ?string $token = null)
    {
        if ($token !== null && hash_equals(PublicScheduleAccess::token(), $token)) {
            $request->session()->put('public_daily_schedule_access', true);
        }
        abort_unless($request->session()->get('public_daily_schedule_access') === true, 404);

        $date = $request->date('date')?->toDateString() ?? now('Asia/Jakarta')->toDateString();
        $day = Carbon::parse($date, 'Asia/Jakarta');
        $assets = Asset::orderBy('name')->get()->keyBy('id');

        $trainingItems = Schedule::with(['training', 'pengajar', 'bookings.asset', 'assetLoanRequest'])
            ->whereDate('date', $date)
            ->where(fn ($query) => $query->whereNull('schedule_type')->orWhere('schedule_type', '!=', 'break'))
            ->get()->map(function ($schedule) use ($assets) {
                $locations = $schedule->bookings->pluck('asset.name')->filter();
                if ($locations->isEmpty() && $schedule->assetLoanRequest) {
                    $locations = $assets->only($schedule->assetLoanRequest->asset_ids ?? [])->pluck('name')->filter();
                }
                return [
                    'group' => $this->unitLabel($schedule->training?->bidang), 'order' => 10,
                    'start' => substr((string) $schedule->start_time, 0, 5), 'end' => substr((string) $schedule->end_time, 0, 5),
                    'title' => $schedule->activity ?: $schedule->training?->nama_pelatihan ?: 'Kegiatan Pelatihan',
                    'parent' => $schedule->training?->nama_pelatihan,
                    'location' => $schedule->external_place ?: ($locations->join(', ') ?: $schedule->training?->lokasi),
                    'zoom' => $schedule->link_zoom, 'executor' => $schedule->pengajar?->name ?: $schedule->pic, 'description' => null,
                ];
            });

        $agendaItems = AgendaSchedule::with(['agenda.creator', 'bookings.asset', 'assetLoanRequest'])
            ->whereDate('starts_at', '<=', $date)->whereDate('ends_at', '>=', $date)->get()
            ->map(function ($schedule) use ($date, $assets) {
                $locations = $schedule->bookings->pluck('asset.name')->filter();
                if ($locations->isEmpty() && $schedule->assetLoanRequest) {
                    $locations = $assets->only($schedule->assetLoanRequest->asset_ids ?? [])->pluck('name')->filter();
                }
                $agenda = $schedule->agenda;
                return [
                    'group' => $agenda?->agenda_type === 'pimpinan' ? 'AGENDA KEPALA BPSDM' : $this->unitLabel($agenda?->bidang ?: $agenda?->creator?->bidang),
                    'order' => $agenda?->agenda_type === 'pimpinan' ? 0 : 5,
                    'start' => $schedule->starts_at->toDateString() === $date ? $schedule->starts_at->format('H:i') : '00:00',
                    'end' => $schedule->ends_at->toDateString() === $date ? $schedule->ends_at->format('H:i') : '23:59',
                    'title' => $schedule->title ?: $agenda?->name ?: 'Agenda Kegiatan', 'parent' => null,
                    'location' => $schedule->external_place ?: $locations->join(', '), 'zoom' => $schedule->zoom_link,
                    'executor' => $schedule->participants_info, 'description' => $agenda?->description ?: $schedule->notes,
                ];
            });

        $items = $agendaItems->concat($trainingItems)->sortBy(fn ($item) => sprintf('%02d-%s', $item['order'], $item['start']))->values();
        $groups = $items->groupBy('group');
        $now = now('Asia/Jakarta');
        $isToday = $date === $now->toDateString();
        $isPastDate = $day->lt($now->copy()->startOfDay());
        $shareText = $this->shareText($day, $groups);
        $shareUrl = PublicScheduleAccess::url();

        return view('public.daily_schedule', compact('date', 'day', 'groups', 'items', 'now', 'isToday', 'isPastDate', 'shareText', 'shareUrl'));
    }

    private function unitLabel(?string $field): string
    {
        $field = trim((string) $field); $lower = Str::lower($field);
        if (Str::contains($lower, 'sekretariat')) return 'AGENDA SEKRETARIAT';
        if (Str::contains($lower, ['sertifikasi', 'kelembagaan'])) return 'AGENDA BIDANG SKPK';
        if (Str::contains($lower, 'manajerial')) return 'AGENDA BIDANG PKM';
        if (Str::contains($lower, 'teknis umum')) return 'AGENDA BIDANG PKTU';
        if (Str::contains($lower, 'teknis inti')) return 'AGENDA BIDANG PKTI';
        return $field !== '' && $field !== '-' ? 'AGENDA '.Str::upper($field) : 'AGENDA BPSDM';
    }

    private function shareText(Carbon $day, $groups): string
    {
        $lines = ['Assalamualaikum Wr. Wb.', '', 'Mohon izin Pimpinan, Bapak/Ibu seluruh Pegawai BPSDM Provinsi Jawa Barat. Berikut kami sampaikan Agenda '.$day->translatedFormat('l, d F Y').' sebagai berikut:', ''];
        foreach ($groups as $group => $items) {
            $lines[] = $group; $lines[] = $day->translatedFormat('l, d F Y'); $lines[] = '';
            foreach ($items->values() as $index => $item) {
                $lines[] = ($index + 1).'. Pkl. '.$item['start'].' WIB s.d. '.$item['end'].' WIB';
                $lines[] = '   '.$item['title'];
                if ($item['parent'] && $item['parent'] !== $item['title']) $lines[] = '   :: Pelatihan: '.$item['parent'];
                if ($item['description']) foreach (preg_split('/\R/', $item['description']) as $detail) if (trim($detail) !== '') $lines[] = '   '.trim($detail);
                if ($item['location']) $lines[] = '   :: Tempat: '.$item['location'];
                if ($item['zoom']) $lines[] = '   :: Link: '.$item['zoom'];
                if ($item['executor']) $lines[] = '   :: Pelaksana: '.$item['executor'];
                $lines[] = '';
            }
        }
        return trim(implode("\n", $lines));
    }
}

<?php

namespace App\Services;

use App\Models\Institution;
use App\Models\InstitutionMunicipality;
use App\Models\Participant;
use App\Models\TrainingMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as UserCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ParticipantUserRecap
{
    public const EXPORT_BATCH_SIZE = 1000;

    public function data(Request $request): array
    {
        [$byMunicipality, $groupId, $search] = $this->filters($request);
        $groups = $this->groups($byMunicipality);
        $participants = $this->query($byMunicipality, $groupId, $search)->paginate(15)->withQueryString();
        $participants->setCollection($this->rows($participants->getCollection(), $byMunicipality, $groups->pluck('name', 'id')));

        return compact('byMunicipality', 'groups', 'groupId', 'search', 'participants');
    }

    public function filters(Request $request): array
    {
        $search = $request->query('search');

        return [
            $request->query('group') === 'kabupaten-kota',
            $request->integer('group_id'),
            is_string($search) ? trim(mb_substr($search, 0, 100)) : '',
        ];
    }

    public function exportBatch(bool $byMunicipality, int $groupId, string $search, int $afterId): array
    {
        $users = $this->query($byMunicipality, $groupId, $search)
            ->where('id', '>', $afterId)
            ->reorder('id')
            ->limit(self::EXPORT_BATCH_SIZE)
            ->get();

        return [
            $users->isEmpty() ? collect() : $this->rows($users, $byMunicipality, $this->groups($byMunicipality)->pluck('name', 'id')),
            $users->last()?->id ?? $afterId,
        ];
    }

    private function groups(bool $byMunicipality): Collection
    {
        return ($byMunicipality ? InstitutionMunicipality::query() : Institution::query())->orderBy('name')->get(['id', 'name', 'is_active']);
    }

    private function query(bool $byMunicipality, int $groupId, string $search): Builder
    {
        $groupColumn = $byMunicipality ? 'institution_municipality_id' : 'institution_id';

        return User::query()
            ->select('id', 'name', 'nip', 'nik', 'nip_nik', 'institution_id', 'institution_municipality_id', 'instansi')
            ->where(fn ($query) => $query->where('role', 'participant')->orWhere('user_type', 'peserta'))
            ->whereNotNull($groupColumn)
            ->when($byMunicipality, fn ($query) => $query->where('institution_scope', 'municipality'),
                fn ($query) => $query->where(fn ($scope) => $scope->where('institution_scope', 'province')->orWhereNull('institution_scope')))
            ->when($groupId !== 0, fn ($query) => $query->where($groupColumn, $groupId))
            ->when($search !== '', fn ($query) => $query->where(fn ($fields) => $fields
                ->where('name', 'like', "%{$search}%")
                ->orWhere('nip', 'like', "%{$search}%")
                ->orWhere('nik', 'like', "%{$search}%")
                ->orWhere('nip_nik', 'like', "%{$search}%")
                ->orWhere('instansi', 'like', "%{$search}%")))
            ->orderBy('name')->orderBy('id');
    }

    private function rows(UserCollection $users, bool $byMunicipality, Collection $groupNames): Collection
    {
        $trainings = $this->activeTrainings($users);

        return $users->map(fn (User $user) => [
            'name' => $user->name,
            'nip' => $user->nip ?: $this->legacyIdentifier($user, 18),
            'nik' => $user->nik ?: $this->legacyIdentifier($user, 16),
            'institution' => $byMunicipality ? $user->instansi : ($groupNames[$user->institution_id] ?? $user->instansi),
            'municipality' => $byMunicipality ? ($groupNames[$user->institution_municipality_id] ?? null) : null,
            'trainings' => $trainings[$user->id] ?? collect(),
        ]);

    }

    private function activeTrainings(UserCollection $users): array
    {
        if ($users->isEmpty()) return [];

        $ids = $users->modelKeys();
        $identifiers = $users->flatMap(fn (User $user) => array_filter([
            $user->nip ?: $this->legacyIdentifier($user, 18),
            $user->nik ?: $this->legacyIdentifier($user, 16),
        ]))->unique()->values();

        // Only use older enrollment identifiers if they identify one account across the database.
        $owners = [];
        if ($identifiers->isNotEmpty()) {
            User::query()->whereIn('nip', $identifiers)->orWhereIn('nik', $identifiers)->orWhereIn('nip_nik', $identifiers)
                ->get(['id', 'nip', 'nik', 'nip_nik'])->each(function (User $user) use (&$owners, $identifiers) {
                    foreach (array_unique([$user->nip, $user->nik, $user->nip_nik]) as $identifier) {
                        if ($identifiers->containsStrict($identifier)) $owners[$identifier][] = $user->id;
                    }
                });
        }
        $legacyUsers = collect($owners)->filter(fn ($matches) => count($matches) === 1 && in_array($matches[0], $ids, true))
            ->map(fn ($matches) => $matches[0]);

        $forum = TrainingMessage::whereIn('user_id', $ids)->distinct()->get(['user_id', 'training_id'])
            ->groupBy('user_id')->map(fn ($messages) => $messages->pluck('training_id')->flip());

        return Participant::query()
            ->select('id', 'user_id', 'nip_nik', 'training_id')
            ->where(fn ($query) => $query->whereIn('user_id', $ids)->orWhere(fn ($old) => $old
                ->whereNull('user_id')->whereIn('nip_nik', $legacyUsers->keys())))
            ->with('training:id,nama_pelatihan,tgl_mulai')
            ->withExists([
                'attendances as has_presence' => fn ($query) => $query->where('status', 'hadir'),
                'evaluationResultsL1 as has_l1' => fn ($query) => $query->whereColumn('evaluation_results_l1.training_id', 'participants.training_id'),
                'evaluationL2 as has_l2',
                'evaluationResultsL34 as has_self_evaluation' => fn ($query) => $query
                    ->where('evaluator_role', 'mandiri')->whereColumn('evaluation_results_l34.training_id', 'participants.training_id'),
            ])
            ->get()
            ->groupBy(fn (Participant $row) => $row->user_id ?? $legacyUsers[$row->nip_nik])
            ->map(fn ($rows, $userId) => $rows
                ->filter(fn (Participant $row) => $row->training && ($row->has_presence || $row->has_l1 || $row->has_l2
                    || $row->has_self_evaluation || isset($forum[$userId][$row->training_id])))
                ->pluck('training')->unique('id')->sortByDesc('tgl_mulai')->values())
            ->all();
    }

    private function legacyIdentifier(User $user, int $length): ?string
    {
        return preg_match('/^\d{'.$length.'}$/D', (string) $user->nip_nik) ? $user->nip_nik : null;
    }
}

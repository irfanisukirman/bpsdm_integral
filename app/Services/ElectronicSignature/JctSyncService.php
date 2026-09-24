<?php

namespace App\Services\ElectronicSignature;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JctSyncService
{
    public function isConfigured(): bool
    {
        return filled(config('database.connections.jct.url')) || filled(config('database.connections.jct.host'));
    }

    public function diagnostics(): array
    {
        if (!$this->isConfigured()) {
            return [
                'configured' => false,
                'connected' => false,
                'message' => 'Database bridge JCT belum dikonfigurasi. Isi JCT_DB_HOST, JCT_DB_PORT, JCT_DB_DATABASE, JCT_DB_USERNAME, dan JCT_DB_PASSWORD.',
            ];
        }

        try {
            $connection = $this->db();
            $connection->getPdo();
            $orderingCount = (int) $connection->table('ordering_num_template')->count();
            $templateCount = (int) $connection->table('template')->count();

            return [
                'configured' => true,
                'connected' => true,
                'ordering_count' => $orderingCount,
                'template_count' => $templateCount,
                'message' => 'Database bridge JCT terhubung.',
            ];
        } catch (\Throwable $exception) {
            report($exception);
            return [
                'configured' => true,
                'connected' => false,
                'message' => 'Database bridge JCT belum dapat dihubungi. Periksa host, port, nama database, username, password, dan akses jaringan server.',
            ];
        }
    }
    /**
     * Daftar user_id peserta untuk sebuah template JCT.
     * Mengikuti perilaku ttdintec: baca ordering_num_template dengan LIKE id template.
     */
    public function participantUserIds(string $templateId): array
    {
        if (!$this->isConfigured() || $templateId === '') {
            return [];
        }
        try {
            return $this->db()->table('ordering_num_template')
                ->where('id', 'like', '%'.$templateId.'%')
                ->whereNotNull('user_id')
                ->where('user_id', '!=', '')
                ->pluck('user_id')
                ->map(fn ($value) => (string) $value)
                ->filter()
                ->unique()
                ->values()
                ->all();
        } catch (\Throwable $exception) {
            report($exception);
            return [];
        }
    }

    /**
     * Seluruh baris ordering_num_template untuk sebuah template.
     * Setara getInfoDownload() pada ttdintec.
     */
    public function orderingRows(string $templateId): array
    {
        if (!$this->isConfigured() || $templateId === '') {
            return [];
        }
        try {
            return $this->db()->table('ordering_num_template')
                ->where('id', 'like', '%'.$templateId.'%')
                ->whereNotNull('user_id')
                ->where('user_id', '!=', '')
                ->get()
                ->map(fn ($row) => (array) $row)
                ->all();
        } catch (\Throwable $exception) {
            report($exception);
            return [];
        }
    }

    /**
     * Baris ordering_num_template milik seorang user pada sebuah template.
     */
    public function orderingRow(?string $templateId, ?string $userId): ?object
    {
        if (!$this->isConfigured() || blank($templateId) || blank($userId)) {
            return null;
        }
        try {
            return $this->db()->table('ordering_num_template')
                ->where('id', 'like', '%'.$templateId.'%')
                ->where('user_id', $userId)
                ->first();
        } catch (\Throwable $exception) {
            report($exception);
            return null;
        }
    }

    /**
     * Menulis pengaturan penandatanganan ke tabel template JCT.
     * Setara TTD_sertif() pada ttdintec.
     */
    public function syncTemplate(string $templateId, ?string $signerPersonId, array $pemarafPayload, int $signMode): bool
    {
        if (!$this->isConfigured() || $templateId === '') {
            return false;
        }
        try {
            return (bool) $this->db()->table('template')
                ->where('id_template', $templateId)
                ->update([
                    'signer' => $signerPersonId,
                    'pemaraf' => json_encode($pemarafPayload, JSON_UNESCAPED_UNICODE),
                    'sign_mode' => in_array($signMode, [1, 2], true) ? $signMode : 1,
                ]);
        } catch (\Throwable $exception) {
            report($exception);
            return false;
        }
    }

    /**
     * Menandai pengunduhan/versi sertifikat pada ordering_num_template.
     * Setara downloadAllSertif() pada ttdintec.
     */
    public function syncDownloaded(?string $templateId, ?string $userId, ?string $statusPemarafJson = null): bool
    {
        if (!$this->isConfigured() || blank($templateId) || blank($userId)) {
            return false;
        }
        $payload = ['versi_tandatangan' => now()->format('Y-m-d')];
        if ($statusPemarafJson !== null) {
            $payload['status_pemaraf'] = $statusPemarafJson;
        }
        try {
            return (bool) $this->db()->table('ordering_num_template')
                ->where('id', 'like', '%'.$templateId.'%')
                ->where('user_id', $userId)
                ->update($payload);
        } catch (\Throwable $exception) {
            report($exception);
            return false;
        }
    }

    /**
     * Menandai status_tdt=1 pemaraf pada status_pemaraf dokumen.
     * Jika $personId diberikan, dicocokkan via person_id; jika tidak, dicocokkan via urutan
     * (setara perilaku berjenjang ttdintec: pemaraf tanda tangan mengikuti urutan).
     * Setara blok pemaraf pada ttdAllBsre() ttdintec.
     */
    public function syncPemarafSigned(?string $templateId, ?string $userId, ?string $personId = null, ?int $urutan = null): bool
    {
        if (!$this->isConfigured() || blank($templateId) || blank($userId)) {
            return false;
        }
        try {
            $row = $this->orderingRow($templateId, $userId);
            if (!$row) {
                return false;
            }
            $raw = $row->status_pemaraf ?? '[]';
            $arr = json_decode((string) $raw, true);
            if (!is_array($arr)) {
                $arr = [];
            }
            $touched = false;
            foreach ($arr as &$person) {
                $matchesPerson = $personId !== null && isset($person['person_id']) && (string) $person['person_id'] === (string) $personId;
                $matchesUrutan = $urutan !== null && isset($person['urutan']) && (int) $person['urutan'] === (int) $urutan;
                if ($matchesPerson || $matchesUrutan) {
                    $person['status_tdt'] = 1;
                    $person['signed_date'] = now()->format('Y-m-d H:i:s');
                    $touched = true;
                    break;
                }
            }
            unset($person);
            if (!$touched) {
                $arr[] = [
                    'person_id' => (string) ($personId ?: ''),
                    'urutan' => $urutan ?: (count($arr) + 1),
                    'status_tdt' => 1,
                    'signed_date' => now()->format('Y-m-d H:i:s'),
                ];
            }
            return (bool) $this->db()->table('ordering_num_template')
                ->where('id', 'like', '%'.$templateId.'%')
                ->where('user_id', $userId)
                ->update(['status_pemaraf' => json_encode($arr, JSON_UNESCAPED_UNICODE)]);
        } catch (\Throwable $exception) {
            report($exception);
            return false;
        }
    }

    /**
     * Menandai status_proses_ttde='true' untuk dokumen milik signer.
     * Setara blok signer pada ttdAllBsre() ttdintec.
     */
    public function syncSignerSigned(?string $templateId, ?string $userId): bool
    {
        if (!$this->isConfigured() || blank($templateId) || blank($userId)) {
            return false;
        }
        try {
            $row = $this->orderingRow($templateId, $userId);
            if (!$row) {
                return false;
            }
            if (in_array($row->status_proses_ttde ?? null, [true, 1, '1', 'true'], true)) {
                return true;
            }
            $this->db()->table('ordering_num_template')
                ->where('id', 'like', '%'.$templateId.'%')
                ->where('user_id', $userId)
                ->update(['status_proses_ttde' => 'true']);
            $updated = $this->orderingRow($templateId, $userId);
            return $updated && in_array($updated->status_proses_ttde ?? null, [true, 1, '1', 'true'], true);
        } catch (\Throwable $exception) {
            report($exception);
            return false;
        }
    }

    /**
     * Memastikan seluruh pemaraf pada dokumen sudah menandatangani (status_tdt=1).
     * Setara is_all_pemaraf_done() pada ttdintec.
     */
    public function isAllPemarafDone(?string $templateId, ?string $userId): bool
    {
        $row = $this->orderingRow($templateId, $userId);
        if (!$row) {
            return true;
        }
        $arr = json_decode((string) ($row->status_pemaraf ?? '[]'), true);
        if (!is_array($arr) || empty($arr)) {
            return true;
        }
        foreach ($arr as $person) {
            if ((int) ($person['status_tdt'] ?? 0) !== 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * Membangun payload pemaraf (urutan + person_id + status_tdt=0).
     * Setara TTD_sertif() pada ttdintec.
     */
    public function buildPemarafPayload(Collection|array $personIds): array
    {
        $payload = [];
        $sequence = 1;
        foreach ($personIds as $personId) {
            $personId = trim((string) $personId);
            if ($personId === '' || in_array($personId, array_column($payload, 'person_id'), true)) {
                continue;
            }
            $payload[] = [
                'urutan' => $sequence++,
                'person_id' => $personId,
                'status_tdt' => 0,
            ];
        }
        return $payload;
    }

    private function db()
    {
        return DB::connection('jct');
    }
}

<?php

namespace App\Services;

class TechnicalQuestionBankSyncService
{
    public function catalog(): array
    {
        return json_decode(file_get_contents(database_path('data/technical_questions_2026_11_15.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function sync(bool $apply = false): array
    {
        return app(ManagerialQuestionBankSyncService::class)->sync($apply, $this->catalog());
    }
}

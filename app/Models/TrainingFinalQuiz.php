<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TrainingFinalQuiz extends Model
{
    public const MAX_QUESTIONS = 100;

    public function isPretest(): bool
    {
        return $this->kind === 'pretest';
    }

    protected $fillable = [
        'training_id', 'title', 'pass_score', 'is_visible', 'revision', 'questions', 'is_public', 'opens_at', 'closes_at',
        'shuffle_questions', 'shuffle_answers', 'shuffle_groups',
    ];

    protected $casts = [
        'questions' => 'array', 'is_visible' => 'boolean', 'revision' => 'integer',
        'is_public' => 'boolean', 'opens_at' => 'datetime', 'closes_at' => 'datetime',
        'shuffle_questions' => 'boolean', 'shuffle_answers' => 'boolean', 'shuffle_groups' => 'boolean',
    ];

    protected $hidden = ['questions', 'public_token'];

    protected static function booted(): void
    {
        static::creating(function (self $quiz) {
            $quiz->public_token ??= (string) Str::uuid();
        });
    }

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function scheduleMessage(): ?string
    {
        $now = now();
        if ($this->opens_at?->gt($now)) {
            return 'Kuis dibuka pada '.$this->opens_at->format('d-m-Y, H:i').' WIB.';
        }
        if ($this->closes_at?->lte($now)) {
            return 'Pengisian kuis sudah ditutup.';
        }

        return null;
    }

    public function attempts()
    {
        return $this->hasMany(TrainingFinalQuizAttempt::class, 'quiz_id');
    }

    public function questionsForParticipant(array $questions, int $participantId): array
    {
        $seed = $this->id.':'.$participantId.':'.$this->revision;
        $rank = fn (string $scope, string $key) => hash_hmac('sha256', $seed.':'.$scope.':'.$key, config('app.key'));

        if ($this->shuffle_questions || $this->shuffle_groups) {
            $groups = collect($questions)->groupBy(fn (array $question) => Str::lower(trim($question['group'] ?? '')));
            if ($this->shuffle_groups) {
                $groups = $groups->sortKeysUsing(fn ($a, $b) => $rank('group', $a) <=> $rank('group', $b));
            }
            $questions = $groups->flatMap(function ($group) use ($rank) {
                return $this->shuffle_questions
                    ? $group->sortBy(fn (array $question) => $rank('question', $question['id']))->values()
                    : $group;
            })->all();
        }

        if ($this->shuffle_answers) {
            foreach ($questions as &$question) {
                $scope = 'answer:'.$question['id'];
                uksort($question['options'], fn ($a, $b) => $rank($scope, $a) <=> $rank($scope, $b));
            }
            unset($question);
        }

        return $questions;
    }

    public function accessTokens()
    {
        return $this->hasMany(TrainingFinalQuizAccessToken::class, 'quiz_id');
    }
}

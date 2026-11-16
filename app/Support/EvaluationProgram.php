<?php

namespace App\Support;

final class EvaluationProgram
{
    public static function normalize(?string $value): string
    {
        return match (strtoupper(trim((string) $value))) {
            'CPNS', 'LATSAR' => 'LATSAR',
            'PKP', 'PKA', 'PKN' => strtoupper(trim((string) $value)),
            'MANAJERIAL' => 'Manajerial',
            'PKTI/PKTU', 'TEKNIS', '' => 'Teknis',
            'SEMUA' => 'semua',
            default => trim((string) $value),
        };
    }

    public static function aliases(?string $value): array
    {
        return match (self::normalize($value)) {
            'LATSAR' => ['LATSAR', 'CPNS'],
            'Manajerial' => ['MANAJERIAL'],
            'Teknis' => ['TEKNIS', 'PKTI/PKTU'],
            default => [strtoupper((string) $value)],
        };
    }

    public static function isManagerial(?string $program): bool
    {
        return in_array(self::normalize($program), ['LATSAR', 'PKP', 'PKA', 'PKN', 'Manajerial'], true);
    }

    public static function roles(?string $program): array
    {
        return self::isManagerial($program)
            ? ['mandiri', 'atasan'] : ['mandiri', 'atasan', 'rekan'];
    }
}

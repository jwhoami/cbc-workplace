<?php

declare(strict_types=1);

namespace App\Helpers;

use Normalizer;

/**
 * Deterministic, idempotent diacritic folder for accent-insensitive search (FR-009b).
 *
 * Used at write-time by JobListingObserver to populate `title_folded` and
 * `description_folded` columns, and at read-time at the controller boundary
 * to fold incoming keyword queries before LIKE matching.
 *
 * Algorithm:
 *   1. Normalize to NFKD (decompose accented characters into base + combining mark)
 *   2. Strip the combining marks (Unicode category Mn)
 *   3. Lowercase via mb_strtolower
 *
 * "diseñador" → "disenador"; "DISEÑADOR" → "disenador"; "café" → "cafe".
 *
 * The transform is idempotent: fold(fold($s)) === fold($s).
 */
final class DiacriticFolder
{
    public static function fold(?string $input): string
    {
        if ($input === null || $input === '') {
            return '';
        }

        $normalized = Normalizer::isNormalized($input, Normalizer::FORM_KD)
            ? $input
            : Normalizer::normalize($input, Normalizer::FORM_KD);

        if ($normalized === false) {
            return mb_strtolower($input, 'UTF-8');
        }

        $stripped = preg_replace('/\p{Mn}+/u', '', $normalized) ?? $normalized;

        return mb_strtolower($stripped, 'UTF-8');
    }
}

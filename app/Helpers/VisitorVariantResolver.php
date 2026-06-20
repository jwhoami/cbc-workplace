<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Enums\VisitorVariant;
use Illuminate\Support\Facades\Auth;

/**
 * Classifies the current request's visitor for FR-019 Apply-CTA branching.
 *
 * Order matters: an admin who is also a member should be classified as
 * Admin (no Apply CTA per Edge Case bullet 6), so we check admin guard
 * first.
 */
final class VisitorVariantResolver
{
    public static function resolve(): VisitorVariant
    {
        if (Auth::guard('admin')->check()) {
            return VisitorVariant::Admin;
        }

        $member = Auth::guard('member')->user();
        if ($member === null) {
            return VisitorVariant::Anonymous;
        }

        $hasCandidateProfile = method_exists($member, 'candidateProfile')
            && $member->candidateProfile()->exists();

        return $hasCandidateProfile
            ? VisitorVariant::MemberCandidate
            : VisitorVariant::MemberWithoutCandidateProfile;
    }
}

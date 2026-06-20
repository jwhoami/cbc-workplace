<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CandidateProfile;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Promueve cuentas seedeadas por Spec009DemoSeeder a credenciales
 * predecibles (`*@example.com` / `password`) para que el pipeline de
 * capturas de las guías oficiales pueda autenticarse de forma
 * reproducible. Idempotente: se puede correr múltiples veces.
 *
 * Pre-requisitos: ejecutar primero Spec009DemoSeeder.
 */
class GuidesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $verifiedOrg = Organization::query()
            ->where('verification_state', 1)
            ->whereNull('suspended_at')
            ->where('member_id', '!=', Member::query()
                ->where('email', 'org-admin@example.com')
                ->value('id')
            )
            ->with('member')
            ->first();

        if ($verifiedOrg && $verifiedOrg->member) {
            $verifiedOrg->member->forceFill([
                'email' => 'org-verified@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
                'is_blocked' => false,
            ])->save();
        }

        $candidateProfile = CandidateProfile::query()
            ->with('member')
            ->first();

        if ($candidateProfile && $candidateProfile->member) {
            $candidateProfile->member->forceFill([
                'email' => 'candidate@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
                'is_blocked' => false,
            ])->save();
        }

        $suspendTarget = Member::query()
            ->where('email', 'org-admin@example.com')
            ->first();

        if ($suspendTarget) {
            $suspendTarget->forceFill([
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ])->save();
        }
    }
}

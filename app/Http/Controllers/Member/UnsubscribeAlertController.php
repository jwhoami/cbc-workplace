<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Actions\Member\DisableJobAlertByTokenAction;
use App\Http\Controllers\Controller;
use App\Models\JobAlert;
use App\Models\Member;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UnsubscribeAlertController extends Controller
{
    public function __invoke(Member $member, JobAlert $alert): SymfonyResponse
    {
        if ($alert->member_id !== $member->id) {
            return response()
                ->view('unsubscribe-alert', ['outcome' => 'not-found', 'alert' => null])
                ->header('Cache-Control', 'no-store');
        }

        DisableJobAlertByTokenAction::run($member, $alert);

        return response()
            ->view('unsubscribe-alert', ['outcome' => 'disabled', 'alert' => $alert->fresh()])
            ->header('Cache-Control', 'no-store');
    }
}

<?php

namespace App\Actions\Member;

use App\Enums\MembershipState;
use App\Helpers\AppUtil;
use App\Mail\Member\AffilateRequest;
use App\Models\Member;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class RequestAffiliation
{
    use AsAction;

    public function handle(Member $member, array $data)
    {
        $member->membership_state = MembershipState::PENDING;
        $member->membership_reason = $data['reason'];
        $member->save();

        $member->addComment('Solicitud de Afiliación');

        $approvers = AppUtil::getAffiliateApprovers();

        foreach ($approvers as $user) {
            Mail::to($user)->send(new AffilateRequest($member));
        }
    }
}

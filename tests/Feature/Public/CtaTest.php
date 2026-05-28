<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\CandidateProfile;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 6 — User Story 4 (FR-019): the Apply CTA on the offer detail page
 * adapts to the visitor variant. Four cases:
 *  - Anonymous visitor → sign-in / register prompt
 *  - Member without candidate profile → complete-profile prompt
 *  - Member with candidate profile → Apply button (POST form to spec-006 route)
 *  - Admin → renders nothing (Edge Case bullet 6)
 */
class CtaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function makeActiveOffer(): JobListing
    {
        $owner = Member::factory()->create();
        $org = Organization::factory()->create([
            'member_id' => $owner->id,
            'is_active' => true,
        ]);

        return JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Coordinador de Eventos',
            'application_deadline' => now()->addDays(30),
        ]);
    }

    public function test_anonymous_visitor_sees_signin_prompt(): void
    {
        $offer = $this->makeActiveOffer();

        $response = $this->get('/bolsa-de-trabajo/'.$offer->slug);

        $response->assertOk();
        $response->assertSee('data-cta-variant="anonymous"', escape: false);
        $response->assertSee(__('public.cta.anonymous.title'));
        $response->assertSee(__('public.cta.anonymous.sign_in'));
        $response->assertSee(__('public.cta.anonymous.register'));

        $expectedRedirect = urlencode(url('/bolsa-de-trabajo/'.$offer->slug));
        $response->assertSee('/member/login?redirect='.$expectedRedirect, escape: false);

        $response->assertDontSee(__('public.cta.member_no_profile.complete_profile'));
        $response->assertDontSee(__('public.cta.member_candidate.button'));
    }

    public function test_member_without_profile_sees_complete_profile_prompt(): void
    {
        $offer = $this->makeActiveOffer();
        $member = Member::factory()->create();
        $this->assertNull($member->candidateProfile);

        $response = $this->actingAs($member, 'member')->get('/bolsa-de-trabajo/'.$offer->slug);

        $response->assertOk();
        $response->assertSee('data-cta-variant="member_no_profile"', escape: false);
        $response->assertSee(__('public.cta.member_no_profile.title'));
        $response->assertSee(__('public.cta.member_no_profile.complete_profile'));
        $response->assertSee('/member/candidate-profiles/create', escape: false);

        $response->assertDontSee(__('public.cta.anonymous.sign_in'));
        $response->assertDontSee(__('public.cta.member_candidate.button'));
    }

    public function test_member_with_candidate_sees_apply_button(): void
    {
        $offer = $this->makeActiveOffer();
        $member = Member::factory()->create();
        CandidateProfile::factory()->create(['member_id' => $member->id]);

        $response = $this->actingAs($member->fresh(), 'member')->get('/bolsa-de-trabajo/'.$offer->slug);

        $response->assertOk();
        $response->assertSee('data-cta-variant="member_candidate"', escape: false);
        $response->assertSee(__('public.cta.member_candidate.button'));

        // Redirects directly to the interactive Filament application page (GET)
        $response->assertDontSee('<form', escape: false);
        $response->assertSee('href="'.url('/member/apply/'.$offer->slug).'"', escape: false);

        $response->assertDontSee(__('public.cta.anonymous.sign_in'));
        $response->assertDontSee(__('public.cta.member_no_profile.complete_profile'));
    }

    public function test_admin_sees_no_apply_cta(): void
    {
        $offer = $this->makeActiveOffer();
        $admin = User::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get('/bolsa-de-trabajo/'.$offer->slug);

        $response->assertOk();
        $response->assertDontSee('data-cta-variant=', escape: false);
        $response->assertDontSee(__('public.cta.anonymous.sign_in'));
        $response->assertDontSee(__('public.cta.member_no_profile.complete_profile'));
        $response->assertDontSee(__('public.cta.member_candidate.button'));
    }
}

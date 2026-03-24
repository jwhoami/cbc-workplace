<?php

namespace Tests\Feature\Member\Auth;

use App\Enums\MembershipState;
use App\Filament\Member\Pages\EditProfile;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class EditProfileTest extends TestCase
{
    use RefreshDatabase;

    protected Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->member = Member::factory()->create([
            'email' => 'member@gmail.com',
            'membership_state' => MembershipState::UNDEFINED,
        ]);
        Livewire::actingAs($this->member, 'member');
        $this->get('/member');
    }

    public function test_it_can_save_his_profile(): void
    {
        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => 'Member 1',
            ])
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_it_can_upload_avatars(): void
    {
        Storage::fake('tmp-for-tests');
        Storage::fake('avatars');

        Livewire::test(EditProfile::class)
            ->fillForm([
                'avatar' => UploadedFile::fake()->image('image.jpeg'),
            ])
            ->call('save')
            ->assertHasNoErrors();

        $member = Member::query()->where('email', 'member@gmail.com')->first();
        $this->assertNotNull($member->avatar);
        Storage::disk('avatars')->assertExists($member->avatar);
    }
}

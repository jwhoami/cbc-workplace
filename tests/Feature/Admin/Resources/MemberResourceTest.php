<?php

namespace Tests\Feature\Admin\Resources;

use App\Filament\Admin\Resources\MemberResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MemberResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name' => 'admin',
            'title' => 'Admin',
            'is_active' => true,
            'is_admin' => true,
            'perm' => [],
        ]);
        Livewire::actingAs(User::factory()->create(['role_id' => $adminRole->id]), 'admin');
        $this->get('/admin');
    }

    public function test_it_renders_list(): void
    {
        $this->get(MemberResource::getUrl('index'))
            ->assertSuccessful();
    }
}

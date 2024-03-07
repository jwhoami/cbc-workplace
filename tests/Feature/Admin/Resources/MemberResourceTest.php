<?php

namespace Tests\Feature\Admin\Resources;

use App\Filament\Admin\Resources\MemberResource;
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

    Livewire::actingAs(User::factory()->create(), 'admin');
    $this->get('/admin');
  }

  public function test_it_renders_list(): void
  {
    $this->get(MemberResource::getUrl('index'))
      ->assertSuccessful();
  }
}

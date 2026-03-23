<?php

namespace Tests\Feature\Admin\Resources;

use App\Filament\Admin\Resources\JobCategoryResource;
use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\JobCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class JobCategoryResourceTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();

    $adminRole = Role::create([
      'name' => 'admin',
      'title' => 'Administrator',
      'is_active' => true,
      'is_admin' => true,
      'perm' => ['*.*'],
    ]);

    $user = User::factory()->create([
      'role_id' => $adminRole->id,
      'is_active' => true,
    ]);

    Livewire::actingAs($user, 'admin');
    $this->get('/admin');
  }

  public function test_migration_adds_slug_and_icon_columns(): void
  {
    $this->assertTrue(Schema::hasColumn('categories', 'slug'));
    $this->assertTrue(Schema::hasColumn('categories', 'icon'));
  }

  public function test_seeder_creates_nine_job_categories(): void
  {
    $this->seed(JobCategorySeeder::class);

    $count = Category::where('scope', 'JobListing')->count();
    $this->assertEquals(9, $count);
  }

  public function test_seeder_is_idempotent(): void
  {
    $this->seed(JobCategorySeeder::class);
    $this->seed(JobCategorySeeder::class);

    $count = Category::where('scope', 'JobListing')->count();
    $this->assertEquals(9, $count);
  }

  public function test_it_renders_list(): void
  {
    $this->get(JobCategoryResource::getUrl('index'))
      ->assertSuccessful();
  }

  public function test_admin_can_create_job_category(): void
  {
    Livewire::test(JobCategoryResource\Pages\ListJobCategories::class)
      ->callAction('create', [
        'name' => 'Test Category',
        'slug' => 'test-category',
        'icon' => 'heroicon-o-star',
        'order' => 1,
      ]);

    $this->assertDatabaseHas('categories', [
      'name' => 'Test Category',
      'slug' => 'test-category',
      'icon' => 'heroicon-o-star',
      'scope' => 'JobListing',
    ]);
  }

  public function test_admin_can_edit_job_category(): void
  {
    $category = Category::factory()->create([
      'name' => 'Original',
      'scope' => 'JobListing',
      'slug' => 'original',
      'icon' => 'heroicon-o-star',
    ]);

    Livewire::test(JobCategoryResource\Pages\ListJobCategories::class)
      ->callTableAction('edit', $category, [
        'name' => 'Updated',
        'slug' => 'updated',
        'icon' => 'heroicon-o-heart',
        'order' => 5,
      ]);

    $this->assertDatabaseHas('categories', [
      'id' => $category->id,
      'name' => 'Updated',
      'slug' => 'updated',
    ]);
  }

  public function test_admin_can_delete_job_category(): void
  {
    $category = Category::factory()->create([
      'name' => 'To Delete',
      'scope' => 'JobListing',
      'slug' => 'to-delete',
    ]);

    Livewire::test(JobCategoryResource\Pages\ListJobCategories::class)
      ->callTableAction('delete', $category);

    $this->assertDatabaseMissing('categories', [
      'id' => $category->id,
    ]);
  }

  public function test_slug_auto_generates_from_name_when_empty(): void
  {
    Livewire::test(JobCategoryResource\Pages\ListJobCategories::class)
      ->callAction('create', [
        'name' => 'Tecnologia e Informatica',
        'slug' => '',
        'icon' => 'heroicon-o-computer-desktop',
        'order' => 1,
      ]);

    $this->assertDatabaseHas('categories', [
      'name' => 'Tecnologia e Informatica',
      'slug' => 'tecnologia-e-informatica',
      'scope' => 'JobListing',
    ]);
  }

  public function test_duplicate_slug_within_same_scope_is_rejected(): void
  {
    Category::factory()->create([
      'name' => 'Existing',
      'scope' => 'JobListing',
      'slug' => 'duplicate-slug',
    ]);

    Livewire::test(JobCategoryResource\Pages\ListJobCategories::class)
      ->callAction('create', [
        'name' => 'Another',
        'slug' => 'duplicate-slug',
        'icon' => 'heroicon-o-star',
        'order' => 2,
      ])
      ->assertHasActionErrors(['slug' => 'unique']);

    $count = Category::where('scope', 'JobListing')
      ->where('slug', 'duplicate-slug')
      ->count();
    $this->assertEquals(1, $count);
  }

  public function test_activity_log_records_crud_operations(): void
  {
    $category = Category::factory()->create([
      'name' => 'Log Test',
      'scope' => 'JobListing',
      'slug' => 'log-test',
    ]);

    $category->update(['name' => 'Log Test Updated']);

    $this->assertDatabaseHas('activity_log', [
      'subject_type' => Category::class,
      'subject_id' => $category->id,
      'event' => 'updated',
    ]);
  }

  public function test_venture_categories_not_shown_in_job_category_list(): void
  {
    Category::factory()->create([
      'name' => 'Venture Cat',
      'scope' => 'Venture',
    ]);
    Category::factory()->create([
      'name' => 'Job Cat',
      'scope' => 'JobListing',
      'slug' => 'job-cat',
    ]);

    $this->get(JobCategoryResource::getUrl('index'))
      ->assertSuccessful()
      ->assertDontSee('Venture Cat')
      ->assertSee('Job Cat');
  }
}

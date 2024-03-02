<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCaseAdmin extends BaseTestCase
{
  use CreatesApplication;

  protected function setUp(): void
  {
    parent::setUp();

    $this->actingAs(User::factory()->create(), 'admin');
  }
}

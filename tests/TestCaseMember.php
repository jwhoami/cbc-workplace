<?php

namespace Tests;

use App\Models\Member;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCaseMember extends BaseTestCase
{
  use CreatesApplication;

  protected function setUp(): void
  {
    parent::setUp();

    $this->actingAs(Member::factory()->create(), 'member');
  }
}

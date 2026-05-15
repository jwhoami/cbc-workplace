<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Wipe Laravel's Storage::fake() testing-disks directory before each test.
     *
     * Prevents pre-existing root-owned directories (left over from container
     * runs that executed as root) from blocking FilesystemIterator during
     * Storage::fake() teardown. Idempotent and safe: only runs if the
     * directory exists AND is writable by the current user, so a stale
     * unwritable state surfaces a clear ownership error instead of being
     * silently retained.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $testingDisks = storage_path('framework/testing/disks');
        if (is_dir($testingDisks) && is_writable($testingDisks)) {
            File::deleteDirectory($testingDisks);
        }
    }
}

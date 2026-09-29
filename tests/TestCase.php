<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;
use App\Support\Qty;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        if (Schema::hasTable('roles')) {
            foreach (['Administrator', 'Admission', 'Accounting', 'Supply Personnel', 'Faculty', 'Student'] as $role) {
                Role::findOrCreate($role);
            }
        }
    }

    protected function assertQty(int|float $expected, mixed $actual): void
    {
        $this->assertSame(Qty::of($expected), Qty::of($actual));
    }
}

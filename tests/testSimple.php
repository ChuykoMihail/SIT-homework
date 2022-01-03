<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class testSimple extends TestCase
{

    public function testIsKey(): void
    {
        $this->assertArrayHasKey('foo', ['foo' => 'baz']);
    }

    public function testInArray(): void
    {
        $this->assertContains(4, [1, 2, 3, 4]);
    }
}
<?php

namespace SwFwLessTest\components\utils\runtime\php;

use PHPUnit\Framework\TestCase;
use SwFwLess\components\utils\runtime\php\Version;

class VersionTest extends TestCase
{
    public function testLessThan()
    {
        $this->assertTrue(Version::lessThan('8.1.0', '7.4.0'));
        $this->assertFalse(Version::lessThan('7.1.0', '7.4.0'));
        $this->assertTrue(Version::lessThan('8.1', '7.4.0'));
        $this->assertFalse(Version::lessThan('7.1', '7.4.0'));
    }

    public function testLessThanOrEquals()
    {
        $this->assertTrue(Version::lessThanOrEquals('8.1.0', '7.4.0'));
        $this->assertTrue(Version::lessThanOrEquals('7.4.0', '7.4.0'));
        $this->assertFalse(Version::lessThanOrEquals('7.1.0', '7.4.0'));
        $this->assertTrue(Version::lessThanOrEquals('8.1', '7.4.0'));
        $this->assertFalse(Version::lessThanOrEquals('7.4', '7.4.0'));
        $this->assertFalse(Version::lessThanOrEquals('7.1', '7.4.0'));
    }

    public function testGreaterThan()
    {
        $this->assertTrue(Version::greaterThan('7.1.0', '7.4.0'));
        $this->assertFalse(Version::greaterThan('8.1.0', '7.4.0'));
        $this->assertTrue(Version::greaterThan('7.1', '7.4.0'));
        $this->assertFalse(Version::greaterThan('8.1', '7.4.0'));
    }

    public function testGreaterThanOrEquals()
    {
        $this->assertTrue(Version::greaterThanOrEquals('7.1.0', '7.4.0'));
        $this->assertTrue(Version::greaterThanOrEquals('7.1.0', '7.1.0'));
        $this->assertFalse(Version::greaterThanOrEquals('8.1.0', '7.4.0'));
        $this->assertTrue(Version::greaterThanOrEquals('7.1', '7.4.0'));
        $this->assertTrue(Version::greaterThanOrEquals('7.1', '7.1.0'));
        $this->assertFalse(Version::greaterThanOrEquals('8.1', '7.4.0'));
    }

    public function testEquals()
    {
        $this->assertTrue(Version::equals('7.1.0', '7.1.0'));
        $this->assertFalse(Version::equals('7.1.0', '7.4.0'));
        $this->assertFalse(Version::equals('7.1', '7.1.0'));
        $this->assertFalse(Version::equals('7.1', '7.4.0'));
    }

    public function testNotEquals()
    {
        $this->assertTrue(Version::notEquals('7.1.0', '7.4.0'));
        $this->assertFalse(Version::notEquals('7.1.0', '7.1.0'));
        $this->assertTrue(Version::notEquals('7.1', '7.4.0'));
        $this->assertTrue(Version::notEquals('7.1', '7.1.0'));
    }
}

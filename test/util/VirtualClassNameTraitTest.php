<?php
declare(strict_types=1);

namespace knotlib\command\test\util;

use knotlib\command\test\classes\MyVirtualClassNameTraitClient;
use PHPUnit\Framework\TestCase;

final class VirtualClassNameTraitTest extends TestCase
{
    public function testVirtualClassToReal()
    {
        $virtual_class = 'knotlib.command.test.classes.MyVirtualClassNameTraitClient';
        $this->assertEquals(MyVirtualClassNameTraitClient::class, MyVirtualClassNameTraitClient::virtualClassToReal($virtual_class));

        $virtual_class = 'Hello.World.David';
        $this->assertEquals('Hello\\World\\David', MyVirtualClassNameTraitClient::virtualClassToReal($virtual_class));
    }
    public function testRealClassToVirtual()
    {
        $virtual_class = 'knotlib.command.test.classes.MyVirtualClassNameTraitClient';
        $this->assertEquals($virtual_class, MyVirtualClassNameTraitClient::realClassToVirtual(MyVirtualClassNameTraitClient::class));

        $virtual_class = 'Hello.World.David';
        $this->assertEquals($virtual_class, MyVirtualClassNameTraitClient::realClassToVirtual('Hello\\World\\David'));
    }
    public function testClassExists()
    {
        $virtual_class = 'knotlib.command.test.classes.MyVirtualClassNameTraitClient';
        $this->assertTrue(MyVirtualClassNameTraitClient::classExists($virtual_class));

        $virtual_class = 'Hello.World.David';
        $this->assertFalse(MyVirtualClassNameTraitClient::classExists($virtual_class));
    }
}
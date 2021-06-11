<?php
declare(strict_types=1);

namespace knotlib\command\test\filesystem;

use PHPUnit\Framework\TestCase;

use knotlib\command\filesystem\CommandLibFileSystem;
use knotlib\kernel\filesystem\Dir;

final class CommandLibFileSystemTest extends TestCase
{
    public function testGetDir()
    {
        $fs = new CommandLibFileSystem();
        $base_dir = dirname(__DIR__, 2);

        $this->assertEquals($base_dir . '/template', $fs->getDirectory(Dir::TEMPLATE));
    }
}
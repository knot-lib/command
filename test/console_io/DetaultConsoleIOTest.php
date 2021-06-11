<?php
declare(strict_types=1);

namespace knotlib\command\test\console_io;

use knotlib\command\console_io\DefaultConsoleIO;
use PHPUnit\Framework\TestCase;

final class DetaultConsoleIOTest extends TestCase
{
    public function testOutput()
    {
        ob_start();
        (new DefaultConsoleIO())->output('{0}, give me {1:d} dollars!', 'David', 123);
        $output = ob_get_clean();

        $this->assertEquals('David, give me 123 dollars!', $output);
    }
}
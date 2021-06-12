<?php
declare(strict_types=1);

namespace knotlib\command\test;

use knotlib\command\console_io\DefaultConsoleIO;
use knotlib\command\service\DI;
use knotlib\command\test\classes\ConcreteCommand;
use knotlib\di\Container;
use knotlib\kernel\di\DiContainerInterface;
use knotlib\support\adapter\PsrContainerAdapter;
use PHPUnit\Framework\TestCase;

final class CommandTest extends TestCase
{
    /** @var DiContainerInterface */
    private $di;

    public function setUp() : void
    {
        $this->di = new Container();

        $this->di[DI::URI_COMPONENT_CONSOLE_IO] = new DefaultConsoleIO();
    }

    public function testConfigure()
    {
        $cmd = new ConcreteCommand();

        $this->assertEquals('', $cmd->getCommandPath());
        $this->assertEquals([], $cmd->getOrderdArgs());
        $this->assertEquals([], $cmd->getCommandHelps());

        $cmd->configure();

        $this->assertEquals('hello:world:command', $cmd->getCommandPath());
        $this->assertEquals(['message'], $cmd->getOrderdArgs());
        $this->assertEquals(['knot hello:world:command message'], $cmd->getCommandHelps());
    }
    public function testExecute()
    {
        $cmd = new ConcreteCommand();

        $cmd->setDi(new PsrContainerAdapter($this->di));

        ob_start();
        $cmd->execute(['Hello, David!']);
        $output = ob_get_clean();

        $this->assertEquals('Hello, David!' . PHP_EOL, $output);
    }
}
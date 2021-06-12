<?php
declare(strict_types=1);

namespace knotlib\command\test\classes;

use knotlib\command\Command;

final class ConcreteCommand extends Command
{
    public function configure(): void
    {
        $this
            ->setCommandPath('hello:world:command')
            ->setOrderdArgs([
                'message'
            ])
            ->setCommandHelps([
               'knot hello:world:command message'
            ]);
    }

    public function execute(array $args) : void
    {
        $message = $args[0] ?? '';

        $this->getConsoleIO()->output($message)->eol();
    }

}
<?php
declare(strict_types=1);

namespace knotlib\command;

use Throwable;

use knotlib\command\service\DI;
use knotlib\kernel\di\DiContainerInterface;

abstract class Command extends CommandDescriptor implements CommandInterface
{
    /** @var DiContainerInterface */
    private $di;

    /**
     * Set DI container
     *
     * @param DiContainerInterface $di
     */
    public function setDi(DiContainerInterface $di)
    {
        $this->di = $di;
    }

    /**
     * Get DI container
     *
     * @return DiContainerInterface
     */
    public function getDi() : DiContainerInterface
    {
        return $this->di;
    }

    /**
     * Get Console IO
     *
     * @return ConsoleIOInterface
     */
    public function getConsoleIO() : ConsoleIOInterface
    {
        return $this->di[DI::URI_COMPONENT_CONSOLE_IO];
    }

    /**
     * Configure command
     */
    public function configure() : void
    {
        // Override this method to configure descriptor
    }

    /**
     * Get descriptor object
     *
     * @return CommandDescriptor
     */
    public function getDescriptor() : CommandDescriptor
    {
        return $this;
    }

    /**
     * Handle exception while occured in executing the command
     *
     * @param Throwable $ex
     *
     * @return void
     */
    public function handleException(Throwable $ex) : void
    {
        /** @var ConsoleIOInterface $io */
        $io = $this->di[DI::URI_COMPONENT_CONSOLE_IO];

        $io->output('Exception had occured: ' . $ex->getMessage())->eol();
        $io->output('Exception Stack: ')->eol()->output($ex->getTraceAsString())->eol();
    }
}
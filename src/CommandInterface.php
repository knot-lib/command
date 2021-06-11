<?php
declare(strict_types=1);

namespace knotlib\command;

use Throwable;

use knotlib\kernel\di\DiContainerInterface;

interface CommandInterface
{
    /**
     * Set DI container
     *
     * @param DiContainerInterface $di
     */
    public function setDi(DiContainerInterface $di);

    /**
     * Get DI container
     *
     * @return DiContainerInterface
     */
    public function getDi() : DiContainerInterface;

    /**
     * Get Console IO
     *
     * @return ConsoleIOInterface
     */
    public function getConsoleIO() : ConsoleIOInterface;

    /**
     * Configure command
     */
    public function configure() : void;

    /**
     * Get descriptor object
     *
     * @return CommandDescriptor
     */
    public function getDescriptor() : CommandDescriptor;

    /**
     * Execute command
     *
     * @param array $args
     *
     * @return void
     */
    public function execute(array $args) : void;

    /**
     * Handle exception while occured in executing the command
     *
     * @param Throwable $ex
     *
     * @return void
     */
    public function handleException(Throwable $ex) : void;
}
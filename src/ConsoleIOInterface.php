<?php
declare(strict_types=1);

namespace knotlib\command;

interface ConsoleIOInterface
{
    /**
     * Input user command from console
     *
     * @param string $message               message to show in console
     * @param callable|null $callback       callback to receive user input from console
     *
     * @return ConsoleIOInterface
     */
    public function ask(string $message, callable $callback = null) : ConsoleIOInterface;

    /**
     * Output message to console
     *
     * @param string $format               message or format
     * @param ... $values
     *
     * @return ConsoleIOInterface
     */
    public function output(string $format, ... $values) : ConsoleIOInterface;

    /**
     * Output end of line
     *
     * @return ConsoleIOInterface
     */
    public function eol() : ConsoleIOInterface;
}
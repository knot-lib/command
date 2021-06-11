<?php
declare(strict_types=1);

namespace knotlib\command\console_io;

use knotlib\command\ConsoleIOInterface;
use stk2k\string\StringUtil;

final class DefaultConsoleIO implements ConsoleIOInterface
{
    /**
     * {@inheritDoc}
     */
    public function ask(string $message, callable $callback = null) : ConsoleIOInterface
    {
        echo $message . PHP_EOL;
        if ($callback){
            $input = trim(fgets(STDIN));
            ($callback)($input);
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function output(string $format, ... $values) : ConsoleIOInterface
    {
        echo StringUtil::formatArray($format, $values);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function eol() : ConsoleIOInterface
    {
        echo PHP_EOL;
        return $this;
    }
}
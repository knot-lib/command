<?php
declare(strict_types=1);

namespace knotlib\command;

interface CommandDescriptorProviderInterface
{
    /**
     * Provide command descriptors
     *
     * @return CommandDescriptor[]
     */
    public static function provide() : array;
}
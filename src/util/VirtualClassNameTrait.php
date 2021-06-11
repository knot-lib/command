<?php
declare(strict_types=1);

namespace knotlib\command\util;

trait VirtualClassNameTrait
{
    /**
     * Returns real class name
     * i.e.
     *   Hello\World\My\Class
     *
     * @param string $virtual_class_name
     *
     * @return string
     */
    public static function virtualClassToReal(string $virtual_class_name) : string
    {
        return str_replace('.', '\\', $virtual_class_name);
    }

    /**
     * Returns virautl class name
     *
     * i.e.
     *   Hello.World.My.Class
     *
     * @param string $real_class_name
     *
     * @return string
     */
    public static function realClassToVirtual(string $real_class_name) : string
    {
        return str_replace('\\', '.', $real_class_name);
    }

    /**
     * Returns class exists
     *
     * @param string $class_name
     *
     * @return bool
     */
    public static function classExists(string $class_name): bool
    {
        $real_class_name = self::virtualClassToReal($class_name);
        return class_exists($real_class_name);
    }
}
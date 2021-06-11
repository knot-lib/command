<?php
declare(strict_types=1);

namespace knotlib\command\service;

use knotlib\services\DI as ServiceDI;

final class DI extends ServiceDI
{
    //=====================================
    // Components
    //=====================================

    /* Console I/O Component */
    const URI_COMPONENT_CONSOLE_IO       = self::SCHEME_COMPONENT . 'console_io';

    //====================================
    // Arrays
    //====================================

    //====================================
    // Strings
    //====================================

    //=====================================
    // Service
    //=====================================

    /* Command Autoload Service */
    const URI_SERVICE_COMMAND_AUTOLOAD    = self::SCHEME_SERVICE . 'command_autoload';

    /* Command DB Service */
    const URI_SERVICE_COMMAND_DB          = self::SCHEME_SERVICE . 'command_db';

    /* Alias DB File Service */
    const URI_SERVICE_ALIAS_DB            = self::SCHEME_SERVICE . 'alias_db';

    /* Command Descriptor Service */
    const URI_SERVICE_COMMAND_DESCRIPTOR  = self::SCHEME_SERVICE . 'command_descriptor';

    /* Command Execute Service */
    const URI_SERVICE_COMMAND_EXEC        = self::SCHEME_SERVICE . 'command_exec';

}
<?php
declare(strict_types=1);

namespace knotlib\command\service;

use stk2k\string\StringUtil;

use knotlib\kernel\di\DiContainerInterface;
use knotlib\kernel\kernel\ApplicationInterface;

class CommandBaseService
{
    /** @var DiContainerInterface */
    private $di;

    /** @var ApplicationInterface */
    private $app;

    /**
     * CommandBaseService constructor.
     *
     * @param DiContainerInterface $di
     * @param ApplicationInterface $app
     */
    public function __construct(DiContainerInterface $di, ApplicationInterface $app)
    {
        $this->di = $di;
        $this->app = $app;
    }

    /**
     * @return DiContainerInterface
     */
    public function getDi() : DiContainerInterface
    {
        return $this->di;
    }

    /**
     * @return ApplicationInterface
     */
    public function getApp() : ApplicationInterface
    {
        return $this->app;
    }

    /**
     * failure output
     *
     * @param string|null $format
     */
    public function fail(string $format = null, ... $values)
    {
        $logger = $this->di[DI::URI_SERVICE_LOGGER];
        $io = $this->di[DI::URI_COMPONENT_CONSOLE_IO];

        $msg = $format ? 'failed: ' . StringUtil::formatArray($format, $values) : 'failed.';
        $logger->error( $msg );
        $io->output($msg)->eol();
        exit;
    }

    /**
     * success output
     *
     * @param string|null $format
     */
    public function success(string $format = null, ... $values)
    {
        $logger = $this->di[DI::URI_SERVICE_LOGGER];
        $io = $this->di[DI::URI_COMPONENT_CONSOLE_IO];

        $msg = $format ? 'success: ' . StringUtil::formatArray($format, $values) : 'success.';
        $logger->info( $msg );
        $io->output($msg)->eol();
        exit;
    }
}
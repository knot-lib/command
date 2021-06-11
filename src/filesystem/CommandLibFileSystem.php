<?php
declare(strict_types=1);

namespace knotlib\command\filesystem;

use knotlib\kernel\filesystem\Dir;
use knotlib\kernel\filesystem\FileSystemInterface;
use knotlib\kernel\filesystem\AbstractFileSystem;

final class CommandLibFileSystem extends AbstractFileSystem implements FileSystemInterface
{
    /** @var array */
    private $dir_map;

    /**
     * CommandFileSystem constructor.
     *
     * @throws
     */
    public function __construct()
    {
        $base_dir = dirname(__DIR__, 2);

        $this->dir_map = [
            Dir::TEMPLATE => $base_dir . '/template',
        ];
    }

    /**
     * Get directory path
     *
     * @param string $dir
     *
     * @return string
     *
     * @throws
     */
    public function getDirectory(string $dir) : string
    {
        return $this->dir_map[$dir] ?? parent::getDirectory($dir);
    }
}
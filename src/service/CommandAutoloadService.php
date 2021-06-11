<?php
declare(strict_types=1);

namespace knotlib\command\service;

use knotlib\kernel\di\DiContainerInterface;
use knotlib\kernel\kernel\ApplicationInterface;
use stk2k\filesystem\File;
use stk2k\filesystem\FileSystem;

use knotlib\kernel\FileSystem\Dir;

use knotlib\command\filesystem\CommandLibFileSystem;

final class CommandAutoloadService extends CommandBaseService
{
    const FILENAME_COMMAND_AUTOLOAD_TPL    = 'command_autoload.tpl.php';
    const FILENAME_COMMAND_AUTOLOAD_CACHE  = 'command_autoload.cache.php';

    /** @var string */
    private $autoload_tpl_file;

    /** @var string */
    private $autoload_cache_file;

    /**
     * CommandClassMapCacheService constructor.
     *
     * @param DiContainerInterface $di
     * @param ApplicationInterface $app
     *
     * @throws
     */
    public function __construct(DiContainerInterface $di, ApplicationInterface $app)
    {
        parent::__construct($di, $app);

        $fs = $this->getDi()->get(DI::URI_SERVICE_FILESYSTEM);

        $this->autoload_tpl_file = (new CommandLibFileSystem())->getFile(Dir::TEMPLATE, self::FILENAME_COMMAND_AUTOLOAD_TPL);

        $this->autoload_cache_file = $fs->getFile(Dir::CACHE, self::FILENAME_COMMAND_AUTOLOAD_CACHE);
    }

    /**
     * Generate command class map cache for autoloading
     *
     * @return string
     *
     * @throws
     */
    public function generateAutoloadFile() : string
    {
        if (!is_readable($this->autoload_tpl_file)){
            parent::fail('Autoload template file not found: {0}', $this->autoload_tpl_file);
        }

        $cmd_db = $this->getDi()->get(DI::URI_SERVICE_COMMAND_DB);

        $this->$cmd_db->load();

        extract([
            'command_db' => $cmd_db->getCommandDb()
        ]);

        ob_start();
        /** @noinspection PhpIncludeInspection */
        require $this->autoload_tpl_file;
        $contents = ob_get_clean();

        $php_code = '<?php' . PHP_EOL . $contents;

        (new File($this->autoload_cache_file))->getParent()->mkdir();

        $ret = file_put_contents($this->autoload_cache_file, $php_code);
        if ($ret === false){
            parent::fail('Failed to save autoload cache file: {0}', $this->autoload_cache_file);
        }

        return $this->autoload_cache_file;
    }

    /**
     * Load command autoload
     *
     */
    public function autoload()
    {
        if (!FileSystem::exists($this->autoload_cache_file)){
            $this->generateAutoloadFile();
        }

        /** @noinspection PhpIncludeInspection */
        require $this->autoload_cache_file;
    }
}
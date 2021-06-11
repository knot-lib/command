<?php
declare(strict_types=1);

namespace knotlib\command\service;

use knotlib\kernel\di\DiContainerInterface;
use knotlib\kernel\kernel\ApplicationInterface;
use stk2k\filesystem\File;
use stk2k\filesystem\Exception\MakeDirectoryException;

use knotlib\kernel\filesystem\Dir;

final class AliasDbService extends CommandBaseService
{
    const FILENAME_ALIAS_DB = 'alias_db.json';

    /** @var string */
    private $alias_db_file;

    /** @var array */
    private $alias_db;

    /**
     * AliasDbFileService constructor.
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

        $this->alias_db_file = $fs->getFile(Dir::DATA, self::FILENAME_ALIAS_DB);
        $this->alias_db = [];
    }

    /**
     * @return array
     */
    public function getAliasDB() : array
    {
        return $this->alias_db;
    }

    /**
     * Returns whether alias exists
     *
     * @param string $alias
     *
     * @return bool
     */
    public function isAlias(string $alias) : bool
    {
        return isset($this->alias_db[$alias]);
    }

    /**
     * Get command path
     *
     * @param string $alias
     *
     * @return string
     */
    public function getCommandPath(string $alias) : string
    {
        return $this->alias_db[$alias] ?? '';
    }

    /**
     * Set alias
     *
     * @param string $command_path
     * @param string $alias
     */
    public function setAlias(string $command_path, string $alias)
    {
        $this->alias_db[$alias] = $command_path;
    }

    /**
     * Import alias from command DB
     *
     * @param CommandDbService $db_file
     */
    public function importAlias(CommandDbService $db_file)
    {
        $this->alias_db = [];
        foreach($db_file->getCommandDb() as $item){
            $cmd_path = $item->getCommandPath();
            foreach($item->getAliases() as $alias){
                $this->alias_db[$alias] = $cmd_path;
            }
        }
    }

    /**
     * Load command alias DB file
     *
     * @return void
     */
    public function load() : void
    {
        $this->alias_db = [];

        if (!file_exists($this->alias_db_file)){
            parent::fail('Alias DB file not found: {0}', $this->alias_db_file);
        }

        $contents = file_get_contents($this->alias_db_file);
        if (empty($contents)){
            parent::fail('Alias DB file is empty: {0}', $this->alias_db_file);
        }

        $alias_db = json_decode($contents, true);
        if (!is_array($alias_db)){
            parent::fail('Alias DB file is not JSON: {0}', $this->alias_db_file);
        }

        $this->alias_db = $alias_db;
    }

    /**
     * Generate command alias DB file
     */
    public function save() : void
    {
        $contents = json_encode($this->alias_db, JSON_PRETTY_PRINT);

        $alias_dir = (new File($this->alias_db_file))->getParent();
        try{
            $alias_dir->mkdir();
        }
        catch(MakeDirectoryException $ex)
        {
            parent::fail('Failed to make descriptor directory({0}) : {1}', $alias_dir->getPath(), $ex->getMessage());
        }

        $ret = file_put_contents($this->alias_db_file, $contents);
        if ($ret === false){
            parent::fail('Failed to save alias DB file: {0}', $this->alias_db_file);
        }
    }
}
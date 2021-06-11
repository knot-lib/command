<?php
declare(strict_types=1);

namespace knotlib\command\service;

use knotlib\kernel\di\DiContainerInterface;
use knotlib\kernel\kernel\ApplicationInterface;
use stk2k\filesystem\File;
use stk2k\filesystem\FileSystem;

use knotlib\command\CommandDescriptor;
use knotlib\kernel\filesystem\Dir;

final class CommandDbService extends CommandBaseService
{
    const FILENAME_COMMAND_DB    = 'command_db.json';

    /** @var string */
    private $command_db_file;

    /** @var CommandDescriptor[] */
    private $command_db;

    /**
     * CommandInstallService constructor.
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

        $this->command_db_file = $fs->getFile(Dir::DATA, self::FILENAME_COMMAND_DB);
        $this->command_db = [];
    }

    /**
     * @return string
     */
    public function getCommandDbFile() : string
    {
        return $this->command_db_file;
    }

    /**
     * @return CommandDescriptor[]
     */
    public function getCommandDb() : array
    {
        return $this->command_db;
    }

    /**
     * Returns decriptor
     *
     * @param string $command_path
     *
     * @return CommandDescriptor|null
     */
    public function getDesciptor(string $command_path): ?CommandDescriptor
    {
        return $this->command_db[$command_path] ?? null;
    }

    /**
     * Set descriptor
     *
     * @param string $command_path
     * @param CommandDescriptor $descriptor
     */
    public function setDesciptor(string $command_path, CommandDescriptor $descriptor)
    {
        $this->command_db[$command_path] = $descriptor;
    }

    /**
     * Load command DB
     *
     * @return void
     */
    public function load() : void
    {
        $this->command_db = [];

        if (!FileSystem::exists($this->command_db_file)){
            return;
        }
        $contents = file_get_contents($this->command_db_file);
        if (empty($contents)){
            return;
        }
        $descriptor_list = json_decode($contents, true);
        if (!is_array($descriptor_list)){
            return;
        }

        $command_db = [];

        foreach($descriptor_list as $descriptor){
            $command_path       = $descriptor['command_path'] ?? '';
            $aliases            = $descriptor['aliases'] ?? [];
            $class_root         = $descriptor['class_root'] ?? '';             // i.e: /root/to/my/command
            $class_name         = $descriptor['class_name'] ?? '';             // i.e: MyPackage.MyCommand
            $name_space         = $descriptor['name_space'] ?? '';             // i.e: MyPackage
            $required_modules   = $descriptor['required_modules'] ?? [];
            $ordered_args       = $descriptor['args']['ordered'] ?? [];
            $named_args         = $descriptor['args']['named'] ?? [];
            $command_helps      = $descriptor['command_helps'] ?? '';

            $command_db[$command_path] = (new CommandDescriptor)
                ->setCommandPath($command_path)
                ->setAliases($aliases)
                ->setClassRoot($class_root)
                ->setClassName($class_name)
                ->setNameSpace($name_space)
                ->setRequiredModules($required_modules)
                ->setOrderdArgs($ordered_args)
                ->setNamedArgs($named_args)
                ->setCommandHelps($command_helps);
        }

        $this->command_db = $command_db;
    }

    /**
     * Save command DB
     *
     * @throws
     */
    public function save()
    {
        $contents = json_encode($this->command_db, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);

        (new File($this->command_db_file))->getParent()->mkdir();

        $ret = file_put_contents($this->command_db_file, $contents);
        if ($ret === false){
            parent::fail('Failed to save command DB file: {0}', $this->command_db);
        }
    }


}
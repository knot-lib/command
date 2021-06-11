<?php
declare(strict_types=1);

namespace knotlib\command\service;

use knotlib\command\CommandDescriptor;
use knotlib\kernel\di\DiContainerInterface;
use knotlib\kernel\filesystem\Dir;
use knotlib\kernel\kernel\ApplicationInterface;

use knotlib\command\CommandDescriptorProviderInterface;
use knotlib\command\util\VirtualClassNameTrait;

final class CommandInstallerService extends CommandBaseService
{
    use VirtualClassNameTrait;

    const COMMAND_DESCRIPTOR_SUFFIX        = '.command.json';

    const CONSOLE_SEPARATOR = '-----------------------------------------------------------------------------------------';

    /** @var CommandDescriptorService */
    private $descriptor;

    /** @var CommandDbService */
    private $cmd_db;

    /** @var AliasDbService */
    private $alias_db;

    /**
     * CommandInstallerService constructor.
     *
     * @param DiContainerInterface $di
     * @param ApplicationInterface $app
     */
    public function __construct(DiContainerInterface $di, ApplicationInterface $app)
    {
        parent::__construct($di, $app);
    }

    /**
     * Make commands
     *
     * @param string $provider_class
     */
    public function make(string $provider_class) : void
    {
        $di= $this->getDi();
        $io = $di[DI::URI_COMPONENT_CONSOLE_IO];

        $provider_class = self::virtualClassToReal($provider_class);

        if (!class_exists($provider_class)){
            parent::fail('Provider class not found: {0}', $provider_class);
        }
        if (!in_array(CommandDescriptorProviderInterface::class, class_implements($provider_class))){
            parent::fail('Provider class must implement CommandDescriptorProviderInterface: {0}', $provider_class);
        }

        $io->output(self::CONSOLE_SEPARATOR)->eol();

        $descriptor_list = forward_static_call([$provider_class, 'provide']);

        $total = 0;
        foreach($descriptor_list as $descriptor){
            $descriptor_file = $this->descriptor->generateCommandDescriptorFile($descriptor);

            $io->output('Generated descriptor: [{0}]', basename($descriptor_file))->eol();
            $total ++;
        }

        $io->output(self::CONSOLE_SEPARATOR)->eol();

        $io->output('Made {0:d} command descriptor(s).', $total)->eol();
    }

    /**
     * Install commands
     *
     * @param string $command_path
     */
    public function install(string $command_path) : void
    {
        $di= $this->getDi();
        $io = $di[DI::URI_COMPONENT_CONSOLE_IO];
        $fs = $di[DI::URI_SERVICE_FILESYSTEM];

        $io->output(self::CONSOLE_SEPARATOR)->eol();

        $this->cmd_db->load();

        $total = 0;
        if ($command_path === 'all' || empty($command_id)){
            // read all descriptor files
            $command_dir = $fs->getFile(Dir::COMMAND, '*' . self::COMMAND_DESCRIPTOR_SUFFIX);
            foreach(glob($command_dir) as $descriptor_file){
                $descriptor = $this->descriptor->readCommandDescriptor($descriptor_file);

                $this->cmd_db->setDesciptor($descriptor->getCommandPath(), $descriptor);

                $io->output('Command installed: [{0}]', $descriptor->getCommandPath())->eol();
                $total ++;
            }
        }
        else{
            $descriptor_file = $this->descriptor->commandPathToDescriptorFile($command_id);

            $descriptor = $this->descriptor->readCommandDescriptor($descriptor_file);

            $this->cmd_db->setDesciptor($descriptor->getCommandPath(), $descriptor);

            $io->output('Command installed: [{0}]', $descriptor->getCommandPath())->eol();
            $total ++;
        }

        $this->cmd_db->save();

        $this->alias_db->importAlias($this->cmd_db);
        $this->alias_db->save();

        $io->output(self::CONSOLE_SEPARATOR)->eol();

        $io->output(sprintf('Saved %d commands into database.', $total))->eol();
    }

    /**
     * List commands
     *
     * @param string $command_path
     */
    public function listCommands(string $command_path) : void
    {
        $di= $this->getDi();
        $io = $di[DI::URI_COMPONENT_CONSOLE_IO];
        $fs = $di[DI::URI_SERVICE_FILESYSTEM];

        $total = 0;

        $io->output(str_pad('ID', 25))->eol();

        $io->output(self::CONSOLE_SEPARATOR)->eol();

        // read all descriptor files
        $command_dir = $fs->getFile(Dir::COMMAND, '*' . self::COMMAND_DESCRIPTOR_SUFFIX);
        foreach(glob($command_dir) as $descriptor_file){
            $descriptor = $this->descriptor->readCommandDescriptor($descriptor_file);

            $io->output($descriptor->getCommandPath())->eol();
            $total ++;
        }

        $io->output(self::CONSOLE_SEPARATOR)->eol();

        $io->output(sprintf('%d commands found in database.', $total))->eol();
    }

    /**
     * Show command help
     *
     * @param string $command_path
     */
    public function showCommandHelps(string $command_path) : void
    {
        $di= $this->getDi();
        $io = $di[DI::URI_COMPONENT_CONSOLE_IO];
        $fs = $di[DI::URI_SERVICE_FILESYSTEM];

        $io->output(str_pad('ID', 25) . 'COMMAND LINE')->eol();

        $io->output(self::CONSOLE_SEPARATOR)->eol();

        if ($command_path === 'all' || empty($command_id)){
            // read all descriptor files
            $command_dir = $fs->getFile(Dir::COMMAND, '*' . self::COMMAND_DESCRIPTOR_SUFFIX);
            $files = glob($command_dir);
            foreach($files as $key => $descriptor_file){
                $desc = $this->descriptor->readCommandDescriptor($descriptor_file);
                $this->showCommandHelp($desc, $key < count($files) - 1);
            }
        }
        else{
            $descriptor_file = $this->descriptor->commandPathToDescriptorFile($command_id);
            $desc = $this->descriptor->readCommandDescriptor($descriptor_file);
            $this->showCommandHelp($desc, false);
        }

        $io->output(self::CONSOLE_SEPARATOR)->eol();
    }

    private function showCommandHelp(CommandDescriptor $desc, bool $show_separatpr)
    {
        $di= $this->getDi();
        $io = $di[DI::URI_COMPONENT_CONSOLE_IO];

        $command_helps = $desc->getCommandHelps();

        if (is_array($command_helps)){
            foreach($command_helps as $key => $help){
                $line = $key === 0 ? str_pad($desc->getCommandPath(), 25) . $help : str_repeat(' ', 25) . $help;
                $io->output($line)->eol();
            }
            if ($show_separatpr){
                $io->output(self::CONSOLE_SEPARATOR)->eol();
            }
        }
        else if (is_string($command_helps)){
            $line = str_pad($desc->getCommandPath(), 25) . $command_helps;
            $io->output($line)->eol();
        }
    }

    /**
     * Generate autoload cahe
     *
     * @param CommandAutoloadService $autoload_s
     */
    public function generateAutoloadCache(CommandAutoloadService $autoload_s)
    {
        $di= $this->getDi();
        $io = $di[DI::URI_COMPONENT_CONSOLE_IO];

        $autoload_file = $autoload_s->generateAutoloadFile();

        $io->output('Generated autoload cache: ' . $autoload_file)->eol();
    }


    /**
     * Execute command
     *
     * @param string $command_path
     * @param int $skip_args
     */
    public function execute(string $command_path, int $skip_args)
    {
        $di= $this->getDi();
        $exec = $di[DI::URI_SERVICE_COMMAND_EXEC];
        $autoload = $di[DI::URI_SERVICE_COMMAND_AUTOLOAD];

        $autoload->autoload();
        $exec->executeCommand($di, $command_path, $skip_args);
    }
}
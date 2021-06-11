<?php /** @noinspection DuplicatedCode */
declare(strict_types=1);

namespace knotlib\command\service;

use knotlib\kernel\kernel\ApplicationInterface;
use stk2k\filesystem\Exception\MakeDirectoryException;
use stk2k\filesystem\File;

use knotlib\kernel\filesystem\Dir;
use knotlib\kernel\di\DiContainerInterface;
use knotlib\command\CommandDescriptor;
use knotlib\command\util\VirtualClassNameTrait;

class CommandDescriptorService extends CommandBaseService
{
    use VirtualClassNameTrait;

    const FILENAME_DESCRIPTOR_TPL = 'command_descriptor.tpl.php';
    const COMMAND_DESCRIPTOR_SUFFIX        = '.command.json';

    /** @var string */
    private $descriptor_template_file;

    /**
     * CommandSpecService constructor.
     *
     * @param DiContainerInterface $di
     * @param ApplicationInterface $app
     */
    public function __construct(DiContainerInterface $di, ApplicationInterface $app)
    {
        parent::__construct($di, $app);

        $fs = $this->getDi()->get(DI::URI_SERVICE_FILESYSTEM);

        $this->descriptor_template_file = $fs->getFile(Dir::TEMPLATE, self::FILENAME_DESCRIPTOR_TPL);
    }

    /**
     * Returns command descriptor path from command id
     *
     * @param string $command_path
     *
     * @return string
     *
     * @throws
     */
    public function commandPathToDescriptorFile(string $command_path) : string
    {
        $filename_base = str_replace(':', '_', $command_path);

        $fs = $this->getDi()->get(DI::URI_SERVICE_FILESYSTEM);

        return $fs->getFile(Dir::COMMAND, $filename_base . self::COMMAND_DESCRIPTOR_SUFFIX);
    }

    /**
     * Generate command descriptor
     *
     * @param CommandDescriptor $desc
     *
     * @return string
     *
     * @throws
     */
    public function generateCommandDescriptorFile(CommandDescriptor $desc) : string
    {
        $descriptor_file = $this->commandPathToDescriptorFile($desc->getCommandPath());

        $desc_dir = (new File($descriptor_file))->getParent();
        try{
            $desc_dir->mkdir();
        }
        catch(MakeDirectoryException $ex)
        {
            parent::fail('Failed to make descriptor directory({0}) : {1}', $desc_dir->getPath(), $ex->getMessage());
        }

        ob_start();
        /** @noinspection PhpIncludeInspection */
        require $this->descriptor_template_file;
        $contents = ob_get_clean();

        $ret = file_put_contents($descriptor_file, $contents);
        if ($ret === false){
            parent::fail('Failed to save descriptor file: {0}', $descriptor_file);
        }

        return $descriptor_file;
    }

    /**
     * Validate command dscriptor
     *
     * @param array $descriptor
     * @param string $descriptor_file
     */
    private function validateCommandDescriptor(array $descriptor, string $descriptor_file)
    {
        $command_path        = $descriptor['command_path'] ?? '';
        $class_root          = $descriptor['class_root'] ?? '';             // i.e: /root/to/my/command
        $class_name          = $descriptor['class_name'] ?? '';             // i.e: MyPackage.MyCommand
        $required            = $descriptor['required'] ?? [];
        $command_help        = $descriptor['command_help'] ?? [];

        if (empty($command_path)){
            parent::fail('Command path is not set. Specify "command_path" in descriptor. file={0}', $descriptor_file);
        }
        if (empty($class_root) || !file_exists($class_root) || !is_dir($class_root)){
            parent::fail('Class root not found. file={0}', $descriptor_file);
        }
        if (empty($class_name) || !self::classExists($class_name)){
            parent::fail('Class not found({0}). file={1}', $class_name, $descriptor_file);
        }
        if (is_array($required)){
            foreach($required as $module){
                if (!self::classExists($module)){
                    parent::fail('Module not found({0}). file={1}', $module, $descriptor_file);
                }
            }
        }
        if (empty($command_help)){
            parent::fail('Command help is mandatory({0}). file={1}', $descriptor_file);
        }
    }

    /**
     * Read command descriptor file
     *
     * @param string $descriptor_file
     *
     * @return CommandDescriptor
     */
    public function readCommandDescriptor(string $descriptor_file) : CommandDescriptor
    {
        if (!is_readable($descriptor_file)){
            parent::fail('Command descriptor is not readable. file={1}', $descriptor_file);
        }

        $descriptor = json_decode(file_get_contents($descriptor_file), true);

        if (!is_array($descriptor)){
            parent::fail('Top level of command spec must be an array. file={1}', $descriptor_file);
        }

        self::validateCommandDescriptor($descriptor, $descriptor_file);

        $command_path = $descriptor['command_path'] ?? '';
        $aliases = $descriptor['aliases'] ?? [];
        $class_root = $descriptor['class_root'] ?? '';
        $class_name = $descriptor['class_name'] ?? '';
        $name_space = $descriptor['name_space'] ?? '';
        $required_modules = $descriptor['required_modules'] ?? [];
        $ordered_args = $descriptor['ordered_args'] ?? [];
        $named_args = $descriptor['named_args'] ?? [];
        $command_helps = $descriptor['command_helps'] ?? [];

        return (new CommandDescriptor)
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

}
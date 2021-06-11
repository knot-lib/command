<?php
declare(strict_types=1);

namespace knotlib\command;

use JsonSerializable;

use knotlib\command\util\VirtualClassNameTrait;

class CommandDescriptor implements JsonSerializable
{
    use VirtualClassNameTrait;

    const SPECKEY_COMMAND_PATH      = 'command_path';
    const SPECKEY_ALIASES           = 'aliases';
    const SPECKEY_CLASS_ROOT        = 'class_root';
    const SPECKEY_CLASS_NAME        = 'class_name';
    const SPECKEY_NAME_SPACE        = 'name_space';
    const SPECKEY_REQUIRED_MODULES  = 'required_modules';
    const SPECKEY_ORDERED_ARGS      = 'ordered_args';
    const SPECKEY_NAMED_ARGS        = 'named_args';
    const SPECKEY_COMMAND_HELPS     = 'command_helps';

    /** @var string */
    private $command_path;

    /** @var string[] */
    private $aliases;

    /** @var string */
    private $class_root;

    /** @var string */
    private $class_name;

    /** @var string */
    private $name_space;

    /** @var array */
    private $required_modules;

    /** @var array */
    private $ordered_args;

    /** @var array */
    private $named_args;

    /** @var string[] */
    private $command_helps;

    /**
     * CommandSpec constructor.
     *
     * @param array $command_spec
     */
    public function __construct(array $command_spec = [])
    {
        $this->command_path        = $command_spec[self::SPECKEY_COMMAND_PATH] ?? '';
        $this->aliases             = $command_spec[self::SPECKEY_ALIASES] ?? [];
        $this->class_root          = $command_spec[self::SPECKEY_CLASS_ROOT] ?? '';
        $this->class_name          = $command_spec[self::SPECKEY_CLASS_NAME] ?? '';
        $this->name_space          = $command_spec[self::SPECKEY_NAME_SPACE] ?? '';
        $this->required_modules    = $command_spec[self::SPECKEY_REQUIRED_MODULES] ?? [];
        $this->ordered_args        = $command_spec[self::SPECKEY_ORDERED_ARGS] ?? [];
        $this->named_args          = $command_spec[self::SPECKEY_NAMED_ARGS] ?? [];
        $this->command_helps       = $command_spec[self::SPECKEY_COMMAND_HELPS] ?? [];
    }

    /**
     * Get command path(ex: 'password:encrypt')
     *
     * @return string
     */
    public function getCommandPath() : string
    {
        return $this->command_path;
    }

    /**
     * Set command name(ex: 'password:encrypt')
     *
     * @param string $command_path
     *
     * @return self
     */
    public function setCommandPath(string $command_path) : self
    {
        $this->command_path = $command_path;
        return $this;
    }

    /**
     * Get alias names(ex: ['pass:enc', 'pe'])
     *
     * @return string[]
     */
    public function getAliases() : array
    {
        return $this->aliases;
    }

    /**
     * Set alias names(ex: ['pass:enc', 'pe'])
     *
     * @param array $aliases
     *
     * @return self
     */
    public function setAliases(array $aliases) : self
    {
        $this->aliases = $aliases;
        return $this;
    }

    /**
     * Get class name(ex: 'knotphp.command.command.acme.PasswordEncryptComand')
     *
     * @return string
     */
    public function getClassName() : string
    {
        return self::virtualClassToReal($this->class_name);
    }

    /**
     * Set class name(ex: 'knotphp.command.command.acme.PasswordEncryptComand')
     *
     * @param string $class_name
     *
     * @return self
     */
    public function setClassName(string $class_name) : self
    {
        $this->class_name = $class_name;
        return $this;
    }

    /**
     * Get class root(ex: '/path/to/command')
     *
     * @return string
     */
    public function getClassRoot() : string
    {
        return $this->class_root;
    }

    /**
     * Set class root(ex: '/path/to/command')
     *
     * @param string $class_root
     *
     * @return self
     */
    public function setClassRoot(string $class_root) : self
    {
        $this->class_root = $class_root;
        return $this;
    }

    /**
     * Get name space(ex: 'KnotPhp.Command')
     *
     * @return string
     */
    public function getNameSpace() : string
    {
        return self::virtualClassToReal($this->name_space);
    }

    /**
     * Set name space(ex: 'KnotPhp.Command')
     *
     * @param string $name_space
     *
     * @return self
     */
    public function setNameSpace(string $name_space) : self
    {
        $this->name_space = $name_space;
        return $this;
    }

    /**
     * Get required modules(ex: [MyModuleA::class, MyModuleB::class] )
     *
     * @return array
     */
    public function getRequiredModules() : array
    {
        return array_map(function($item){
            return self::virtualClassToReal($item);
        }, $this->required_modules);
    }

    /**
     * Set required modules(ex: [MyModuleA::class, MyModuleB::class] )
     *
     * @param array $required_modules
     *
     * @return self
     */
    public function setRequiredModules(array $required_modules) : self
    {
        $this->required_modules = $required_modules;
        return $this;
    }

    /**
     * Get ordered arguments(ex: ['text', 'algorythm'] )
     *
     * @return array
     */
    public function getOrderdArgs() : array
    {
        return $this->ordered_args;
    }

    /**
     * Set ordered arguments(ex: ['text', 'algorythm'] )
     *
     * @param array $ordered_args
     *
     * @return self
     */
    public function setOrderdArgs(array $ordered_args) : self
    {
        $this->ordered_args = $ordered_args;
        return $this;
    }

    /**
     * Get named arguments(ex: ['--app' => 'app', '-a' => 'app'] )
     *
     * @return array
     */
    public function getNamedArgs() : array
    {
        return $this->named_args;
    }

    /**
     * Set named arguments(ex: ['--app' => 'app', '-a' => 'app'] )
     *
     * @param array $named_args
     *
     * @return self
     */
    public function setNamedArgs(array $named_args) : self
    {
        $this->named_args = $named_args;
        return $this;
    }

    /**
     * Get command helps(ex: ['knot db:generate:model table [-a|--app app]', 'knot db:gen:model table [-a|--app app]'] )
     *
     * @return string[]
     */
    public function getCommandHelps() : array
    {
        return $this->command_helps;
    }

    /**
     * Set command helps(ex: ['knot db:generate:model table [-a|--app app]', 'knot db:gen:model table [-a|--app app]'] )
     *
     * @param array $command_helps
     *
     * @return self
     */
    public function setCommandHelps(array $command_helps) : self
    {
        $this->command_helps = $command_helps;
        return $this;
    }

    /**
     * @return array
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function jsonSerialize()
    {
        return [
            'command_path' => $this->command_path,
            'aliases' => $this->aliases,
            'class_root' => $this->class_root,
            'class_name' => self::realClassToVirtual($this->class_name),
            'name_space' => self::realClassToVirtual($this->name_space),
            'required_modules' => array_map(function($item){
                    return self::realClassToVirtual($item);
                }, $this->required_modules),
            'args' => [
                'ordered' => $this->ordered_args,
                'named' => $this->named_args,
            ],
            'command_helps' => $this->command_helps,
        ];
    }


}
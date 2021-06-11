<?php
declare(strict_types=1);

namespace knotlib\command\service;

use knotlib\kernel\kernel\ApplicationInterface;
use Throwable;

use knotlib\kernel\di\DiContainerInterface;
use knotlib\command\CommandInterface;

final class CommandExecService extends CommandBaseService
{
    /**
     * CommandExecService constructor.
     *
     * @param DiContainerInterface $di
     * @param ApplicationInterface $app
     */
    public function __construct(DiContainerInterface $di, ApplicationInterface $app)
    {
        parent::__construct($di, $app);
    }

    /**
     * Execute command
     *
     * @param string $command_path
     * @param int $skip_args
     *
     * @return int
     */
    public function executeCommand(string $command_path, int $skip_args) : int
    {
        $di = $this->getDi();
        $io = $di[DI::URI_COMPONENT_CONSOLE_IO];
        $command_db = $di[DI::URI_SERVICE_COMMAND_DB];
        $alias_db = $di[DI::URI_SERVICE_ALIAS_DB];

        $command_db->load();
        $alias_db->load();

        // if alias is specified, expand it
        if ($alias_db->isAlias($command_path)){
            $command_path = $alias_db->getCommandPath($command_path);
        }

        // get descriptor from DB
        $descriptor = $command_db->getDesciptor($command_path);

        if (!$descriptor){
            parent::fail('Command not found: {0}', $command_path);
        }

        // create command object
        $class_name = $descriptor->getClassName();
        $ordered_args = $descriptor->getOrderdArgs();
        $named_args = $descriptor->getNamedArgs();
        $required_modules = $descriptor->getRequiredModules();

        if (!class_exists($class_name)){
            parent::fail('Command class not exists: {0}', $class_name);
        }

        if (!in_array(CommandInterface::class, class_implements($class_name))){
            parent::fail('Command class must implement CommandInterface: {0}', $class_name);
        }

        /** @var CommandInterface $command_obj */
        $command_obj = new $class_name($di);

        // install modules required by command
        foreach($required_modules as $module){
            $this->getApp()->installModule($module);
            $io->output("Installed required module: $module")->eol();
        }

        // execute command
        $ret = -1;
        try{
            $combined_args = $this->getArgs($ordered_args, $named_args, $skip_args);

            $io->output("Executing command($command_path)")->eol();
            $ret = $command_obj->execute($combined_args, $io);
        }
        catch(Throwable $ex)
        {
            if (!$command_obj->handleException($ex, $io)){
                parent::fail('Failed to execute command({0}): {1}', $command_path, $ex->getMessage());
            }
        }

        $io->output("Command({0}) finished with exit code {1:d}}", $command_path, $ret)->eol();

        return $ret;
    }

    /**
     * @param array $ordered_args
     * @param array $named_args
     * @param int $skip_args
     *
     * @return array
     */
    private function getArgs(array $ordered_args, array $named_args, int $skip_args) : array
    {
        $request = $this->getApp()->request();
        $seq_params = array_filter($request->getServerParams(), function($value, $key){
            return is_int($key);
        }, ARRAY_FILTER_USE_BOTH);
        $named_params = array_filter($request->getServerParams(), function($value, $key){
            return is_string($key);
        }, ARRAY_FILTER_USE_BOTH);

        $args = [];

        foreach($ordered_args as $idx => $key){
            if (isset($seq_params[$skip_args + $idx])){
                $args[$key] = $seq_params[$skip_args + $idx];
            }
        }

        foreach($named_args as $spec => $key){
            if (isset($named_params[$spec])){
                $args[$key] = $named_params[$spec];
            }
        }

        return $args;
    }
}
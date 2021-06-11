<?php
declare(strict_types=1);

use knotlib\command\CommandDescriptor;

/** @var CommandDescriptor $desc */

$class_root = $desc->getClassRoot();
$class_root = str_replace('\\', '/', $class_root);

$class_name = $desc->getClassName();
$class_name = str_replace('\\', '.', $class_name);

$name_space = $desc->getNameSpace();
$name_space = str_replace('\\', '.', $name_space);

$required_modules = $desc->getRequiredModules();
foreach($required_modules as $key => $module){
    $module = str_replace('\\', '.', $module);
    $required_modules[$key] = $module;
}

$data = [
    'command_path' => $desc->getCommandHelps(),
    'aliases' => $desc->getAliases(),
    'class_root' => $class_root,
    'class_name' => $class_name,
    'name_space' => $name_space,
    'required' => $required_modules,
    'ordered_args'  => $desc->getOrderdArgs(),
    'named_args' => $desc->getNamedArgs(),
    'command_helps' => $desc->getCommandHelps(),
];
echo json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);


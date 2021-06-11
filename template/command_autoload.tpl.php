<?php
declare(strict_types=1);

use knotlib\command\CommandDescriptor;

/** @var CommandDescriptor[] $command_db */
?>
spl_autoload_register(function($class_name)
{
<?php foreach($command_db as $descriptor) :

    $command_id = $descriptor->getCommandPath();
    $class_base = str_replace('\\', '\\\\', $descriptor->getNameSpace());
?>
    // PSR-4 autoload code of [<?php echo $command_id; ?>]
    if (strpos($class_name, '<?php echo $class_base; ?>') === 0) {
        $paths = substr($class_name, strlen('<?php echo $class_base; ?>'));
        $paths = array_filter(explode('\\',$paths));
        $file = '<?php echo $descriptor->getClassRoot(); ?>/' . implode('/',$paths) . '.php';
        require_once $file;
        return;
    }
<?php endforeach; ?>
});


<?php

class AutoLoader {
    protected static $paths = array(
        '',
        'Amazon\DynamoDB\\',
        'Amazon\DynamoDB\Context\\',
        'Amazon\DynamoDB\Aws\\'
    );

    public static function addPath($path)
    {
        $path = realpath($path);
        if( $path )
        {
            self::$paths[] = $path;
        }
    }

    public static function load($class)
    {
        $classPath = $class.".php";

        foreach (self::$paths as $path)
        {
            if (is_file($path . $classPath))
            {
                require_once $path . $classPath;
                return;
            }
        }
    }
}

spl_autoload_register(array('AutoLoader', 'load'));

?>

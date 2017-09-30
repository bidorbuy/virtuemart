<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit8b33650adadfc421a525964f9e69a3c2
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit8b33650adadfc421a525964f9e69a3c2', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader();
        spl_autoload_unregister(array('ComposerAutoloaderInit8b33650adadfc421a525964f9e69a3c2', 'loadClassLoader'));

        $useStaticLoader = PHP_VERSION_ID >= 50600 && !defined('HHVM_VERSION') && (!function_exists('zend_loader_file_encoded') || !zend_loader_file_encoded());
        if ($useStaticLoader) {
            require_once __DIR__ . '/autoload_static.php';

            call_user_func(\Composer\Autoload\ComposerStaticInit8b33650adadfc421a525964f9e69a3c2::getInitializer($loader));
        } else {
            $classMap = require __DIR__ . '/autoload_classmap.php';
            if ($classMap) {
                $loader->addClassMap($classMap);
            }
        }

        $loader->setClassMapAuthoritative(true);
        if (method_exists($loader, 'setApcuPrefix')) { $loader->setApcuPrefix('dmDloEZYsrwx5EfgU/BVe'); }
        $loader->register(true);

        return $loader;
    }
}

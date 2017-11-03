<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit130328a3892ee876cba987ba7e36912f
{
    public static $files = array (
        '5f6ea78188a74ae6f96fa6029143ab5a' => __DIR__ . '/..' . '/servo/fluidxml/source/FluidXml/fluid.php',
    );

    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\SimpleCache\\' => 16,
            'PhpOffice\\PhpSpreadsheet\\' => 25,
        ),
        'F' => 
        array (
            'FluidXml\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\SimpleCache\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/simple-cache/src',
        ),
        'PhpOffice\\PhpSpreadsheet\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet',
        ),
        'FluidXml\\' => 
        array (
            0 => __DIR__ . '/..' . '/servo/fluidxml/source/FluidXml',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit130328a3892ee876cba987ba7e36912f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit130328a3892ee876cba987ba7e36912f::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
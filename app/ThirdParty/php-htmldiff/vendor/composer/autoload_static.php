<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9b2730a7ce3346347bcf15582628740f
{
    public static $files = array (
        '2cffec82183ee1cea088009cef9a6fc3' => __DIR__ . '/..' . '/ezyang/htmlpurifier/library/HTMLPurifier.composer.php',
    );

    public static $prefixesPsr0 = array (
        'H' => 
        array (
            'HTMLPurifier' => 
            array (
                0 => __DIR__ . '/..' . '/ezyang/htmlpurifier/library',
            ),
        ),
        'C' => 
        array (
            'Caxy\\HtmlDiff' => 
            array (
                0 => __DIR__ . '/..' . '/caxy/php-htmldiff/lib',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit9b2730a7ce3346347bcf15582628740f::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit9b2730a7ce3346347bcf15582628740f::$classMap;

        }, null, ClassLoader::class);
    }
}

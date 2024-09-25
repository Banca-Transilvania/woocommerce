<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2e5b1c78cf10439941eb27d7055f0102
{
    public static $prefixLengthsPsr4 = array (
        'B' => 
        array (
            'BTransilvania\\Api\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'BTransilvania\\Api\\' => 
        array (
            0 => __DIR__ . '/..' . '/banca-transilvania/ipay-sdk/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2e5b1c78cf10439941eb27d7055f0102::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2e5b1c78cf10439941eb27d7055f0102::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit2e5b1c78cf10439941eb27d7055f0102::$classMap;

        }, null, ClassLoader::class);
    }
}

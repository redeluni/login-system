<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite306e4af1ed3243182e310ec821a2277
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite306e4af1ed3243182e310ec821a2277::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite306e4af1ed3243182e310ec821a2277::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}

<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * General utility class in Swift Mailer, not to be instantiated.
 *
 *
 * @author Chris Corbyn
 */
abstract class Xxx
{
    /** Swift Mailer Version number generated during dist release process */
    const VERSION = '@SWIFT_VERSION_NUMBER@';
    
    public static $initialized = false;
    public static $inits = array();

    /**
     * Registers an initializer callable that will be called the first time
     * a SwiftMailer class is autoloaded.
     *
     * This enables you to tweak the default configuration in a lazy way.
     *
     * @param mixed $callable A valid PHP callable that will be called when autoloading the first Swift class
     */
    public static function init($callable)
    {
        self::$inits[] = $callable;
    }

    /**
     * Internal autoloader for spl_autoload_register().
     *
     * @param string $class
     */
    public static function autoload($class)
    {
        // Don't interfere with other autoloaders
        if (0 !== strpos($class, 'Xxx_')) {#以Xxx开头的类 才往下执行，否则返回
            return;
        }
		#./Xxx/abc/a.php
        $path = dirname(__FILE__).'/'.str_replace('_', '/', $class).'.php';#把类名中 _替换成目录

        if (!file_exists($path)) {
            return;
        }

        require $path;#加载文件

        if (self::$inits && !self::$initialized) {
            self::$initialized = true;#只回调一次
            foreach (self::$inits as $init) {
                call_user_func($init);#执行方法
            }
        }
    }

    /**
     * Configure autoloading using Swift Mailer.
     *
     * This is designed to play nicely with other autoloaders.
     * 注册自动加载器
     * @param mixed $callable A valid PHP callable that will be called when autoloading the first Swift class
     */
    public static function registerAutoload($callable = null)
    {
        if (null !== $callable) {
            self::$inits[] = $callable;
        }
        spl_autoload_register(array('Xxx', 'autoload'));
    }
}

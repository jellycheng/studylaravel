<?php

class Xxx_DependencyContainer{

	public function __construct()
    {
    }

    /**
     * Returns a singleton of the DependencyContainer.
     * 单类
     * @return Swift_DependencyContainer
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

}
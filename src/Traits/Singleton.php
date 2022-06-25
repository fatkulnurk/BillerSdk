<?php

namespace Fatkulnurk\BillerSdk\Traits;

trait Singleton
{
    private static $instance = null;

    // The object is created from within the class itself
    // only if the class has no instance.
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
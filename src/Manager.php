<?php

namespace NTLAB\Propel\Behavior;

use Propel\Generator\Model\Behavior;

class Manager
{
    protected static $instance = null;

    protected $properties = array();

    /**
     * Get manager instance.
     *
     * @return \NTLAB\Propel\Behavior\Manager
     */
    public static function getInstance()
    {
        if (null == static::$instance) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setProperty('enableMixinBehavior', true);
        $this->setProperty('mixinBehaviorRegisterMethod', 'sfPropelBehavior::add');
        $this->setProperty('mixinCallableMethod', '\sfMixer::getCallable');
        $this->setProperty('mixinCallablesMethod', '\sfMixer::getCallables');
    }

    /**
     * Get behavior property value.
     *
     * @param string $name
     * @return mixed
     */
    public function getProperty($name, $default = null)
    {
        return isset($this->properties[$name]) ? $this->properties[$name] : $default;
    }

    /**
     * Set behavior property value.
     *
     * @param string $name
     * @param mixed $value
     * @return \NTLAB\Propel\Behavior\Manager
     */
    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;

        return $this;
    }
 }

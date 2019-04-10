<?php

namespace NTLAB\Propel\Behavior;

use Propel\Generator\Model\Behavior;

/**
 * Base behavior class.
 */
abstract class Base extends Behavior
{
    /**
     * @var \NTLAB\Propel\Behavior\Manager
     */
    protected $manager;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->manager = Manager::getInstance();
        $this->addParameter(array('name' => 'disabled', 'value' => 'false'));
        $this->configure();
    }

    /**
     * Class configuration called when the object instantiated.
     *
     * @return void
     */
    protected function configure()
    {
    }

    /**
     * Returns a build property from propel.ini.
     *
     * @param string $name
     * @return mixed
     */
    public function getProperty($name, $default = null)
    {
        return $this->manager->getProperty($name, $default);
    }

    /**
     * Returns true if the current behavior has been disabled.
     *
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->booleanValue($this->getParameter('disabled'));
    }
}

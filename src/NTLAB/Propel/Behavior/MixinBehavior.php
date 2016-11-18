<?php

namespace NTLAB\Propel\Behavior;

use Propel\Generator\Builder\Om\ClassTools;

/**
 * Adds support for symfony's {@link sfMixer} behaviors.
 */
class MixinBehavior extends Base
{
    protected function getMixinClassName($base = true)
    {
      return ($base ? 'Base' : '').$this->getTable()->getPhpName();
    }

    public function preDelete($builder)
    {
        if ($this->isDisabled()) {
            return;
        }

        return $this->renderTemplate('mixinPreDelete', array('method' => $this->getProperty('mixinCallablesMethod'), 'class' => $this->getMixinClassName()));
    }

    public function postDelete($builder)
    {
        if ($this->isDisabled()) {
            return;
        }

        return $this->renderTemplate('mixinPostDelete', array('method' => $this->getProperty('mixinCallablesMethod'), 'class' => $this->getMixinClassName()));
    }

    public function preSave($builder)
    {
        if ($this->isDisabled()) {
            return;
        }

        return $this->renderTemplate('mixinPreSave', array('method' => $this->getProperty('mixinCallablesMethod'), 'class' => $this->getMixinClassName()));
    }

    public function postSave($builder)
    {
      if ($this->isDisabled()) {
          return;
      }

      return $this->renderTemplate('mixinPostSave', array('method' => $this->getProperty('mixinCallablesMethod'), 'class' => $this->getMixinClassName()));
    }

    public function objectCall($builder)
    {
        if ($this->isDisabled()) {
            return;
        }

        return $this->renderTemplate('mixinObjectCall', array('method' => $this->getProperty('mixinCallableMethod'), 'class' => $this->getMixinClassName()));
    }
  
    public function objectFilter(&$script, $builder)
    {
        if ($this->isDisabled()) {
            return;
        }

        if ($this->getTable()->getAttribute('behaviors')) {
            $script .= $this->getBehaviorsInclude($builder);
            $this->createBehaviorsFile($builder);
        }
    }

    /**
     * Creates the current model's behaviors configuration file.
     *
     * Any existing behaviors file will be either deleted or overwritten.
     *
     * @return boolean Returns true if the model has behaviors
     */
    protected function createBehaviorsFile($builder)
    {
        if (file_exists($file = $this->getBehaviorsFilePath($builder, true))) {
            unlink($file);
        }
        if ($behaviors = $this->getTable()->getAttribute('behaviors')) {
            $code = $this->renderTemplate('mixinBehavior', array('method' => $this->getProperty('mixinBehaviorRegisterMethod'), 'class' => $this->getMixinClassName(false), 'parameters' => var_export(unserialize($behaviors), true)));
            file_put_contents($file, $code);

            return true;
        }
    }

    /**
     * Returns PHP code for including the current model's behaviors configuration file.
     *
     * @return string
     */
    protected function getBehaviorsInclude($builder)
    {
        return <<<EOF

// symfony mixin behavior
include '{$this->getBehaviorsFilePath($builder)}';

EOF;
    }

    /**
     * Get the model behavior file name.
     *
     * @return string
     */
    protected function getBehaviorsFileName()
    {
        return sprintf('%sBehaviors', $this->getTable()->getPhpName());
    }

    /**
     * Returns the path to the current model's behaviors configuration file.
     *
     * @param boolean $absolute
     * @return string
     */
    protected function getBehaviorsFilePath($builder, $absolute = false)
    {
        $base = $absolute ? getcwd().DIRECTORY_SEPARATOR : '';

        return $base.ClassTools::createFilePath($builder->getPackagePath(), $this->getBehaviorsFileName());
    }
}

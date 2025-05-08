<?php

namespace NTLAB\Propel\Behavior;

use Propel\Generator\Builder\Om\ClassTools;
use NTLAB\Object\PHP as PHPObj;

/**
 * Adds support for symfony's {@link sfMixer} behavior.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class MixinBehavior extends Base
{
    protected function getMixinClassName($base = true)
    {
        return $this->getTable()->getNamespace().'\\'.($base ? 'Base\\' : '').$this->getTable()->getPhpName();
    }

    public function preDelete($builder)
    {
        if ($this->isDisabled()) {
            return;
        }

        return $this->renderTemplate('mixinPreDelete', ['method' => $this->getProperty('mixinCallablesMethod'), 'class' => $this->getMixinClassName()],
            $this->getTemplatesDir());
    }

    public function postDelete($builder)
    {
        if ($this->isDisabled()) {
            return;
        }

        return $this->renderTemplate('mixinPostDelete', ['method' => $this->getProperty('mixinCallablesMethod'), 'class' => $this->getMixinClassName()],
            $this->getTemplatesDir());
    }

    public function preSave($builder)
    {
        if ($this->isDisabled()) {
            return;
        }

        return $this->renderTemplate('mixinPreSave', ['method' => $this->getProperty('mixinCallablesMethod'), 'class' => $this->getMixinClassName()],
            $this->getTemplatesDir());
    }

    public function postSave($builder)
    {
      if ($this->isDisabled()) {
          return;
      }

      return $this->renderTemplate('mixinPostSave', ['method' => $this->getProperty('mixinCallablesMethod'), 'class' => $this->getMixinClassName()],
            $this->getTemplatesDir());
    }

    public function objectCall($builder)
    {
        if ($this->isDisabled()) {
            return;
        }

        return $this->renderTemplate('mixinObjectCall', ['method' => $this->getProperty('mixinCallableMethod'), 'class' => $this->getMixinClassName()],
            $this->getTemplatesDir());
    }
  
    public function objectFilter(&$script, $builder)
    {
        if ($this->isDisabled()) {
            return;
        }
        if ($this->getTable()->hasBehavior('symfony_mixin')) {
            if ($this->createBehaviorsFile($builder, $script)) {
                $script .= $this->getBehaviorsInclude($builder);
            }
        }
    }

    /**
     * Creates the current model's behaviors configuration file.
     *
     * Any existing behaviors file will be either deleted or overwritten.
     *
     * @return boolean Returns true if the model has behaviors
     */
    protected function createBehaviorsFile($builder, &$script)
    {
        if (file_exists($file = $this->getBehaviorsFilePath($builder, true))) {
            unlink($file);
        }
        if ($configuration = $this->getTable()->getBehavior('symfony_mixin')) {
            if (count($behaviors = $configuration->getParameter('behaviors'))) {
                if ($virtualMethodsCallable = $this->getProperty('mixinBehaviorGetVirtualMethods')) {
                    if (false !== strpos($virtualMethodsCallable, '::')) {
                        $virtualMethodsCallable = explode('::', $virtualMethodsCallable);
                    }
                    if (is_callable($virtualMethodsCallable)) {
                        foreach (array_keys($behaviors) as $behavior) {
                            if (count($methods = call_user_func($virtualMethodsCallable, $behavior))) {
                                $this->includeVirtualMethods($script, $methods);
                            }
                        }
                    }
                }
                $code = $this->renderTemplate('mixinBehavior', ['method' => $this->getProperty('mixinBehaviorRegisterMethod'), 'class' => $this->getMixinClassName(false), 'parameters' => PHPObj::create($behaviors)],
                    $this->getTemplatesDir());
                file_put_contents($file, $code);

                return true;
            }
        }
    }

    /**
     * Include virtual methods from behaviors.
     *
     * @param string $script
     * @param array $methods
     */
    protected function includeVirtualMethods(&$script, $methods)
    {
        $script = preg_replace_callback('#(\s\*\s\@package\s+)(.*)#m', function ($matches) use ($methods) {
            $methods = array_map(fn ($a) => explode(' ', $a, 2), $methods);
            $methods = array_map(fn ($a) => (
                str_pad(str_replace('@package', $a[0], $matches[1]), strlen($matches[1])).
                str_replace('$this', 'Child'.$this->getTable()->getPhpName(), $a[1])
            ), $methods);
            $methods[] = $matches[0];

            return implode("\n", $methods);
        }, $script);
    }

    /**
     * Returns PHP code for including the current model's behaviors configuration file.
     *
     * @return string
     */
    protected function getBehaviorsInclude($builder)
    {
        return <<<EOF

// symfony behavior
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

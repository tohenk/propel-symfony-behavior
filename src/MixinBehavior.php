<?php

/*
 * The MIT License
 *
 * Copyright (c) 2016-2025 Toha <tohenk@yahoo.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace NTLAB\Propel\Behavior;

use NTLAB\Object\PHP as PHPObj;
use Propel\Generator\Builder\Om\ClassTools;

/**
 * Adds support for symfony's {@link sfMixer} behavior.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class MixinBehavior extends Base
{
    public const BEHAVIORS_PARAMETER = 'behaviors';

    protected function getMixinClassName($base = true)
    {
        return $this->getTable()->getNamespace().'\\'.($base ? 'Base\\' : '').$this->getTable()->getPhpName();
    }

    /**
     * Get registered behaviors for current table.
     *
     * @return array
     */
    protected function getBehaviors()
    {
        $behaviors = [];
        if ($configuration = $this->getTable()->getBehavior(Manager::BEHAVIOR_MIXIN)) {
            $behaviors = $configuration->getParameter(static::BEHAVIORS_PARAMETER);
        }

        return $behaviors;
    }

    /**
     * Render mixin builder.
     *
     * @param string $type
     * @return string
     */
    protected function renderMixinBuilder($type)
    {
        if (!$this->isDisabled()) {
            if (count($behaviors = $this->getBehaviors())) {
                $result = [];
                if ($buildersCallable = $this->getProperty('mixinBehaviorGetBuilders')) {
                    if (false !== strpos($buildersCallable, '::')) {
                        $buildersCallable = explode('::', $buildersCallable);
                    }
                    if (is_callable($buildersCallable)) {
                        foreach ($behaviors as $behavior => $parameter) {
                            if (is_array($buildersCallables = call_user_func($buildersCallable, $behavior))) {
                                foreach ($buildersCallables as $key => $callable) {
                                    if ($key === $type && is_callable($callable) && $retval = call_user_func($callable, $this, $parameter)) {
                                        $result[] = $retval;
                                    }
                                }
                            }
                        }
                    }
                }
                if (count($result)) {
                    return strtr(implode("\n", $result), [
                        'self' => '\\'.$this->getMixinClassName(),
                    ]);
                }
            }
        }
    }

    /**
     * Render mixin template.
     *
     * @param string $template
     * @param array $vars
     * @return string
     */
    protected function renderMixinTemplate($template, $vars = [])
    {
        if (!$this->isDisabled()) {
            return $this->renderTemplate($template, array_merge([
                'callables' => $this->getProperty('mixinCallablesMethod'),
                'callable' => $this->getProperty('mixinCallableMethod'),
                'class' => $this->getMixinClassName(),
                'model' => $this->getTable()->getPhpName(),
            ], $vars), $this->getTemplatesDir());
        }
    }

    /**
     * Creates the current model's behaviors configuration file.
     *
     * Any existing behaviors file will be either deleted or overwritten.
     *
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     * @param string $script
     * @return void
     */
    protected function createObjectBehaviors($builder, &$script)
    {
        if (!$this->isDisabled()) {
            if (file_exists($file = $this->getBehaviorsFilePath($builder, true))) {
                unlink($file);
            }
            if (count($behaviors = $this->getBehaviors())) {
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
                file_put_contents($file, $this->renderTemplate('behavior', [
                    'method' => $this->getProperty('mixinBehaviorRegisterMethod'),
                    'class' => $this->getMixinClassName(false),
                    'model' => $this->getTable()->getPhpName(),
                    'parameters' => PHPObj::create($behaviors),
                ], $this->getTemplatesDir()));

                $script .= $this->getBehaviorsInclude($builder);
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
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
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
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     * @param boolean $absolute
     * @return string
     */
    protected function getBehaviorsFilePath($builder, $absolute = false)
    {
        $base = $absolute ? getcwd().DIRECTORY_SEPARATOR : '';

        return $base.ClassTools::createFilePath($builder->getPackagePath(), $this->getBehaviorsFileName());
    }

    /**
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     */
    public function objectAttributes($builder)
    {
        return $this->renderMixinBuilder('attributes');
    }

    /**
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     */
    public function objectMethods($builder)
    {
        return $this->renderMixinBuilder('methods');
    }

    /**
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     */
    public function objectCall($builder)
    {
        return $this->renderMixinTemplate('objectCall');
    }
  
    /**
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     */
    public function postHydrate($builder)
    {
        return $this->renderMixinTemplate('postHydrate');
    }

    /**
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     */
    public function preDelete($builder)
    {
        return $this->renderMixinTemplate('preDelete');
    }

    /**
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     */
    public function postDelete($builder)
    {
        return $this->renderMixinTemplate('postDelete');
    }

    /**
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     */
    public function preSave($builder)
    {
        return $this->renderMixinTemplate('preSave');
    }

    /**
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     */
    public function postSave($builder)
    {
        return $this->renderMixinTemplate('postSave');
    }

    /**
     * @param string $script
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     */
    public function objectFilter(&$script, $builder)
    {
        $this->createObjectBehaviors($builder, $script);
    }

    /**
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     */
    public function queryAttributes($builder)
    {
        return $this->renderMixinBuilder('query-attributes');
    }

    /**
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     */
    public function queryMethods($builder)
    {
        return $this->renderMixinBuilder('query-methods');
    }

    /**
     * Create behavior.
     *
     * @param array $behaviors
     * @return \NTLAB\Propel\Behavior\MixinBehavior
     */
    public static function create($behaviors)
    {
        $behavior = new static();
        $behavior->setName(Manager::BEHAVIOR_MIXIN);
        $behavior->addParameter(['name' => static::BEHAVIORS_PARAMETER, 'value' => $behaviors]);

        return $behavior;
    }
}

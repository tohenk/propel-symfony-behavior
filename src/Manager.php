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

/**
 * Symfony Propel behavior manager.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Manager
{
    public const BEHAVIOR_SYMFONY = 'symfony';
    public const BEHAVIOR_MIXIN = 'symfony_mixin';
    public const BEHAVIOR_TIMESTAMPABLE = 'timestampable';

    protected static $instance = null;

    protected $properties = [];

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
        if ($configuration = Util::getConfiguration()) {
            foreach ([
                'enableMixinBehavior' => 'enabled',
                'mixinBehaviorRegisterMethod' => 'registerMethod',
                'mixinBehaviorGetVirtualMethods' => 'getVirtualMethods',
                'mixinBehaviorGetBuilders' => 'getBuilders',
                'mixinCallableMethod' => 'callableMethod',
                'mixinCallablesMethod' => 'callablesMethod',
            ] as $key => $value) {
                if (isset($configuration[$value])) {
                    $this->setProperty($key, $configuration[$value]);
                }
            }
        } else {
            $this->setProperty('enableMixinBehavior', false);
        }
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

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

use Propel\Generator\Model\Behavior;

/**
 * Base behavior class.
 *
 * @author Toha <tohenk@yahoo.com>
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
        $this->addParameter(['name' => 'disabled', 'value' => 'false']);
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

    /**
     * Get templates builder directory.
     *
     * @return string
     */
    protected function getTemplatesDir()
    {
        return __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR;
    }
}

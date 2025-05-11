<?php

/*
 * The MIT License
 *
 * Copyright (c) 2025 Toha <tohenk@yahoo.com>
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

use ReflectionClass;

/**
 * A collection of utilities.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Util
{
    /**
     * Get Composer autoloader instance.
     *
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getComposer()
    {
        if ($autoloaders = spl_autoload_functions()) {
            foreach ($autoloaders as $autoload) {
                if (is_array($autoload)) {
                    $class = $autoload[0];
                    if (is_object($class) && 'Composer\Autoload\ClassLoader' === get_class($class)) {
                        return $class;
                    }
                }
            }
        }
    }

    /**
     * Get symfony behavior configuration.
     *
     * @return array
     */
    public static function getConfiguration()
    {
        if ($composer = static::getComposer()) {
            $fn = function ($composerJson) {
                if (is_readable($composerJson)) {
                    $packages = json_decode(file_get_contents($composerJson), true);
                    $packages = isset($packages['packages']) ? $packages['packages'] : [$packages];
                    foreach ($packages as $package) {
                        if (isset($package['extra']) && isset($package['extra']['symfony-behavior'])) {
                            return $package['extra']['symfony-behavior'];
                        }
                    }
                }
            };
            $r = new ReflectionClass($composer);
            $composerDir = dirname($r->getFileName());
            if ($configuration = $fn(implode(DIRECTORY_SEPARATOR, [$composerDir, 'installed.json']))) {
                return $configuration;
            }

            return $fn(implode(DIRECTORY_SEPARATOR, [$composerDir, '..', '..', 'composer.json']));
        }
    }
}

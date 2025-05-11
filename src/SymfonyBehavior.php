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

use Propel\Generator\Behavior\Timestampable\TimestampableBehavior;

/**
 * Apply symfony default behavior such as mixin and timestampable.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class SymfonyBehavior extends Base
{
    protected function configure()
    {
        $this->addParameter(['name' => 'form', 'value' => 'true']);
        $this->addParameter(['name' => 'filter', 'value' => 'true']);
    }

    public function modifyDatabase(): void
    {
        $forced = $this->getProperty('enableSymfonyBehavior', true);
        foreach ($this->getDatabase()->getTables() as $table) {
            if (!($behavior = $table->getBehavior(Manager::BEHAVIOR_SYMFONY)) && !$forced) {
                continue;
            }

            if (!$behavior) {
                $behavior = clone $this;
                $table->addBehavior($behavior);
            }

            $behaviors = [];
            $parameters = $behavior->getParameters();
            if (isset($parameters['params'])) {
                $behaviors = unserialize(html_entity_decode($parameters['params']));
            }

            // symfony mixin
            if (!isset($behaviors[Manager::BEHAVIOR_MIXIN]) && $this->getProperty('enableMixinBehavior')) {
                $table->addBehavior(MixinBehavior::create($behaviors));
            }

            // timestampable
            if (!isset($behaviors[Manager::BEHAVIOR_TIMESTAMPABLE])) {
                $parameters = [];
                foreach ($table->getColumns() as $column) {
                    if (!isset($parameters['create_column']) && in_array($column->getName(), ['created_at', 'created_on'])) {
                        $parameters['create_column'] = $column->getName();
                    }

                    if (!isset($parameters['update_column']) && in_array($column->getName(), ['updated_at', 'updated_on'])) {
                        $parameters['update_column'] = $column->getName();
                    }
                }

                if (count($parameters)) {
                    if (!isset($parameters['create_column'])) {
                        $parameters['disable_created_at'] = 'true';
                    }
                    if (!isset($parameters['update_column'])) {
                        $parameters['disable_updated_at'] = 'true';
                    }
                    $behavior = new TimestampableBehavior();
                    $behavior->setName(Manager::BEHAVIOR_TIMESTAMPABLE);
                    foreach ($parameters as $param => $value) {
                        $behavior->addParameter(['name' => $param, 'value' => $value]);
                    }
                    $table->addBehavior($behavior);
                }
            }
        }
    }

    /**
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     */
    public function objectMethods($builder)
    {
        if (!$this->isDisabled()) {
            $unices = [];
            foreach ($this->getTable()->getUnices() as $unique)
            {
                $unices[] = sprintf("['%s']", implode("', '", $unique->getColumns()));
            }
            $unices = implode(', ', array_unique($unices));

            return $this->renderTemplate('uniqueColumns', ['unices' => $unices], $this->getTemplatesDir());
        }
    }
}

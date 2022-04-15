<?php

namespace NTLAB\Propel\Behavior;

use Propel\Generator\Behavior\Timestampable\TimestampableBehavior;

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
            if (!($behavior = $table->getBehavior('symfony')) && !$forced) {
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
            if (!isset($behaviors['symfony_mixin']) && $this->getProperty('enableMixinBehavior', true)) {
                $behavior = new MixinBehavior();
                $behavior->setName('symfony_mixin');
                $behavior->addParameter(['name' => 'behaviors', 'value' => $behaviors]);
                $table->addBehavior($behavior);
            }

            // timestampable
            if (!isset($behaviors['timestampable'])) {
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
                    $behavior->setName('timestampable');
                    foreach ($parameters as $param => $value) {
                        $behavior->addParameter(['name' => $param, 'value' => $value]);
                    }
                    $table->addBehavior($behavior);
                }
            }
        }
    }

    public function objectMethods($builder)
    {
        if ($this->isDisabled()) {
            return;
        }

        $unices = [];
        foreach ($this->getTable()->getUnices() as $unique)
        {
            $unices[] = sprintf("['%s']", implode("', '", $unique->getColumns()));
        }
        $unices = implode(', ', array_unique($unices));

        return $this->renderTemplate('uniqueColumns', ['unices' => $unices], $this->getTemplatesDir());
    }
}

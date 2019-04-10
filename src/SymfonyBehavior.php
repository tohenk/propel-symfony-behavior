<?php

namespace NTLAB\Propel\Behavior;

use Propel\Generator\Behavior\Timestampable\TimestampableBehavior;

class SymfonyBehavior extends Base
{
    protected function configure()
    {
        $this->addParameter(array('name' => 'form', 'value' => 'true'));
        $this->addParameter(array('name' => 'filter', 'value' => 'true'));
    }

    public function modifyDatabase()
    {
        foreach ($this->getDatabase()->getTables() as $table) {
            $behaviors = $table->getBehaviors();

            if (!isset($behaviors['symfony'])) {
                $behavior = clone $this;
                $table->addBehavior($behavior);
            }

            // symfony mixin
            if (!isset($behaviors['symfony_mixin']) && $this->getProperty('enableMixinBehavior', true)) {
                $behavior = new MixinBehavior();
                $behavior->setName('symfony_mixin');
                $table->addBehavior($behavior);
            }

            // timestampable
            if (!isset($behaviors['timestampable'])) {
                $parameters = array();
                foreach ($table->getColumns() as $column) {
                    if (!isset($parameters['create_column']) && in_array($column->getName(), array('created_at', 'created_on'))) {
                        $parameters['create_column'] = $column->getName();
                    }

                    if (!isset($parameters['update_column']) && in_array($column->getName(), array('updated_at', 'updated_on'))) {
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
                        $behavior->addParameter(array('name' => $param, 'value' => $value));
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

        $unices = array();
        foreach ($this->getTable()->getUnices() as $unique)
        {
          $unices[] = sprintf("array('%s')", implode("', '", $unique->getColumns()));
        }
        $unices = implode(', ', array_unique($unices));

        return $this->renderTemplate('uniqueColumns', array('unices' => $unices));
    }
}

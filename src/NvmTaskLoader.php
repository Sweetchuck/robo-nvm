<?php

namespace Sweetchuck\Robo\Nvm;

use League\Container\ContainerAwareInterface;
use Robo\Collection\CollectionBuilder;

trait NvmTaskLoader
{
    /**
     * @return \Sweetchuck\Robo\Nvm\Task\ListLocalTask|\Robo\Collection\CollectionBuilder
     */
    protected function taskNvmListLocal(
        array $options = [],
        ?NvmShFinderInterface $nvmShFinder = null
    ): CollectionBuilder {
        /** @var \Sweetchuck\Robo\Nvm\Task\ListLocalTask|\Robo\Collection\CollectionBuilder $task */
        $task = $this->task(Task\ListLocalTask::class, $nvmShFinder);
        if ($this instanceof ContainerAwareInterface) {
            $task->setContainer($this->getContainer());
        }

        $task->setOptions($options);

        return $task;
    }

    /**
     * @return \Sweetchuck\Robo\Nvm\Task\WhichTask|\Robo\Collection\CollectionBuilder
     */
    protected function taskNvmWhich(
        array $options = [],
        ?NvmShFinderInterface $nvmShFinder = null
    ): CollectionBuilder {
        /** @var \Sweetchuck\Robo\Nvm\Task\WhichTask|\Robo\Collection\CollectionBuilder $task */
        $task = $this->task(Task\WhichTask::class, $nvmShFinder);
        if ($this instanceof ContainerAwareInterface) {
            $task->setContainer($this->getContainer());
        }

        $task->setOptions($options);

        return $task;
    }
}

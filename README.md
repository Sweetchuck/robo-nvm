# Robo task to run NVM related tasks

[![CircleCI](https://circleci.com/gh/Sweetchuck/robo-nvm.svg?style=svg)](https://circleci.com/gh/Sweetchuck/robo-nvm)
[![codecov](https://codecov.io/gh/Sweetchuck/robo-nvm/branch/master/graph/badge.svg)](https://codecov.io/gh/Sweetchuck/robo-nvm)


## Install

`composer require --dev sweetchuck/robo-nvm`


## Usage

```php
<?php

use Sweetchuck\Robo\Nvm\NvmTaskLoader;

class RoboFile extends \Robo\Tasks
{
    use NvmTaskLoader;

    /**
     * @command nvm:list-local
     */
    public function nvmListLocal()
    {
        $result = $this
            ->taskNvmListLocal()
            ->run()
            ->stopOnFail();

        $this->say(sprintf(
            'Current node is: %s',
            $result['nvm.listLocal.current']
        ));

        $this->say(sprintf(
            'Available NodeJS versions: %s',
            implode(', ', $result['nvm.listLocal.versions'])
        ));
    }

    /**
     * @command nvm:which
     */
    public function nvmWhich()
    {
        $result = $this
            ->taskNvmWhich()
            ->run()
            ->stopOnFail();

        $this->say(sprintf(
            'Path to "node" executable is: %s',
            $result['nvm.which.nodeExecutable']
        ));

        $this->say(sprintf(
            'Path to "bin" directory is: %s',
            $result['nvm.which.binDir']
        ));
    }
}

```

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
    use \Sweetchuck\Robo\Nvm\NvmTaskLoader;

    public function nvmListLocal()
    {
        $result = $this
            ->taskNvmListLocal()
            ->run()
            ->stopOnFail();

        $this->say($result['nvm.listLocal.current']);
        $this->say(implode(', ', $result['nvm.listLocal.versions']));
    }
}

```

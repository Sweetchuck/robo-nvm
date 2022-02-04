# Robo task to run NVM related tasks

[![CircleCI](https://circleci.com/gh/Sweetchuck/robo-nvm/tree/1.x.svg?style=svg)](https://circleci.com/gh/Sweetchuck/robo-nvm/?branch=1.x)
[![codecov](https://codecov.io/gh/Sweetchuck/robo-nvm/branch/1.x/graph/badge.svg?token=HSF16OGPyr)](https://app.codecov.io/gh/Sweetchuck/robo-nvm/branch/1.x)


## Install

`composer require --dev sweetchuck/robo-nvm`


## Task - taskNvmListLocal

```php
<?php

declare(strict_types = 1);

class RoboFile extends \Robo\Tasks
{
    use \Sweetchuck\Robo\Nvm\NvmTaskLoader;

    /**
     * @command nvm:list-local
     */
    public function nvmListLocal()
    {
        return $this
            ->collectionBuilder()
            ->addTask($this->taskNvmListLocal())
            ->addCode(function (\Robo\State\Data $data): int {
                $output = $this->output();
                $output->writeln(sprintf(
                    'Current node is: %s',
                    $data['nvm.listLocal.current'],
                ));

                $output->writeln('Available NodeJS versions:');
                foreach ($data['nvm.listLocal.versions'] as $value) {
                    $output->writeln("    $value");
                }

                return 0;
            });
    }
}

```

Run: `vendor/bin/robo nvm:list-local`<br />
> <pre>Current node is: v9.3.0
>     v9.3.0
>     v11.5.0
>     v15.0.1</pre>


## Task - taskNvmWhich

```php
<?php

declare(strict_types = 1);

class RoboFile extends \Robo\Tasks
{
    use \Sweetchuck\Robo\Nvm\NvmTaskLoader;

    /**
     * @command nvm:which
     */
    public function nvmWhich()
    {
        return $this
            ->collectionBuilder()
            ->addTask($this->taskNvmWhich())
            ->addCode(function (\Robo\State\Data $data): int {
                $output = $this->output();
                $output->writeln(sprintf(
                    'nvm.which.nodeExecutable = %s',
                    $data['nvm.which.nodeExecutable'],
                ));
                $output->writeln(sprintf(
                    'nvm.which.binDir = %s',
                    $data['nvm.which.binDir'],
                ));

                return 0;
            });
    }
}
```

Run: `vendor/bin/robo nvm:which`

> <pre>nvm.which.nodeExecutable = /home/me/.nvm/versions/node/v9.3.0/bin/node
> nvm.which.binDir = /home/me/.nvm/versions/node/v9.3.0/bin</pre>

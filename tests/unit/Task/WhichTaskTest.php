<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm\Tests\Unit\Task;

use Sweetchuck\Robo\Nvm\Task\BaseTask;
use Sweetchuck\Robo\Nvm\Task\WhichTask;

class WhichTaskTest extends TaskTestBase
{

    protected function createTaskInstance(): BaseTask
    {
        return new WhichTask();
    }

    public function casesGetCommand(): array
    {
        return [
            'basic' => [
                ". '/home/me/.nvm/nvm.sh'; nvm which '8.9'",
                [
                    'arguments' => ['8.9'],
                ],
            ],
            'workingDirectory' => [
                "cd 'my-dir' && . '/home/me/.nvm/nvm.sh'; nvm which '8.9'",
                [
                    'workingDirectory' => 'my-dir',
                    'arguments' => ['8.9'],
                ],
            ],

        ];
    }

    public function casesRunSuccess(): array
    {
        return [
            'basic' => [
                [
                    'exitCode' => 0,
                    'assets' => [
                        'nvm.which.binDir' => '/foo/bar/bin',
                        'nvm.which.nodeExecutable' => '/foo/bar/bin/node',
                    ],
                ],
                [
                    'exitCode' => 0,
                    'stdOutput' => "/foo/bar/bin/node\n",
                    'stdError' => '',
                ],
            ],
        ];
    }
}

<?php

namespace Sweetchuck\Robo\Nvm\Tests\Unit\Task;

use Sweetchuck\Robo\Nvm\Task\WhichTask;

class WhichTaskTest extends BaseCliTaskTestBase
{

    /**
     * {@inheritdoc}
     */
    protected function initTask()
    {
        $this->task = new WhichTask();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function casesGetCommand(): array
    {
        return [
            'basic' => [
                "nvm which '8.9'",
                [
                    'arguments' => ['8.9'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
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

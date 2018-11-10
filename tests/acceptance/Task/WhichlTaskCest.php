<?php

namespace Sweetchuck\Robo\Nvm\Tests\Acceptance\Task;

use Robo\Collection\CollectionBuilder;

class WhichlTaskCest extends TaskCestBase
{

    /**
     * {@inheritdoc}
     */
    protected function runSuccessCases(): array
    {
        $userName = getenv('USER');

        $logHeader = [
            'notice',
            'runs "<info>{command}</info>"',
            [
                'command' => ". '/home/$userName/.nvm/nvm.sh'; nvm which",
                'name' => 'NVM - Which',
            ],
        ];

        return [
            'empty' => [
                'expected' => [
                    'exitCode' => 0,
                    'stdOutput' => '',
                    'stdError' => '',
                    'logs' => [
                        $logHeader,
                    ],
                    'assets' => [
                        'nvm.which.nodeExecutable' => null,
                        'nvm.which.binDir' => null,
                    ],
                ],
                'process' => [
                    'exitCode' => 0,
                    'stdOutput' => '',
                    'stdError' => '',
                ],
            ],
            'basic' => [
                'expected' => [
                    'exitCode' => 0,
                    'stdOutput' => '',
                    'stdError' => '',
                    'logs' => [
                        $logHeader,
                    ],
                    'assets' => [
                        'nvm.which.nodeExecutable' => '/a/b/c/bin/node',
                        'nvm.which.binDir' => '/a/b/c/bin',
                    ],
                ],
                'process' => [
                    'exitCode' => 0,
                    'stdOutput' => implode(PHP_EOL, [
                        '/a/b/c/bin/node',
                        '',
                    ]),
                    'stdError' => '',
                ],
            ],
        ];
    }

    /**
     * @return \Sweetchuck\Robo\Nvm\Task\WhichTask|\Robo\Collection\CollectionBuilder
     */
    protected function getTask(): CollectionBuilder
    {
        return $this->taskBuilder->taskNvmWhich();
    }
}

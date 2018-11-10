<?php

namespace Sweetchuck\Robo\Nvm\Tests\Acceptance\Task;

use Robo\Collection\CollectionBuilder;

class ListLocalTaskCest extends TaskCestBase
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
                'command' => ". '/home/$userName/.nvm/nvm.sh'; nvm ls --no-colors",
                'name' => 'NVM - List local',
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
                        'nvm.listLocal.current' => null,
                        'nvm.listLocal.versions' => [],
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
                        'nvm.listLocal.current' => 'v4.5.6',
                        'nvm.listLocal.versions' => [
                            'v1.2.3',
                            'v4.5.6',
                            'v7.8.9',
                        ],
                    ],
                ],
                'process' => [
                    'exitCode' => 0,
                    'stdOutput' => implode(PHP_EOL, [
                        '    v1.2.3',
                        '->  v4.5.6',
                        '    v7.8.9',
                        '',
                    ]),
                    'stdError' => '',
                ],
            ],
        ];
    }


    /**
     * @return \Sweetchuck\Robo\Nvm\Task\ListLocalTask|\Robo\Collection\CollectionBuilder
     */
    protected function getTask(): CollectionBuilder
    {
        return $this->taskBuilder->taskNvmListLocal();
    }
}

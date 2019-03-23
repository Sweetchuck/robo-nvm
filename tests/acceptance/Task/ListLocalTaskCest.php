<?php

namespace Sweetchuck\Robo\Nvm\Tests\Acceptance\Task;

use Robo\Collection\CollectionBuilder;
use Sweetchuck\Robo\Nvm\NvmShFinderInterface;
use Sweetchuck\Robo\Nvm\Test\Helper\Dummy\DummyNvmShFinder;

class ListLocalTaskCest extends TaskCestBase
{
    /**
     * @return \Sweetchuck\Robo\Nvm\Task\ListLocalTask|\Robo\Collection\CollectionBuilder
     */
    protected function getTask(array $options = [], array $constructorArgs = []): CollectionBuilder
    {
        return $this->taskBuilder->taskNvmListLocal($options, ...$constructorArgs);
    }

    /**
     * {@inheritdoc}
     */
    protected function runSuccessCases(): array
    {
        $logHeader = [
            'notice',
            'runs "<info>{command}</info>"',
            [
                'command' => ". '/my/path/to/nvm.sh'; nvm ls --no-colors",
                'name' => 'NVM - List local',
            ],
        ];

        $nvmShFinderDummy = new DummyNvmShFinder('/my/path/to/nvm.sh');

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
                'constructorArgs' => [
                    $nvmShFinderDummy,
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
                'constructorArgs' => [
                    $nvmShFinderDummy,
                ],
            ],
        ];
    }
}

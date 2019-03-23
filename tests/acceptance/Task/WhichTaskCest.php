<?php

namespace Sweetchuck\Robo\Nvm\Tests\Acceptance\Task;

use Robo\Collection\CollectionBuilder;
use Sweetchuck\Robo\Nvm\Test\Helper\Dummy\DummyNvmShFinder;

class WhichTaskCest extends TaskCestBase
{

    /**
     * @return \Sweetchuck\Robo\Nvm\Task\WhichTask|\Robo\Collection\CollectionBuilder
     */
    protected function getTask(array $options = [], array $constructorArgs = []): CollectionBuilder
    {
        return $this->taskBuilder->taskNvmWhich($options, ...$constructorArgs);
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
                'command' => ". '/my/path/to/nvm.sh'; nvm which",
                'name' => 'NVM - Which',
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
                        'nvm.which.nodeExecutable' => null,
                        'nvm.which.binDir' => null,
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
                'constructorArgs' => [
                    $nvmShFinderDummy,
                ],
            ],
        ];
    }
}

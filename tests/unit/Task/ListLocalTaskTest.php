<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm\Tests\Unit\Task;

class ListLocalTaskTest extends TaskTestBase
{

    /**
     * {@inheritdoc}
     */
    protected function initTask()
    {
        $this->task = $this->taskBuilder->taskNvmListLocal();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function casesGetCommand(): array
    {
        return [
            'basic' => [
                ". '/home/me/.nvm/nvm.sh'; nvm ls --no-colors",
                [],
            ],
            'with version' => [
                ". '/home/me/.nvm/nvm.sh'; nvm ls --no-colors '8.9'",
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
                        'nvm.listLocal.current' => 'v7.10.0',
                        'nvm.listLocal.versions' => [
                            'v6.2.2',
                            'v7.3.0',
                            'v7.10.0',
                            'v8.6.0',
                            'v8.9.0',
                            'v9.0.0',
                            'v9.3.0',
                        ],
                    ],
                ],
                [
                    'exitCode' => 0,
                    'stdOutput' => implode(PHP_EOL, [
                        '         v6.2.2',
                        '         v7.3.0',
                        '->      v7.10.0',
                        '         v8.6.0',
                        '         v8.9.0',
                        '         v9.0.0',
                        '         v9.3.0',
                        'default -> 7.10.0 (-> v7.10.0)',
                        'node -> stable (-> v9.3.0) (default)',
                        'stable -> 9.3 (-> v9.3.0) (default)',
                        'iojs -> N/A (default)',
                        'lts/* -> lts/carbon (-> N/A)',
                        'lts/argon -> v4.9.1 (-> N/A)',
                        'lts/boron -> v6.14.1 (-> N/A)',
                        'lts/carbon -> v8.11.1 (-> N/A)',
                    ]),
                    'stdError' => '',
                ],
            ],
        ];
    }
}

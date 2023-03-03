<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm\Tests\Unit\OutputParser;

use Codeception\Test\Unit;
use Sweetchuck\Robo\Nvm\OutputParser\ListOutputParser;

/**
 * @covers \Sweetchuck\Robo\Nvm\OutputParser\ListOutputParser
 * @covers \Sweetchuck\Robo\Nvm\OutputParser\ParserBase
 */
class ListOutputParserTest extends Unit
{

    public function casesParse(): array
    {
        return [
            'exitCode 1' => [[], 1],
            'empty' => [
                [
                    'assets' => [
                        'nvm.list.current' => null,
                        'nvm.list.versions' => [],
                    ],
                ],
                0,
            ],
            "with current" => [
                [
                    'assets' => [
                        'nvm.list.current' => 'v9.3.0',
                        'nvm.list.versions' => [
                            'v9.2.1',
                            'v9.3.0',
                            'v9.11.2',
                        ],
                    ],
                ],
                0,
                implode(PHP_EOL, [
                    '         v9.2.1',
                    '->       v9.3.0',
                    '        v9.11.2',
                    'default -> 9.3.0 (-> v9.3.0)',
                    'node -> stable (-> v9.11.2) (default)',
                    'iojs -> N/A (default)',
                    'lts/* -> lts/dubnium (-> N/A)',
                    'lts/argon -> v4.9.1 (-> N/A)',
                    '',
                ]),
            ],
            "without current" => [
                [
                    'assets' => [
                        'nvm.list.current' => null,
                        'nvm.list.versions' => [
                            'v9.2.1',
                            'v9.3.0',
                            'v9.11.2',
                        ],
                    ],
                ],
                0,
                implode(PHP_EOL, [
                    '         v9.2.1',
                    '         v9.3.0',
                    '        v9.11.2',
                    'default -> 9.3.0 (-> v9.3.0)',
                    'node -> stable (-> v9.11.2) (default)',
                    'iojs -> N/A (default)',
                    'lts/* -> lts/dubnium (-> N/A)',
                    'lts/argon -> v4.9.1 (-> N/A)',
                    '',
                ]),
            ],
            "no-colors" => [
                [
                    'assets' => [
                        'nvm.list.current' => 'v9.3.0',
                        'nvm.list.versions' => [
                            'v9.2.1',
                            'v9.3.0',
                            'v9.11.2',
                        ],
                    ],
                ],
                0,
                implode(PHP_EOL, [
                    '         v9.2.1 *',
                    '->       v9.3.0 *',
                    '        v9.11.2 *',
                    'default -> 9.3.0 (-> v9.3.0 *)',
                    'node -> stable (-> v9.11.2 *) (default)',
                    'iojs -> N/A (default)',
                    'lts/* -> lts/dubnium (-> N/A)',
                    'lts/argon -> v4.9.1 (-> N/A)',
                    '',
                ]),
            ],
        ];
    }

    /**
     * @dataProvider casesParse
     */
    public function testParse(
        array $expected,
        int $exitCode = 0,
        string $stdOutput = '',
        string $stdError = ''
    ): void {
        $parser = new ListOutputParser();
        static::assertSame($expected, $parser->parse($exitCode, $stdOutput, $stdError));
    }
}

<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm\Tests\Unit\OutputParser;

use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use Sweetchuck\Robo\Nvm\OutputParser\ParserBase;
use Sweetchuck\Robo\Nvm\OutputParser\WhichOutputParser;

#[CoversClass(WhichOutputParser::class)]
#[CoversClass(ParserBase::class)]
class WhichOutputParserTest extends Unit
{

    public static function casesParse(): array
    {
        return [
            'exitCode 1' => [[], 1],
            'empty' => [
                [
                    'assets' => [
                        'nvm.which.nodeExecutable' => null,
                        'nvm.which.binDir' => null,
                    ],
                ],
                0,
                "\n",
            ],
            'basic' => [
                [
                    'assets' => [
                        'nvm.which.nodeExecutable' => '/foo/bar/bin/node',
                        'nvm.which.binDir' => '/foo/bar/bin',
                    ],
                ],
                0,
                "/foo/bar/bin/node\n",
            ],
            'withSpam' => [
                [
                    'assets' => [
                        'nvm.which.nodeExecutable' => '/home/me/.nvm/versions/node/v9.3.0/bin/node',
                        'nvm.which.binDir' => '/home/me/.nvm/versions/node/v9.3.0/bin',
                    ],
                ],
                0,
                implode(PHP_EOL, [
                    "Found '/a/b/c/.nvmrc' with version <9.3>",
                    '/home/me/.nvm/versions/node/v9.3.0/bin/node',
                    '',
                ]),
            ],
        ];
    }

    #[DataProvider('casesParse')]
    public function testParse(
        array $expected,
        int $exitCode,
        string $stdOutput = '',
        string $stdError = '',
    ): void {
        $parser = new WhichOutputParser();
        static::assertSame($expected, $parser->parse($exitCode, $stdOutput, $stdError));
    }
}

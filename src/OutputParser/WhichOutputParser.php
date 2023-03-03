<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm\OutputParser;

use Symfony\Component\Filesystem\Path;

class WhichOutputParser extends ParserBase
{
    protected array $assetNameMapping = [
        'nodeExecutable' => 'nvm.which.nodeExecutable',
        'binDir' => 'nvm.which.binDir',
    ];

    public function parse(int $exitCode, string $stdOutput, string $stdError): array
    {
        if ($exitCode !== 0) {
            return [];
        }

        $assetNameNodeExecutable = $this->getExternalAssetName('nodeExecutable');
        $assetNameBinDir = $this->getExternalAssetName('binDir');

        $return = [
            'assets' => [
                $assetNameNodeExecutable => null,
                $assetNameBinDir => null,
            ],
        ];

        $stdOutput = trim($stdOutput);
        if (!$stdOutput) {
            return $return;
        }

        $lines = explode(PHP_EOL, $stdOutput);
        $nodeExecutable = end($lines) ?: null;

        $return['assets'][$assetNameNodeExecutable] = $nodeExecutable;
        $return['assets'][$assetNameBinDir] = $nodeExecutable ? Path::getDirectory($nodeExecutable) : null;

        return $return;
    }
}

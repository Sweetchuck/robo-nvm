<?php

namespace Sweetchuck\Robo\Nvm\OutputParser;

use Webmozart\PathUtil\Path;

class WhichOutputParser extends ParserBase
{
    /**
     * {@inheritdoc}
     */
    protected $assetNameMapping = [
        'binDir' => 'nvm.which.binDir',
        'nodeExecutable' => 'nvm.which.nodeExecutable',
    ];

    /**
     * {@inheritdoc}
     */
    public function parse(int $exitCode, string $stdOutput, string $stdError): array
    {
        if ($exitCode !== 0) {
            return [];
        }

        $assetNameNodeExecutable = $this->getExternalAssetName('nodeExecutable');
        $assetNameBinDir = $this->getExternalAssetName('binDir');

        $nodeExecutable = trim($stdOutput);

        return [
            'assets' => [
                $assetNameNodeExecutable => $nodeExecutable,
                $assetNameBinDir => Path::getDirectory($nodeExecutable),
            ],
        ];
    }
}

<?php

namespace Sweetchuck\Robo\Nvm\OutputParser;

use Webmozart\PathUtil\Path;

class WhichOutputParser extends ParserBase
{
    /**
     * {@inheritdoc}
     */
    protected $assetNameMapping = [
        'nodeExecutable' => 'nvm.which.nodeExecutable',
        'binDir' => 'nvm.which.binDir',
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

        $return = [
            'assets' => [
                $assetNameNodeExecutable => null,
                $assetNameBinDir => null,
            ],
        ];

        $nodeExecutable = trim($stdOutput);
        if (!$nodeExecutable) {
            return $return;
        }

        $return['assets'][$assetNameNodeExecutable] = $nodeExecutable;
        $return['assets'][$assetNameBinDir] = Path::getDirectory($nodeExecutable);

        return $return;
    }
}

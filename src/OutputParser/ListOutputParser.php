<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm\OutputParser;

class ListOutputParser extends ParserBase
{
    protected array $assetNameMapping = [
        'current' => 'nvm.list.current',
        'versions' => 'nvm.list.versions',
    ];

    public function parse(int $exitCode, string $stdOutput, string $stdError): array
    {
        if ($exitCode !== 0) {
            return [];
        }

        $assetNameCurrent = $this->getExternalAssetName('current');
        $assetNameVersions = $this->getExternalAssetName('versions');
        $return = [
            'assets' => [
                $assetNameCurrent => null,
                $assetNameVersions => [],
            ],
        ];

        $pattern = '/^(?P<current>->){0,1}\s+(?P<versions>[^\s]+)/smiu';
        $matches = [];
        preg_match_all($pattern, $stdOutput, $matches);
        if (empty($matches['versions'])) {
            return $return;
        }

        $return['assets'][$assetNameVersions] = $matches['versions'];
        if (!empty($matches['current'])) {
            $index = array_search('->', $matches['current']);
            if ($index !== false) {
                $return['assets'][$assetNameCurrent] = $matches['versions'][$index];
            }
        }

        return $return;
    }
}

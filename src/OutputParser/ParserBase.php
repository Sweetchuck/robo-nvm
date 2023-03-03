<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm\OutputParser;

use Sweetchuck\Robo\Nvm\OutputParserInterface;

abstract class ParserBase implements OutputParserInterface
{

    // region assetNameMapping
    protected array $assetNameMapping = [];

    public function getAssetNameMapping(): array
    {
        return $this->assetNameMapping;
    }

    public function setAssetNameMapping(array $value): static
    {
        $this->assetNameMapping = $value;

        return $this;
    }
    // endregion

    protected function getExternalAssetName(string $internalAssetName): string
    {
        return $this->assetNameMapping[$internalAssetName];
    }

    abstract public function parse(int $exitCode, string $stdOutput, string $stdError): array;
}

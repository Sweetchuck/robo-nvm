<?php

namespace Sweetchuck\Robo\Nvm;

interface OutputParserInterface
{
    public function getAssetNameMapping(): array;

    /**
     * @return $this
     */
    public function setAssetNameMapping(array $value);

    public function parse(int $exitCode, string $stdOutput, string $stdError): array;
}

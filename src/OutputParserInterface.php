<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm;

interface OutputParserInterface
{
    public function getAssetNameMapping(): array;

    public function setAssetNameMapping(array $value): static;

    public function parse(int $exitCode, string $stdOutput, string $stdError): array;
}

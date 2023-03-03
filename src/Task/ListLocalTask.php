<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm\Task;

use Sweetchuck\Robo\Nvm\OutputParser\ListOutputParser;

class ListLocalTask extends BaseCliTask
{
    protected string $taskName = 'NVM - List local';

    protected string $outputParserClass = ListOutputParser::class;

    protected array $outputParserAssetNameMapping = [
        'current' => 'nvm.listLocal.current',
        'versions' => 'nvm.listLocal.versions',
    ];

    protected function initOptions(): static
    {
        parent::initOptions();
        $this->options['command']['value'] = 'ls';
        $this->options['noColors'] = [
            'type' => 'option:flag',
            'cliName' => 'no-colors',
            'value' => true,
        ];

        return $this;
    }

    protected function runInitAssets(): static
    {
        parent::runInitAssets();
        $this->assets['nvm.listLocal.versions'] = null;

        return $this;
    }
}

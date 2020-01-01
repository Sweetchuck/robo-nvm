<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm\Task;

use Sweetchuck\Robo\Nvm\OutputParser\ListOutputParser;

class ListLocalTask extends BaseCliTask
{
    /**
     * {@inheritdoc}
     */
    protected $taskName = 'NVM - List local';

    /**
     * {@inheritdoc}
     */
    protected $outputParserClass = ListOutputParser::class;

    /**
     * {@inheritdoc}
     */
    protected $outputParserAssetNameMapping = [
        'current' => 'nvm.listLocal.current',
        'versions' => 'nvm.listLocal.versions',
    ];

    /**
     * {@inheritdoc}
     */
    protected function initOptions()
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

    protected function runInitAssets()
    {
        parent::runInitAssets();
        $this->assets['nvm.listLocal.versions'] = null;

        return $this;
    }
}

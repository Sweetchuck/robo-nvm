<?php

namespace Sweetchuck\Robo\Nvm\Task;

use Sweetchuck\Robo\Nvm\OutputParser\WhichOutputParser;

class WhichTask extends BaseCliTask
{
    /**
     * {@inheritdoc}
     */
    protected $taskName = 'NVM - Which';

    /**
     * {@inheritdoc}
     */
    protected $outputParserClass = WhichOutputParser::class;

    /**
     * {@inheritdoc}
     */
    protected function initOptions()
    {
        parent::initOptions();
        $this->options['command']['value'] = 'which';

        return $this;
    }

    protected function runInitAssets()
    {
        parent::runInitAssets();
        $this->assets['nvm.which.binDir'] = null;
        $this->assets['nvm.which.nodeExecutable'] = null;

        return $this;
    }
}

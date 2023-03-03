<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm\Task;

use Sweetchuck\Robo\Nvm\OutputParser\WhichOutputParser;

class WhichTask extends BaseCliTask
{
    protected string $taskName = 'NVM - Which';

    protected string $outputParserClass = WhichOutputParser::class;

    protected function initOptions(): static
    {
        parent::initOptions();
        $this->options['command']['value'] = 'which';

        return $this;
    }

    protected function runInitAssets(): static
    {
        parent::runInitAssets();
        $this->assets['nvm.which.nodeExecutable'] = null;
        $this->assets['nvm.which.binDir'] = null;

        return $this;
    }
}

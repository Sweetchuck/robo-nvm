<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm\Tests\Helper\Dummy;

use Sweetchuck\Robo\Nvm\NvmShFinderInterface;

class DummyNvmShFinder implements NvmShFinderInterface
{

    protected string $nvmSh = '';

    public function __construct(string $nvmSh)
    {
        $this->nvmSh = $nvmSh;
    }

    public function find(): string
    {
        return $this->nvmSh;
    }
}

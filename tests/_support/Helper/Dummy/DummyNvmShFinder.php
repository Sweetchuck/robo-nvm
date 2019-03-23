<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm\Test\Helper\Dummy;

use Sweetchuck\Robo\Nvm\NvmShFinderInterface;

class DummyNvmShFinder implements NvmShFinderInterface
{

    /**
     * @var string
     */
    protected $nvmSh = '';

    public function __construct(string $nvmSh)
    {
        $this->nvmSh = $nvmSh;
    }

    /**
     * {@inheritdoc}
     */
    public function find(): string
    {
        return $this->nvmSh;
    }
}

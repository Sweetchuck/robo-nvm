<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm;

interface NvmShFinderInterface
{
    public function find(): string;
}

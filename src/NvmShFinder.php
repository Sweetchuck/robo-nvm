<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm;

use Symfony\Component\Filesystem\Filesystem;

class NvmShFinder implements NvmShFinderInterface
{

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $fs;

    public function __construct(? Filesystem $fs = null)
    {
        $this->fs = $fs ?: new Filesystem();
    }

    public function find(): string
    {
        foreach ($this->getCandidates() as $candidate) {
            if ($this->fs->exists($candidate)) {
                return $candidate;
            }
        }

        return '';
    }

    /**
     * @return string[]
     */
    protected function getCandidates(): array
    {
        $candidates = [];

        $dir = getenv('NVM_DIR');
        if ($dir) {
            $candidates[] = "$dir/nvm.sh";
        }

        $dir = getenv('NVM_HOME');
        if ($dir) {
            $candidates[] = "$dir/nvm.sh";
        }

        $dir = getenv('XDG_CONFIG_HOME');
        if ($dir) {
            $candidates[] = "$dir/.nvm/nvm.sh";
        }

        $dir = getenv('HOME');
        if ($dir) {
            $candidates[] = "$dir/.nvm/nvm.sh";
        }

        $candidates[] = '/usr/local/opt/nvm/nvm.sh';

        return $candidates;
    }
}

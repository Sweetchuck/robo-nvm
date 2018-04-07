<?php

namespace Sweetchuck\Robo\Nvm\Test\Helper\RoboFiles;

use Robo\Collection\CollectionBuilder;
use Robo\Tasks;
use Sweetchuck\Robo\Nvm\NvmTaskLoader;
use Robo\State\Data as RoboStateData;
use Symfony\Component\Yaml\Yaml;
use Webmozart\PathUtil\Path;

class NvmRoboFile extends Tasks
{
    use NvmTaskLoader;

    /**
     * @var string
     */
    protected $composerBinDir = 'bin';

    /**
     * @command nvm:list:local
     */
    public function listLocal(array $args): CollectionBuilder
    {
        $rootDir = $this->getRoboNvmRootDir();

        return $this
            ->collectionBuilder()
            ->addTask(
                $this
                    ->taskNvmListLocalTask()
                    ->setNvmExecutable("$rootDir/{$this->composerBinDir}/nvm")
                    ->setArguments($args)
            )
            ->addCode(function (RoboStateData $data) {
                $assets = $data->getData();
                unset($assets['time']);

                $this
                    ->output()
                    ->write(Yaml::dump($assets, 99));
            });
    }

    protected function getRoboNvmRootDir(): string
    {
        return Path::makeRelative(__DIR__ . '/../../../..', getcwd());
    }

    protected function getNvmExecutable(string $workingDirectory = ''): string
    {
        $rootDir = $this->getRoboNvmRootDir();

        return Path::makeRelative("$rootDir/{$this->composerBinDir}/Nvm", $workingDirectory);
    }
}

<?php

namespace Sweetchuck\Robo\Nvm\Tests\Unit\Task;

use Codeception\Test\Unit;
use Robo\Application;
use Robo\Robo;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcess;
use Sweetchuck\Robo\Nvm\Test\Helper\Dummy\DummyProcessHelper;
use Symfony\Component\Console\Helper\HelperSet;

abstract class BaseCliTaskTestBase extends Unit
{
    /**
     * @var \Sweetchuck\Robo\Nvm\Test\UnitTester
     */
    protected $tester;

    /**
     * @var \Sweetchuck\Robo\Nvm\Task\BaseCliTask
     */
    protected $task;

    protected $originalContainer;

    protected $container;

    // @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    public function _before()
    {
        parent::_before();
        $this
            ->backupContainer()
            ->initContainer()
            ->initTask();
    }

    protected function _after()
    {
        $this->restoreContainer();
        parent::_after();
    }
    //phpcs:enable PSR2.Methods.MethodDeclaration.Underscore

    protected function backupContainer()
    {
        $this->originalContainer = Robo::hasContainer() ? Robo::getContainer() : null;
        if ($this->originalContainer) {
            Robo::unsetContainer();
        }

        return $this;
    }

    protected function initContainer()
    {
        $this->container = Robo::createDefaultContainer();

        $application = new Application('RoboNvmTest', '1.0.0');
        $application->setHelperSet(new HelperSet(['process' => new DummyProcessHelper()]));
        $this->container->add('application', $application);

        return $this;
    }

    protected function restoreContainer()
    {
        if ($this->originalContainer) {
            Robo::setContainer($this->originalContainer);

            return $this;
        }

        Robo::unsetContainer();

        return $this;
    }

    /**
     * @return $this
     */
    abstract protected function initTask();

    abstract public function casesGetCommand(): array;

    /**
     * @dataProvider casesGetCommand
     */
    public function testGetCommand(string $expected, array $options)
    {
        $options += [
            'nvmShFilePath' => '/home/me/.nvm/nvm.sh',
        ];
        $this->task->setOptions($options);

        $this->tester->assertEquals($expected, $this->task->getCommand());
    }

    abstract public function casesRunSuccess(): array;

    /**
     * @dataProvider casesRunSuccess
     */
    public function testRunSuccess(array  $expected, array $processProphecy, array $options = []): void
    {
        $instanceIndex = count(DummyProcess::$instances);
        DummyProcess::$prophecy[$instanceIndex] = $processProphecy;

        $this->task->setContainer($this->container);
        $result = $this->task
            ->setOptions($options)
            ->run();

        if (array_key_exists('exitCode', $expected)) {
            $this->assertEquals($expected['exitCode'], $result->getExitCode());
        }

        if (array_key_exists('message', $expected)) {
            $this->assertEquals($expected['message'], $result->getMessage());
        }

        if (array_key_exists('assets', $expected)) {
            $assets = $result->getData();
            foreach ($expected['assets'] as $assetName => $assetValue) {
                $this->assertEquals($assetValue, $assets[$assetName]);
            }
        }
    }
}

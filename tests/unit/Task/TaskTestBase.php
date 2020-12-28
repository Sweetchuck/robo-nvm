<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm\Tests\Unit\Task;

use Codeception\Test\Unit;
use League\Container\Container as LeagueContainer;
use Robo\Application as RoboApplication;
use Robo\Collection\CollectionBuilder;
use Robo\Config\Config;
use Robo\Robo;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyOutput;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcess;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcessHelper;
use Sweetchuck\Robo\Nvm\Test\Helper\Dummy\DummyTaskBuilder;
use Symfony\Component\ErrorHandler\BufferingLogger;

abstract class TaskTestBase extends Unit
{
    /**
     * @var \Sweetchuck\Robo\Nvm\Test\UnitTester
     */
    protected $tester;

    /**
     * @var \League\Container\ContainerInterface
     */
    protected $container;

    /**
     * @var \Robo\Config\Config
     */
    protected $config;

    /**
     * @var \Robo\Collection\CollectionBuilder
     */
    protected $builder;

    /**
     * @var \Sweetchuck\Robo\Nvm\Test\Helper\Dummy\DummyTaskBuilder
     */
    protected $taskBuilder;

    /**
     * @var \Sweetchuck\Robo\Nvm\Task\BaseCliTask
     */
    protected $task;

    public function _before()
    {
        parent::_before();

        Robo::unsetContainer();
        DummyProcess::reset();

        $this->container = new LeagueContainer();
        $application = new RoboApplication('Sweetchuck - Robo PHPUnit', '1.0.0');
        $application->getHelperSet()->set(new DummyProcessHelper(), 'process');
        $this->config = new Config();
        $input = null;
        $output = new DummyOutput([
            'verbosity' => DummyOutput::VERBOSITY_DEBUG,
        ]);

        $this->container->add('container', $this->container);

        Robo::configureContainer($this->container, $application, $this->config, $input, $output);
        $this->container->share('logger', BufferingLogger::class);

        $this->builder = CollectionBuilder::create($this->container, null);
        $this->taskBuilder = new DummyTaskBuilder();
        $this->taskBuilder->setContainer($this->container);
        $this->taskBuilder->setBuilder($this->builder);

        $this->initTask();
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

        $result = $this
            ->task
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

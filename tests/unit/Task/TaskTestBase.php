<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm\Tests\Unit\Task;

use Codeception\Lib\Notification;
use Codeception\Stub;
use Codeception\Test\Unit;
use League\Container\Container as LeagueContainer;
use PHPUnit\Framework\SkippedTestSuiteError;
use Psr\Container\ContainerInterface;
use Robo\Application as RoboApplication;
use Robo\Collection\CollectionBuilder;
use Robo\Config\Config as RoboConfig;
use Robo\Robo;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyOutput;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcess;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcessHelper;
use Sweetchuck\Robo\Nvm\Tests\Helper\Dummy\DummyTaskBuilder;
use Sweetchuck\Robo\Nvm\Tests\UnitTester;

abstract class TaskTestBase extends Unit
{
    protected UnitTester $tester;

    protected ContainerInterface $container;

    protected RoboConfig $config;

    protected CollectionBuilder $builder;

    protected DummyTaskBuilder $taskBuilder;

    public function _before()
    {
        parent::_before();

        Robo::unsetContainer();
        DummyProcess::reset();

        $this->container = new LeagueContainer();
        $application = new RoboApplication('Sweetchuck - Robo NVM', '3.0.0');
        $application->getHelperSet()->set(new DummyProcessHelper(), 'process');
        $this->config = new RoboConfig();
        $input = null;
        $output = new DummyOutput([
            'verbosity' => DummyOutput::VERBOSITY_DEBUG,
        ]);

        Robo::configureContainer($this->container, $application, $this->config, $input, $output);
        Robo::finalizeContainer($this->container);

        $this->builder = CollectionBuilder::create($this->container, null);
        $this->taskBuilder = new DummyTaskBuilder();
        $this->taskBuilder->setContainer($this->container);
        $this->taskBuilder->setBuilder($this->builder);
        $this->taskBuilder->setOutput($output);
        $this->taskBuilder->setLogger($this->container->get('logger'));
    }

    protected function _after()
    {
        Robo::unsetContainer();
    }

    protected function createTask(array $properties = []): CollectionBuilder
    {
        return $this->createTaskInstance();
        $cb = $this->createTaskInstance();

        $task = $cb->getCollectionBuilderCurrentTask();

        $output = $this->container->get('output');

        $properties += [
            'processClass' => DummyProcess::class,
            'container' => $this->container,
        ];

        /** @var \Sweetchuck\Robo\Nvm\Task\BaseCliTask $task */
        $task = Stub::copy($task, $properties);
        $cb = Stub::copy(
            $cb,
            [
                'currentTask' => $task,
                'container' => $this->container,
            ],
        );

        $cb->setOutput($output);
        $cb->setContainer($this->container);
        $task->setOutput($output);

        return $task;
    }

    abstract protected function createTaskInstance(): CollectionBuilder;

    abstract public function casesGetCommand(): array;

    /**
     * @dataProvider casesGetCommand
     */
    public function testGetCommand(
        string $expected,
        array $options,
        array $taskProperties = []
    ): void {
        $options += [
            'nvmShFilePath' => '/home/me/.nvm/nvm.sh',
        ];
        $task = $this->createTask($taskProperties);
        $task->setOptions($options);

        $this->tester->assertSame($expected, $task->getCommand());
    }

    abstract public function casesRunSuccess(): array;

    /**
     * @dataProvider casesRunSuccess
     */
    public function testRunSuccess(
        array $expected,
        array $processProphecy,
        array $options = [],
        array $taskProperties = [],
    ): void {
        throw new SkippedTestSuiteError('@todo segmentation fault');

        $task = $this->createTask($taskProperties);

        $instanceIndex = count(DummyProcess::$instances);
        DummyProcess::$prophecy[$instanceIndex] = $processProphecy;

        $result = $task
            ->setOptions($options)
            ->run();

        if (array_key_exists('exitCode', $expected)) {
            $this->assertSame($expected['exitCode'], $result->getExitCode());
        }

        if (array_key_exists('message', $expected)) {
            $this->assertSame($expected['message'], $result->getMessage());
        }

        if (array_key_exists('assets', $expected)) {
            $assets = $result->getData();
            foreach ($expected['assets'] as $assetName => $assetValue) {
                $this->assertSame($assetValue, $assets[$assetName]);
            }
        }
    }
}

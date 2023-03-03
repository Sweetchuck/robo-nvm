<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm\Tests\Unit\Task;

use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;
use League\Container\Container as LeagueContainer;
use Psr\Container\ContainerInterface;
use Robo\Application as RoboApplication;
use Robo\Collection\CollectionBuilder;
use Robo\Config\Config as RoboConfig;
use Robo\Robo;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyOutput;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcess;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcessHelper;
use Sweetchuck\Robo\Nvm\Task\BaseTask;
use Sweetchuck\Robo\Nvm\Tests\Helper\Dummy\DummyTaskBuilder;
use Sweetchuck\Robo\Nvm\Tests\UnitTester;
use Symfony\Component\Console\Logger\ConsoleLogger;

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

    protected function createTask(): BaseTask
    {
        $container = new LeagueContainer();
        $application = new RoboApplication('Sweetchuck - Robo NVM', '3.0.0');
        $application->getHelperSet()->set(new DummyProcessHelper(), 'process');
        $config = new RoboConfig();
        $output = new DummyOutput([]);
        $loggerOutput = new DummyOutput([]);
        $logger = new ConsoleLogger($loggerOutput);

        $container->add('output', $output);
        $container->add('logger', $logger);
        $container->add('config', $config);
        $container->add('application', $application);

        $task = $this->createTaskInstance();
        $task->setContainer($container);
        $task->setOutput($output);
        $task->setLogger($logger);

        return $task;
    }

    abstract protected function createTaskInstance(): BaseTask;

    abstract public function casesGetCommand(): array;

    #[DataProvider('casesGetCommand')]
    public function testGetCommand(
        string $expected,
        array $options,
    ): void {
        $options += [
            'nvmShFilePath' => '/home/me/.nvm/nvm.sh',
        ];
        $task = $this->createTask();
        $task->setOptions($options);

        $this->tester->assertSame($expected, $task->getCommand());
    }

    abstract public function casesRunSuccess(): array;

    #[DataProvider('casesRunSuccess')]
    public function testRunSuccess(
        array $expected,
        array $processProphecy,
        array $options = [],
    ): void {
        DummyProcess::$prophecy[] = $processProphecy;

        $result = $this
            ->createTask()
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

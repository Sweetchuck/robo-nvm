<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm\Tests\Acceptance\Task;

use Codeception\Example;
use League\Container\Container as LeagueContainer;
use Robo\Collection\CollectionBuilder;
use Robo\Config\Config;
use Robo\Robo;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyOutput;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcess;
use Sweetchuck\Robo\Nvm\Test\AcceptanceTester;
use Sweetchuck\Robo\Nvm\Test\Helper\Dummy\DummyProcessHelper;
use Sweetchuck\Robo\Nvm\Test\Helper\Dummy\DummyTaskBuilder;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Debug\BufferingLogger;

abstract class TaskCestBase
{

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
     * @var \Sweetchuck\Robo\Nvm\Test\AcceptanceTester
     */
    protected $tester;

    public function _before(AcceptanceTester $I)
    {
        $this->tester = $I;
        Robo::unsetContainer();

        $this->container = new LeagueContainer();
        $application = new SymfonyApplication('MarvinIncubator - DrushUnit', '1.0.0');
        $this->config = (new Config());
        $input = null;
        $output = new DummyOutput([
            'verbosity' => DummyOutput::VERBOSITY_DEBUG,
        ]);

        $this->container->add('container', $this->container);

        Robo::configureContainer($this->container, $application, $this->config, $input, $output);
        $this->container->share('logger', BufferingLogger::class);

        /** @var \Robo\Application $app */
        $app = $this->container->get('application');
        $app->getHelperSet()->set(
            new DummyProcessHelper(),
            'process'
        );

        $this->builder = CollectionBuilder::create($this->container, null);
        $this->taskBuilder = new DummyTaskBuilder();
        $this->taskBuilder->setContainer($this->container);
        $this->taskBuilder->setBuilder($this->builder);
    }

    /**
     * @return \Sweetchuck\Robo\Nvm\Task\BaseCliTask|\Robo\Collection\CollectionBuilder
     */
    abstract protected function getTask(array $options = [], array $constructorArgs = []): CollectionBuilder;

    abstract protected function runSuccessCases(): array;

    /**
     * @dataProvider runSuccessCases
     */
    public function runSuccessTest(AcceptanceTester $I, Example $example): void
    {
        DummyProcess::$prophecy[] = $example['process'];

        $result = $this
            ->getTask(
                $example['options'] ?? [],
                $example['constructorArgs'] ?? []
            )
            ->run();

        if (array_key_exists('exitCode', $example['expected'])) {
            $I->assertSame($example['expected']['exitCode'], $result->getExitCode());
        }

        /** @var \Sweetchuck\Codeception\Module\RoboTaskRunner\DummyOutput $output */
        $output = $this->container->get('output');
        if (array_key_exists('stdOutput', $example['expected'])) {
            $I->assertSame(
                $example['expected']['stdOutput'],
                $output->output,
                'stdOutput'
            );
        }

        if (array_key_exists('stdError', $example['expected'])) {
            $I->assertSame(
                $example['expected']['stdError'],
                $output->getErrorOutput()->output,
                'stdError'
            );
        }

        if (array_key_exists('logs', $example['expected'])) {
            /** @var \Symfony\Component\Debug\BufferingLogger $logger */
            $logger = $this->container->get('logger');
            $this->assertRoboTaskLogEntries($example['expected']['logs'], $logger->cleanLogs());
        }

        if (!empty($example['expected']['assets'])) {
            foreach ($example['expected']['assets'] as $key => $value) {
                $I->assertSame($value, $result[$key], "Asset: '$key'");
            }
        }
    }

    protected function assertRoboTaskLogEntries(array $expected, array $actual)
    {
        $this->tester->assertSame(count($expected), count($actual), 'Number of log messages');

        foreach ($actual as $key => $log) {
            unset($log[2]['task']);
            $this->tester->assertSame(
                $expected[$key],
                $log,
                "Log message; key: '$key'"
            );
        }
    }
}

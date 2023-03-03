<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Nvm\Task;

use Consolidation\AnnotatedCommand\Output\OutputAwareInterface;
use Robo\Common\OutputAwareTrait;
use Robo\Contract\CommandInterface;
use Sweetchuck\Robo\Nvm\NvmShFinder;
use Sweetchuck\Robo\Nvm\NvmShFinderInterface;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Process\Process;

/**
 * @method string getNvmShFilePath()
 * @method $this  setNvmShFilePath(string $filePath)
 * @method string getNvmExecutable()
 * @method $this  setNvmExecutable(string $path)
 * @method array  getArguments()
 * @method $this  setArguments(array $arguments)
 */
abstract class BaseCliTask extends BaseTask implements CommandInterface, OutputAwareInterface
{
    use OutputAwareTrait;

    protected string $shell = '/bin/bash';

    protected array $cmdPattern = [];

    protected array $cmdArgs = [];

    protected string $command = '';

    protected array $optionGroupWeights = [
        'other' => 100,
    ];

    protected NvmShFinderInterface $nvmShFinder;

    public function __construct(?NvmShFinderInterface $nvmShFinder = null)
    {
        $this->nvmShFinder = $nvmShFinder ?: new NvmShFinder();
        parent::__construct();
    }

    protected function initOptions(): static
    {
        parent::initOptions();
        $this->options += [
            'nvmShFilePath' => [
                'type' => 'other',
                'value' => '',
            ],
            'command' => [
                'type' => 'other',
                'value' => '',
            ],
            'arguments' => [
                'type' => 'argument:multi',
                'value' => [],
                'weight' => 999,
            ],
        ];

        return $this;
    }

    public function addArgument(string $argument): static
    {
        $this->options['arguments']['value'][$argument] = true;

        return $this;
    }

    public function removeArgument(string $argument): static
    {
        unset($this->options['arguments']['value'][$argument]);

        return $this;
    }

    //region getCommand
    /**
     * {@inheritdoc}
     */
    public function getCommand()
    {
        return $this
            ->getCommandInit()
            ->getCommandChangeDirectory()
            ->getCommandPrefix()
            ->getCommandEnvironmentVariables()
            ->getCommandNvmExecutable()
            ->getCommandNvmCommand()
            ->getCommandNvmOptions()
            ->getCommandNvmArguments()
            ->getCommandBuild();
    }

    protected function getCommandInit(): static
    {
        $this->cmdPattern = [];
        $this->cmdArgs = [];

        return $this;
    }

    protected function getCommandChangeDirectory(): static
    {
        if ($this->options['workingDirectory']['value']) {
            $this->cmdPattern[] = 'cd %s &&';
            $this->cmdArgs[] = escapeshellarg($this->options['workingDirectory']['value']);
        }

        return $this;
    }

    protected function getCommandPrefix(): static
    {
        return $this;
    }

    protected function getCommandEnvironmentVariables(): static
    {
        return $this;
    }

    protected function getCommandNvmExecutable(): static
    {
        $nvmShFilePath = $this->options['nvmShFilePath']['value'] ?: $this->nvmShFinder->find();
        if ($nvmShFilePath) {
            $this->cmdPattern[] = '. %s;';
            $this->cmdArgs[] = escapeshellarg($nvmShFilePath);
        }

        $this->cmdPattern[] = 'nvm';

        return $this;
    }

    protected function getCommandNvmCommand(): static
    {
        if ($this->options['command']['value']) {
            $this->cmdPattern[] = '%s';
            $this->cmdArgs[] = $this->options['command']['value'];
        }

        return $this;
    }

    protected function getCommandNvmOptions(): static
    {
        foreach ($this->options as $optionName => $option) {
            $optionCliName = $option['cliName'];
            switch ($option['type']) {
                case 'option:flag':
                    if ($option['value']) {
                        $this->cmdPattern[] = "--$optionCliName";
                    }
                    break;

                case 'option:tri-state':
                    if ($option['value'] !== null) {
                        $this->cmdPattern[] = $option['value'] ? "--$optionCliName" : "--no-$optionCliName";
                    }
                    break;

                case 'option:value':
                    // @todo Handle empty strings or "0".
                    if ($option['value']) {
                        $this->cmdPattern[] = "--$optionCliName=%s";
                        $this->cmdArgs[] = escapeshellarg($option['value']);
                    }
                    break;

                case 'option:value:list':
                    $values = array_keys($option['value'], true, true);
                    if ($values) {
                        $separator = $option['separator'] ?? ',';
                        $this->cmdPattern[] = "--$optionCliName=%s";
                        $this->cmdArgs[] = escapeshellarg(implode($separator, $values));
                    }
                    break;

                case 'option:value:multi':
                    $values = array_keys($option['value'], true, true);
                    if ($values) {
                        $this->cmdPattern[] = str_repeat("--$optionCliName=%s", count($values));
                        foreach ($values as $value) {
                            $this->cmdArgs[] = escapeshellarg($value);
                        }
                    }
                    break;

                case 'argument:multi':
                    foreach (array_keys($option['value'], true, true) as $value) {
                        $this->cmdPattern[] = '%s';
                        $this->cmdArgs[] = escapeshellarg($value);
                    }
                    break;
            }
        }

        return $this;
    }

    protected function getCommandNvmArguments(): static
    {
        return $this;
    }

    protected function getCommandBuild(): string
    {
        return vsprintf(implode(' ', $this->cmdPattern), $this->cmdArgs);
    }
    //endregion

    protected function runInit(): static
    {
        $this->command = $this->getCommand();

        return $this;
    }

    protected function runHeader(): static
    {
        $this->printTaskInfo(
            'runs "<info>{command}</info>"',
            [
                'command' => $this->command,
            ]
        );

        return $this;
    }

    protected function runDoIt(): static
    {
        $processRunCallbackWrapper = function (string $type, string $data): void {
            $this->processRunCallback($type, $data);
        };

        $process = $this
            ->getProcessHelper()
            ->run(
                $this->output(),
                [
                    $this->shell,
                    '-c',
                    $this->command,
                ],
                null,
                $processRunCallbackWrapper,
            );

        $this->processExitCode = $process->getExitCode();
        $this->processStdOutput = $process->getOutput();
        $this->processStdError = $process->getErrorOutput();

        return $this;
    }

    protected function getProcessHelper(): ProcessHelper
    {
        // @todo Check that everything is available.
        return $this
            ->getContainer()
            ->get('application')
            ->getHelperSet()
            ->get('process');
    }

    protected function processRunCallback(string $type, string $data): void
    {
        switch ($type) {
            case Process::OUT:
                $this->output()->write($data);
                break;

            case Process::ERR:
                $this->printTaskError($data);
                break;
        }
    }
}

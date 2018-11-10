<?php

namespace Sweetchuck\Robo\Nvm\Task;

use Robo\Common\OutputAwareTrait;
use Robo\Contract\CommandInterface;
use Robo\Contract\OutputAwareInterface;
use Sweetchuck\Robo\Nvm\Utils;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Process\Process;

/**
 * @method string getNvmExecutable()
 * @method $this  setNvmExecutable(string $path)
 * @method array  getArguments()
 * @method $this  setArguments(array $arguments)
 */
abstract class BaseCliTask extends BaseTask implements CommandInterface, OutputAwareInterface
{
    use OutputAwareTrait;

    /**
     * @var array
     */
    protected $cmdPattern = [];

    /**
     * @var array
     */
    protected $cmdArgs = [];

    /**
     * @var string
     */
    protected $command = '';

    /**
     * @var array
     */
    protected $optionGroupWeights = [
        'other' => 100,
    ];

    protected function initOptions()
    {
        parent::initOptions();
        $this->options += [
            'nvmShFilePath' => [
                'type' => 'other',
                'value' => $this->getDefaultNvmShFilePath(),
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

    protected function getDefaultNvmShFilePath(): string
    {
        $nvmDir = getenv('NVM_DIR');
        if ($nvmDir) {
            return "$nvmDir/nvm.sh";
        }

        $home = getenv('HOME');
        if ($home) {
            return "$home/.nvm/nvm.sh";
        }

        return '';
    }

    /**
     * @return $this
     */
    public function addArgument(string $argument)
    {
        $this->options['arguments']['value'][$argument] = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeArgument(string $argument)
    {
        unset($this->options['arguments']['value'][$argument]);

        return $this;
    }

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

    /**
     * @return $this
     */
    protected function getCommandInit()
    {
        $this->cmdPattern = [];
        $this->cmdArgs = [];

        return $this;
    }

    /**
     * @return $this
     */
    protected function getCommandChangeDirectory()
    {
        if ($this->options['workingDirectory']['value']) {
            $this->cmdPattern[] = 'cd %s &&';
            $this->cmdArgs[] = escapeshellarg($this->options['workingDirectory']['value']);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function getCommandPrefix()
    {
        return $this;
    }

    protected function getCommandEnvironmentVariables()
    {
        return $this;
    }

    /**
     * @return $this
     */
    protected function getCommandNvmExecutable()
    {
        $nvmShFilePath = $this->options['nvmShFilePath']['value'];
        $this->cmdPattern[] = '. %s; nvm';
        $this->cmdArgs[] = escapeshellarg($nvmShFilePath);

        return $this;
    }

    /**
     * @return $this
     */
    protected function getCommandNvmCommand()
    {
        if ($this->options['command']['value']) {
            $this->cmdPattern[] = '%s';
            $this->cmdArgs[] = $this->options['command']['value'];
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function getCommandNvmOptions()
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
                    $values = Utils::filterEnabled($option['value']);
                    if ($values) {
                        $separator = $option['separator'] ?? ',';
                        $this->cmdPattern[] = "--$optionCliName=%s";
                        $this->cmdArgs[] = escapeshellarg(implode($separator, $values));
                    }
                    break;

                case 'option:value:multi':
                    $values = Utils::filterEnabled($option['value']);
                    if ($values) {
                        $this->cmdPattern[] = str_repeat("--$optionCliName=%s", count($values));
                        foreach ($values as $value) {
                            $this->cmdArgs[] = escapeshellarg($value);
                        }
                    }
                    break;

                case 'argument:multi':
                    foreach (Utils::filterEnabled($option['value']) as $value) {
                        $this->cmdPattern[] = '%s';
                        $this->cmdArgs[] = escapeshellarg($value);
                    }
                    break;
            }
        }

        return $this;
    }

    protected function getCommandNvmArguments()
    {
        return $this;
    }

    protected function getCommandBuild(): string
    {
        return vsprintf(implode(' ', $this->cmdPattern), $this->cmdArgs);
    }

    /**
     * {@inheritdoc}
     */
    protected function runInit()
    {
        $this->command = $this->getCommand();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function runHeader()
    {
        $this->printTaskInfo(
            'runs "<info>{command}</info>"',
            [
                'command' => $this->command,
            ]
        );

        return $this;
    }

    protected function runValidate()
    {
        parent::runValidate();
        $this->runValidateNvmExecutable();

        return $this;
    }

    /**
     * @return $this
     */
    protected function runValidateNvmExecutable()
    {
        if (empty($this->options['nvmShFilePath']['value'])) {
            throw new \InvalidArgumentException('@todo', 1);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function runDoIt()
    {
        $processRunCallbackWrapper = function (string $type, string $data): void {
            $this->processRunCallback($type, $data);
        };

        $process = $this
            ->getProcessHelper()
            ->run($this->output(), $this->command, null, $processRunCallbackWrapper);

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

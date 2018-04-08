<?php

namespace Sweetchuck\Robo\Nvm\Task;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Result;
use Robo\Task\BaseTask as RoboBaseTask;
use Robo\TaskInfo;
use Stringy\StaticStringy;
use Sweetchuck\Robo\Nvm\Comparer\ArrayComparer;
use Sweetchuck\Robo\Nvm\OutputParserInterface;

/**
 * @method string getAssetNamePrefix()
 * @method $this  setAssetNamePrefix(string $prefix)
 * @method string getWorkingDirectory()
 * @method $this  setWorkingDirectory(string $path)
 */
abstract class BaseTask extends RoboBaseTask implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var string
     */
    protected $taskName = 'NVM';

    /**
     * @var array
     */
    protected $assets = [];

    /**
     * @var int
     */
    protected $processExitCode = 0;

    /**
     * @var string
     */
    protected $processStdOutput = '';

    /**
     * @var string
     */
    protected $processStdError = '';

    /**
     * @var \Sweetchuck\Robo\Nvm\OutputParserInterface
     */
    protected $outputParser;

    /**
     * @var string
     */
    protected $outputParserClass = '';

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $outputParserAssetNameMapping = [];

    public function __construct()
    {
        $this
            ->initOptions()
            ->expandOptions();
    }

    /**
     * @return $this
     */
    protected function initOptions()
    {
        $this->options += [
            'workingDirectory' => [
                'type' => 'other',
                'value' => '',
            ],
            'assetNamePrefix' => [
                'type' => 'other',
                'value' => '',
            ],
        ];

        return $this;
    }

    protected function expandOptions()
    {
        foreach (array_keys($this->options) as $optionName) {
            $this->options[$optionName]['name'] = $optionName;
            if (!array_key_exists('cliName', $this->options[$optionName])) {
                $this->options[$optionName]['cliName'] = StaticStringy::dasherize($optionName);
            }
        }
        uasort($this->options, new ArrayComparer([
            'weight' => 500,
            'name' => '',
        ]));

        return $this;
    }

    public function __call(string $name, array $arguments)
    {
        $matches = [];
        if (!preg_match('/^(?P<action>get|set)[A-Z]/u', $name, $matches)) {
            throw new \InvalidArgumentException("@todo $name");
        }

        $method = $this->parseMethodName($name);
        if (!$method || !isset($this->options[$method['optionName']])) {
            throw new \InvalidArgumentException("@todo $name");
        }

        $optionName = $method['optionName'];
        switch ($method['action']) {
            case 'get':
                return $this->options[$optionName]['value'];

            case 'set':
                if (count($arguments) !== 1) {
                    throw new \InvalidArgumentException("The '$name' method has to be called with 1 argument.");
                }

                $value = reset($arguments);

                if (is_array($this->options[$optionName]['value'])) {
                    if (gettype(reset($value)) !== 'boolean') {
                        $value = array_fill_keys($value, true);
                    }
                }

                $this->options[$optionName]['value'] = $value;

                return $this;
        }

        throw new \Exception("Action not supported: $name");
    }

    protected function parseMethodName(string $methodName): ?array
    {
        $actions = [
            preg_quote('get'),
            preg_quote('set'),
        ];

        $pattern = '/^(?P<action>' . implode('|', $actions) . ')(?P<optionName>[A-Z].*)/';
        $matches = [];
        if (!preg_match($pattern, $methodName, $matches)) {
            return null;
        }

        $firstChar = mb_substr($matches['optionName'], 0, 1);

        return [
            'action' => $matches['action'],
            'optionName' => mb_strtolower($firstChar) . mb_substr($matches['optionName'], 1),
        ];
    }

    protected function getOutputParser(): ?OutputParserInterface
    {
        if (!$this->outputParser && $this->outputParserClass) {
            $this->outputParser = new $this->outputParserClass();
            if ($this->outputParserAssetNameMapping) {
                $this->outputParser->setAssetNameMapping($this->outputParserAssetNameMapping);
            }
        }

        return $this->outputParser;
    }

    /**
     * @return $this
     */
    public function setOptions(array $options)
    {
        foreach ($options as $optionName => $value) {
            if (!isset($this->options[$optionName])) {
                // @todo Debug log.
                continue;
            }

            $methodName = 'set' . mb_strtoupper(mb_substr($optionName, 0, 1)) . mb_substr($optionName, 1);
            if (method_exists($this, $methodName)) {
                // @todo And the method is public
                $this->{$methodName}($value);

                continue;
            }

            $this->__call($methodName, [$value]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        return $this
            ->runInit()
            ->runHeader()
            ->runValidate()
            ->runDoIt()
            ->runInitAssets()
            ->runProcessOutputs()
            ->runReturn();
    }

    /**
     * @return $this
     */
    protected function runInit()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function runValidate()
    {
        return $this;
    }

    /**
     * @return $this
     */
    protected function runHeader()
    {
        return $this;
    }

    /**
     * @return $this
     */
    abstract protected function runDoIt();

    /**
     * @return $this
     */
    protected function runInitAssets()
    {
        $this->assets = [];

        return $this;
    }

    /**
     * @return $this
     */
    protected function runProcessOutputs()
    {
        $outputParser = $this->getOutputParser();
        if ($outputParser) {
            // @todo ExitCode control.
            $result = $outputParser->parse($this->processExitCode, $this->processStdOutput, $this->processStdError);
            if (isset($result['assets'])) {
                $this->assets = $result['assets'];
            }
        }

        return $this;
    }

    protected function runReturn(): Result
    {
        return new Result(
            $this,
            $this->getTaskResultCode(),
            $this->getTaskResultMessage(),
            $this->getAssetsWithPrefixedNames()
        );
    }

    protected function getTaskResultCode(): int
    {
        return $this->processExitCode;
    }

    protected function getTaskResultMessage(): string
    {
        return $this->processStdError;
    }

    protected function getAssetsWithPrefixedNames(): array
    {
        $prefix = $this->getAssetNamePrefix();
        if (!$prefix) {
            return $this->assets;
        }

        $assets = [];
        foreach ($this->assets as $key => $value) {
            $assets["{$prefix}{$key}"] = $value;
        }

        return $assets;
    }

    public function getTaskName(): string
    {
        return $this->taskName ?: TaskInfo::formatTaskName($this);
    }

    /**
     * The array key is the relevant value and the array value will be a boolean.
     *
     * @param string[]|bool[] $items
     *   Items.
     * @param bool $include
     *   Default value.
     *
     * @return bool[]
     *   Key is the relevant value, the value is a boolean.
     *
     * @todo Move to Utils.
     */
    protected function createIncludeList(array $items, bool $include): array
    {
        $item = reset($items);
        if (gettype($item) !== 'boolean') {
            $items = array_fill_keys($items, $include);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTaskContext($context = null)
    {
        if (!$context) {
            $context = [];
        }

        if (empty($context['name'])) {
            $context['name'] = $this->getTaskName();
        }

        return parent::getTaskContext($context);
    }
}

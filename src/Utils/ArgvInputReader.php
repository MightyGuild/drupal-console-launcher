<?php

namespace Drupal\Console\Utils;

use Symfony\Component\Console\Input\ArgvInput;

/**
 * Class ArgvInputReader
 * @package Drupal\Console\Utils
 */
class ArgvInputReader
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var
     */
    protected $input;

    /**
     * ArgvInputReader constructor.
     */
    public function __construct()
    {
        $this->options = [];
        $this->setOptionsFromPlaceHolders();
        $this->readArgvInputValues();
    }

    /**
     * @param array $targetConfig
     */
    public function setOptionsFromTargetConfiguration($targetConfig)
    {
        $options = [];
        if (array_key_exists('root', $targetConfig)) {
            $options['root'] = $targetConfig['root'];
        }
        if (array_key_exists('uri', $targetConfig)) {
            $options['uri'] = $targetConfig['uri'];
        }

        if (array_key_exists('remote', $targetConfig)) {
            $this->set('remote', true);
        }

        $this->setArgvOptions($options);
    }

    /**
     * @param array $options
     */
    public function setOptionsFromConfiguration($options)
    {
        $this->setArgvOptions($options);
    }

    /**
     * @param $options
     */
    private function setArgvOptions($options)
    {
        foreach ($options as $key => $option) {
            if ($option == 1) {
                $_SERVER['argv'][] = sprintf('--%s', $key);
                continue;
            }
            if (!empty($option)) {
                $_SERVER['argv'][] = sprintf('--%s=%s', $key, $option);
            }
        }
        $this->readArgvInputValues();
    }

    /**
     * SetPlaceHolderAsOption
     */
    private function setOptionsFromPlaceHolders()
    {
        if (count($_SERVER['argv'])>2
            && stripos($_SERVER['argv'][1], '@')===0
            && stripos($_SERVER['argv'][2], '@')===0
        ) {
            $_SERVER['argv'][1] = sprintf(
                '--source=%s',
                substr($_SERVER['argv'][1], 1)
            );

            $_SERVER['argv'][2] = sprintf(
                '--target=%s',
                substr($_SERVER['argv'][2], 1)
            );

            return;
        }

        if (count($_SERVER['argv'])>1 && stripos($_SERVER['argv'][1], '@')===0) {
            $_SERVER['argv'][1] = sprintf(
                '--target=%s',
                substr($_SERVER['argv'][1], 1)
            );
        }
    }

    /**
     * ReadArgvInputValues.
     */
    private function readArgvInputValues()
    {
        $input = new ArgvInput();

        $source = $input->getParameterOption(['--source', '-s'], null);
        $target = $input->getParameterOption(['--target', '-t'], null);
        $root = $input->getParameterOption(['--root'], null);
        $root = (strpos($root, '/') === 0) ? $root : sprintf(
            '%s/%s',
            getcwd(),
            $root
        );
        $uri = $input->getParameterOption(['--uri', '-l']) ?: 'default';
        if ($uri && !preg_match('/^(http|https):\/\//', $uri)) {
            $uri = sprintf('http://%s', $uri);
        }

        $composer = $input->hasParameterOption(['--composer']);

        $this->set('command', $input->getFirstArgument());
        $this->set('root', $root);
        $this->set('uri', $uri);
        $this->set('source', $source);
        $this->set('target', $target);
        $this->set('composer', $composer);
    }

    /**
     * @param $option
     * @param $value
     */
    private function set($option, $value)
    {
        if ($value) {
            $this->options[$option] = $value;

            return;
        }

        if (!array_key_exists($option, $this->options)) {
            unset($this->options[$option]);
        }
    }

    /**
     * @param $option
     * @param null   $value
     * @return string
     */
    public function get($option, $value = null)
    {
        if (!array_key_exists($option, $this->options)) {
            return $value;
        }

        return $this->options[$option];
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->options;
    }
}
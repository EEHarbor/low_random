<?php

namespace Tests\Support {
    final class BridgeHelperCalls
    {
        public $calls = array();
        public $results = array();

        /**
         * Record a global helper boundary and return its configured native result.
         *
         * @param string $name
         * @param mixed $value
         * @return mixed
         */
        public function invoke($name, $value)
        {
            $this->calls[] = array($name, $value);
            return $this->results[$name];
        }
    }

    final class BridgeRegistry
    {
        public $loaded = array();
        public $calls = array();

        /**
         * Match EE Facade's loaded-name guard, including already-loaded null values.
         *
         * @param string $name
         * @param mixed $value
         * @return void
         * @throws \RuntimeException
         */
        public function set($name, $value)
        {
            $this->calls[] = array('set', $name, $value);

            if (array_key_exists($name, $this->loaded)) {
                throw new \RuntimeException('Cannot overwrite ' . $name . ' on the loader.');
            }

            $this->loaded[$name] = $value;
        }

        /**
         * Remove precisely the requested service as EE Facade::remove does.
         *
         * @param string $name
         * @return void
         */
        public function remove($name)
        {
            $this->calls[] = array('remove', $name);
            unset($this->loaded[$name]);
        }
    }

    final class BridgeFormatter
    {
        public $calls = array();
        public $slug = 'slug-result';
        public $urlResult = 'prepared-result';

        /**
         * Match the text formatter's fluent slug transformation boundary.
         *
         * @return self
         */
        public function urlSlug()
        {
            $this->calls[] = 'urlSlug';
            return $this;
        }

        /**
         * Return the configured compiled slug without duplicating EE formatting.
         *
         * @return string
         */
        public function compile()
        {
            $this->calls[] = 'compile';
            return $this->slug;
        }

        /**
         * Return the native formatter result for the supplied URL shape.
         *
         * @return string|self
         */
        public function url()
        {
            $this->calls[] = 'url';
            return $this->urlResult;
        }
    }

    final class BridgeFormatFactory
    {
        public $calls = array();
        public $formatters = array();

        /**
         * Create a distinct text formatter and retain its exact source input.
         *
         * @param string $type
         * @param string $content
         * @return BridgeFormatter
         */
        public function make($type, $content)
        {
            $this->calls[] = array($type, $content);
            $formatter = new BridgeFormatter();
            $this->formatters[] = $formatter;
            return $formatter;
        }
    }

    final class BridgeVariableParser
    {
        public $calls = array();
        public $parameters = array();
        public $variables = array();

        /**
         * Preserve the current parser's tag-source and defaults arguments.
         *
         * @param string $source
         * @param array $defaults
         * @return array
         */
        public function parseTagParameters($source, array $defaults = array())
        {
            $this->calls[] = array('parseTagParameters', $source, $defaults);
            return $this->parameters;
        }

        /**
         * Return the configured current parser variable structure.
         *
         * @param string $source
         * @return array
         */
        public function extractVariables($source)
        {
            $this->calls[] = array('extractVariables', $source);
            return $this->variables;
        }
    }

    final class BridgeTagFunctions
    {
        public $calls = array();
        public $parameters = array();
        public $variables = array();

        /**
         * Preserve the parser's source and defaults without reproducing its grammar.
         *
         * @param string $source
         * @param array $defaults
         * @return array
         */
        public function assign_parameters($source, array $defaults = array())
        {
            $this->calls[] = array('parameters', $source, $defaults);
            return $this->parameters;
        }

        /**
         * Preserve the variable parser's input and structured return value.
         *
         * @param string $source
         * @return array
         */
        public function assign_variables($source)
        {
            $this->calls[] = array('variables', $source);
            return $this->variables;
        }
    }
}

namespace {
    /**
     * Observe delegation to EE's URL title helper rather than emulate formatting.
     *
     * @param string $value
     * @return string
     */
    function url_title($value)
    {
        return ee('Test/BridgeHelpers')->invoke('url_title', $value);
    }

    /**
     * Observe URL preparation independently of the native formatter implementation.
     *
     * @param string $value
     * @return string
     */
    function prep_url($value)
    {
        return ee('Test/BridgeHelpers')->invoke('prep_url', $value);
    }

    /**
     * Observe the global string helper boundary after its required loader call.
     *
     * @param string $value
     * @return string
     */
    function reduce_double_slashes($value)
    {
        return ee('Test/BridgeHelpers')->invoke('reduce_double_slashes', $value);
    }
}

<?php

namespace Tests\Support;

final class Url
{
    public $path;
    public $variables;

    /**
     * Preserve the path and query variables supplied to EE's CP URL service.
     *
     * @param string $path
     * @param array $variables
     * @return void
     */
    public function __construct($path, array $variables = array())
    {
        $this->path = $path;
        $this->variables = $variables;
    }

    /**
     * Provide a stable compiled URL for breadcrumb and redirect assertions.
     *
     * @return string
     */
    public function compile()
    {
        return '/cp/' . $this->path . ($this->variables ? '?' . http_build_query($this->variables) : '');
    }

    /**
     * Match the URL service's string-conversion boundary.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->compile();
    }
}

final class UrlFactory
{
    public $calls = array();

    /**
     * Support EE's factory alias with and without an initial URL path.
     *
     * @param string|null $path
     * @return self|Url
     */
    public function __invoke($path = null)
    {
        return $path === null ? $this : $this->make($path);
    }

    /**
     * Build a distinct URL value and record its exact input contract.
     *
     * @param string $path
     * @param array $variables
     * @return Url
     */
    public function make($path, array $variables = array())
    {
        $this->calls[] = array($path, $variables);
        return new Url($path, $variables);
    }
}

final class RequestInput
{
    public $get = array();
    public $post = array();
    public $calls = array();

    /**
     * Match EE Input's missing/null result and preserve fixture value types.
     *
     * @param string $name
     * @param bool $xssClean
     * @return mixed
     */
    public function get($name, $xssClean = false)
    {
        $this->calls[] = array($name, $xssClean);
        return $this->get[$name] ?? false;
    }

    /**
     * Match the POST lookup boundary for already-clean scalar license fields.
     *
     * @param string $name
     * @param bool $xssClean
     * @return mixed
     */
    public function post($name, $xssClean = false)
    {
        $this->calls[] = array('post', $name, $xssClean);
        return $this->post[$name] ?? false;
    }

    /**
     * Match EE's POST-first lookup and GET fallback for missing/null POST values.
     *
     * @param string $name
     * @param bool $xssClean
     * @return mixed
     */
    public function get_post($name, $xssClean = false)
    {
        return isset($this->post[$name]) ? $this->post($name, $xssClean) : $this->get($name, $xssClean);
    }
}

final class SessionCache
{
    public $cache = array();

    /**
     * Read one request-cache item using EE's isset/default contract.
     *
     * @param string $class
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function cache($class, $key, $default = false)
    {
        return isset($this->cache[$class][$key]) ? $this->cache[$class][$key] : $default;
    }

    /**
     * Store one request-cache value and return the session object as EE does.
     *
     * @param string $class
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function set_cache($class, $key, $value)
    {
        $this->cache[$class][$key] = $value;
        return $this;
    }
}

final class CacheStore
{
    public $values = array();
    public $calls = array();

    /**
     * Read a persistent-cache value without coercing zero or false.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $this->calls[] = array('get', $key);
        return array_key_exists($key, $this->values) ? $this->values[$key] : false;
    }

    /**
     * Record the native cache save result and make the value observable.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function save($key, $value)
    {
        $this->calls[] = array('save', $key, $value);
        $this->values[$key] = $value;
        return true;
    }

    /**
     * Remove only the requested cache entry.
     *
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        $this->calls[] = array('delete', $key);
        unset($this->values[$key]);
        return true;
    }
}

final class ViewService
{
    public $calls = array();
    public $variables = array();

    /**
     * Record a namespaced view lookup without reading a production template.
     *
     * @param string $name
     * @return self
     */
    public function make($name)
    {
        $this->calls[] = array('make', $name);
        return $this;
    }

    /**
     * Record a string view lookup distinctly from a template name.
     *
     * @param string $source
     * @return self
     */
    public function makeFromString($source)
    {
        $this->calls[] = array('makeFromString', $source);
        return $this;
    }

    /**
     * Expose the complete view variables passed by the archive bridge.
     *
     * @param array $variables
     * @return string
     */
    public function render(array $variables)
    {
        $this->variables = $variables;
        return '<rendered-view>';
    }
}

final class Alert
{
    public $calls = array();

    /**
     * Record only the supported native fluent alert operations.
     *
     * @param string $method
     * @param array $arguments
     * @return self
     * @throws \RuntimeException
     */
    public function __call($method, array $arguments)
    {
        if (!in_array($method, array('asIssue', 'asWarning', 'asSuccess', 'withTitle', 'addToBody', 'canClose', 'cannotClose', 'defer'), true)) {
            throw new \RuntimeException('Unexpected alert operation: ' . $method);
        }

        $this->calls[] = array($method, $arguments);
        return $this;
    }

    /**
     * Expose a real optional method for the archive's method_exists guard.
     *
     * @return self
     */
    public function cannotClose()
    {
        return $this->__call('cannotClose', array());
    }
}

final class AlertFactory
{
    public $created = array();

    /**
     * Create a distinct alert using either the EE3 or current inline API.
     *
     * @param string $method
     * @param array $arguments
     * @return Alert
     * @throws \RuntimeException
     */
    public function __call($method, array $arguments)
    {
        if ($method !== 'make' && $method !== 'makeInline') {
            throw new \RuntimeException('Unexpected alert factory: ' . $method);
        }

        $alert = new Alert();
        $this->created[] = array($method, $arguments, $alert);
        return $alert;
    }
}

final class RedirectSignal extends \RuntimeException
{
    public $url;

    /**
     * Represent EE redirect's terminating control flow without exiting PHPUnit.
     *
     * @param mixed $url
     * @return void
     */
    public function __construct($url)
    {
        parent::__construct('EE redirect');
        $this->url = $url;
    }
}

final class RedirectFunctions
{
    public $redirects = array();

    /**
     * Preserve the native non-returning redirect boundary for controller tests.
     *
     * @param mixed $url
     * @return void
     * @throws RedirectSignal
     */
    public function redirect($url)
    {
        $this->redirects[] = $url;
        throw new RedirectSignal($url);
    }
}

final class PagerModel
{
    public $rows = array();
    public $calls = array();

    /**
     * Return a native countable collection without changing the original query.
     *
     * @return \ArrayObject
     */
    public function all()
    {
        $this->calls[] = array('all');
        return new \ArrayObject($this->rows);
    }

    /**
     * Apply the requested page size to the original model builder.
     *
     * @param int $limit
     * @return self
     */
    public function limit($limit)
    {
        $this->calls[] = array('limit', $limit);
        return $this;
    }

    /**
     * Apply the computed offset to the original model builder.
     *
     * @param int $offset
     * @return self
     */
    public function offset($offset)
    {
        $this->calls[] = array('offset', $offset);
        return $this;
    }
}

final class Pagination
{
    public $calls = array();

    /**
     * Record the native fluent pagination options and final rendering boundary.
     *
     * @param string $method
     * @param array $arguments
     * @return self|string
     * @throws \RuntimeException
     */
    public function __call($method, array $arguments)
    {
        if (!in_array($method, array('perPage', 'currentPage', 'displayPageLinks', 'queryStringVariable', 'render'), true)) {
            throw new \RuntimeException('Unexpected pagination operation: ' . $method);
        }

        $this->calls[] = array($method, $arguments);
        return $method === 'render' ? '<pagination>' : $this;
    }
}

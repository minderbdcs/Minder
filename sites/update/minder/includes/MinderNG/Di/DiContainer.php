<?php
/* @description     fork of Dice - A minimal Dependency Injection Container for PHP originally written by Tom Butler
 * @see             http://r.je/dice.html
 * @version         1.1.1+1
 */
namespace MinderNG\Di;

class DiContainer {
    private $rules = array();
    private $instances = array();
    /**
     * @var ReflectionManager
     */
    private $_manager;

    function __construct(ReflectionManager $manager = null)
    {
        $this->_manager = $manager;
    }

    public function assign($object) {
        $this->instances[strtolower(get_class($object))] = $object;
    }

    public function addRule($name, Rule $rule) {
        $rule->substitutions = array_change_key_case($rule->substitutions);
        $this->rules[strtolower(trim($name, '\\'))] = $rule;
    }

    public function getRule($name) {
        if (isset($this->rules[strtolower(trim($name, '\\'))])) return $this->rules[strtolower(trim($name, '\\'))];
        foreach ($this->rules as $key => $value) {
            if ($key !== '*' && is_subclass_of($name, $key) && $value->inherit == true) return $value;
        }
        return isset($this->rules['*']) ? $this->rules['*'] : new Rule;
    }

    public function create($component, array $args = array(), $callback = null, $forceNewInstance = false) {
        if ($component instanceof Instance) $component = $component->name;
        $component = trim($component, '\\');

        if (!isset($this->rules[strtolower($component)]) && !class_exists($component)) throw new \Exception('Class does not exist for creation: ' . $component);

        if (!$forceNewInstance && isset($this->instances[strtolower($component)])) return $this->instances[strtolower($component)];

        $rule = $this->getRule($component);
        $className = (!empty($rule->instanceOf)) ? $rule->instanceOf : $component;
        $share = $this->getParams($rule->shareInstances);
        $params = $this->getMethodParams($className, '__construct', $rule, array_merge($share, $args, $this->getParams($rule->constructParams)), $share);

        if (is_callable($callback, true)) call_user_func_array($callback, array($params));

        $object = $this->getManager()->newInstance($className, $params);
        if ($rule->shared == true) $this->instances[strtolower($component)] = $object;
        foreach ($rule->call as $call) call_user_func_array(array($object, $call[0]), $this->getMethodParams($className, $call[0], $rule, array_merge($this->getParams($call[1]), $args)));
        return $object;
    }

    private function getParams(array $params = array(),array $newInstances = array()) {
        for ($i = 0; $i < count($params); $i++) {
            if ($params[$i] instanceof Instance) $params[$i] = $this->create($params[$i]->name, array(), null, in_array(strtolower($params[$i]->name), array_map('strtolower', $newInstances)));
            else $params[$i] = ( !(is_array($params[$i]) && isset($params[$i][0]) && is_string($params[$i][0])) && is_callable($params[$i])) ? call_user_func($params[$i], $this) : $params[$i];
        }
        return $params;
    }

    private function getMethodParams($className, $method, Rule $rule, array $args = array(), array $share = array()) {
        if (!method_exists($className, $method)) return array();
        $params = $this->getManager()->reflectionMethod($className, $method);
        $parameters = array();
        foreach ($params as $param) {
            foreach ($args as $argName => $arg) {
                $paramClassName = $param['className'];
                if ($paramClassName && is_object($arg) && $arg instanceof $paramClassName) {
                    $parameters[] = $arg;
                    unset($args[$argName]);
                    continue 2;
                }
            }
            $paramClassName = $param['className'] ? strtolower($param['className']) : false;
            if ($paramClassName && isset($rule->substitutions[$paramClassName])) $parameters[] = is_string($rule->substitutions[$paramClassName]) ? new Instance($rule->substitutions[$paramClassName]) : $rule->substitutions[$paramClassName];
            else if ($paramClassName && class_exists($param['className'])) $parameters[] = $this->create($param['className'], $share, null, in_array($paramClassName, array_map('strtolower', $rule->newInstances)));
            else if (is_array($args) && count($args) > 0) $parameters[] = array_shift($args);
            else $parameters[] = $param['defaultValue'];
        }
        return $this->getParams($parameters, $rule->newInstances);
    }

    /**
     * @return ReflectionManager
     */
    public function getManager()
    {
        if (empty($this->_manager)) {
            $this->_manager = new ReflectionManager();
        }

        return $this->_manager;
    }
}
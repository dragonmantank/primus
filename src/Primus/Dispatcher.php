<?php

namespace Primus;

/**
 * A quick and dirty dispatcher based on Aura.Dispatcher
 * Using this cheap dispatcher since we need to support PHP 5.3. If we ever move to PHP 5.4 we should swap this out
 * with Aura.Dispatcher
 *
 * @package Primus
 */
class Dispatcher
{
    protected $methodParam = '';
    protected $objects = array();
    protected $objectParam = '';

    public function dispatch($object, $params)
    {
        // Get the method from the params
        $method = $params[$this->methodParam];
        // Invoke it
        if(is_callable(array($object, $method))) {
            $result = $object->$method();
        } else if($object instanceof \Closure) {
            $result = $object($params);
        } else if(is_object($object) && is_callable($object)) {
            $result = $object->__invoke($params);
        } else {
            return $object;
        }

        $this->dispatch($result, $params);
    }

    public function setMethodParam($methodParam)
    {
        $this->methodParam = $methodParam;
    }

    public function setObject($identifier, $object)
    {
        $this->objects[$identifier] = $object;
    }

    public function setObjectParam($objectParam)
    {
        $this->objectParam = $objectParam;
    }

    public function __invoke($params = array())
    {
        $identifier = $params[$this->objectParam];
        if(isset($this->objects[$identifier])) {
            $object = $this->objects[$identifier];
            $this->dispatch($object, $params);
        }
    }
}
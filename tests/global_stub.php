<?php

$globalStub = null;

class GlobalStub {
  private $methods = array();
  private $lastMethod = null;

  private $namespace = null;

  public function method($method) {
    $this->methods[$method] = array(
      'name' => $method,
      'return_value' => null
    );

    eval('  namespace '.$this->namespace.';
            function '.$method.' () {
              global $globalStub;
              $args = func_get_args();
              return $globalStub->getReturnValue("' . $method . '", $args);
            }
    ');

    $this->lastMethod = $method;

    return $this;
  }

  public function willReturn($method, $val = null) {
    if (!isset($val)) {
      $val = $method;
      $method = $this->lastMethod;
    }

    if ($method == null) {
      throw new \Exception("Method must be defined or function must be chained", 1);
    }

    $this->methods[$method]['return_value'] = $val;

    return $this;
  }

  public function getReturnValue($method, $args) {
    return $this->methods[$method]['return_value'];
  }

  public function setNamespace($namespace) {
    $this->namespace = $namespace;
  }


}

$globalStub = new GlobalStub();

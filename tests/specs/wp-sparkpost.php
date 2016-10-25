<?php
namespace WPSparkPost;

class TestSparkPost extends \WP_UnitTestCase {
  public function invokeMethod(&$object, $methodName, array $parameters = array())
  {
    $reflection = new \ReflectionClass(get_class($object));
    $method = $reflection->getMethod($methodName);
    $method->setAccessible(true);

    return $method->invokeArgs($object, $parameters);
  }

  public function getProperty($object, $property) {
    $reflection = new \ReflectionClass(get_class($object));
    $reflection_property = $reflection->getProperty($property);
    $reflection_property->setAccessible(true);
    return $reflection_property->getValue($object);
  }
}

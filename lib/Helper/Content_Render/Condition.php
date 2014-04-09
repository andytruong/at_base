<?php
namespace Drupal\at_base\Helper\Content_Render;

/**
 * @todo  Doc
 */
class Condition {
  private $data;
  private $args;

  public function __construct($data, $args) {
    $this->data = $data;
    $this->args = $args ? $args : array();
  }

  public function check() {
    // If conditions are not provided, content is always rendered.
    if (empty($this->data['conditions'])) {
      return TRUE;
    }
    $conditions = $this->data['conditions'];

    // Condition type.
    $condition_type = 'and';
    if (!empty($conditions['type']) && in_array($conditions['type'], array('and', 'or', 'xor', 'not'))) {
      $condition_type = $conditions['type'];
    }

    // Condition callbacks
    $callbacks = array();
    if (!empty($conditions['callbacks'])) {
      $callbacks = $conditions['callbacks'];
    }

    if (empty($callbacks)) {
      if ($condition_type == 'not') {
        // Not of (always TRUE) is FALSE.
        return FALSE;
      }
      return TRUE;
    }

    // Default return value.
    switch ($condition_type) {
      case 'and':
        $result = TRUE;
        break;

      case 'or':
        $result = FALSE;
        break;

      case 'xor':
        $result = FALSE;
        break;

      case 'not':
        $result = TRUE;
        break;

      default:
        // Wont reach here.
        break;
    }

    foreach ($callbacks as $callback) {
      if ($condition_type == 'and') {
        $result = $result && $this->callCallback($callback);
      }
      else if ($condition_type == 'or') {
        $result = $result || $this->callCallback($callback);
      }
      else if ($condition_type == 'xor') {
        $result = $result ^ $this->callCallback($callback);
      }
      else {
        // Not
        $result = $result && $this->callCallback($callback);
      }
    }

    if ($condition_type == 'not') {
      $result = !$result;
    }

    return $result;
  }

  private function callCallback($callback) {
    if (is_array($callback) && !empty($callback) && count($callback) == 2 || is_string($callback)) {
      if (is_string($callback)) {
        $callable = $callback;
      }
      else {
        list($callable, $arguments) = $callback;
      }

      if (strpos($callable, '@' === 0)) {
        // Getting service.
        $callable = str_replace('@', '', $callable);
        list($service_name, $method) = explode($callable, ':');
        $service = at_container($service_name);
        $callable = array($service, $method);
      }

      if (is_callable($callable)) {
        return call_user_func_array($callable, $arguments);
      }
    }

    throw new \Exception('Callback is not callable.');
  }
}

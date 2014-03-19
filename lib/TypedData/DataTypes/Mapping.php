<?php
namespace Drupal\at_base\TypedData\DataTypes;

class Mapping extends Mapping_Base {
  protected function validateDefinition(&$error) {
    if (!parent::validateDefinition($error)) {
      return FALSE;
    }

    if (!isset($this->def['mapping'])) {
      $error = 'Missing mappaping property for data definition.';
      return FALSE;
    }

    if (!is_array($this->def['mapping'])) {
      $error = 'Mapping property of data definition must be an array.';
      return FALSE;
    }

    return TRUE;
  }

  protected function validateInput(&$error) {
    if (!is_array($this->value)) {
      $error = 'Input must be an array.';
      return FALSE;
    }

    return $this->validateRequiredProperties($error)
      && $this->validateAllowingExtraProperties($error)
      && parent::validateInput($error)
    ;
  }

  protected function validateRequiredProperties(&$error) {
    foreach ($this->def['mapping'] as $k => $item_def) {
      if (!empty($item_def['required']) && !isset($this->value[$k])) {
        $error = "Property {$k} is required.";
        return FALSE;
      }
    }
    return TRUE;
  }

  private function validateAllowingExtraProperties(&$error) {
    if (!$this->allow_extra_properties) {
      foreach (array_keys($this->value) as $k) {
        if (!isset($this->def['mapping'][$k])) {
          $error = 'Unexpected key found: '. $k .'.';
          return FALSE;
        }
      }
    }

    return TRUE;
  }
}

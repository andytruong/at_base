<?php

namespace Drupal\at_base\Twig\Filters;

/**
 * Callback for drupalView Twig filter.
 *
 * @todo  More test case for view with custom template, …
 */
class Views {
  private $view;
  private $name;
  private $display_id = 'default';
  private $template;
  private $arguments = array();

  public function __construct($name, $display_id = 'default', $arguments = array()) {
    if (!$this->view = views_get_view($name)) {
      throw new \Exception('View not found: '. $this->name);
    }

    $this->name = $name;
    $this->setDisplayId($display_id);
    $this->setArguments($arguments);
  }

  public function setDisplayId($display_id) {
    $this->display_id = $display_id;
    $this->view->set_display($display_id);

    if (!$this->view->access($this->display_id)) {
      throw new \Exception('<!-- Access denied: '. $this->name .':'. $this->display_id .' -->');
    }
  }

  public function setArguments($arguments) {
    $this->arguments = $arguments;

    if (is_array($this->arguments)) {
      $this->view->set_arguments($this->arguments);
    }
  }

  public function setTemplate($template) {
    $this->template = at_container('helper.real_path')->get($template);
  }

  /**
   * Find Twig template for view on context theme.
   *
   * @todo Remove this magic
   */
  protected function suggestTemplate() {
    $suggestions[] = path_to_theme() . "/templates/views/{$this->name}.{$this->display_id}.html.twig";
    $suggestions[] = path_to_theme() . "/templates/views/{$this->name}.html.twig";
    foreach ($suggestions as $path) {
      if (is_file(DRUPAL_ROOT . '/' . $path)) {
        return $path;
      }
    }
  }

  public function execute() {
    // ---------------------
    // No template, use default
    // ---------------------
    if (!$this->template && (!$this->template = $this->suggestTemplate())) {
      $this->view->pre_execute();
      return $this->view->preview($this->display_id, $this->arguments);
    }

    // ---------------------
    // With template
    // ---------------------
    // Many tags rendered by views, we get rid of them
    if (!empty($view->display[$display_id]->display_options['fields'])) {
      foreach (array_keys($view->display[$display_id]->display_options['fields']) as $k) {
        $view->display[$display_id]->display_options['fields'][$k]['element_default_classes'] = 0;
        $view->display[$display_id]->display_options['fields'][$k]['element_type'] = 0;
      }
    }

    $this->view->pre_execute();
    $this->view->execute();

    module_load_include('inc', 'views', 'theme/theme');
    $vars['view'] = $this->view;
    template_preprocess_views_view($vars);
    return at_container('twig')->render($this->template, $vars);
  }

  public static function render($name, $display_id = 'default') {
    $args = func_get_args();
    array_shift($args); // $name
    if (count($args)) {
      $a1 = array_shift($args); // $display_id

      if (is_array($a1)) {
        $display_id = isset($a1['display_id']) ? $a1['display_id'] : 'default';
      }
    }

    try {
      $me = new self($name, $display_id, $args);

      if (is_array($a1)) {
        foreach ($a1 as $k => $v) {
          switch ($k) {
            case 'template':   $me->setTemplate($v);  break;
            case 'display_id': $me->setDisplayId($v); break;
            case 'arguments':  $me->setArguments($v); break;
          }
        }
      }

      return $me->execute();
    }
    catch (\Exception $e) {
      return $e->getMessage();
    }
  }
}

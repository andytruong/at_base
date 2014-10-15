<?php

namespace Drupal\at_base\Helper\Content_Render;

/**
 * Interface of caching handler for Conent_Render service.
 *
 * @see Drupal\at_base\Helper\Content_Render::render()
 */
interface CacheHandlerInterface {

  /**
   * Cache options
   *
   * @see  at_cache()
   * @return CacheHandlerInterface
   */
  public function setOptions($options);

  /**
   * @param callable $callback
   * @return CacheHandlerInterface
   */
  public function setCallback($callback);

  /**
   * Render content.
   */
  public function render();
}

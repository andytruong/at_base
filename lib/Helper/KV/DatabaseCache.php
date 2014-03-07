<?php
namespace Drupal\at_base\Helper\KV;

use Doctrine\Common\Cache\CacheProvider;

class DatabaseCache extends CacheProvider {

  const DOCTRINE_NAMESPACE_CACHEKEY = 'DoctrineNamespaceCacheKey[%s]';

  /**
   * The namespace to prefix all cache ids with.
   *
   * @var string
   */
  private $namespace = '';

  /**
   * The namespace version.
   *
   * @var string
   */
  private $namespaceVersion;

  /**
   * Sets the namespace to prefix all cache ids with.
   *
   * @param string $namespace
   *
   * @return void
   */
  public function setNamespace($namespace)
  {
      $this->namespace        = (string) $namespace;
      $this->namespaceVersion = null;
  }

  /**
   * Retrieves the namespace that prefixes all cache ids.
   *
   * @return string
   */
  public function getNamespace()
  {
      return $this->namespace;
  }

  /**
   * {@inheritdoc}
   */
  public function fetch($id)
  {
      return $this->doFetch($this->getNamespacedId($id));
  }

  /**
   * {@inheritdoc}
   */
  public function contains($id)
  {
      return $this->doContains($this->getNamespacedId($id));
  }

  /**
   * {@inheritdoc}
   */
  public function save($id, $data, $lifeTime = 0)
  {
      return $this->doSave($this->getNamespacedId($id), $data, $lifeTime);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($id)
  {
      return $this->doDelete($this->getNamespacedId($id));
  }

  /**
   * {@inheritdoc}
   */
  public function getStats()
  {
      return $this->doGetStats();
  }

  /**
   * Flushes all cache entries.
   *
   * @return boolean TRUE if the cache entries were successfully flushed, FALSE otherwise.
   */
  public function flushAll()
  {
      return $this->doFlush();
  }

  /**
   * Deletes all cache entries.
   *
   * @return boolean TRUE if the cache entries were successfully deleted, FALSE otherwise.
   */
  public function deleteAll()
  {
      $namespaceCacheKey = $this->getNamespaceCacheKey();
      $namespaceVersion  = $this->getNamespaceVersion() + 1;

      $this->namespaceVersion = $namespaceVersion;

      return $this->doSave($namespaceCacheKey, $namespaceVersion);
  }

  /**
   * Prefixes the passed id with the configured namespace value.
   *
   * @param string $id The id to namespace.
   *
   * @return string The namespaced id.
   */
  private function getNamespacedId($id)
  {
      $namespaceVersion  = $this->getNamespaceVersion();

      return sprintf('%s[%s][%s]', $this->namespace, $id, $namespaceVersion);
  }

  /**
   * Returns the namespace cache key.
   *
   * @return string
   */
  private function getNamespaceCacheKey()
  {
      return sprintf(self::DOCTRINE_NAMESPACE_CACHEKEY, $this->namespace);
  }

  /**
   * Returns the namespace version.
   *
   * @return string
   */
  private function getNamespaceVersion()
  {
      if (null !== $this->namespaceVersion) {
          return $this->namespaceVersion;
      }

      $namespaceCacheKey = $this->getNamespaceCacheKey();
      $namespaceVersion = $this->doFetch($namespaceCacheKey);

      if (false === $namespaceVersion) {
          $namespaceVersion = 1;

          $this->doSave($namespaceCacheKey, $namespaceVersion);
      }

      $this->namespaceVersion = $namespaceVersion;

      return $this->namespaceVersion;
  }

  /**
   * Fetches an entry from the cache.
   *
   * @param string $id The id of the cache entry to fetch.
   *
   * @return string|bool The cached data or FALSE, if no cache entry exists for the given id.
   */
  abstract protected function doFetch($id);

  /**
   * Tests if an entry exists in the cache.
   *
   * @param string $id The cache id of the entry to check for.
   *
   * @return boolean TRUE if a cache entry exists for the given cache id, FALSE otherwise.
   */
  abstract protected function doContains($id);

  /**
   * Puts data into the cache.
   *
   * @param string $id       The cache id.
   * @param string $data     The cache entry/data.
   * @param int    $lifeTime The lifetime. If != 0, sets a specific lifetime for this
   *                           cache entry (0 => infinite lifeTime).
   *
   * @return boolean TRUE if the entry was successfully stored in the cache, FALSE otherwise.
   */
  abstract protected function doSave($id, $data, $lifeTime = 0);

  /**
   * Deletes a cache entry.
   *
   * @param string $id The cache id.
   *
   * @return boolean TRUE if the cache entry was successfully deleted, FALSE otherwise.
   */
  abstract protected function doDelete($id);

  /**
   * Flushes all cache entries.
   *
   * @return boolean TRUE if the cache entry was successfully deleted, FALSE otherwise.
   */
  abstract protected function doFlush();

  /**
   * Retrieves cached information from the data store.
   *
   * @since 2.2
   *
   * @return array|null An associative array with server's statistics if available, NULL otherwise.
   */
  abstract protected function doGetStats();

}

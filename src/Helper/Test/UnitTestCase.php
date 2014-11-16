<?php

namespace Drupal\at_base\Helper\Test;

use DrupalUnitTestCase;

require_once dirname(__FILE__) . '/Cache.php';
require_once dirname(__FILE__) . '/Database.php';

/**
 * cache_get()/cache_set() does not work on unit test cases.
 */
abstract class UnitTestCase extends DrupalUnitTestCase
{

    protected $container;

    public static function getInfo()
    {
        return array(
            'name'        => 'AT Unit',
            'description' => 'Make sure the at_cache() is working correctly.',
            'group'       => 'AT Unit'
        );
    }

    public function setUp()
    {
        $this->container = at_container('container');

        // Mock db, cache
        unset($this->container['wrapper.db']);
        unset($this->container['wrapper.cache']);

        $this->container['wrapper.db'] = function() {
            return new Database();
        };
        $this->container['wrapper.cache'] = function() {
            return new Cache();
        };

        $this->setUpModules();
        parent::setUp('at_base', 'atest_base');
    }

    protected function setUpModules()
    {
        // at_modules() > system_list() > need db, fake it!
        // 'id' => "ATConfig:{$module}:{$id}:{$key}:" . ($include_at_base ? 1 : 0),
        $cids_1 = array('atmodules:at_base:', 'atmodules:at_base:services', 'atmodules:at_base:twig_functions');
        $data_1 = array('at_base', 'atest_base');
        foreach ($cids_1 as $cid) {
            at_container('wrapper.cache')->set($cid, $data_1, 'cache_bootstrap');
        }

        $cids_2 = array('atmodules:at_base:twig_filters');
        $data_2 = array('at_base');
        foreach ($cids_2 as $cid) {
            at_container('wrapper.cache')->set($cid, $data_2, 'cache_bootstrap');
        }
    }

}
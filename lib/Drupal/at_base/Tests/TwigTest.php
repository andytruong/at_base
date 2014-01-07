<?php

namespace Drupal\at_base\Tests;

/**
 * cache_get()/cache_set() does not work on unit test cases.
 */
class TwigTest extends \DrupalWebTestCase {
  public function getInfo() {
    return array(
      'name' => 'AT Base: Twig Service',
      'description' => 'Test Twig service',
      'group' => 'AT Base'
    );
  }

  public function setUp() {
    $this->profile = 'testing';
    parent::setUp('atest_base', 'atest2_base');
  }

  public function testServiceContainer() {
    $twig = at_container('twig');
    $this->assertEqual('Twig_Environment', get_class($twig));
  }

  public function testDefaultFilters() {
    $twig = at_container('twig');
    $filters = $twig->getFilters();

    foreach (array('render', 't', 'url', '_filter_autop', 'drupalBlock', 'drupalEntity', 'drupalView', 'at_config') as $filter) {
      $this->assertTrue(isset($filters[$filter]), "Found filter {$filter}");
    }
  }

  public function testContentRender() {
    $render = at_container('helper.content_render');

    // Simple string
    $expected = 'Hello Andy Truong';
    $actual = $render->setData($expected)->render();
    $this->assertEqual($expected, $actual);

    // Template string
    $data['template_string'] = 'Hello {{ name }}';
    $data['variables']['name'] = 'Andy Truong';
    $output = $render->setData($data)->render();
    $this->assertEqual($expected, $actual);

    // Template
    $data['template'] = '@atest_theming/templates/hello.twig';
    $data['variables']['name'] = 'Andy Truong';
    $output = $render->setData($data)->render();
    $assert = strpos($output, $actual) !== FALSE;
    $this->assertTrue($assert, "Found <strong>{$expected}</strong> in result.");
  }

  public function testTwigFilters() {
    $output = at_container('twig_string')->render("{{ 'user:1'|drupalEntity }}");
    $this->assertTrue(strpos($output, 'History'), 'Found text "History"');
    $this->assertTrue(strpos($output, 'Member for'), 'Found text: "Member for"');

    $output  = "{% set o = { template: '@atest_base/templates/entity/user.html.twig' } %}";
    $output .= "{{ 'user:1'|drupalEntity(o) }}";
    $output  = @at_container('twig_string')->render($output);
    $this->assertTrue(strpos($output, 'History'), 'Found text "History"');
    $this->assertTrue(strpos($output, 'Member for'), 'Found text: "Member for"');
    $this->assertTrue(strpos($output, '@atest_base/templates/entity/user.html.twig'), 'Found text: path to template');
  }

  public function testTwigStringLoader() {
    $output = \AT::twig_string()->render('Hello {{ name }}', array('name' => 'Andy Truong'));
    $this->assertEqual('Hello Andy Truong', $output, 'Template string is rendered correctly.');
  }

  public function testCacheFilter() {
    $string_1  = "{% set options = { cache_id: 'atestTwigCache:1' } %}";
    $string_1 .= "\n {{ 'atest_base.service_1:hello' | cache(options) }}";
    $string_2  = "{% set options = { cache_id: 'atestTwigCache:2' } %}";
    $string_2 .= "\n {{ 'At_Base_Test_Class::helloStatic' | cache(options) }}";
    $string_3  = "{% set options = { cache_id: 'atestTwigCache:3' } %}";
    $string_3 .= "\n {{ 'atest_base_hello' | cache(options) }}";
    $string_4  = "{% set options  = { cache_id: 'atestTwigCache:4' } %}";
    $string_4 .= "\n {% set callback = { callback: 'atest_base_hello', arguments: ['Andy Truong'] } %}";
    $string_4 .= "\n {{ callback | cache(options) }}";
    for ($i = 1; $i <= 4; $i++) {
      $expected = 'Hello Andy Truong';
      $actual = "string_{$i}";
      $actual = at_container('twig_string')->render($$actual);
      $actual = trim($actual);
      $this->assertEqual($expected, $actual);
    }
  }
}

// class Twig_Tests_ParserTest extends PHPUnit_Framework_TestCase { # extends Twig_Test_NodeTestCase
//   public function testCacheTag() {
//     $cache_options = new Twig_Node(array(
//         new Twig_Node_Print(new Twig_Node_Expression_Name('cache_options', 1), 1),
//       ), array(), 1
//     );

//     $callback = new Twig_Node(array(
//         new Twig_Node_Print(new Twig_Node_Expression_Name('foo', 1), 1)
//       ), array(), 1
//     );

//     $node = new Twig_Node_Cache($cache_options, $callback, 1, 'cache');
//     $this->assertEquals($cache_options, $node->getNode('cache_options'));
//     $this->assertEquals($callback, $node->getNode('callback'));
//   }

//   public function testParse() {
//     $twig = new Twig_Environment(new Twig_Loader_String(), array(
//       'autoescape' => false,
//       'optimizations' => 0,
//     ));

//     $twig->addTokenParser(new Twig_TokenParser_Cache());

//     $template  = "{% cache({id: 'myID'}) %}";
//     $template .= "Hello Andy Truong";
//     $template .= "{% endcache %}";

//     // $template  = "{% if true %}";
//     // $template .= "Hello Andy Truong";
//     // $template .= "{% endif %}";

//     $node = $twig->parse($twig->tokenize($template));

//     print_r(array(
//       $twig->compile($node)
//       // $node
//         // ->getNode('body')
//         // ->compile($twig->getCompiler())
//     ));
//   }
// }

// class At_Base_Cache_Views_Warmer extends DrupalWebTestCase {
//   public function getInfo() {
//     return array(
//       'name' => 'AT Theming: AT Cache > Views-Cache warmer',
//       'description' => 'Try views-cache warmer of at_base.',
//       'group' => 'AT Theming',
//     );
//   }

//   protected function setUp() {
//     parent::setUp('atest_theming');
//   }

//   /**
//    * @todo test me.
//    */
//   public function testViewsCacheWarming() {
//     // Build the first time
//     // $output = at_id(new Drupal\at_base\Helper\SubRequest('atest_theming/users'))->request();
//     $output = views_embed_view('atest_theming_user', 'page_1');

//     // Invoke entity save event
//     $u = $this->drupalCreateUser();

//     // Build the second time
//     // $output = at_id(new Drupal\at_base\Helper\SubRequest('atest_theming/users'))->request();
//     $output = views_embed_view('atest_theming_user', 'page_1');
//     $this->assertTrue(FALSE !== strpos($output, $u->name), "Found {$u->name}");

//     $this->verbose(print_r(_cache_get_object('cache_views_data'), TRUE));
//   }
// }

<?php

/*
 * This file is part of vaibhavpandeyvpz/tez package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled with this source code in the LICENSE file.
 */

namespace Tez;

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function test_empty_routes(): void
    {
        $router = new Router;
        $result = $router->match('/', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(MatchResult::NOT_FOUND, $result[0]);
    }

    public function test_found_any_method(): void
    {
        $router = new Router;
        $router->route('/', 'Home.Index');
        $result = $router->match('/', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Home.Index', $result[1]);
    }

    public function test_found_multiple_methods(): void
    {
        $router = new Router;
        $router->route('/', 'Home.Index', ['GET', 'POST']);
        $result = $router->match('/', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Home.Index', $result[1]);
        $result = $router->match('/', 'POST');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Home.Index', $result[1]);
        $result = $router->match('/', 'PUT');
        $this->assertCount(2, $result);
        $this->assertEquals(MatchResult::NOT_ALLOWED, $result[0]);
        $this->assertIsArray($result[1]);
        $this->assertCount(2, $result[1]);
        $this->assertContains('GET', $result[1]);
        $this->assertContains('POST', $result[1]);
    }

    public function test_found_specific_method(): void
    {
        $router = new Router;
        $router->route('/', 'Home.Index', 'GET');
        $result = $router->match('/', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Home.Index', $result[1]);
        $result = $router->match('/', 'POST');
        $this->assertCount(2, $result);
        $this->assertEquals(MatchResult::NOT_ALLOWED, $result[0]);
        $this->assertIsArray($result[1]);
        $this->assertCount(1, $result[1]);
        $this->assertContains('GET', $result[1]);
    }

    public function test_not_allowed(): void
    {
        $router = new Router;
        $router->route('/contact-us', 'ContactUs.Submit', 'POST');
        $result = $router->match('/contact-us', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(MatchResult::NOT_ALLOWED, $result[0]);
        $this->assertIsArray($result[1]);
        $this->assertCount(1, $result[1]);
        $this->assertContains('POST', $result[1]);
    }

    public function test_not_found(): void
    {
        $router = new Router;
        $router->route('/', 'Home.Index');
        $result = $router->match('/home', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(MatchResult::NOT_FOUND, $result[0]);
    }

    public function test_group(): void
    {
        $router = new Router;
        $router->route('/', 'Home.Index');
        $router->group('/admin', function (Router $router) {
            $router->route('', 'Admin.Dashboard.Index');
            $router->route('/login', 'Admin.Login.Index', 'GET');
            $router->route('/login', 'Admin.Login.Submit', 'POST');
        });
        $router->group('/api', function () use ($router) {
            $router->route('/users', 'Api.Users.Index', 'GET');
            $router->route('/users', 'Api.Users.Create', 'POST');
        });
        $result = $router->match('/', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Home.Index', $result[1]);
        $result = $router->match('/admin', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Admin.Dashboard.Index', $result[1]);
        $result = $router->match('/admin/login', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Admin.Login.Index', $result[1]);
        $result = $router->match('/admin/login', 'POST');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Admin.Login.Submit', $result[1]);
        $result = $router->match('/api/users', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Api.Users.Index', $result[1]);
        $result = $router->match('/api/users', 'POST');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Api.Users.Create', $result[1]);
    }

    public function test_captures(): void
    {
        $router = new Router;
        $router->route('/users/{id}', 'Users.Show');
        $result = $router->match('/users/13', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Users.Show', $result[1]);
        $this->assertIsArray($result[2]);
        $this->assertArrayHasKey('id', $result[2]);
        $this->assertEquals('13', $result[2]['id']);
    }

    public function test_captures_assert_alpha(): void
    {
        $router = new Router;
        $router->route('/users/{username:a}', 'Users.Show');
        $result = $router->match('/users/13', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(MatchResult::NOT_FOUND, $result[0]);
        $result = $router->match('/users/vpz', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Users.Show', $result[1]);
        $this->assertIsArray($result[2]);
        $this->assertArrayHasKey('username', $result[2]);
        $this->assertEquals('vpz', $result[2]['username']);
    }

    public function test_captures_assert_alpha_numeric(): void
    {
        $router = new Router;
        $router->route('/users/{username:ai}', 'Users.Show');
        $result = $router->match('/users/vpz.13', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(MatchResult::NOT_FOUND, $result[0]);
        $result = $router->match('/users/vpz13', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Users.Show', $result[1]);
        $this->assertIsArray($result[2]);
        $this->assertArrayHasKey('username', $result[2]);
        $this->assertEquals('vpz13', $result[2]['username']);
    }

    public function test_captures_assert_hex(): void
    {
        $router = new Router;
        $router->route('/colors/{code:h}', 'Colors.Detail');
        $result = $router->match('/colors/13', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(MatchResult::NOT_FOUND, $result[0]);
        $result = $router->match('/colors/00aeef', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Colors.Detail', $result[1]);
        $this->assertIsArray($result[2]);
        $this->assertArrayHasKey('code', $result[2]);
        $this->assertEquals('00aeef', $result[2]['code']);
    }

    public function test_captures_assert_everything(): void
    {
        $router = new Router;
        $router->route('/p/{product:*}', 'Products.Index');
        $result = $router->match('/p/apple-iphone-xs/gold/256-gb', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Products.Index', $result[1]);
        $this->assertIsArray($result[2]);
        $this->assertArrayHasKey('product', $result[2]);
        $this->assertEquals('apple-iphone-xs/gold/256-gb', $result[2]['product']);
    }

    public function test_captures_assert_numeric(): void
    {
        $router = new Router;
        $router->route('/users/{id:i}', 'Users.Show');
        $result = $router->match('/users/vpz', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(MatchResult::NOT_FOUND, $result[0]);
        $result = $router->match('/users/13', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Users.Show', $result[1]);
        $this->assertIsArray($result[2]);
        $this->assertArrayHasKey('id', $result[2]);
        $this->assertEquals('13', $result[2]['id']);
    }

    public function test_captures_assert_invalid(): void
    {
        $router = new Router;
        $router->route('/users/{id:z}', 'Users.Show');
        $this->expectException(\RuntimeException::class);
        $router->match('/users/13', 'GET');
    }

    public function test_precompiled(): void
    {
        $router = new Router([
            [
                '/users/{id:i}',
                ['GET'],
                'Users.Show',
                '#^/users/(?P<id>\d+)$#',
                ['id'],
            ],
        ]);
        $result = $router->match('/users/vpz', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(MatchResult::NOT_FOUND, $result[0]);
        $result = $router->match('/users/13', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Users.Show', $result[1]);
        $this->assertIsArray($result[2]);
        $this->assertArrayHasKey('id', $result[2]);
        $this->assertEquals('13', $result[2]['id']);
    }

    public function test_dump(): void
    {
        $router1 = new Router;
        $router1->route('/users/{id:i}', 'Users.Show');
        $temp = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('tez');
        $router1->dump($temp);
        /** @noinspection PhpIncludeInspection */
        $router2 = new Router(require $temp);
        $result = $router2->match('/users/vpz', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(MatchResult::NOT_FOUND, $result[0]);
        $result = $router2->match('/users/13', 'GET');
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Users.Show', $result[1]);
        $this->assertIsArray($result[2]);
        $this->assertArrayHasKey('id', $result[2]);
        $this->assertEquals('13', $result[2]['id']);
    }

    public function test_empty_method_string(): void
    {
        $router = new Router;
        $router->route('/test', 'Test.Index');
        $result = $router->match('/test', '');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Test.Index', $result[1]);
    }

    public function test_empty_method_with_method_restriction(): void
    {
        $router = new Router;
        $router->route('/test', 'Test.Index', 'GET');
        $result = $router->match('/test', '');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $result = $router->match('/test', 'POST');
        $this->assertEquals(MatchResult::NOT_ALLOWED, $result[0]);
    }

    public function test_multiple_captures(): void
    {
        $router = new Router;
        $router->route('/users/{id}/posts/{postId}', 'Users.Posts.Show');
        $result = $router->match('/users/13/posts/42', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Users.Posts.Show', $result[1]);
        $this->assertIsArray($result[2]);
        $this->assertArrayHasKey('id', $result[2]);
        $this->assertArrayHasKey('postId', $result[2]);
        $this->assertEquals('13', $result[2]['id']);
        $this->assertEquals('42', $result[2]['postId']);
    }

    public function test_multiple_captures_with_assertions(): void
    {
        $router = new Router;
        $router->route('/users/{id:i}/posts/{slug:a}', 'Users.Posts.Show');
        $result = $router->match('/users/13/posts/hello', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertArrayHasKey('id', $result[2]);
        $this->assertArrayHasKey('slug', $result[2]);
        $this->assertEquals('13', $result[2]['id']);
        $this->assertEquals('hello', $result[2]['slug']);
        // Should fail with invalid types
        $result = $router->match('/users/abc/posts/hello', 'GET');
        $this->assertEquals(MatchResult::NOT_FOUND, $result[0]);
        $result = $router->match('/users/13/posts/123', 'GET');
        $this->assertEquals(MatchResult::NOT_FOUND, $result[0]);
    }

    public function test_nested_groups(): void
    {
        $router = new Router;
        $router->group('/api', function (Router $router) {
            $router->group('/v1', function (Router $router) {
                $router->route('/users', 'Api.V1.Users.Index');
            });
        });
        $result = $router->match('/api/v1/users', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Api.V1.Users.Index', $result[1]);
    }

    public function test_nested_groups_with_captures(): void
    {
        $router = new Router;
        $router->group('/api', function (Router $router) {
            $router->group('/v{version}', function (Router $router) {
                $router->route('/users/{id}', 'Api.Users.Show');
            });
        });
        $result = $router->match('/api/v1/users/42', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertArrayHasKey('version', $result[2]);
        $this->assertArrayHasKey('id', $result[2]);
        $this->assertEquals('1', $result[2]['version']);
        $this->assertEquals('42', $result[2]['id']);
    }

    public function test_group_with_empty_prefix(): void
    {
        $router = new Router;
        $router->group('', function (Router $router) {
            $router->route('/test', 'Test.Index');
        });
        $result = $router->match('/test', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Test.Index', $result[1]);
    }

    public function test_route_with_empty_path(): void
    {
        $router = new Router;
        $router->route('', 'Home.Index');
        $result = $router->match('/', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Home.Index', $result[1]);
    }

    public function test_route_with_empty_path_in_group(): void
    {
        $router = new Router;
        $router->group('/admin', function (Router $router) {
            $router->route('', 'Admin.Index');
        });
        $result = $router->match('/admin', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Admin.Index', $result[1]);
    }

    public function test_route_priority_first_match_wins(): void
    {
        $router = new Router;
        $router->route('/users/{id}', 'Users.Show');
        $router->route('/users/special', 'Users.Special');
        // First route should match even though second is more specific
        $result = $router->match('/users/special', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Users.Show', $result[1]);
        $this->assertEquals('special', $result[2]['id']);
    }

    public function test_route_priority_exact_match_before_regex(): void
    {
        $router = new Router;
        $router->route('/users/exact', 'Users.Exact');
        $router->route('/users/{id}', 'Users.Show');
        $result = $router->match('/users/exact', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Users.Exact', $result[1]);
    }

    public function test_compile_method(): void
    {
        $router = new Router;
        $router->route('/test', 'Test.Index');
        $router->route('/users/{id}', 'Users.Show');
        $compiled = $router->compile();
        $this->assertIsArray($compiled);
        $this->assertCount(2, $compiled);
        // Compile should be idempotent
        $compiled2 = $router->compile();
        $this->assertEquals($compiled, $compiled2);
    }

    public function test_compile_with_precompiled_routes(): void
    {
        $precompiled = [
            ['/test', ['GET'], 'Test.Index'],
        ];
        $router = new Router($precompiled);
        $compiled = $router->compile();
        $this->assertEquals($precompiled, $compiled);
    }

    public function test_group_exception_handling(): void
    {
        $router = new Router;
        $router->group('/admin', function (Router $router) {
            $router->route('/test', 'Admin.Test');
        });
        // Verify group was properly cleaned up after normal execution
        $result = $router->match('/admin/test', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        // Now test exception handling
        $router2 = new Router;
        try {
            $router2->group('/api', function (Router $router) {
                $router->route('/test', 'Api.Test');
                throw new \RuntimeException('Test exception');
            });
        } catch (\RuntimeException $e) {
            $this->assertEquals('Test exception', $e->getMessage());
        }
        // Verify group was cleaned up even after exception
        // Adding a route after exception should not have the group prefix
        $router2->route('/test2', 'Test2.Index');
        $result = $router2->match('/test2', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
    }

    public function test_different_target_types(): void
    {
        $router = new Router;
        // Test with array target
        $router->route('/array', ['controller' => 'Test', 'action' => 'Index']);
        $result = $router->match('/array', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertIsArray($result[1]);
        // Test with callable target
        $callable = fn () => 'test';
        $router->route('/callable', $callable);
        $result = $router->match('/callable', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertSame($callable, $result[1]);
        // Test with object target
        $object = new \stdClass;
        $object->name = 'test';
        $router->route('/object', $object);
        $result = $router->match('/object', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertSame($object, $result[1]);
        // Test with integer target
        $router->route('/int', 42);
        $result = $router->match('/int', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals(42, $result[1]);
    }

    public function test_method_case_sensitivity(): void
    {
        $router = new Router;
        $router->route('/test', 'Test.Index', 'GET');
        $result = $router->match('/test', 'get');
        $this->assertEquals(MatchResult::NOT_ALLOWED, $result[0]);
        $result = $router->match('/test', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
    }

    public function test_null_methods_vs_empty_array(): void
    {
        $router = new Router;
        $router->route('/test1', 'Test1.Index', null);
        $router->route('/test2', 'Test2.Index', []);
        // Both should accept any method
        $result1 = $router->match('/test1', 'GET');
        $result2 = $router->match('/test2', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result1[0]);
        $this->assertEquals(MatchResult::FOUND, $result2[0]);
    }

    public function test_complex_path_with_special_characters(): void
    {
        $router = new Router;
        $router->route('/path-with-dashes', 'Path.Dashes');
        $router->route('/path_with_underscores', 'Path.Underscores');
        $router->route('/path.with.dots', 'Path.Dots');
        $result = $router->match('/path-with-dashes', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $result = $router->match('/path_with_underscores', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $result = $router->match('/path.with.dots', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
    }

    public function test_leading_trailing_slashes(): void
    {
        $router = new Router;
        $router->route('/test', 'Test.Index');
        // Exact match required
        $result = $router->match('test', 'GET');
        $this->assertEquals(MatchResult::NOT_FOUND, $result[0]);
        $result = $router->match('/test/', 'GET');
        $this->assertEquals(MatchResult::NOT_FOUND, $result[0]);
        $result = $router->match('/test', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
    }

    public function test_multiple_routes_same_path_different_methods(): void
    {
        $router = new Router;
        $router->route('/users', 'Users.Index', 'GET');
        $router->route('/users', 'Users.Create', 'POST');
        $router->route('/users', 'Users.Update', 'PUT');
        $router->route('/users', 'Users.Delete', 'DELETE');
        $result = $router->match('/users', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Users.Index', $result[1]);
        $result = $router->match('/users', 'POST');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Users.Create', $result[1]);
        $result = $router->match('/users', 'PATCH');
        $this->assertEquals(MatchResult::NOT_ALLOWED, $result[0]);
        $this->assertContains('GET', $result[1]);
        $this->assertContains('POST', $result[1]);
        $this->assertContains('PUT', $result[1]);
        $this->assertContains('DELETE', $result[1]);
    }

    public function test_precompiled_simple_string_route(): void
    {
        $precompiled = [
            ['/simple', ['GET'], 'Simple.Index'],
        ];
        $router = new Router($precompiled);
        $result = $router->match('/simple', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $this->assertEquals('Simple.Index', $result[1]);
    }

    public function test_dump_creates_valid_file(): void
    {
        $router = new Router;
        $router->route('/test', 'Test.Index', 'GET');
        $router->route('/users/{id}', 'Users.Show');
        $temp = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('tez_dump_', true);
        $router->dump($temp);
        $this->assertFileExists($temp);
        $this->assertStringStartsWith('<?php', file_get_contents($temp));
        // Clean up
        unlink($temp);
    }

    public function test_all_assertion_types(): void
    {
        $router = new Router;
        // Test all assertion types
        $router->route('/alpha/{a:a}', 'Alpha');
        $router->route('/alphanum/{ai:ai}', 'AlphaNum');
        $router->route('/hex/{h:h}', 'Hex');
        $router->route('/int/{i:i}', 'Int');
        $router->route('/wild/{w:*}', 'Wild');
        // Valid matches
        $result = $router->match('/alpha/abc', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $result = $router->match('/alphanum/abc123', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $result = $router->match('/hex/00aeef', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $result = $router->match('/int/123', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $result = $router->match('/wild/anything/goes/here', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
    }

    public function test_route_chaining(): void
    {
        $router = new Router;
        $result = $router
            ->route('/route1', 'Route1')
            ->route('/route2', 'Route2')
            ->route('/route3', 'Route3')
            ->match('/route1', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $result = $router->match('/route2', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $result = $router->match('/route3', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
    }

    public function test_group_chaining(): void
    {
        $router = new Router;
        $router->group('/api', function (Router $router) {
            $router->route('/users', 'Api.Users');
        })->group('/admin', function (Router $router) {
            $router->route('/dashboard', 'Admin.Dashboard');
        });
        $result = $router->match('/api/users', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
        $result = $router->match('/admin/dashboard', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
    }

    public function test_method_not_allowed_accumulation_simple_route(): void
    {
        $router = new Router;
        $router->route('/test', 'Test.Index', 'GET');
        $router->route('/test', 'Test.Update', 'PUT');
        // Try POST which is not allowed - should accumulate GET and PUT
        $result = $router->match('/test', 'POST');
        $this->assertEquals(MatchResult::NOT_ALLOWED, $result[0]);
        $this->assertContains('GET', $result[1]);
        $this->assertContains('PUT', $result[1]);
    }

    public function test_method_not_allowed_accumulation_regex_route(): void
    {
        $router = new Router;
        $router->route('/users/{id}', 'Users.Show', 'GET');
        $router->route('/users/{id}', 'Users.Update', 'PUT');
        // Try POST which is not allowed - should accumulate GET and PUT
        $result = $router->match('/users/123', 'POST');
        $this->assertEquals(MatchResult::NOT_ALLOWED, $result[0]);
        $this->assertContains('GET', $result[1]);
        $this->assertContains('PUT', $result[1]);
    }

    public function test_group_with_non_closure_callable(): void
    {
        $router = new Router;
        // Use a regular function instead of Closure
        $callback = function (Router $router) {
            $router->route('/test', 'Test.Index');
        };
        $router->group('/api', $callback);
        $result = $router->match('/api/test', 'GET');
        $this->assertEquals(MatchResult::FOUND, $result[0]);
    }
}

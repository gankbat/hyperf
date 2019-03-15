<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Consul;

use Mockery;
use ReflectionClass;
use Hyperf\Consul\Client;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \Hyperf\Consul\Client
 */
class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var \ReflectionMethod
     */
    private $method;

    protected function setUp()
    {
        $this->client = new Client(function () {
            return Mockery::mock(\GuzzleHttp\Client::class);
        });
        $reflectionClass = new ReflectionClass(Client::class);
        $method = $reflectionClass->getMethod('resolveOptions');
        $method->setAccessible(true);
        $this->method = $method;
    }

    public function testResolveOptions()
    {
        $options = [
            'foo' => 'bar',
            'hello' => 'world',
            'baz' => 'inga',
        ];

        $availableOptions = [
            'foo', 'baz',
        ];

        $result = $this->method->invoke($this->client, $options, $availableOptions);

        $expected = [
            'foo' => 'bar',
            'baz' => 'inga',
        ];

        $this->assertSame($expected, $result);
    }

    public function testResolveOptionsWithoutMatchingOptions()
    {
        $options = [
            'hello' => 'world',
        ];

        $availableOptions = [
            'foo', 'baz',
        ];

        $result = $this->method->invoke($this->client, $options, $availableOptions);

        $this->assertSame([], $result);
    }
}
<?php

namespace App\Tests\Session;

use App\Session\MemcachedFactory;
use PHPUnit\Framework\TestCase;

class MemcachedFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_a_memcached_object_from_a_connection_string()
    {
        $connectionString = 'memcached://hostname:0123';
        $memcached = MemcachedFactory::createFromUrl($connectionString);
        self::assertEquals($memcached->getServerList()[0]['host'], 'hostname');
        self::assertEquals($memcached->getServerList()[0]['port'], '123');
    }
}

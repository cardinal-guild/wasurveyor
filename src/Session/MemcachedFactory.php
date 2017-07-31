<?php
namespace App\Session;

class MemcachedFactory
{
    /**
     * Dokku exposes memcached details in the format
     *
     * memcached://host-name:port
     *
     * We need to extract the host name and port from the string given
     * and return a new \MemcachedFactory object to allow us to use this as a
     * session handler in Symfony
     *
     * @param string $connectionString
     *
     * @return \Memcached
     */
    public static function createFromUrl(string $connectionString): \Memcached
    {
        $parts = parse_url($connectionString);
        $memcached = new \Memcached;
        $memcached->addServer($parts['host'], $parts['port']);
        return $memcached;
    }
}

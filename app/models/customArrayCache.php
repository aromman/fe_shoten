<?php
class CustomArrayCache
{
    /**
     * Array is static and private
     * – private – so that it can be accessed only from the
     * methods of the class
     * – static – so that the property is available in all instances
     */
    private static array $memory = [];

    // Method for storing data in memory
    public function store(string $key, $value): bool
    {
        self::$memory[$key] = $value;

        return true;
    }

    // Method for getting data from memory
    public function fetch(string $key)
    {
        return self::$memory[$key] ?? null;
    }

    // Method for deleting data from memory
    public function delete(string $key): bool
    {
        unset(self::$memory[$key]);

        return true;
    }

    // Method for checking the availability of key data
    public function exists(string $key): bool
    {
        return array_key_exists($key, self::$memory);
    }
}
?>
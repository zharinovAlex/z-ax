<?php

namespace Core;

/**
 * Class Autoloader (PSR-4)
 *
 * @author  Alexander Zharinov <zharinovalex88@gmail.com>
 */
class Autoloader
{
    const PHP_EXTENSION = '.php';

    /**
     * An associative array where the key is a namespace prefix and the value
     * is an array of base directories for classes in that namespace.
     *
     * @var array
     */
    private $prefixes = [];

    /**
     * Register custom function as __autoload() implementation
     *
     * @return \Core\Autoloader
     */
    public function register(): Autoloader
    {
        spl_autoload_register([$this, 'loadClass']);

        return $this;
    }

    /**
     * Loads the class file for a given class name.
     *
     * @param string $class
     *
     * @return mixed
     */
    public function loadClass(string $class)
    {
        // the current namespace prefix
        $prefix = $class;

        // Find and load (if exists) file by using a fully-qualified class name
        while (false !== $pos = strrpos($prefix, '\\')) {
            // retain the trailing namespace separator in the prefix
            $prefix = substr($class, 0, $pos + 1);

            // all the rest - the relative class name
            $relativeClass = substr($class, $pos + 1);

            // try to load a mapped file for the prefix and relative class
            $mappedFile = $this->loadMappedFile($prefix, $relativeClass);
            if ($mappedFile) {
                return $mappedFile;
            }

            // remove the trailing namespace separator for the next iteration of strrpos()
            $prefix = rtrim($prefix, '\\');
        }

        // never found a mapped file
        return false;
    }

    /**
     * Adds a base directory for a namespace prefix.
     *
     * @param string $prefix
     * @param string $baseDirectory
     * @param bool   $prepend
     *
     * @return \Core\Autoloader
     */
    public function addNamespace(string $prefix, string $baseDirectory, bool $prepend = false): Autoloader
    {
        // normalize namespace prefix
        $prefix = trim($prefix, '\\') . '\\';

        // normalize the base directory with a trailing separator
        $baseDirectory = rtrim($baseDirectory, DIRECTORY_SEPARATOR) . '/';

        // initialize the namespace prefix array
        if (false === isset($this->prefixes[$prefix])) {
            $this->prefixes[$prefix] = [];
        }

        // retain the base directory for the namespace prefix
        if ($prepend) {
            array_unshift($this->prefixes[$prefix], $baseDirectory);
        } else {
            array_push($this->prefixes[$prefix], $baseDirectory);
        }

        return $this;
    }

    /**
     * Load the mapped file for a namespace prefix and relative class.
     *
     * @param string $prefix
     * @param string $relativeClass
     *
     * @return mixed
     */
    private function loadMappedFile(string $prefix, string $relativeClass)
    {
        if (false === isset($this->prefixes[$prefix])) {
            return false;
        }

        // look through base directories for this namespace prefix
        foreach ($this->prefixes[$prefix] as $baseDir) {

            // replace the namespace prefix with the base directory,
            // replace namespace separators with directory separators
            // in the relative class name, append with php extension
            $file = $baseDir
                    . str_replace('\\', '/', $relativeClass)
                    . self::PHP_EXTENSION;

            // if the mapped file exists, require it
            if ($this->requireFile($file)) {
                return $file;
            }
        }

        // never found it
        return false;
    }

    /**
     * If a file exists, require it from the file system.
     *
     * @param string $file
     *
     * @return bool
     */
    private function requireFile(string $file)
    {
        if (file_exists($file)) {
            require_once $file;

            return true;
        }

        return false;
    }
}
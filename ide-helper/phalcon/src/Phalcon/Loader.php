<?php

namespace Phalcon;

/**
 * Phalcon\Loader
 *
 * This component helps to load your project classes automatically based on some conventions
 *
 * <code>
 * use Phalcon\Loader;
 *
 * // Creates the autoloader
 * $loader = new Loader();
 *
 * // Register some namespaces
 * $loader->registerNamespaces(
 *     [
 *         "Example\\Base"    => "vendor/example/base/",
 *         "Example\\Adapter" => "vendor/example/adapter/",
 *         "Example"          => "vendor/example/",
 *     ]
 * );
 *
 * // Register autoloader
 * $loader->register();
 *
 * // Requiring this class will automatically include file vendor/example/adapter/Some.php
 * $adapter = new \Example\Adapter\Some();
 * </code>
 */
class Loader implements \Phalcon\Events\EventsAwareInterface
{

    protected $_eventsManager = null;


    protected $_foundPath = null;


    protected $_checkedPath = null;


    protected $_classes = array();


    protected $_extensions = array('php');


    protected $_namespaces = array();


    protected $_directories = array();


    protected $_files = array();


    protected $_registered = false;


    protected $fileCheckingCallback = 'is_file';


    /**
     * Sets the file check callback.
     *
     * <code>
     * // Default behavior.
     * $loader->setFileCheckingCallback("is_file");
     *
     * // Faster than `is_file()`, but implies some issues if
     * // the file is removed from the filesystem.
     * $loader->setFileCheckingCallback("stream_resolve_include_path");
     *
     * // Do not check file existence.
     * $loader->setFileCheckingCallback(null);
     * </code>
     *
     * @param mixed $callback
     * @return Loader
     */
    public function setFileCheckingCallback($callback = null) {}

    /**
     * Sets the events manager
     *
     * @param \Phalcon\Events\ManagerInterface $eventsManager
     */
    public function setEventsManager(\Phalcon\Events\ManagerInterface $eventsManager) {}

    /**
     * Returns the internal event manager
     *
     * @return \Phalcon\Events\ManagerInterface
     */
    public function getEventsManager() {}

    /**
     * Sets an array of file extensions that the loader must try in each attempt to locate the file
     *
     * @param array $extensions
     * @return Loader
     */
    public function setExtensions(array $extensions) {}

    /**
     * Returns the file extensions registered in the loader
     *
     * @return array
     */
    public function getExtensions() {}

    /**
     * Register namespaces and their related directories
     *
     * @param array $namespaces
     * @param bool $merge
     * @return Loader
     */
    public function registerNamespaces(array $namespaces, $merge = false) {}

    /**
     * @param array $namespace
     * @return array
     */
    protected function prepareNamespace(array $namespace) {}

    /**
     * Returns the namespaces currently registered in the autoloader
     *
     * @return array
     */
    public function getNamespaces() {}

    /**
     * Register directories in which "not found" classes could be found
     *
     * @param array $directories
     * @param bool $merge
     * @return Loader
     */
    public function registerDirs(array $directories, $merge = false) {}

    /**
     * Returns the directories currently registered in the autoloader
     *
     * @return array
     */
    public function getDirs() {}

    /**
     * Registers files that are "non-classes" hence need a "require". This is very useful for including files that only
     * have functions
     *
     * @param array $files
     * @param bool $merge
     * @return Loader
     */
    public function registerFiles(array $files, $merge = false) {}

    /**
     * Returns the files currently registered in the autoloader
     *
     * @return array
     */
    public function getFiles() {}

    /**
     * Register classes and their locations
     *
     * @param array $classes
     * @param bool $merge
     * @return Loader
     */
    public function registerClasses(array $classes, $merge = false) {}

    /**
     * Returns the class-map currently registered in the autoloader
     *
     * @return array
     */
    public function getClasses() {}

    /**
     * Register the autoload method
     *
     * @param bool $prepend
     * @return Loader
     */
    public function register($prepend = false) {}

    /**
     * Unregister the autoload method
     *
     * @return Loader
     */
    public function unregister() {}

    /**
     * Checks if a file exists and then adds the file by doing virtual require
     */
    public function loadFiles() {}

    /**
     * Autoloads the registered classes
     *
     * @param string $className
     * @return bool
     */
    public function autoLoad($className) {}

    /**
     * Get the path when a class was found
     *
     * @return string
     */
    public function getFoundPath() {}

    /**
     * Get the path the loader is checking for a path
     *
     * @return string
     */
    public function getCheckedPath() {}

}

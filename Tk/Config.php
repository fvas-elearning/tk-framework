<?php
/**
 * Created by PhpStorm.
 * User: mifsudm
 * Date: 6/17/15
 * Time: 8:32 AM
 */

namespace Tk;


use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LogLevel;
use Tk\Utils\Registry;

/**
 * Class Config
 *
 * This config class is a specific array type object to contain the
 * applications config and dependency values and functions.
 *
 * It can be used as a standard array it extends the \Tk\Registry
 * Example usage:
 * <code>
 * <?php
 * $request = Request::createFromGlobals();
 * $cfg = \Tk\Config::getInstance();
 *
 * $cfg->setAppPath($appPath);
 * $cfg->setRequest($request);
 * $cfg->setAppUrl($request->getBasePath());
 * $cfg->setAppDataPath($cfg->getAppPath().'/data');
 * $cfg->setAppCachePath($cfg->getAppDataPath().'/cache');
 * $cfg->setAppTempPath($cfg->getAppDataPath().'/temp');
 * // Useful for dependency management to create application objects
 * $cfg->setStdObject(function($test1, $test2, $test3) {
 *     $cfg = \Tk\Registry::getInstance();
 *     $obj = new \stdClass();
 *     $obj->test1 = $test1;
 *     $obj->test2 = $test1;
 *     $obj->test3 = $test1;
 *     return $obj;
 * });
 *
 * $var = $cfg->getStdObject('test param', 'test2', 'test3');
 * // or
 * $var = $cfg->createStdObject('test param', 'test2', 'test3');
 * // or
 * $var = $cfg->isStdObject('test param', 'test2', 'test3');
 * var_dump($var);
 *
 *
 *  // Output:
 *  //  object(stdClass)[15]
 *  //      public 'test1' => string 'test param' (length=10)
 *  //      public 'test2' => string 'test param' (length=10)
 *  //      public 'test3' => string 'test param' (length=10)
 *
 *  // The following returns the closure object not the result
 *
 * $var = $cfg->get('std.object');
 * var_dump($var);
 *
 * // Output
 * // object(Closure)[14]
 *
 *
 * </code>
 *
 * Internally the Config values are stored in an array. So to set a value there is a couple of ways to do this:
 *
 *   $cfg->setSitePath($path);
 *   same as
 *   $sfg['site.path'] = $path
 *
 * To get a values stored in the registry you can do the following using the array access methods:
 *
 *   $val = $cfg->getSitePath();
 *   same as
 *   $val = $cfg['site.path']
 *
 * NOTICE: When using the array access methods to get a closure (anonymous function) the
 * closure object will be returned. You must call getClosureObject($params) to execute the closure function
 * and return the executed result. (see above example)
 *
 *
 */
class Config extends Registry
{

    /**
     * @var Config
     */
    static $instance = null;



    /**
     * Get an instance of this object
     *
     * @return Config
     */
    static function getInstance()
    {
        if (static::$instance == null) {
            static::$instance = new static();
        }
        return static::$instance;
    }


    /**
     * Construct the config object and initiate default settings
     *
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * init the default params.
     *
     */
    protected function init()
    {
        $this->setAppScripTime(microtime(true));
        // Setup isCli function in config.
        $this->setCli(false);
        if (substr(php_sapi_name(), 0, 3) == 'cli') {
            $this->setCli(true);
        }

        // Add request to config
        $request = Request::createFromGlobals();
        $this->setRequest($request);

        // Setup the app path
        $appUrl = $request->getBasePath();
        $appUrl = rtrim($appUrl, '/');
        $this->setAppUrl($appUrl);

        $appPath = rtrim(dirname(dirname(dirname(dirname(__DIR__)))), '/');
        $this->setAppPath($appPath);

        $this->setDebug(false);

        $this->setSystemLogPath(ini_get('error_log'));
        $this->setSystemLogLevel(LogLevel::ERROR);

        $this->setDataPath($this->getAppPath() . '/data');
        $this->setDataUrl($this->getAppUrl() . '/data');

        $this->setVendorPath($this->getAppPath() . '/vendor');
        $this->setVendorUrl($this->getAppUrl() . '/vendor');

        $this->setSrcPath($this->getAppPath() . '/src');
        $this->setSrcUrl($this->getAppUrl() . '/src');

        $this->setCachePath($this->getAppPath() . '/cache');
        $this->setCacheUrl($this->getAppUrl() . '/cache');

        $this->setTempPath($this->getAppPath() . '/temp');
        $this->setTempUrl($this->getAppUrl() . '/temp');
    }



}
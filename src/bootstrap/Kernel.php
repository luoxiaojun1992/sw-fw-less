<?php

namespace SwFwLess\bootstrap;

use SwFwLess\components\Config;
use SwFwLess\components\functions;
use SwFwLess\components\provider\KernelProvider;
use SwFwLess\components\utils\runtime\php\Version;

class Kernel
{
    public static $app;

    const VERSION = '0.1.0';

    const RAW_FUNCTIONS_SWITCH = true;

    protected $sapi;

    /**
     * @return Kernel
     */
    public static function getApp()
    {
        return static::$app;
    }

    /**
     * @param Kernel $app
     */
    public static function setApp(Kernel $app): void
    {
        static::$app = $app;
    }

    /**
     * Kernel constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        Kernel::setApp($this);
        $this->bootstrap();
    }

    public function sapi()
    {
        return $this->sapi;
    }

    public function version()
    {
        return Kernel::VERSION;
    }

    public function setSapi($sapi)
    {
        $this->sapi = $sapi;
        return $this;
    }

    /**
     * @throws \Exception
     */
    protected function checkEnvironment()
    {
        if (!extension_loaded('swoole')) {
            throw new \Exception('Swoole extension is not installed.');
        }

        if (Version::lessThan('7.1')) {
            throw new \Exception('PHP7.1+ is not installed.');
        }
    }

    /**
     * @param bool $reboot
     * @throws \Exception
     */
    protected function bootstrap($reboot = false)
    {
        $this->checkEnvironment();

        $functionsWithoutNamespace = defined('RAW_FUNCTIONS') ? RAW_FUNCTIONS : static::RAW_FUNCTIONS_SWITCH;
        if ($functionsWithoutNamespace) {
            include_once __DIR__ . '/../components/old_functions.php';
        }

        //Load Env
        if (file_exists(APP_BASE_PATH . '.env')) {
            $dotEnv = (new \Dotenv\Dotenv(APP_BASE_PATH));
            if ($reboot) {
                $dotEnv->overload();
            } else {
                $dotEnv->load();
            }
        }

        //Init Config
        $configFormat = defined('CONFIG_FORMAT') ?
            CONFIG_FORMAT :
            Config::DEFAULT_CONFIG_FORMAT;
        \SwFwLess\components\Config::init(
            APP_BASE_PATH . 'config/app',
            $configFormat
        );

        //Boot providers
        KernelProvider::init(functions\config('providers'));
    }
}

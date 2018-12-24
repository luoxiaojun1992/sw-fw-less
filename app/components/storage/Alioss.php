<?php

namespace App\components\storage;

use App\components\Config;
use League\Flysystem\Filesystem;
use OSS\Http\RequestCore;
use Xxtime\Flysystem\Aliyun\OssAdapter;

class Alioss
{
    private static $instance;

    private $config;

    /**
     * Qiniu constructor.
     */
    public function __construct()
    {
        $this->config = Config::get('storage');

        if (extension_loaded('swoole')) {
            class_alias(AliossCoRequest::class, RequestCore::class);
        }

        AliossStreamWrapper::register();
    }

    /**
     * @return Qiniu|null
     */
    public static function create()
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        $storageConfig = Config::get('storage');
        if ($storageConfig['switch']) {
            if (in_array('alioss', $storageConfig['types'])) {
                return self::$instance = new self();
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function bucket()
    {
        return $this->config['ext']['alioss']['bucket'];
    }

    /**
     * @return string
     */
    public function endpoint()
    {
        return $this->config['ext']['alioss']['endpoint'];
    }

    /**
     * @param null $bucket
     * @return Filesystem
     * @throws \Exception
     */
    public function prepare($bucket = null)
    {
        $aliossConfig = $this->config['ext']['alioss'];
        $bucket = $bucket ? : $aliossConfig['default_bucket'];
        $bucketConfig = $aliossConfig['buckets'][$bucket];
        $local = new OssAdapter([
            'access_id' => $bucketConfig['access_id'],
            'access_secret' => $bucketConfig['access_secret'],
            'bucket' => $bucket,
            'endpoint' => $bucketConfig['endpoint'],
            'timeout' => $bucketConfig['timeout'],
            'connectTimeout' => $bucketConfig['connectTimeout'],
            'isCName' => $bucketConfig['isCName'],
            'securityToken' => $bucketConfig['securityToken'],
        ]);
        return new Filesystem($local);
    }
}

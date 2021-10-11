<?php

namespace SwFwLess\components\storage\samba;

use Icewind\SMB\BasicAuth;
use Icewind\SMB\ServerFactory;
use League\Flysystem\Filesystem;
use RobGridley\Flysystem\Smb\SmbAdapter;
use SwFwLess\components\Config;

class Samba
{
    private static $instance;

    private $config;

    /**
     * Qiniu constructor.
     */
    public function __construct()
    {
        $this->config = Config::get('storage');
    }

    /**
     * @return Samba|null
     */
    public static function create()
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        $storageConfig = Config::get('storage');
        if ($storageConfig['switch']) {
            if (in_array('samba', $storageConfig['types'])) {
                return self::$instance = new self();
            }
        }

        return null;
    }

    /**
     * @param $workgroup
     * @param $shareName
     * @return Filesystem
     * @throws \Icewind\SMB\Exception\DependencyException
     */
    public function prepare($workgroup, $shareName)
    {
        $sambaConfig = $this->config['ext']['samba'];
        $username = $sambaConfig['username'];
        $password = $sambaConfig['password'];
        $host = $sambaConfig['host'];

        $factory = new ServerFactory();
        $auth = new BasicAuth(
            $username, $workgroup, $password
        );
        $server = $factory->createServer($host, $auth);
        $share = $server->getShare($shareName);
        $local = new SmbAdapter($share);
        return new Filesystem($local);
    }
}

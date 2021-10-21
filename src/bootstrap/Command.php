<?php

namespace SwFwLess\bootstrap;

use SwFwLess\components\provider\KernelProvider;

class Command extends Kernel
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param bool $reboot
     * @throws \Exception
     */
    protected function bootstrap($reboot = false)
    {
        $this->setSapi(php_sapi_name());

        parent::bootstrap($reboot);

        KernelProvider::bootCommand();
    }

    public function run()
    {
        echo 'Developing...', PHP_EOL;
    }
}

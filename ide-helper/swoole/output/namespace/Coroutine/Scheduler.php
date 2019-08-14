<?php
namespace Swoole\Coroutine;

class Scheduler
{

    private $_list;

    /**
     * @param $func[required]
     * @param $params[optional]
     * @return mixed
     */
    public function add($func, $params=null){}

    /**
     * @param $n[required]
     * @param $func[optional]
     * @param $params[optional]
     * @return mixed
     */
    public function parallel($n, $func=null, $params=null){}

    /**
     * @param $settings[required]
     * @return mixed
     */
    public function set($settings){}

    /**
     * @return mixed
     */
    public function start(){}


}

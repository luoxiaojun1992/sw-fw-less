<?php

namespace App\components;

use phpDocumentor\Reflection\DocBlockFactory;

/**
 * Class RedLock
 *
 * {@inheritdoc}
 *
 * Redis实现分布式独占锁
 *
 * @method  bool lock(string $key, int $ttl = 0, bool $guard = false) 加独占锁
 * @method  bool unlock(string $key) 释放独占锁
 * @package Lxj\RedLock
 */
class RedLock
{
    private static $instance;

    private $locked_keys = [];

    //方法参数包含以"key"命名的参数，自动为参数添加前缀，详见getKey方法
    private $methods_with_keys = [];

    /**
     * @var RedisPool
     */
    private $redisPool;

    /**
     * @param RedisPool|null $redisPool
     * @return RedLock
     */
    public static function create(RedisPool $redisPool = null)
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self($redisPool);
    }

    /**
     * RedLock constructor.
     * @param RedisPool|null $redisPool
     */
    public function __construct(RedisPool $redisPool = null)
    {
        $this->redisPool = $redisPool;
    }

    /**
     * 加独占锁
     *
     * {@inheritdoc}
     *
     * 通过__call魔术方法调用，勿删除
     *
     * @param     $key
     * @param     int $ttl
     * @param     bool $guard 是否自动释放
     * @redis_key
     * @return    bool
     * @throws \Exception
     */
    private function __lock($key, $ttl = 0, $guard = false)
    {
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick();
        try {
            $redis = $redis->multi(\Redis::PIPELINE);
            //因为redis整数对象有缓存，此处value使用1
            $redis->setnx($key, 1);
            if ($ttl > 0) {
                $redis->expire($key, $ttl);
            }
            $result = $redis->exec();
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
        if ($result[0] > 0) {
            $this->addLockedKey($key, $guard);
            return true;
        }
        return false;
    }

    /**
     * 释放独占锁
     *
     * {@inheritdoc}
     *
     * 通过__call魔术方法调用，勿删除
     *
     * @param     $key
     * @redis_key
     * @return    bool
     * @throws \Exception
     */
    private function __unlock($key)
    {
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick();
        try {
            $result = $redis->del($key);
            if ($result > 0) {
                unset($this->locked_keys[$key]);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }

    private function addLockedKey($key, $guard = false)
    {
        $this->locked_keys[$key] = [
            'key' => $key,
            'guard' => $guard,
        ];
    }

    /**
     * 清除所有锁
     */
    public function flushAll()
    {
        foreach ($this->locked_keys as $locked_key) {
            if (!$locked_key['guard']) {
                $this->__unlock($locked_key['key']);
            }
        }
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed|null
     * @throws \ReflectionException
     */
    public function __call($name, $arguments)
    {
        $method = '__' . $name;
        if (method_exists($this, $method)) {
            $reflection_method = new \ReflectionMethod($this, $method);
            if (!in_array($name, $this->methods_with_keys)) {
                $doc_block = $reflection_method->getDocComment();
                $doc_params = DocBlockFactory::createInstance()->create($doc_block)->getTagsByName('redis_key');
                if (count($doc_params) > 0) {
                    $this->methods_with_keys[$name] = $name;
                }
            }
            if (in_array($name, $this->methods_with_keys)) {
                foreach ($reflection_method->getParameters() as $i => $parameter) {
                    if ($parameter->getName() == 'key') {
                        if (isset($arguments[$i])) {
                            $arguments[$i] = $this->redisPool->getKey($arguments[$i]);
                        }
                    }
                }
            }
            return call_user_func_array([$this, $method], $arguments);
        }
        return null;
    }

    public function __destruct()
    {
        $this->flushAll();
    }
}

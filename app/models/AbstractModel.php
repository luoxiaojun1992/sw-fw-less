<?php

namespace App\models;

use App\components\Query;
use Aura\SqlQuery\QueryInterface;

class AbstractModel
{
    protected static $table = '';

    /** @var QueryInterface|Query */
    protected $query;

    private $attributes = [];

    /**
     * @return AbstractModel
     */
    public static function select()
    {
        return new static(Query::select()->from(static::$table));
    }

    /**
     * @return AbstractModel
     */
    public static function update()
    {
        return new static(Query::update()->table(static::$table));
    }

    /**
     * @return AbstractModel
     */
    public static function insert()
    {
        return new static(Query::insert()->into(static::$table));
    }

    /**
     * @return AbstractModel
     */
    public static function delete()
    {
        return new static(Query::delete()->from(static::$table));
    }

    /**
     * AbstractModel constructor.
     * @param QueryInterface|Query $query
     */
    public function __construct($query = null)
    {
        if ($query) {
            $this->query = $query;
        }
    }

    /**
     * @param $attributes
     * @return $this
     */
    private function setAttributes($attributes)
    {
        foreach ($attributes as $name => $value) {
            $setter = 'set' . str_replace('_', '', ucwords($name));
            if (method_exists($this, $setter)) {
                call_user_func_array([$this, $setter], [$value]);
            } else {
                $this->attributes[$name] = $value;
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return AbstractModel
     */
    public function first()
    {
        $result = $this->query->first();
        return (new static())->setAttributes($result);
    }

    /**
     * @return array
     */
    public function get()
    {
        $models = [];
        $results = $this->query->get();
        foreach ($results as $result) {
            $models[] = (new static())->setAttributes($result);
        }

        return $models;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed|null
     */
    public function __call($name, $arguments)
    {
        call_user_func_array([$this->query, $name], $arguments);
        return $this;
    }
}

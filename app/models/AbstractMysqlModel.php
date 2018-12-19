<?php

namespace App\models;

use App\components\ModelQuery;
use Aura\SqlQuery\Common\DeleteInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\UpdateInterface;
use Aura\SqlQuery\QueryInterface;

abstract class AbstractMysqlModel extends AbstractModel
{
    protected static $table = '';

    /**
     * @return ModelQuery|QueryInterface|SelectInterface|InsertInterface|DeleteInterface|UpdateInterface
     */
    public static function select()
    {
        return ModelQuery::select()->from(static::$table)->setModelClass(static::class);
    }

    /**
     * @return ModelQuery|QueryInterface|SelectInterface|InsertInterface|DeleteInterface|UpdateInterface
     */
    public static function update()
    {
        return ModelQuery::update()->table(static::$table)->setModelClass(static::class);
    }

    /**
     * @return ModelQuery|QueryInterface|SelectInterface|InsertInterface|DeleteInterface|UpdateInterface
     */
    public static function insert()
    {
        return ModelQuery::insert()->into(static::$table)->setModelClass(static::class);
    }

    /**
     * @return ModelQuery|QueryInterface|SelectInterface|InsertInterface|DeleteInterface|UpdateInterface
     */
    public static function delete()
    {
        return ModelQuery::delete()->from(static::$table)->setModelClass(static::class);
    }

    /**
     * @return bool
     */
    public function save()
    {
        $primaryKey = static::$primaryKey;

        $attributes = $this->toArray();

        if (count($attributes) > 0) {
            $primaryValue = $this->{$primaryKey};
            if ($primaryValue) {
                if (count($attributes) > 1) {
                    $updateBuilder = static::update();
                    $updateBuilder->where("`{$primaryKey}` = :primaryValue", ['primaryValue' => $primaryValue]);
                    foreach ($attributes as $attributeName => $attribute) {
                        $updateBuilder->col($attributeName);
                        $updateBuilder->bindValue($attributeName, $this->{$attributeName});
                    }
                    $updateBuilder->write();
                    return true;
                }
            } else {
                $insertBuilder = static::insert();
                foreach ($attributes as $attributeName => $attribute) {
                    $insertBuilder->col($attributeName);
                    $insertBuilder->bindValue($attributeName, $this->{$attributeName});
                }
                return $insertBuilder->write() > 0;
            }
        }

        return false;
    }
}

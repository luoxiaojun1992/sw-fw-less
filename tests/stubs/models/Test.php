<?php

class Test extends \SwFwLess\models\AbstractModel
{
    public $id;

    public $foo;

    public function save()
    {
        $this->fireEvent('creating');
        $this->fireEvent('created');
    }

    protected function beforeCreate($validate = false)
    {
        $this->foo = 'bar';
    }

    protected function afterCreate()
    {
        $this->id = 1;
    }
}

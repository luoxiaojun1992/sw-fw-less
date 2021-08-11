<?php

namespace SwFwLess\components\volcano;

class Executor extends AbstractOperator
{
    public function open()
    {
        // TODO: Implement open() method.
    }

    public function next()
    {
        return $this->nextOperator->next();
    }

    public function close()
    {
        // TODO: Implement close() method.
    }

    public function execute()
    {
        return $this->next();
    }

    public function setPlan(AbstractOperator $plan)
    {
        return $this->setNext($plan);
    }

    protected function explainOperator(?AbstractOperator $operator)
    {
        if (is_null($operator)) {
            return null;
        }
        return [
            'class' => get_class($operator),
            'info' => $operator->info(),
            'sub_operator' => $this->explainOperator($operator->nextOperator)
        ];
    }

    public function explain()
    {
        return $this->explainOperator($this->nextOperator);
    }
}

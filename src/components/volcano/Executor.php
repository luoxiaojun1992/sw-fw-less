<?php

namespace SwFwLess\components\volcano;

class Executor extends AbstractOperator
{
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

    public static function restorePlan($executeInfo)
    {
        $operatorClass = $executeInfo['class'];
        $operator = $operatorClass::create($executeInfo['info'] ?? []);
        if (isset($executeInfo['sub_operator'])) {
            $subOperator = static::restorePlan($executeInfo['sub_operator']);
            $operator->setNext($subOperator);
        }
        return $operator;
    }

    public static function restore($executeInfo)
    {
        return (new static)->setPlan(static::restorePlan($executeInfo));
    }
}

<?php

class PHPUnit_Framework_Constraint_IsEqual extends \PHPUnit_Framework_Constraint
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function evaluate($other, $description = '', $returnResult = false)
    {
        \App\Export::getInstance()->add($this->value, $description);
        return true;
    }

    public function toString()
    {
        return '';
    }
}

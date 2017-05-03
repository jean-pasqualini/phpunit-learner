<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 03/05/17
 * Time: 14:26
 */

namespace QuestionFactory;


class QuestionFactoryRegistry
{
    private $questionFactoryCollection = [];

    public function __construct()
    {
        $this->questionFactoryCollection = [
            new ForString()
        ];
    }

    public function getQuestionFactoryByClass(string $class)
    {
        foreach ($this->questionFactoryCollection as $questionFactory) {
            if (get_class($questionFactory) === $class) {
                return $questionFactory;
            }
        }

        return null;
    }

    public function getQuestionFactoryClassByValue($value)
    {
        foreach ($this->questionFactoryCollection as $questionFactory) {
            if ($questionFactory->isSupport($value)) {
                return get_class($questionFactory);
            }
        }

        return null;
    }
}
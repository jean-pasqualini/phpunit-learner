<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 02/05/17
 * Time: 14:17
 */

namespace QuestionFactory;


class ForBoolean
{
    public function generate(bool $expect):array
    {
        return [
            [
                'question' => 'oui ou non',
                'choices' => [true => 'oui', false => 'non'],
                'response' => $expect
            ]
        ];
    }

    public function isSupport($value)
    {
        return is_bool($value);
    }
}
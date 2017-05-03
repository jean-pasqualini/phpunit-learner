<?php

namespace QuestionFactory;

class ForArrayOfString extends AbstractQuestionFactory
{
    public function generate(array $expect)
    {
        foreach($expect as $item) {
            yield [
                'question' => $this->textToHoleFormatter($item),
                'choices' => [],
                'response' => $item
            ];
        }
    }

    public function isSupport($value)
    {
        if (
            !is_array($value)
            || in_array(false, array_map('is_string', $value))
        ) {
            return false;
        }

        return true;
    }
}
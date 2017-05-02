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

    public function isSupport($type)
    {
        return 'string' === $type;
    }
}
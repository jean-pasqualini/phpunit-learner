<?php

namespace QuestionFactory;

class ForString extends AbstractQuestionFactory
{
    public function generate($expect):array
    {
        $question = $expect;
        $question = $this->classShortNameFormatter($question);
        $question = $this->textToHoleFormatter($question);

        return [
            [
                'question' => $question,
                'choices' => [],
                'response' => $expect
            ]
        ];
    }

    public function isSupport($value)
    {
        return is_string($value);
    }
}
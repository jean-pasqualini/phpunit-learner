<?php

namespace QuestionFactory;

class AbstractQuestionFactory
{
    private function formatSnakeCase($value)
    {
        $stringSplitted = str_split($value);

        foreach($stringSplitted as $position => $character)
        {
            $previousCharacter = $value[$position - 1] ?? null;
            $nextCharacter = $value[$position + 1] ?? null;

            $start = $position === 0;
            $end = $position === count($stringSplitted) - 1;

            if (!$start && !$end && !in_array('_', [$previousCharacter, $nextCharacter, $character])) {
                $value[$position] = '.';
            }
        }

        return $value;
    }

    private function formatCamelCase($value)
    {
        $stringSplitted = str_split($value);

        foreach($stringSplitted as $position => $character)
        {
            $start = $position === 0;
            $end = $position === count($stringSplitted) - 1;

            if (!$start && !$end && !preg_match('/[A-Z]{1}/', $character)) {
                $value[$position] = '.';
            }
        }

        return $value;
    }

    protected function classShortNameFormatter($value)
    {
        if (class_exists($value)) {
            return substr(strrchr($value, "\\"), 1);
        } else {
            echo $value.PHP_EOL;
        }

        return $value;
    }

    protected function textToHoleFormatter($value)
    {
        $isSnakeCase = (preg_match('/[^A-Z ]+/', $value) && preg_match('/[_]+/', $value));
        $isCamelCase = (preg_match('/[^_ ]+/', $value));

        if ($isSnakeCase) {
            $value = $this->formatSnakeCase($value);
        } elseif ($isCamelCase) {
            $value = $this->formatCamelCase($value);
        } else {
            $value = $value[0] . str_repeat('.', strlen($value) - 2) . $value[strlen($value) - 1];
        }

        return $value;
    }

}
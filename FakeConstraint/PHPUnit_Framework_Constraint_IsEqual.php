<?php

class PHPUnit_Framework_Constraint_IsEqual
{
    private $arguments;
    public static $export = [];

    public function __construct()
    {
        $this->arguments = func_get_args();
    }

    public function evaluate($other, $description = '', $returnResult = false)
    {
        $export = ['export' => false];

        $stack = debug_backtrace(false);
        $location = sprintf(
            '%s:%s:%s',
            $stack[3]['class'],
            $stack[3]['function'],
            $stack[2]['function']
        );
        $lines = file($stack[2]['file']);
        $export['description'] = substr($description, 0, 200);
        $export['code'] = trim(implode(PHP_EOL, array_slice($lines, $stack[2]['line'] - 1, 1)));

        $value = $this->arguments[0];
        // Si est un tableau de un seul niveau constitué uniquement de chaine
        $yes = true;
        if (is_array($value)) {
            foreach ($value as $elmt) {
                if (is_array($elmt) || !is_string($elmt)) {
                    $yes = false;
                }
            }
        } else {
            $yes = false;
        }

        if ($yes) {
            $export['location'] = $location;
            $export['assert'] = [
                'type' => 'array_with_one_depth',
                'expect' => $value,
            ];
            $export['export'] = true;
        }

        if(is_bool($value)) {
            $export['location'] = $location;
            $export['assert'] = [
                'type' => 'boolean',
                'expect' => $value
            ];
            $export['export'] = true;
        }

        if(is_string($value)) {
            $export['location'] = $location;
            $export['assert'] = [
                'type' => 'string',
                'expect' => $value
            ];
            $export['export'] = true;
        }

        if ($export['export']) {
            \App\PHPUnit_Framework_FakeConstraint::$export[] = $export;
        }
        return true;
    }

    public function toString()
    {
        return 'mmmmmmm';
    }
}

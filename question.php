<?php
namespace App;


use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\ArgvInput;

require_once __DIR__.'/vendor/autoload.php';

$output = new ConsoleOutput();
$output->getFormatter()->setStyle('white', new OutputFormatterStyle('black', 'white', array('bold')));

$command = new Command();
$command->configure($inputDefinition = new InputDefinition());
$command->execute(new ArgvInput(null, $inputDefinition), $output);


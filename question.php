<?php
namespace App;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\ArrayInput;

require_once __DIR__.'/vendor/autoload.php';

$output = new ConsoleOutput();
$output->getFormatter()->setStyle('white', new OutputFormatterStyle('black', 'white', array('bold', 'blink')));

$symfonyStyle = new SymfonyStyle(new ArrayInput([]), $output);

$inputDefinition = new InputDefinition();
$inputDefinition->addOption(new InputOption('list'));
$inputDefinition->addOption(new InputOption('filter', null, InputOption::VALUE_REQUIRED));

$input = new ArgvInput(null, $inputDefinition);

$listMode = $input->getOption('list');
$filter = $input->getOption('filter');

$export = json_decode(file_get_contents(__DIR__ . '/questions.json'), true);

if($listMode)
{
    $symfonyStyle->table(['classe', 'method', 'assert', 'description'], array_map(function($item) { return array_merge(explode(':', $item['location']), [$item['description']]); }, $export));
    exit();
}

if($filter) {
    $export = array_filter($export, function($item) use ($filter)
    {
        return strpos($item['location'], $filter) !== false;
    });
}

class Runner {
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

    public function start($export, SymfonyStyle $symfonyStyle)
    {
        foreach($export as $exportItem) {
            if(isset($exportItem['assert']))
            {
                switch($exportItem['assert']['type'])
                {
                    case 'array_with_one_depth':
                        $reponses = $exportItem['assert']['expect'];

                        $symfonyStyle->writeln('<comment>3 erreurs max</comment>');
                        $symfonyStyle->writeln(sprintf('<comment>il y a %s elements à deviner</comment>', count($reponses)));
                        $symfonyStyle->writeln(sprintf('<options=bold>%s</>', $exportItem['description']));
                        $symfonyStyle->writeln(sprintf('<question>%s</question>', $exportItem['location']));

                        $questions = $exportItem['assert']['expect'];
                        foreach($questions as $id => $question) {

                            $isSnakeCase = (preg_match('/[^A-Z ]+/', $question) && preg_match('/[_]+/', $question));
                            $isCamelCase = (preg_match('/[^_ ]+/', $question));

                            if ($isSnakeCase) {
                                $questions[$id] = $this->formatSnakeCase($questions[$id]);
                            } elseif ($isCamelCase) {
                                $questions[$id] = $this->formatCamelCase($questions[$id]);
                            } else {
                                $questions[$id] = $question[0] . str_repeat('.', strlen($question) - 2) . $question[strlen($question) - 1];
                            }
                        }

                        $errorCount = 0;
                        foreach($questions as $id => $question)
                        {
                            if($errorCount >= 3)
                            {
                                $symfonyStyle->error('VOUS AVEZ PERDU');
                                break;
                            }

                            $userReponse = $symfonyStyle->ask(sprintf('<white>%s ?</white>', $question));
                            if($userReponse == $reponses[$id])
                            {
                                $symfonyStyle->success('OUI');
                            } else {
                                $symfonyStyle->error('NON C\'ETAIS \''.$reponses[$id].'\'');
                                $errorCount ++;
                            }
                        }
                        break;

                        case 'boolean':
                            $symfonyStyle->writeln(sprintf('<options=bold>%s</>', $exportItem['description']));
                            $symfonyStyle->writeln(sprintf('<question>%s</question>', $exportItem['location']));
                            $symfonyStyle->writeln(sprintf('<comment>%s</comment>', $exportItem['code']));

                            $userReponse = $symfonyStyle->choice('oui ou non', [
                                'oui' => 'oui',
                                'non' => 'non'
                            ]);
                        break;
                }
            }
        }
    }
}

$run = new Runner();
$run->start($export, $symfonyStyle);


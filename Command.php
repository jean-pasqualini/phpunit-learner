<?php

namespace App;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;

class Command {
    public function configure(InputDefinition $inputDefinition)
    {
        $inputDefinition->addOption(new InputOption('list'));
        $inputDefinition->addOption(new InputOption('filter', null, InputOption::VALUE_REQUIRED));
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $symfonyStyle = new SymfonyStyle(new ArrayInput([]), $output);
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

        $run = new Runner();
        $run->start($export, $symfonyStyle);
    }
}
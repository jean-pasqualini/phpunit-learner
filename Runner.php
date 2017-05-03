<?php
namespace App;

use QuestionFactory\ForString;
use QuestionFactory\QuestionFactoryRegistry;
use Symfony\Component\Console\Style\SymfonyStyle;

class Runner {

    private $questionFactoryRegistry;

    public function __construct()
    {
        $this->questionFactoryRegistry = new QuestionFactoryRegistry();
    }

    public function start($export, SymfonyStyle $symfonyStyle)
    {
        foreach($export as $exportItem) {
            if(isset($exportItem['assert']))
            {
                $questionFactory = $this->questionFactoryRegistry->getQuestionFactoryByClass($exportItem['assert']['type']);
                if (null !== $questionFactory) {
                    $errorCount = 0;

                    if($errorCount >= 3) {
                        $symfonyStyle->error('VOUS AVEZ PERDU');
                        return;
                    }

                    $symfonyStyle->writeln(sprintf('<options=bold>%s</>', $exportItem['description']));
                    $symfonyStyle->writeln(sprintf('<question>%s</question>', $exportItem['location']));
                    $symfonyStyle->writeln(sprintf('<comment>%s</comment>', $exportItem['code']));

                    $questions = $questionFactory->generate($exportItem['assert']['expect']);

                    foreach($questions as $question)
                    {
                        //$symfonyStyle->writeln('<comment>3 erreurs max</comment>');
                        //$symfonyStyle->writeln(sprintf('<comment>il y a %s elements à deviner</comment>', count($reponses)));
                        if(empty($question['choices'])) {
                            $userReponse = $symfonyStyle->ask(sprintf('<white>%s ?</white>', $question['question']));
                        } else {
                            $userReponse = null;
                        }

                        if($userReponse == $question['response']) {
                            $symfonyStyle->success('OUI');
                        } else {
                            $symfonyStyle->error('NON C\'ETAIS \''.$question['response'].'\'');
                            $errorCount ++;
                        }
                    }
                }
            }
        }
    }
}
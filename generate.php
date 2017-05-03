<?php
namespace App {

    use Composer\Autoload\ClassLoader;
    use QuestionFactory\QuestionFactoryRegistry;
    use Symfony\Component\Console\Input\ArgvInput;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputDefinition;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\ConsoleOutput;
    use Symfony\Component\Console\Style\SymfonyStyle;

    /** @var ClassLoader $appLoader */
    $appLoader = require __DIR__ . '/vendor/autoload.php';
    $appLoader->register();

    $inputDefinition = new InputDefinition();
    $inputDefinition->addArgument(new InputArgument('out_file_path'));
    $inputDefinition->addOption(new InputOption('configuration', 'c', InputOption::VALUE_REQUIRED));
    $input = new ArgvInput(null, $inputDefinition);
    $output = new ConsoleOutput();
    $symfonyStyle = new SymfonyStyle($input, $output);

    $configurationFile = $input->getOption('configuration');
    $projectDir = pathinfo($configurationFile, PATHINFO_DIRNAME);
    $projectAutoload = $projectDir . '/vendor/autoload.php';
    $outFilePath = $input->getArgument('out_file_path');
    array_shift($_SERVER['argv']);

    if (!file_exists($projectAutoload)) {
        $symfonyStyle->error(sprintf('the project autoload %s do not exists', $projectAutoload));
        exit();
    }

    $symfonyStyle->title('PHPUNIT LEARNER');

    class Export {

        private $export = array();

        private static $instance;

        public static function getInstance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        private function exportGlobalInformations($description)
        {
            $start = 4;

            $stack = debug_backtrace(false);
            $location = sprintf(
                '%s:%s:%s',
                $stack[$start + 1]['class'],
                $stack[$start + 1]['function'],
                $stack[$start]['function']
            );
            $lines = file($stack[$start]['file']);
            $export['description'] = substr($description, 0, 200);
            $export['code'] = trim(implode(PHP_EOL, array_slice($lines, $stack[$start]['line'] - 1, 1)));
            $export['location'] = $location;

            return $export;
        }

        public function add($value, $description = '')
        {
            $questionFactoryClass = (new QuestionFactoryRegistry())->getQuestionFactoryClassByValue($value);

            if (null !== $questionFactoryClass) {
                $export = $this->exportGlobalInformations($description);
                $export['assert'] = [
                    'type' => $questionFactoryClass,
                    'expect' => $value
                ];

                $this->export[] = $export;
            }
        }

        public function dump($filename)
        {
            return file_put_contents($filename, json_encode($this->export, JSON_PRETTY_PRINT));
        }
    }

    $appLoader->loadClass(\PHPUnit_Framework_Constraint_IsEqual::class);

    $appLoader->unregister();
    require_once $projectAutoload;
    $appLoader->register();

    \PHPUnit_TextUI_Command::main(false);
    if (false !== Export::getInstance()->dump($outFilePath)) {
        $symfonyStyle = new SymfonyStyle($input, new ConsoleOutput());
        $symfonyStyle->success('survey generated in ' . $outFilePath);
    }
}
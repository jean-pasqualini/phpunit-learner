<?php
namespace App {

    use Composer\Autoload\ClassLoader;
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
    }

    define('OUT_FILE_PATH', $outFilePath);

    $appLoader->unregister();

    /** @var ClassLoader $projectLoader */
    $projectLoader = require_once $projectAutoload;

    abstract class PHPUnit_Framework_FakeConstraint extends \PHPUnit_Framework_Constraint
    {
        private $arguments;
        public static $export = [];

        abstract protected function getConstraintName();

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

            switch ($this->getConstraintName()) {
                case \PHPUnit_Framework_Constraint_IsEqual::class:
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

                    break;

                default:
                    break;
            }

            if ($export['export']) {
                self::$export[] = $export;
            }
            return true;
        }

        public function toString()
        {
            return 'mmmmmmm';
        }
    }

    class TestRunner extends \PHPUnit_TextUI_TestRunner
    {
        public static $currentTest;

        /**
         * {@inheritdoc}
         */
        protected function handleConfiguration(array &$arguments)
        {
            $listener = new Class extends \PHPUnit_Framework_BaseTestListener
            {
            };

            $result = parent::handleConfiguration($arguments);
            $arguments['listeners'] = isset($arguments['listeners']) ? $arguments['listeners'] : array();
            $registeredLocally = false;

            if (!$registeredLocally) {
                $arguments['listeners'][] = $listener;
            }
            return $result;
        }
    }

    class Command extends \PHPUnit_TextUI_Command
    {
        protected function createRunner()
        {
            return new TestRunner($this->arguments['loader']);
        }
    }
}

namespace {

    use App\PHPUnit_Framework_FakeConstraint;

    class PHPUnit_Framework_Constraint_IsEqual extends PHPUnit_Framework_FakeConstraint
    {
        public function getConstraintName()
        {
            return __CLASS__;
        }
    }
}

namespace App {
    Command::main(false);
    file_put_contents(OUT_FILE_PATH, json_encode(PHPUnit_Framework_FakeConstraint::$export, JSON_PRETTY_PRINT));
    echo PHP_EOL.' QUESTIONNAIRE GENERER !!! '.PHP_EOL;
}
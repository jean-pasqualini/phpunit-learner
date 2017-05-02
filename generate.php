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

    [
        \PHPUnit_Framework_Constraint_IsEqual::class
    ];

    $appLoader->unregister();

    /** @var ClassLoader $projectLoader */
    $projectLoader = require_once $projectAutoload;

    abstract class PHPUnit_Framework_FakeConstraint extends \PHPUnit_Framework_Constraint
    {
        public static $export = [];
    }
}

namespace App {
    \PHPUnit_TextUI_Command::main(false);
    file_put_contents(OUT_FILE_PATH, json_encode(PHPUnit_Framework_FakeConstraint::$export, JSON_PRETTY_PRINT));
    echo PHP_EOL.' QUESTIONNAIRE GENERER !!! '.PHP_EOL;
}
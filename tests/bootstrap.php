<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php')) {
    require dirname(__DIR__) . '/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

// boot the framework kernel in the "test" environment
$kernel = new Kernel('test', true);
$kernel->boot();

// initialize the console application with the necessary commands
$application = new Application($kernel);
$application->setAutoExit(false);

// Prepare output buffers
$output = new ConsoleOutput(OutputInterface::VERBOSITY_NORMAL, true);
$silent = new NullOutput();

// Drop the current database
$application->run(new ArrayInput([
    'command' => 'doctrine:database:drop',
    '--force' => true,
    '--if-exists' => true,
]), $output);

// Create an empty database
$application->run(new ArrayInput([
    'command' => 'doctrine:database:create',
    '--verbose' => true,
]), $output);

// Run any new migrations
$application->run(new ArrayInput([
    'command' => 'doctrine:migrations:migrate',
    '--no-interaction' => true,
]), $silent);
$output->writeln('<info>Migrations have been run.</info>');

// bootstrap complete
$kernel->shutdown();

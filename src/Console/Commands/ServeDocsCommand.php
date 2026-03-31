<?php
namespace Console\Commands;

use Console\Console;
use Console\ConsoleCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Runs built-in PHP server for serving Doctum API documentation.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/console#local-servers">here</a>.
 */
class ServeDocsCommand extends ConsoleCommand {
    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        $this->setName('serve:api')
            ->setDescription('Starts built-in PHP server for API documentation')
            ->setHelp('Run php console serve:api and navigate to http://localhost:8001')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Host Address', 'localhost')
            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'Port number', 8001);
    }

    /**
     * Executes the command.
     */
    protected function handle(): int
    {
        $host = $this->getOption('host');
        $message = "Enter name/IP Address for host";
        Console::argOptionValidate($host, $message, $this->question(), ['required'], true);

        $port = $this->getOption('port');
        $message = "Enter value for an unused port";
        Console::argOptionValidate($port, $message, $this->question(), ['integer', 'required', "isPortUsed:$host"], true);

        // Define the Doctum documentation directory
        $docsDir = ROOT.DS.'vendor'.DS.'chappy-php'.DS.'chappy-php-framework'.DS.'docs';

        if (!is_dir($docsDir)) {
            $this->output->writeln("<error>Doctum documentation directory not found: $docsDir</error>");
            return self::FAILURE;
        }

        $this->output->writeln("<info>Starting Doctum server at http://{$host}:{$port}</info>");
        $this->output->writeln("<info>Press Ctrl+C to stop the server.</info>");

        // Run PHP built-in server
        $command = sprintf('php -S %s:%s -t %s', escapeshellarg($host), escapeshellarg($port), escapeshellarg($docsDir));
        passthru($command);

        return self::SUCCESS;
    }
}

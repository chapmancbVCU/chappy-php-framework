<?php
namespace Console\Commands;

use Console\Console;
use Console\ConsoleCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Runs built-in PHP server.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/console#local-servers">here</a>.
 */
class ServeCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('serve')
            ->setDescription('Starts built-in PHP server')
            ->setHelp('run php console serve and navigate to localhost:8000')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Host Address', 'localhost')
            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'Port number', 8000);
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $host = $this->getOption('host');
        $message = "Enter name/IP Address for host";
        Console::argOptionValidate($host, $message, $this->question(), ['required'], true);

        $port = $this->getOption('port');
        $message = "Enter value for an unused port";
        Console::argOptionValidate($port, $message, $this->question(), ['integer', 'required',  "isPortUsed:$host"], true);

        $this->output->writeln("<info>Starting PHP development server at http://{$host}:{$port}</info>");
        $this->output->writeln("<info>Press Ctrl+C to stop the server.</info>");

        // Run PHP built-in server
        $command = sprintf('php -S %s:%s -t . server.php', escapeshellarg($host), escapeshellarg($port));
        passthru($command);

        return self::SUCCESS;
    }
}

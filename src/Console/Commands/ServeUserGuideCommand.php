<?php
namespace Console\Commands;

use Console\Console;
use Console\ConsoleCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Performs the command for serving the Jekyll user guide locally.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/console#local-servers">here</a>.
 */
class ServeUserGuideCommand extends ConsoleCommand {
    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        $this
            ->setName('serve:docs')
            ->setDescription('Serves the user guide locally using Jekyll')
            ->setHelp('Run php console serve:docs --host=127.0.0.1 --port=4000 to serve the user guide')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Host address', '127.0.0.1')
            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'Port number', 4000);
    }

    /**
     * Executes the command.
     */
    protected function handle(): int
    {
        $host = $this->getOption('host') ?: '127.0.0.1';
        $message = "Enter name/IP Address for host";
        Console::argOptionValidate($host, $message, $this->question(), ['required'], true);

        $port = (int) $this->getOption('port') ?: 4000;
        $message = "Enter value for an unused port";
        Console::argOptionValidate($port, $message, $this->question(), ['integer', 'required',  "isPortUsed:$host"], true);

        // Change to the `docs` directory and serve the Jekyll site with specified host and port
        $command = sprintf('cd docs && bundle exec jekyll serve --host=%s --port=%d', escapeshellarg($host), $port);

        $this->output->writeln("<info>Starting Jekyll server at http://{$host}:{$port}</info>");
        $this->output->writeln("<info>Press Ctrl+C to stop the server.</info>");

        // Execute command and capture output
        $process = popen($command, 'r');

        if (!$process) {
            $this->output->writeln('<error>Failed to start Jekyll server</error>');
            return self::FAILURE;
        }

        // Stream output to console
        while (!feof($process)) {
            $line = fgets($process);
            if ($line !== false) {
                $this->output->writeln(trim($line));
            }
        }
        
        pclose($process);

        return self::SUCCESS;
    }
}

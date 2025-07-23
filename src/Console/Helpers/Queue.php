<?php
declare(strict_types=1);
namespace Console\Helpers;
use Core\Lib\Queue\QueueManager;

/**
 * Supports commands related to queues.
 */
class Queue {
    /**
     * Template for queue migration.
     *
     * @param string $fileName The file and class name.
     * @return string The contents for the queue migration.
     */
    public static function queueTemplate(string $fileName): string {
        return '<?php
namespace Database\Migrations;
use Core\Lib\Database\Schema;
use Core\Lib\Database\Blueprint;
use Core\Lib\Database\Migration;

/**
 * Migration class for the queue table.
 */
class '.$fileName.' extends Migration {
    /**
     * Performs a migration for a new table.
     *
     * @return void
     */
    public function up(): void {
        Schema::create(\'queue\', function (Blueprint $table) {
            $table->id();
            $table->string(\'queue\')->default(\'default\');
            $table->text(\'payload\');
            $table->unsignedInteger(\'attempts\')->default(0);
            $table->timestamp(\'reserved_at\')->nullable();
            $table->timestamp(\'available_at\');
            $table->timestamp(\'created_at\');
        });
    }

    /**
     * Undo a migration task.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists(\'queue\');
    }
}
';
    }

    /**
     * Creates new queue migration.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function queueMigration(): int {
        $fileName = Migrate::fileName();
        return Tools::writeFile(
            Migrate::MIGRATIONS_PATH.$fileName.'.php',
            self::queueTemplate($fileName),
            'Queue migration'
        );
    }

    public static function worker(string $queueName = 'default'): void {
        // Load config
        $config = require CHAPPY_BASE_PATH . DS . 'config' . DS . 'queue.php';

        // Init manager
        $queue = new QueueManager($config);

        // Handle shutdown signals
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, function() { echo "Worker shutting down...\n"; exit; });
        pcntl_signal(SIGINT, function() { echo "Worker interrupted...\n"; exit; });

        echo "Worker started on queue: {$queueName}\n";

        $iterations = 0;
        $maxIterations = 1000; // restart periodically

        while (true) {
            $job = $queue->pop($queueName);

            if ($job) {
                try {
                    echo "Processing job: " . json_encode($job['payload']) . PHP_EOL;

                    // TODO: dispatch to appropriate Job class here:
                    // JobDispatcher::dispatch($job['payload']);

                    if ($job['id']) {
                        $queue->delete($job['id']);
                    }
                } catch (\Exception $e) {
                    echo "Job failed: " . $e->getMessage() . PHP_EOL;
                    // Optionally requeue or record failed job
                }
            }

            usleep(500000); // wait 0.5s before polling again
            $iterations++;
            if ($iterations >= $maxIterations) {
                echo "Restarting worker after {$maxIterations} iterations.\n";
                break;
            }
        }
    }

}
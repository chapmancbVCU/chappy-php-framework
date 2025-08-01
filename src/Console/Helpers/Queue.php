<?php
declare(strict_types=1);
namespace Console\Helpers;
use Core\Lib\Queue\QueueManager;

/**
 * Supports commands related to queues.
 */
class Queue {
    protected static string $jobsPath = CHAPPY_BASE_PATH.DS.'app'.DS.'Jobs'.DS;

    /**
     * Template for Jobs class.
     *
     * @param string $jobName The name of the job.
     * @return string The content of the job class.
     */
    public static function jobTemplate(string $jobName): string {
        return '<?php

namespace App\Jobs;

use Core\Lib\Queue\QueueableJobInterface;

class '.$jobName.' implements QueueableJobInterface {
    protected array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function handle(): void {
        // Implement your handle.
    }

    public function toPayload(): array {
        return [];
    }
}
';
    }

    /**
     * Creates a new job class.
     *
     * @param string $jobName The name of the job class.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeJob(string $jobName): int {
        Tools::pathExists(self::$jobsPath);
        $fullPath = self::$jobsPath.$jobName.'.php';
        return Tools::writeFile(
            $fullPath,
            self::jobTemplate($jobName),
            'Job'
        );
    }

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

    /**
     * Worker for queue.
     *
     * @param string $queueName The name of the queue to run.
     * @param int $max The number of times to run worker.
     * @param bool $once Runs worker for one iteration if set to true.
     * @return void
     */
    public static function worker(string $queueName = 'default', int $max = 0, bool $once = false): void {
        // Load config
        $config = require CHAPPY_BASE_PATH . DS . 'config' . DS . 'queue.php';

        // Init manager
        $queue = new QueueManager($config);

        // Handle shutdown signals
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, function() { echo "Worker shutting down...\n"; exit; });
        pcntl_signal(SIGINT, function() { echo "Worker interrupted...\n"; exit; });

        echo "Worker started on queue: {$queueName}\n";

        if($once) {
            $maxIterations = 1;
        } else if($max > 0) {
            $maxIterations = $max;
        } else {
            $maxIterations = 1000;
        }

        for($i = 0; $i < $maxIterations; $i++) {
            $job = $queue->pop($queueName);

            if ($job) {
                try {
                    echo "Processing job: " . json_encode($job['payload']) . PHP_EOL;

                    $payload = $job['payload'];
                    $jobClass = $payload['job'] ?? null;
                    $data = $payload['data'] ?? [];

                    if (!$jobClass || !class_exists($jobClass)) {
                        throw new \Exception("Invalid job class: " . ($jobClass ?? 'null'));
                    }

                    $instance = new $jobClass($data);
                    $instance->handle();

                    if ($job['id']) {
                        $queue->delete($job['id']);
                    }
                } catch (\Exception $e) {
                    echo "Job failed: " . $e->getMessage() . PHP_EOL;
                    // Optionally requeue or record failed job
                }
            }

            usleep(500000); // wait 0.5s before polling again
        }
    }

}
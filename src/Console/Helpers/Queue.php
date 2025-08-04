<?php
declare(strict_types=1);
namespace Console\Helpers;
use Exception;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Config;
use Core\Lib\Queue\QueueManager;
use Core\Lib\Utilities\DateTime;
use Core\Models\Queue as QueueModel;
use Symfony\Component\Console\Command\Command;

/**
 * Supports commands related to queues.
 */
class Queue {
    protected static string $jobsPath = CHAPPY_BASE_PATH.DS.'app'.DS.'Jobs'.DS;

    /**
     * Handles tasks related to exceptions and retry of jobs.
     *
     * @param Exception $e The exception.
     * @param array $job The array of jobs
     * @return void
     */
    public static function exceptionMessaging(Exception $e, array $job): void {
        Tools::info("Job failed: " . $e->getMessage(), 'warning');
        $payload = $job['payload'] ?? [];
        $maxAttempts = $payload['max_attempts'] ?? Config::get('queue.max_attempts', 3);

        if(Arr::exists($job, 'id')) {
            $queueModel = QueueModel::findById($job['id']); 
        }

        if ($queueModel) {
            $queueModel->attempts += 1;
            $queueModel->exception = $e->getMessage() . "\n" . $e->getTraceAsString();

            if ($queueModel->attempts >= $maxAttempts) {
                $queueModel->failed_at = DateTime::timeStamps();
                Tools::info('Job permanently failed and marked as failed.', 'warning');
            } else {
                $queueModel->available_at = DateTime::nowPlusSeconds(10);
                $decoded = json_decode($queueModel->payload, true);
                $decoded['attempts'] = $queueModel->attempts;
                $queueModel->payload = json_encode($decoded);
                Tools::info("Job will be retried. Attempt: {$queueModel->attempts}", 'warning');
            }

            $queueModel->save();
        }
    }

    /**
     * Deletes a job
     *
     * @param int $jobId Id of the job to delete.
     * @param QueueManager $queue The QueueManager instance.
     * @return void
     */
    public static function deleteJob(array $job, QueueManager $queue): void {
        if (Arr::exists($job, 'id') && $job['id']) {
            $queue->delete($job['id']);
        }
    }

    /**
     * Test if name of job class is valid.
     *
     * @param string $jobClass The name of class to test.
     * @return void
     */
    public static function isValidJob(string $jobClass): void {
        if (!$jobClass || !class_exists($jobClass)) {
            throw new Exception("Invalid job class: " . ($jobClass ?? 'null'));
        }
    }

    /**
     * Determines number of iterations to run worker
     *
     * @param int $max $max The number of times to run worker.
     * @param bool $once Runs worker for one iteration if set to true.
     * @return int The number of iterations.
     */
    public static function iterations(?int $max = 0, bool $once = false): int {
        if($once) {
            return  1;
        } else if($max > 0) {
            return $max;
        } 
        return 1000;
    }

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
            $table->index(\'queue\');
            $table->text(\'payload\');
            $table->text(\'exception\')->nullable();
            $table->unsignedInteger(\'attempts\')->default(0);
            $table->timestamp(\'reserved_at\')->nullable();
            $table->timestamp(\'available_at\');
            $table->index(\'available_at\');
            $table->timestamp(\'failed_at\')->nullable();
            $table->timestamps();
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
     * Manages shutdown signals.
     *
     * @return void
     */
    public static function shutdownSignals(): void {
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, function() { 
            Tools::info("Worker shutting down...", "info"); 
            exit; 
        });
        pcntl_signal(SIGINT, function() { 
            Tools::info("Worker interrupted...", "info"); 
            exit; 
        });
    }

    /**
     * Worker for queue.
     *
     * @param string $queueName The name of the queue to run.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function worker(int $maxIterations, string $queueName = 'default'): int {
        $queue = new QueueManager();
        self::shutdownSignals();
        Tools::info("Worker started on queue: {$queueName}", "info");
        
        for($i = 0; $i < $maxIterations; $i++) {
            $job = $queue->pop($queueName);
            if ($job) {
                try {
                    Tools::info("Processing job: " . json_encode($job['payload']), 'info');
                    $payload = $job['payload'];
                    $jobClass = $payload['job'] ?? null;
                    $data = $payload['data'] ?? [];
                    self::isValidJob($jobClass);
                    $instance = new $jobClass($data);
                    $instance->handle();
                    self::deleteJob($job, $queue);

                } catch (Exception $e) {
                    self::exceptionMessaging($e, $job);
                }
            }

            // wait 0.5s before polling again
            usleep(500000); 
        }

        return Command::SUCCESS;
    }
}
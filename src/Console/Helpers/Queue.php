<?php
declare(strict_types=1);
namespace Console\Helpers;
use Exception;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Config;
use Core\Lib\Queue\QueueManager;
use Core\Lib\Utilities\DateTime;
use Core\Models\Queue as QueueModel;
use Core\Lib\Queue\QueueableJobInterface;
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
    private static function exceptionMessaging(Exception $e, array $queueJob): void {
        Tools::info("Job failed: " . $e->getMessage(), 'warning');
        $payload = $queueJob['payload'] ?? [];
        $maxAttempts = $payload['max_attempts'] ?? Config::get('queue.max_attempts', 3);
        $job = self::findJob($queueJob);

        if ($job) {
            $job->exception = $e->getMessage() . "\n" . $e->getTraceAsString();
            if ($job->attempts >= $maxAttempts) {
                $job->failed_at = self::failedAt();
            } else {
                self::updateAttempts($job);
                $delay = self::calcRetryDelay($job, $payload);
                $job = self::availableAt($delay, $job);
            }

            $job->save();
        }
    }

    private static function findJob(array $queueJob): ?QueueModel {
        return Arr::exists($queueJob, 'id') 
            ? QueueModel::findById($queueJob['id']) 
            : null;
    }

    private static function availableAt(int $delay, QueueModel $job) {
        Tools::info("Job will be retried. Attempt: {$job->attempts}", 'warning');
        return DateTime::nowPlusSeconds($delay);
    }

    private static function failedAt() {
        Tools::info('Job permanently failed and marked as failed.', 'warning');
        return DateTime::timeStamps();
    }

    private static function isQueueableClass(mixed $jobClass): bool {
        return $jobClass && class_exists($jobClass) && is_subclass_of($jobClass, QueueableJobInterface::class);
    }

    private static function calcRetryDelay(QueueModel $job, ?array $payload): int {
        $jobClass = $payload['job'] ?? null;

        if(self::isQueueableClass($jobClass)) {
            $jobData = $payload['data'] ?? [];
            $jobInstance = new $jobClass($jobData);
            $backoff = $jobInstance->backoff();
            return self::resolveBackoffDelay($backoff, $job);
            
        }
        return 10;
    }

    private static function resolveBackoffDelay(mixed $backoff, QueueModel $job): int {
        if(is_array($backoff)) {
            $delay = $backoff[$job->attempts - 1] ?? end($backoff);
        } else if (is_int($backoff)) {
            $delay = $backoff;
        }
        return $delay;
    }

    /**
     * Deletes a job
     *
     * @param int $jobId Id of the job to delete.
     * @param QueueManager $queue The QueueManager instance.
     * @return void
     */
    private static function deleteJob(array $job, QueueManager $queue): void {
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
    private static function isValidJob(string $jobClass): void {
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
    private static function jobTemplate(string $jobName): string {
        return '<?php
namespace App\Jobs;

use Core\Lib\Queue\QueueableJobInterface;

class '.$jobName.' implements QueueableJobInterface {
    protected array $data;
    protected int $delayInSeconds;
    protected int $maxAttempts;

    public function __construct(array $data, int $delayInSeconds = 0, int $maxAttempts = 3) {
        $this->data = $data;
        $this->delayInSeconds = $delayInSeconds;
        $this->maxAttempts = $maxAttempts;
    }

    public function backoff(): int|array {
        // Array or fixed
        return [];
    }

    public function delay(): int {
        return $this->delayInSeconds;
    }

    public function handle(): void {
        // Implement your handle.
    }

    public function maxAttempts(): int {
        return $this->maxAttempts;
    }

    public function toPayload(): array {
        return [
            \'job\' => static::class,
            \'data\' => $this->data,
            \'available_at\' => time() + $this->delay(),
            \'max_attempts\' => $this->maxAttempts()
        ];
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
    private static function queueTemplate(string $fileName): string {
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
    private static function shutdownSignals(): void {
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
     * Updates information about attempts.
     *
     * @param QueueModel $job The job whose attempts we want to update.
     * @return void
     */
    private static function updateAttempts(QueueModel $job): void {
        $job->attempts += 1;
        $decoded = json_decode($job->payload, true);
        $decoded['attempts'] = $job->attempts;
        $job->payload = json_encode($decoded);
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
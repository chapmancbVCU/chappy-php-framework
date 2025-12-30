<?php
declare(strict_types=1);
namespace Console\Helpers;

use Core\Exceptions\FrameworkException;
use Core\Lib\Utilities\Arr;
use Core\Lib\Queue\QueueManager;
use Core\Models\Queue as QueueModel;
use Symfony\Component\Console\Command\Command;

/**
 * Supports commands related to queues.
 */
class Queue {
    /**
     * Path to jobs classes.
     */
    private const JOBS_PATH = CHAPPY_BASE_PATH.DS.'app'.DS.'Jobs'.DS;

    /**
     * Deletes a job
     *
     * @param array $job The job to be deleted.
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
            throw new FrameworkException("Invalid job class: " . ($jobClass ?? 'null'));
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
        return <<<PHP
<?php
namespace App\Jobs;

use Core\Lib\Queue\QueueableJobInterface;
use Core\Lib\Utilities\DateTime;

class {$jobName} implements QueueableJobInterface {
    protected array \$data;
    protected int \$delayInSeconds;
    protected int \$maxAttempts;

    public function __construct(array \$data, int \$delayInSeconds = 0, int \$maxAttempts = 3) {
        \$this->data = \$data;
        \$this->delayInSeconds = \$delayInSeconds;
        \$this->maxAttempts = \$maxAttempts;
    }

    public function backoff(): int|array {
        // Array or fixed
        return [];
    }

    public function delay(): int {
        return \$this->delayInSeconds;
    }

    public function handle(): void {
        // Implement your handle.
    }

    public function maxAttempts(): int {
        return \$this->maxAttempts;
    }

    public function toPayload(): array {
        return [
            'job' => static::class,
            'data' => \$this->data,
            'available_at' => DateTime::nowPlusSeconds(\$this->delay()),
            'max_attempts' => \$this->maxAttempts()
        ];
    }
}
PHP;
    }

    /**
     * Creates a new job class.
     *
     * @param string $jobName The name of the job class.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeJob(string $jobName): int {
        Tools::pathExists(self::JOBS_PATH);
        $fullPath = self::JOBS_PATH.$jobName.'.php';
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
        return <<<PHP
<?php
namespace Database\Migrations;
use Core\Lib\Database\Schema;
use Core\Lib\Database\Blueprint;
use Core\Lib\Database\Migration;

/**
 * Migration class for the queue table.
 */
class {$fileName} extends Migration {
    /**
     * Performs a migration for a new table.
     *
     * @return void
     */
    public function up(): void {
        Schema::create('queue', function (Blueprint \$table) {
            \$table->id();
            \$table->string('queue')->default('default');
            \$table->index('queue');
            \$table->text('payload');
            \$table->text('exception')->nullable();
            \$table->unsignedInteger('attempts')->default(0);
            \$table->timestamp('reserved_at')->nullable();
            \$table->timestamp('available_at');
            \$table->index('available_at');
            \$table->timestamp('failed_at')->nullable();
            \$table->timestamps();
        });
    }

    /**
     * Undo a migration task.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('queue');
    }
}
PHP;
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
            Tools::info("Worker shutting down..."); 
            exit; 
        });
        pcntl_signal(SIGINT, function() {
            Tools::info("Worker interrupted..."); 
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
        Tools::info("Worker started on queue: {$queueName}");
        
        for($i = 0; $i < $maxIterations; $i++) {
            $job = $queue->pop($queueName);
            if ($job) {
                try {
                    Tools::info("Processing job: " . json_encode($job['payload']));
                    $payload = $job['payload'];
                    $jobClass = $payload['job'] ?? null;
                    $data = $payload['data'] ?? [];
                    self::isValidJob($jobClass);
                    $instance = new $jobClass($data);
                    $instance->handle();
                    self::deleteJob($job, $queue);

                } catch (FrameworkException $e) {
                    QueueModel::exceptionMessaging($e, $job);
                }
            }

            // wait 0.5s before polling again
            usleep(500000); 
        }

        return Command::SUCCESS;
    }
}
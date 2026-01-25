<?php
declare(strict_types=1);
namespace Console\Helpers;

/**
 * Collection of stubs for queue/event listener classes.
 */
class QueueStubs {
    /**
     * Template for Jobs class.
     *
     * @param string $jobName The name of the job.
     * @return string The content of the job class.
     */
    public static function jobTemplate(string $jobName): string {
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
     * Template for queue migration.
     *
     * @param string $fileName The file and class name.
     * @return string The contents for the queue migration.
     */
    public static function queueTemplate(string $fileName): string {
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
}
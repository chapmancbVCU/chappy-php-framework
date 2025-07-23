<?php
declare(strict_types=1);
namespace Console\Helpers;

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
            $table->uuid(\'id\');
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
}
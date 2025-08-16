<?php
declare(strict_types=1);
namespace Console\Helpers;

use Symfony\Component\Console\Input\InputInterface;
use Core\Lib\Notifications\Notification;
/**
 * Supports commands related to notifications.
 */
class Notifications {
    protected static string $notificationsPath = CHAPPY_BASE_PATH.DS.'app'.DS.'Notifications'.DS;
    /**
     * Undocumented function
     *
     * @param InputInterface $input
     * @return void
     */
    public static function channelOptions(InputInterface $input) {
        $channelsFromInput = $input->getOption('channel');
        $all = Notification::channelValues();

        if($channelsFromInput === null || $channelsFromInput === '') {
            return $all;
        }

        // Split on commas (tolerate spaces), normalize to lowercase, drop empties
        $tokens = preg_split('/\s*,\s*/', $channelsFromInput, -1, PREG_SPLIT_NO_EMPTY);
        $tokens = array_map(static fn($s) => strtolower($s), $tokens);

        // Special alias
        if (in_array('all', $tokens, true)) {
            return $all;
        }

        // Validate + dedupe
        $invalid = array_diff($tokens, $all);
        if (!empty($invalid)) {
            throw new \InvalidArgumentException(
                'Unknown channel(s): ' . implode(', ', $invalid) .
                '. Allowed: ' . implode(', ', $all) . ' or "all".'
            );
        }

        return array_values(array_unique($tokens));
    }

    public static function makeNotification() {

    }

    /**
     * Template for notifications migration.
     *
     * @param string $fileName The file and class name.
     * @return string The contents for the notifications migration.
     */
    public static function migrationTemplate(string $fileName): string {
        return '<?php
namespace Database\Migrations;
use Core\Lib\Database\Schema;
use Core\Lib\Database\Blueprint;
use Core\Lib\Database\Migration;

/**
 * Migration class for the notifications table.
 */
class '.$fileName.' extends Migration {
    /**
     * Performs a migration for a new table.
     *
     * @return void
     */
    public function up(): void {
        Schema::create(\'notifications\', function (Blueprint $table) {
            $table->uuid(\'id\');
            $table->primary(\'id\');
            $table->string(\'type\');
            $table->string(\'notifiable_type\');
            $table->unsignedBigInteger(\'notifiable_id\');
            $table->text(\'data\');
            $table->timestamp(\'read_at\')->nullable();
            $table->timestamps();
            $table->index(\'notifiable_type\');
            $table->index(\'notifiable_id\');
        });
    }

    /**
     * Undo a migration task.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists(\'notifications\');
    }
}
';
    }

    /**
     * Creates new notifications migration.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function notificationsMigration(): int {
        $fileName = Migrate::fileName();
        return Tools::writeFile(
            Migrate::MIGRATIONS_PATH.$fileName.'.php',
            self::migrationTemplate($fileName),
            'Notifications migration'
        );
    }
}
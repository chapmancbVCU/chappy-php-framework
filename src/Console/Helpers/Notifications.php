<?php
declare(strict_types=1);
namespace Console\Helpers;

use Core\Lib\Utilities\Str;
use Core\Lib\Notifications\Channel;
use Core\Lib\Notifications\Notification;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
/**
 * Supports commands related to notifications.
 */
class Notifications {
    protected static string $notificationsPath = CHAPPY_BASE_PATH.DS.'app'.DS.'Notifications'.DS;
    
    /**
     * Builds an array using input option --channel
     *
     * @param InputInterface $input The input.
     * @return array An array containing channels provided with --channel 
     * option.
     */
    public static function channelOptions(InputInterface $input): array {
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

    public static function makeNotification(?array $channels, string $notificationName) {
        $channelList = self::setViaList($channels);
        $via = self::viaTemplate($channelList);
        dd($via);
        return Command::SUCCESS;
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

    /**
     * Formats list of channels for via function.
     *
     * @param array|null $channels The channels for the notification class.
     * @return string The channels formatted for use in via function.
     */
    private static function setViaList(?array $channels): string  {
        if((sizeof($channels) === Channel::size() || (sizeof($channels) == 0))) {
            return 'Notification::channelValues()';
        }

        $channelArraySize = sizeof($channels);
        $channelList = '[';
        for($i = 0; $i < $channelArraySize; $i++) {
            $channelList .= 'Channel::' . Str::upper($channels[$i]);
            if($i < $channelArraySize - 1) {
                $channelList .= ', ';
            }
        }

        $channelList .= ']';
        return $channelList;
    }

    private static function toDatabaseTemplate(): string {
        return '/**
* Data stored in the notifications table.
*
* @param object $notifiable Any model/object that uses the Notifiable trait.
    * @return array array<string,mixed>
*/
public function toDatabase(object $notifiable): array
{
    return [];
}';
    }

    private static function toLogTemplate(): string {
        return '/**
    * Logs notification to log file.
    *
    * @param object $notifiable Any model/object that uses the Notifiable trait.
    * @return string Contents for the log.
    */
public function toLog(object $notifiable): string {
    return "";
}';
    }

    private static function toMailTemplate(): string {
        return '/**
* Handles notification via E-mail.
*
* @param object $notifiable Any model/object that uses the Notifiable trait.
* @return array array<string,mixed>
*/
public function toMail(object $notifiable): array {
    return [];
}';
    }

    
    private static function viaTemplate(string $channelList): string {
        return '/**
* Specify which channels to deliver to.
* 
* @param object $notifiable Any model/object that uses the Notifiable trait.
* @return array array<string,mixed>
*/
public function via(object $notifiable): array
{
    return '.$channelList.';
}';
    }
}
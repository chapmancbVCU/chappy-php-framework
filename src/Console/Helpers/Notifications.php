<?php
declare(strict_types=1);
namespace Console\Helpers;

use Core\Lib\Utilities\Arr;
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
        $sortedChannels = Arr::sort($channels);
        $classFunctions = '';
        $class = \Console\Helpers\Notifications::class;

        foreach($sortedChannels as $channel) {
            $functionName = 'to'. Str::ucfirst($channel) . 'Template';
            $classFunctions .= call_user_func([$class, $functionName]) . "\n\n";
        }

        $classFunctions .= self::viaTemplate(self::setViaList($sortedChannels));
        $content = self::notificationTemplate($classFunctions, $notificationName);
        $fullPath = self::$notificationsPath.$notificationName.'.php';
        Tools::pathExists(self::$notificationsPath);

        return Tools::writeFile($fullPath, $content, 'Notification');
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

    private static function notificationTemplate(
        string $classFunctions, 
        string $notificationName
    ): string {
        return '<?php
namespace Core\Lib\Notifications;

use App\Models\Users;
use Core\Lib\Notifications\Notification;

/**
 * Document class here.
 */
class '.$notificationName.' extends Notification {
    protected $user;

    /**
     * Undocumented function
     *
     * @param Users $user
     */
    public function __construct(Users $user) {
        $this->user = $user;
    }

    '.$classFunctions.'
}';
    }

    /**
     * Formats list of channels for via function.
     *
     * @param array|null $channels The channels for the notification class.
     * @return string The channels formatted for use in via function.
     */
    private static function setViaList(?array $channels): string  {
        $channelArraySize = sizeof($channels);
        
        if(($channelArraySize === Channel::size() || ($channelArraySize == 0))) {
            return 'Notification::channelValues()';
        }

        $channelList = '[';
        for($i = 0; $i < $channelArraySize; $i++) {
            $channelList .= 'Channel::' . Str::upper($channels[$i]);
            if($i < $channelArraySize - 1) {
                $channelList .= ', ';
            }
        }

        return $channelList .= ']';
    }

    /**
     * Returns contents for the toDatabase function.
     *
     * @return string The contents of the toDatabase function.
     */
    private static function toDatabaseTemplate(): string {
        return '/**
    * Data stored in the notifications table.
    *
    * @param object $notifiable Any model/object that uses the Notifiable trait.
    * @return array array<string,mixed>
    */
    public function toDatabase(object $notifiable): array {
        return [];
    }';
    }

    /**
     * Returns contents for the toLog function.
     *
     * @return string The contents of the toLog function.
     */
    private static function toLogTemplate(): string {
        return '    /**
    * Logs notification to log file.
    *
    * @param object $notifiable Any model/object that uses the Notifiable trait.
    * @return string Contents for the log.
    */
    public function toLog(object $notifiable): string {
        return "";
    }';
    }

    /**
     * Returns contents for the toMail function.
     *
     * @return string The contents of the toMail function.
     */
    private static function toMailTemplate(): string {
        return '    /**
    * Handles notification via E-mail.
    *
    * @param object $notifiable Any model/object that uses the Notifiable trait.
    * @return array array<string,mixed>
    */
    public function toMail(object $notifiable): array {
        return [];
    }';
    }

    /**
     * Returns contents for the via function.
     *
     * @return string The contents of the via function.
     */
    private static function viaTemplate(string $channelList): string {
        return '    /**
    * Specify which channels to deliver to.
    * 
    * @param object $notifiable Any model/object that uses the Notifiable trait.
    * @return array array<string,mixed>
    */
    public function via(object $notifiable): array {
        return '.$channelList.';
    }';
    }
}
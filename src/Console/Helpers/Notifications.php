<?php
declare(strict_types=1);
namespace Console\Helpers;

use Core\DB;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Str;
use Core\Lib\Notifications\Channel;
use Core\Lib\Notifications\Notifiable;
use Core\Lib\Notifications\Notification;
use Symfony\Component\Console\Command\Command;
use Core\Models\Notifications as NotificationModel;
use App\Models\Users;
use Symfony\Component\Console\Input\InputInterface;
/**
 * Supports commands related to notifications.
 */
class Notifications {
    public const NOTIFICATION_NAMESPACE = "App\\Notifications\\";
    public const NOTIFICATION_PATH = CHAPPY_BASE_PATH.DS.'app'.DS.'Notifications'.DS;
    
    /**
     * Builds an array using input option --channel
     *
     * @param InputInterface $input The input.
     * @return array An array containing channels provided with --channel 
     * option.
     */
    public static function channelOptions(InputInterface $input): array {
        $channelsFromInput = $input->getOption('channels');
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

    /**
     * Finds user/notifiable record in database.
     *
     * @param string $user The id, username, or E-mail.
     * @return Users The user to be notified.
     */
    private static function findUser(string $user): Users {
        if(is_int($user)) {
            return Users::findById($user);
        }
        
        $params = [];
        if(is_string($user)) {
            if(Str::contains($user, '@')) {
                $params = [
                    'conditions' => 'email = ?',
                    'bind' => [$user]
                ];
            } else {
                $params = [
                    'conditions' => 'username = ?',
                    'bind' => [$user]
                ];
            }
        }

        return Users::findFirst($params);
    }

    /**
     * Generates a new notification class.
     *
     * @param array|null $channels The channels for the notification.
     * @param string $notificationName The name of the notification
     * @return int A value that indicates success, invalid, or failure. 
     */
    public static function makeNotification(?array $channels, string $notificationName): int {
        $sortedChannels = Arr::sort($channels);
        $classFunctions = '';
        $class = \Console\Helpers\Notifications::class;

        foreach($sortedChannels as $channel) {
            $functionName = 'to'. Str::ucfirst($channel) . 'Template';
            $classFunctions .= call_user_func([$class, $functionName]) . "\n\n";
        }

        $classFunctions .= self::viaTemplate(self::setViaList($sortedChannels));
        $content = self::notificationTemplate($classFunctions, $notificationName);
        $fullPath = self::NOTIFICATION_PATH.$notificationName.'.php';
        Tools::pathExists(self::NOTIFICATION_PATH);

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
            $table->index(\'created_at\');
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
     * Returns name of notification class concatenated to namespace.
     *
     * @param string $notificationName The name of the notification class.
     * @return string The name of the notification class concatenated to 
     * namespace.
     */
    public static function notificationClass(string $notificationName): string {
        return str_starts_with($notificationName, '\\')
            ? ltrim($notificationName, '\\')
            : self::NOTIFICATION_NAMESPACE.$notificationName;
    }

    /**
     * Determines if namespaced notification class exists.
     *
     * @param string $className The name of the notification class to test if 
     * it exists.
     * @return bool True if it exists and false if not.
     */
    public static function notificationClassExists(string $className): bool {
        return class_exists($className) ? true : false;
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
     * Generates the main template for the notification class.
     *
     * @param string $classFunctions The functions to be included.
     * @param string $notificationName The name of the notification class.
     * @return string The contents of the notification class.
     */
    private static function notificationTemplate(
        string $classFunctions, 
        string $notificationName
    ): string {
        return '<?php
namespace Core\Lib\Notifications;

use App\Models\Users;
use Core\Lib\Notifications\Notification;

/**
 * '.$notificationName.' notification.
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
     * Resolves notifiable instance.
     *
     * @param InputInterface $input The input.
     * @return Notifiable The entity that is receiving the notification
     *                            (e.g., a User model instance or identifier).
     */
    public static function resolveNotifiable(InputInterface $input): Notifiable {
        $userOpt = $input->getOption('user');
        if(!$userOpt) {
            Tools::info('No --user provided; using a dummy notifiable string', 'info');
            $notifiable = 'dummy';
        } else {
            $notifiable = self::findUser($userOpt) ?? 'dummy';
        }

        return $notifiable;
    }

    /**
     * Resolves overrides from --with
     *
     * @param InputInterface $input The input.
     * @return array An associative array containing overrides.
     */
    public static function resolveOverridesFromWith(InputInterface $input): array {
        $kv = $input->getOption('with')
            ? array_map('trim', explode(',', $input->getOption('with')))
            : [];
        
        $overrides = [];

        foreach($kv as $pair){
            if(str_contains($pair, ':')) {
                [$k, $v] = explode(':', $pair, 2);
                $overrides[$k] = $v;
            }
        }

        return $overrides;
    }

    /**
     * Performs pruning of old notifications.
     *
     * @param int $days The number of days past to prune.  Any 
     * records older will be pruned.
     * @return int A value that indicates success, invalid, or failure. 
     */
    public static function prune(int $days): int {
        $recordsDeleted = NotificationModel::notificationsToPrune($days);
        $message = "{$recordsDeleted} has been deleted";
        Tools::info($message, 'info');
        return Command::SUCCESS;
    }

    /**
     * Resolves channels from arguments.
     *
     * @param InputInterface $input The input.
     * @return array|null Array of channels.
     */
    public static function resolveChannelsOverride(InputInterface $input): array|null {
        return $input->getOption('channels')
            ? array_map('trim', explode(',', $input->getOption('channels')))
            : null;     //null => use via()
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
            $channelList .= 'Channel::' . Str::upper($channels[$i]) . '->value';
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
    * @return array<string,mixed>
    */
    public function toDatabase(object $notifiable): array {
        return [
            \'user_id\'   => (int)$this->user->id,
            \'username\'  => $this->user->username ?? $this->user->email,
            \'message\'   => "Temp notification for user #{$this->user->id}",
            \'created_at\'=> \Core\Lib\Utilities\DateTime::timeStamps(), // optional
        ];
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
    * @return array<string,mixed>
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
    * @return list<channel>
    */
    public function via(object $notifiable): array {
        return '.$channelList.';
    }';
    }
}
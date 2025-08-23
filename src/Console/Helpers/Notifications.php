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
 * Utilities that support console commands related to Notifications:
 * - Resolving notifiables and channels from CLI options
 * - Building per-send payloads/overrides
 * - Scaffolding notification classes and the notifications migration
 * - Performing dry-runs and actual sends via a notifiable
 */
class Notifications {
    /** @var non-empty-string Namespace prefix where app notifications live. */
    public const NOTIFICATION_NAMESPACE = "App\\Notifications\\";

    /** @var non-empty-string Absolute path where app notifications are stored. */
    public const NOTIFICATION_PATH = CHAPPY_BASE_PATH.DS.'app'.DS.'Notifications'.DS;
    
    /**
     * Build the per-send payload for the test command.
     *
     * Default fields:
     *  - level: "info"
     *  - tags:  ["cli","test"]
     *  - dry_run: boolean (from --dry-run)
     *
     * @param InputInterface          $input     Console input (expects option "dry-run").
     * @param array<string,mixed>     $overrides Arbitrary key/value overrides (takes precedence).
     * @return array<string,mixed>               Merged payload passed to channels.
     */
    public static function buildPayload(InputInterface $input, array $overrides): array {
        return array_merge([
            'level'   => 'info',
            'tags'    => ['cli','test'],
            'dry_run' => $input->getOption('dry-run'),
        ], $overrides);
    }

    /**
     * Resolve the --channels option into a normalized list of channels.
     *
     * Behavior:
     * - If the option is omitted or empty, returns ALL enum channel values
     *   (use {@see resolveChannelsOverride()} if you prefer NULL to defer to via()).
     * - Accepts a comma-separated list, whitespace tolerated.
     * - Accepts the special token "all" to mean all channel enum values.
     * - Validates unknown names and deduplicates while preserving order.
     *
     * @param InputInterface $input Console input (expects option "channels").
     * @return list<string>         Normalized channel names (e.g., ['database','log']).
     *
     * @throws \InvalidArgumentException on unknown channel names.
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
     * Perform a dry-run (no delivery). Prints the intended action and payload.
     *
     * @param object|string $notifiable  Notifiable instance or a sentinel string (e.g., "dummy").
     * @param Notification  $notification The notification instance.
     * @param array<string,mixed> $payload Payload merged from defaults and overrides.
     * @param list<string>|null $channels  Channels override (NULL → will use via()).
     * @return bool                       TRUE if dry-run occurred; FALSE otherwise.
     */
    public static function dryRun(
        object|string $notifiable, 
        Notification $notification, 
        array $payload,
        ?array $channels,
    ): bool {
        if($payload['dry_run']) {
            $output = "<info>[DRY-RUN]</info> Would send ".get_class($notification)
                ." to ".(is_object($notifiable) ? get_class($notifiable) : $notifiable)
                ." via [".implode(',', $channels ?? $notification->via($notifiable))."]";
            
            Tools::info($output);
            Tools::info(json_encode($payload, JSON_PRETTY_PRINT));
            return true;
        }
        return false;
    }

    /**
     * Find a user/notifiable record by numeric id, email, or username.
     *
     * @param non-empty-string $user String token from CLI (id|email|username).
     * @return Users|null            The matched user or NULL if not found.
     */
    private static function findUser(string $user): ?Users {
        if(is_numeric((int)$user)) {
            return Users::findById($user);
        }
        
        $params = str_contains($user, '@')
            ? ['conditions' => 'email = ?', 'bind' => [$user]]
            : ['conditions' => 'username = ?', 'bind' => [$user]];

        return Users::findFirst($params);
    }

    /**
     * Generate a new notification class file into {@see self::NOTIFICATION_PATH}.
     *
     * The generated class includes channel methods (toX) for the provided list,
     * plus a via() that references those channels. If $channels is NULL or empty,
     * all enum channel values are used.
     *
     * @param list<string>|null $channels       Channel names to scaffold (e.g., ['database','log']).
     * @param non-empty-string  $notificationName Class name (without namespace).
     * @return int                              A Tools::writeFile status code.
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
     * Template for the notifications migration class file.
     *
     * @param non-empty-string $fileName The base filename/classname to use.
     * @return string                    The complete PHP contents of the migration.
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
     * Build a fully-qualified notification class name.
     *
     * @param non-empty-string $notificationName Short class name or FQCN (leading backslash allowed).
     * @return class-string<Notification>        FQCN of the notification.
     */
    public static function notificationClass(string $notificationName): string {
        return str_starts_with($notificationName, '\\')
            ? ltrim($notificationName, '\\')
            : self::NOTIFICATION_NAMESPACE.$notificationName;
    }

    /**
     * Determine if a notification class exists.
     *
     * @param class-string $className FQCN to check.
     * @return bool                   TRUE if loadable; FALSE otherwise.
     */
    public static function notificationClassExists(string $className): bool {
        return class_exists($className) ? true : false;
    }

    /**
     * Create a new notifications migration file on disk.
     *
     * @return int A Tools::writeFile status code.
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
     * Generate the PHP contents for a notification class.
     *
     * @param string           $classFunctions   Concatenated channel method bodies + via().
     * @param non-empty-string $notificationName Class name (no namespace).
     * @return string                            Full PHP file contents for the notification class.
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
     * Resolve a notifiable instance (or a sentinel string) from CLI input.
     *
     * @param InputInterface $input Console input (expects option "user").
     * @return object|string        A model instance or "dummy" if none found/provided.
     */
    public static function resolveNotifiable(InputInterface $input): object|string {
        $userOpt = $input->getOption('user');
        if(!$userOpt) {
            Tools::info('No --user provided; using a dummy notifiable string', 'info');
            return 'dummy';
        }   
        return self::findUser($userOpt) ?? 'dummy';
    }

    /**
     * Parse overrides from the --with option (key:value,key2:value2).
     *
     * @param InputInterface             $input Console input (expects option "with").
     * @return array<string,string>             Flattened k=>v overrides.
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
     * Prune old notifications using the model layer.
     *
     * @param int $days Number of days to retain; older rows are deleted.
     * @return int      Command::SUCCESS on completion.
     */
    public static function prune(int $days): int {
        $recordsDeleted = NotificationModel::notificationsToPrune($days);
        $message = "{$recordsDeleted} has been deleted";
        Tools::info($message, 'info');
        return Command::SUCCESS;
    }

    /**
     * Resolve a channels override list from CLI input.
     *
     * If the option is omitted/empty, returns NULL so callers can defer to via().
     *
     * @param InputInterface    $input Console input (expects option "channels").
     * @return list<string>|null       Normalized channels or NULL to defer.
     */
    public static function resolveChannelsOverride(InputInterface $input): ?array {
        return $input->getOption('channels')
            ? array_map('trim', explode(',', $input->getOption('channels')))
            : null;     //null => use via()
    }

    /**
     * Deliver a notification via a notifiable (or simulate if notifiable is not an object).
     *
     * @param list<string>|null $channels    Channel override (NULL → use via()).
     * @param object|string     $notifiable  Notifiable instance or a sentinel string.
     * @param Notification      $notification The notification instance.
     * @param array<string,mixed> $payload   Per-send payload/overrides.
     * @return void
     */
    public static function sendViaNotifiable(
        object|string $notifiable,
        Notification $notification,
        array $payload,
        ?array $channels
    ): void {
        if(is_object($notifiable) && method_exists($notifiable, 'notify')) {
            $notifiable->notify($notification, $channels, $payload);
            return;
        } 

        // Fallback: simulate log-only
        $simChannels = $channels ?? $notification->via((object)['id' => (string)$notifiable]);
        if (in_array('log', $simChannels, true)) {
            // If you have a LogChannel handy, you could resolve and call it here.
            Tools::info('[SIMULATED] log: '.$notification->toLog((object)['id'=>(string)$notifiable]));
        } else {
            Tools::info('[SKIPPED] no deliverable channel for non-object notifiable');
        }
    }

    /**
     * Format a list of channels into PHP code suitable for a generated via() method.
     *
     * If the provided list is empty or equals the full enum size, returns the literal
     * string "Notification::channelValues()" to keep the generated class concise.
     *
     * @param list<string>|null $channels Channel names to embed.
     * @return non-empty-string           PHP expression to place inside via().
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
     * Template for a toDatabase() method body within a scaffolded notification.
     *
     * @return non-empty-string PHP code snippet for inclusion.
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
     * Template for a toLog() method body within a scaffolded notification.
     *
     * @return non-empty-string PHP code snippet for inclusion.
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
     * Template for a toMail() method body within a scaffolded notification.
     *
     * @return non-empty-string PHP code snippet for inclusion.
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
     * Template for the via() method within a scaffolded notification.
     *
     * @param non-empty-string $channelList PHP expression representing the channel list.
     * @return non-empty-string             PHP code snippet for inclusion.
     */
    private static function viaTemplate(string $channelList): string {
        return '    /**
    * Specify which channels to deliver to.
    * 
    * @param object $notifiable Any model/object that uses the Notifiable trait.
    * @return list<\'database\'|\'mail\'|\'log\'
    */
    public function via(object $notifiable): array {
        return '.$channelList.';
    }';
    }
}
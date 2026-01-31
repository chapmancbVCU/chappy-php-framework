<?php
declare(strict_types=1);
namespace Core;
use \PDO;
use Exception;
use \PDOException;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Str;
use Core\Lib\Utilities\ArraySet;
use Core\Lib\Utilities\Env;

/**
 * Support database operations.
 */
class DB {
    /**
     * Number of items returned.
     * @var int
     */
    private $_count = 0;

    /**
     * The database driver.
     * @var string
     */
    private $_dbDriver;

    /**
     * Error status.
     * @var bool
     */
    private $_error = false;

    /**
     * The fetch style.
     * @var int
     */
    private $_fetchStyle = PDO::FETCH_OBJ;

    /**
     * Instance of this class.
     * @var DB
     */
    private static $_instance = null;

    /**
     * Id of last item inserted into database.
     * @var int
     */
    private $_lastInsertID = null;

    /**
     * The PDO object.
     * @var PDO
     */
    private $_pdo;

    /**
     * The query.
     * @var mixed
     */
    private $_query;

    /**
     * The result.
     * @var mixed
     */
    private $_result;
    
    /**
     * This constructor creates a new PDO object as an instance variable.  If 
     * there are any failures the application quits with an error message.
     */
    private function __construct() {
        $config = require CHAPPY_BASE_PATH . DS . 'config' . DS . 'database.php';

        $dbConfig = $config['connections'][$config['default']] ?? null;
        
        if (!$dbConfig) {
            throw new Exception("Database configuration not found.");
        }

        try {
            if ($dbConfig['driver'] === 'sqlite') {
                if ($dbConfig['database'] !== ':memory:' && !file_exists($dbConfig['database'])) {
                    touch($dbConfig['database']);
                }

                $dsn = "sqlite:" . $dbConfig['database'];
                $this->_pdo = new PDO($dsn);
                $this->_pdo->exec("PRAGMA foreign_keys=ON;"); // Enable foreign keys for SQLite
            } else {
                $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
                $this->_pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
                $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $this->_pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            }
            $this->_dbDriver = $dbConfig['driver']; // Store database driver
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Begins a database transaction.
     *
     * @return bool True on success, false on failure
     */
    public function beginTransaction(): bool {
        return $this->_pdo->beginTransaction();
    }

    /** 
     * Constructs join statements for SQL queries.
     *
     * @param array $join Data such as table, conditions, and aliases needed 
     * to construct join query.  Default value is an empty array.
     * @return string The join component of a query.
     */
    protected function _buildJoin(array $join=[]): string {
        $table = $join[0];
        $condition = $join[1];
        $alias = $join[2];
        $type = (isset($join[3]))? Str::upper($join[3]) : "INNER";
        $jString = "{$type} JOIN {$table} {$alias} ON {$condition}";
        return " " . $jString;
    }

    /**
     * Commits the current database transaction.
     *
     * @return bool True on success, false on failure
     */
    public function commit(): bool {
        return $this->_pdo->commit();
    }

    /**
     * Establishes a new database connection using the provided configuration array.
     *
     * This method creates a new PDO instance based on the given connection details
     * and sets it as the singleton instance for the DB class. It can be used to
     * override the default database connection at runtime (e.g., for testing or
     * connecting to a different database).
     *
     * Supported drivers:
     * - **sqlite:** Connects to a SQLite database file or an in-memory database. 
     *   Foreign key constraints are enabled by default.
     * - **mysql:** Connects to a MySQL/MariaDB database using the provided host, port, 
     *   database name, charset, username, and password.
     *
     * @param array $override An associative array containing connection parameters:
     *                        - `driver`   (string)  The database driver ('sqlite' or 'mysql').
     *                        - `database` (string)  Path to SQLite file or database name for MySQL.
     *                        - `host`     (string)  (MySQL) The database host.
     *                        - `port`     (int)     (MySQL) The database port.
     *                        - `charset`  (string)  (MySQL) The character set.
     *                        - `username` (string)  (MySQL) The username for authentication.
     *                        - `password` (string)  (MySQL) The password for authentication.
     *
     * @return void
     *
     * @throws \Exception If the connection attempt fails, an Exception is thrown
     *                    with the corresponding error message.
     */
    public static function connect(array $override): void
    {
        $instance = new self();

        try {
            if ($override['driver'] === 'sqlite') {
                $dsn = 'sqlite:' . ($override['database'] ?? ':memory:');
                $instance->_pdo = new PDO($dsn);
                $instance->_pdo->exec("PRAGMA foreign_keys=ON;");
            } else {
                $dsn = "mysql:host={$override['host']};port={$override['port']};dbname={$override['database']};charset={$override['charset']}";
                $instance->_pdo = new PDO($dsn, $override['username'], $override['password']);
                $instance->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $instance->_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $instance->_pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            }

            $instance->_dbDriver = $override['driver'];
            self::$_instance = $instance;
        } catch (PDOException $e) {
            throw new Exception("Test DB connection failed: " . $e->getMessage());
        }
    }

    /**
     * Getter function for the private _count variable.
     *
     * @return int The number of results found in an SQL query.
     */
    public function count(): int {
        return $this->_count;
    }

    /**
     * Performs delete operation against SQL database.
     *
     * Example setup:
     * $contacts = $db->delete('contacts', 3);
     * 
     * @param string $table The name of the table that contains the record 
     * we want to delete.
     * @param int $id The primary key for the record we want to remove from a 
     * database table.
     * @return bool True if delete operation is successful.  Otherwise, we 
     * return false.
     */
    public function delete(string $table, int $id): bool {
        $sql = "DELETE FROM {$table} WHERE id = ?";
        return !$this->query($sql, [$id])->error();
    }

    /**
     * Getter function for the $_error variable.
     *
     * @return bool The value for the $_error flag.
     */
    public function error(): bool {
        return $this->_error;
    }

    /**
     * Performs find operation against the database.  The user can use 
     * parameters such as conditions, bind, order, limit, and sort.
     * 
     * Example setup:
     * $contacts = $db->find('users', [
     *     'conditions' => ["email = ?"],
     *     'bind' => ['chad.chapman@email.com'],
     *     'order' => "username",
     *     'limit' => 5,
     *     'sort' => 'DESC'
     * ]);
     *
     * @param string $table The name or the table we want to perform 
     * our query against
     * @param array $params An associative array that contains key value pair 
     * parameters for our query such as conditions, bind, limit, offset, 
     * join, order, and sort.  The default value is an empty array.
     * @param bool|string $class A default value of false, it contains the 
     * name of the class we will build based on the name of a model.
     * @return bool|array An array of object returned from an SQL query.
     */
    public function find(string $table, array $params = [], bool|string $class = false): bool|array {
        if($this->_read($table, $params, $class)) {
            return $this->results();
        }
        return false;
    }

    /**
     * Returns the first result performed by an SQL query.  It is a wrapper
     * for the _read function for this purpose.
     *
     * @param @param string $table The name or the table we want to perform 
     * our query against.
     * @param array $params An associative array that contains key value pair 
     * parameters for our query such as conditions, bind, limit, offset, 
     * join, order, and sort.  The default value is an empty array.
     * @param bool|string  $class A default value of false, it contains the 
     * name of the class we will build based on the name of a model.
     * @return array|object|bool An associative array of results returned from an SQL 
     * query.
     */
    public function findFirst(string $table, array $params = [], bool|string $class = false): array|object|bool {
        if($this->_read($table, $params, $class)) {
            return $this->first();
        }
        return false;
    }

    /** 
     * Returns number of records in a table.
     *
     * @param string $table  The name or the table we want to perform 
     * our query against.
     * @param array $params An associative array that contains key value pair 
     * parameters for our query such as conditions, bind, limit, offset, 
     * join, order, and sort.  The default value is an empty array.
     * @return int $count The number of records in a table.
     */
    public function findTotal(string $table, array $params=[]): int {
        $count = 0;
        if($this->_read($table, $params, false, true)) {
            $count = $this->first()->count;
        }
        return $count;
    }

    /**
     * Returns first result in the _result array.
     *
     * @return array|object An associative array that is the first object 
     * in a _result.
     */
    public function first(): array|object {
        return (!empty($this->_result)) ? $this->_result[0] : [];
    }

    /**
     * Formats query parameters for logging, based on the DB_LOG_PARAMS mode.
     *
     * Supported modes (via Env::get('DB_LOG_PARAMS')):
     * - **none**   (default): logs only parameter count and types/lengths (no values).
     * - **masked**: logs redacted values using safeParams().
     * - **full**  : logs full raw parameter values (not recommended outside local/dev).
     *
     * This is designed to prevent sensitive data (passwords, tokens, emails, etc.)
     * from being written to logs in production while still preserving useful debugging
     * context (execution timing, SQL, parameter shape).
     *
     * @param array $params Parameters bound to the prepared SQL statement.
     * @return string A log-safe string representation of the parameters.
     */
    private function formatParamsForLog(array $params): string {
        $rawMode = Env::get('DB_LOG_PARAMS', 'none');
        $mode = $this->normalizeParamLogMode(is_string($rawMode) ? $rawMode : null, 'none');

        return match ($mode) {
            'none'   => $this->paramSummary($params),
            'full'   => json_encode($params),
            default  => json_encode($this->safeParams($params))
        };
    }

    /**
     * Returns columns for a table.
     *
     * @param string $table The name of the table we want to retrieve
     * the column names.
     * @return array An array of objects where each one represents a column 
     * from a database table.
     */
    public function getColumns($table): array {
        $dbDriver = $this->_pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($dbDriver === 'sqlite') {
            return $this->query("PRAGMA table_info({$table})")->results();
        } else {
            return $this->query("SHOW COLUMNS FROM {$table}")->results();
        }
    }

    /**
     * An instance of this class set as a variable.  To be used in other 
     * class because we can't use $this.
     *
     * @return self The instance of this class.
     */
    public static function getInstance(): self {
        if(!isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Returns instance of PDO class.
     *
     * @return PDO The PDO object.
     */
    public function getPDO(): PDO {
        return $this->_pdo;
    }

    /**
     * Appropriately formats column for query with GROUP BY operations.  A 
     * call to the ANY_VALUE function is added if the DB driver is MySQL 
     * or MariaDB.
     *
     * @param string $column Name of the column to format.
     * @return string|null The properly formatted column if DB driver 
     * is properly set or detected.  Otherwise, we return null.
     */
    public static function groupByColumn($column): string|null {
        $dbDriver = DB::getInstance()->getPDO()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        if($dbDriver) {
            return ($dbDriver === 'mysql' || $dbDriver === 'mariadb') ? 
                "ANY_VALUE($column)" : $column;
        }
        error(
            'The DB driver was not properly set.  GROUP BY formatting failed.  Please check DB_CONNECTION value in .env file', 
        );
        return null;
    }

    /**
     * Perform insert operations against the database.
     * 
     * Example setup:
     * $fields = [
     *   'fname' => 'John',
     *   'lname' => 'Doe',
     *   'email' => 'example@email.com'
     * ];
     * $contacts = $db->insert('contacts', $fields);
     * 
     * @param string $table The name of the table we want to perform the 
     * insert operation.
     * @param array $fields An associative array of key value pairs.  The key 
     * is the name of the database field and the value is the value we will 
     * set to a particular field.  The default value is an empty array.
     * @return bool Report whether or not the operation was successful.
     */
    public function insert(string $table, array $fields = []): bool {
        if (empty($fields)) {
            warning("Attempted to insert empty data into {$table}");
            return false;
        }
    
        // Remove ID field from insertion if it's an autoincrement field
        if (isset($fields['id'])) {
            unset($fields['id']);
        }
    
        $fieldString = implode(',', Arr::keys($fields));
        $valueString = implode(',', Arr::fill(0, count($fields), '?'));
        $values = Arr::values($fields);
    
        $sql = "INSERT INTO {$table} ({$fieldString}) VALUES ({$valueString})";
    
        debug("Preparing INSERT query: $sql | Params: " . $this->formatParamsForLog($values));

        if (!$this->query($sql, $values)->error()) {
            return true;
        }
        return false;
    }

    /**
     * Checks if a transaction is currently active.
     *
     * @return bool True if a transaction is active, false otherwise
     */
    public function inTransaction(): bool {
        return $this->_pdo->inTransaction();
    }

    /**
     * The primary key ID of the last insert operation.
     *
     * @return int|string|null The primary key ID from the last insert operation.
     */
    public function lastID(): int|string|null {
        return $this->_lastInsertID;
    }

    /**
     * Normalizes and validates the DB_LOG_PARAMS mode from configuration.
     *
     * Accepts: none, masked, full (case-insensitive, ignores surrounding quotes/whitespace).
     * Falls back to $default if invalid.
     *
     * @param string|null $raw     Raw value from env/config.
     * @param string      $default Default mode if invalid.
     * @return string One of: 'none', 'masked', 'full'.
     */
    private function normalizeParamLogMode(?string $raw, string $default = 'none'): string {
        $mode = $raw ?? $default;

        $mode = trim($mode);
        $mode = trim($mode, "\"'"); // handles 'full' or "full"

        $mode = strtolower($mode);

        $allowed = ['none', 'masked', 'full'];

        if (!in_array($mode, $allowed, true)) {
            // Mis-config shouldn't break execution; fall back safely.
            warning("Invalid DB_LOG_PARAMS='{$raw}'. Using '{$default}'. Allowed: none|masked|full");
            return $default;
        }

        return $mode;
    }

    /**
     * Produces a safe "shape" summary of query parameters without logging values.
     *
     * The summary includes:
     * - total parameter count
     * - parameter types
     * - string lengths and array sizes (when applicable)
     *
     * Example output:
     * `count=3 types=[int,string(12),null]`
     *
     * @param array $params Parameters bound to a prepared statement.
     * @return string A concise summary suitable for logs.
     */
    private function paramSummary(array $params): string {
        $types = array_map(function ($p) {
            $t = gettype($p);
            if (is_string($p)) return "string(" . strlen($p) . ")";
            if (is_int($p)) return "int";
            if (is_float($p)) return "float";
            if (is_bool($p)) return "bool";
            if (is_null($p)) return "null";
            if (is_array($p)) return "array(" . count($p) . ")";
            return $t;
        }, $params);

        return "count=" . count($params) . " types=[" . implode(',', $types) . "]";
    }

    /**
     * Performs database query operations that includes prepare, 
     * binding, execute, and fetch.  
     *
     * @param string $sql The database query we will submit to the database.
     * @param array $params An associative array that contains key value pair 
     * parameters for our query such as conditions, bind, limit, offset, 
     * join, order, and sort.  The default value is an empty array.
     * @param bool|string $class A default value of false, it contains the 
     * name of the class we will build based on the name of a model.
     * @return DB The results of the database query.  If the operation 
     * is not successful the $_error instance variable is set to true and is 
     * returned.
     */
    public function query(string $sql, array $params = [], bool|string $class = false): self {
        $this->_error = false;
        $startTime = microtime(true);

        if ($this->_query = $this->_pdo->prepare($sql)) {
            $x = 1;
            foreach ($params as $param) {
                $this->_query->bindValue($x, $param);
                $x++;
            }

            if ($this->_query->execute()) {
                $executionTime = microtime(true) - $startTime;
                $this->_result = $class ? $this->_query->fetchAll(PDO::FETCH_CLASS, $class) : $this->_query->fetchAll($this->_fetchStyle);
                $this->_count = $this->_query->rowCount();
                $this->_lastInsertID = $this->_pdo->lastInsertId();

                // If multiple rows updated, log a summary
                if ($this->_count > 1) {
                    debug("Executed Batch Query: {$this->_count} rows affected | Execution Time: " . number_format($executionTime, 5) . "s");
                } else {
                    debug("Executed Query: $sql | Params: " . $this->formatParamsForLog($params) . " | Rows Affected: {$this->_count} | Execution Time: " . number_format($executionTime, 5) . "s");
                }
            } else {
                $this->_error = true;
                error("Database Error: " . json_encode($this->_query->errorInfo()) . " | Query: $sql | Params: " . $this->formatParamsForLog($params));
            }
        } else {
            error("Failed to prepare query: $sql | Params: " . $this->formatParamsForLog($params));
        }

        return $this;
    }

    
    /**
     * Supports SELECT operations that maybe ran against a SQL database.  It 
     * supports the ability to order and limit the number of results returned 
     * from a database query.  The user can use parameters such as conditions, 
     * bind, order, limit, and sort.
     *
     * @param string $table The name of the table that contains the 
     * record(s) we want to find.
     * @param array $params An associative array that contains key value pair 
     * parameters for our query such as conditions, bind, limit, offset, 
     * join, order, and sort.  The default value is an empty array.
     * @param bool|string  $class A default value of false, it contains the 
     * name of the class we will build based on the name of a model.
     * @param bool $count Boolean switch for turning on support for count 
     * operations.  Default value is false.
     * @return bool A true or false value depending on a successful operation.
     */
    protected function _read(string $table, array $params=[], bool|string $class = false, bool $count = false):bool {
        $columns = '*';
        $joins = "";
        $conditionString = '';
        $bind = [];
        $order = '';
        $limit = '';
        $offset = '';
        $group = '';
        $forUpdate = '';

        // Detect SQLite
        $dbDriver = $this->_pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        // Fetch Style
        if(isset($params['fetchStyle'])){
            $this->_fetchStyle = $params['fetchStyle'];
        }

        // Conditions
        if(isset($params['conditions'])) {
            if(Arr::isArray($params['conditions'])) {
                foreach($params['conditions'] as $condition) {
                    // Convert `!=` to `<>` for SQLite
                    if ($dbDriver === 'sqlite') {
                        $condition = Str::replace('!=', '<>', $condition);
                    }
                    $conditionString .= ' ' . $condition . ' AND';
                }
                $conditionString = trim($conditionString);
                $conditionString = rtrim($conditionString, ' AND');
            } else {
                $conditionString = $params['conditions'];
                if ($dbDriver === 'sqlite') {
                    $conditionString = Str::replace('!=', '<>', $conditionString);
                }
            }
            if($conditionString != '') {
                $conditionString = ' WHERE ' . $conditionString;
            }
        }

        // Columns
        if(Arr::exists($params, 'columns')){
            $columns = $params['columns'];
        }

        // Joins and raw joins
        if(Arr::exists($params, 'joins')){
            foreach($params['joins'] as $join){
                $joins .= $this->_buildJoin($join);
            }
            $joins .= " ";
        }

        if(Arr::exists($params, 'joinsRaw')) {
            foreach($params['joinsRaw'] as $raw) {
                $joins .= ' ' .$raw;
            }
        }

        // Bind
        if(Arr::exists($params, 'bind')) {
            $bind = $params['bind'];
        }

        if (Arr::exists($params, 'group')) {
            $group = ' GROUP BY ' . $params['group'];
        }

        // Order
        if(Arr::exists($params, 'order')) {
            $order = ' ORDER BY ' . $params['order'];
        }

        // Limit
        if(Arr::exists($params, 'limit')) {
            $limit = ' LIMIT ' . $params['limit'];
        }

        // Offset
        if(Arr::exists($params, 'offset')) {
            $offset = ' OFFSET ' . $params['offset'];
        }

        // For Update
        if (Arr::exists($params, 'lock') && $params['lock'] === true && $dbDriver !== 'sqlite') {
            $forUpdate .= ' FOR UPDATE';
        }

        $sql = ($count) ? "SELECT COUNT(*) as count " : "SELECT {$columns} ";
        $sql .= "FROM {$table}{$joins}{$conditionString}{$group}{$order}{$limit}{$offset}{$forUpdate}";

        if($this->query($sql, $bind, $class)) {
            if(!count($this->_result)) return false;
            return true;
        }
        return false;
    }

    /**
     * Returns value of query results.
     *
     * @return array An array of objects that contain results of a database 
     * query.
     */
    public function results(): array {
        return $this->_result;
    }

    /**
     * Rolls back the current database transaction.
     *
     * @return bool True on success, false on failure
     */
    public function rollBack(): bool {
        return $this->_pdo->rollBack();
    }

    /**
     * Returns a redacted copy of query parameters suitable for logging.
     *
     * This method attempts to prevent common secret leakage by:
     * - Redacting token-like strings (base64-ish, JWT-ish, Bearer tokens)
     * - Truncating long strings to a short prefix + length indicator
     * - Masking email usernames (optional behavior included)
     * - Summarizing arrays/objects rather than dumping them
     *
     * Note: This is a best-effort sanitizer for logs. For maximum safety,
     * prefer DB_LOG_PARAMS=none in production.
     *
     * @param array $params Parameters bound to a prepared statement.
     * @return array A sanitized array of parameters safe to JSON encode for logs.
     */
    private function safeParams(array $params): array {
        return array_map(function ($p) {
            if (is_null($p) || is_int($p) || is_float($p) || is_bool($p)) {
                return $p;
            }

            if (is_string($p)) {
                $s = $p;

                // common secret patterns
                $looksLikeSecret =
                    strlen($s) >= 20 && preg_match('/^[A-Za-z0-9+\/=_\-.]+$/', $s) // tokens/base64-ish
                    || str_contains($s, 'Bearer ')
                    || preg_match('/eyJ[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+/', $s); // JWT-ish

                if ($looksLikeSecret) {
                    return '[REDACTED len=' . strlen($s) . ']';
                }

                // general string masking
                $len = strlen($s);
                if ($len > 64) {
                    return substr($s, 0, 8) . '…[len=' . $len . ']';
                }

                // emails can be masked too if desired
                if (filter_var($s, FILTER_VALIDATE_EMAIL)) {
                    [$u, $d] = explode('@', $s, 2);
                    return substr($u, 0, 2) . '…@' . $d;
                }

                return $s;
            }

            // arrays/objects shouldn't usually be here; summarize
            if (is_array($p)) {
                return '[array count=' . count($p) . ']';
            }

            return '[type=' . gettype($p) . ']';
        }, $params);
    }

    /**
     * Checks whether a given table exists in the currently connected database.
     *
     * This method runs a driver-specific query to determine if the table is present.
     * For SQLite, it queries the `sqlite_master` system table. For MySQL/MariaDB,
     * it uses the `SHOW TABLES LIKE` statement.
     *
     * @param string $table The name of the table to check for existence.
     *
     * @return bool Returns true if the table exists in the database, or false if it does not.
     */
    public function tableExists(string $table): bool {
        $sql = ($this->_dbDriver === 'sqlite')
            ? "SELECT name FROM sqlite_master WHERE type='table' AND name=?"
            : "SHOW TABLES LIKE ?";


        $this->query($sql, ['table' => $table]);
        return $this->count() > 0;
    }

    /**
     * Performs update operation on a SQL database record.
     *
     * Example setup:
     * $fields = [
     *   'fname' => 'John',
     *   'email' => 'example@email.com'
     * ];
     * $contacts = $db->update('contacts', 3, $fields);
     * 
     * @param string $table $table The name of the table that contains the 
     * record we want to update.
     * @param int $id The primary key for the record we want to remove from a 
     * database table.
     * @param array $fields The value of the fields we want to set for the 
     * database record.  The default value is an empty array.
     * @return bool True if the update operation is successful.  Otherwise, 
     * we return false.
     */
    public function update(string $table, int $id, array $fields = []): bool {
        $setString = implode('=?, ', Arr::keys($fields)) . '=?';
        $values = (new ArraySet($fields))->values()->push($id)->all();
        $sql = "UPDATE {$table} SET {$setString} WHERE id = ?";
        return !$this->query($sql, $values)->error();
    }

    /**
     * Updates records in a table using params-style conditions.
     *
     * @param string $table The table to update.
     * @param array $fields Key/value pairs to set.
     * @param array $params Params like ['conditions' => 'queue = ?', 'bind' => [$queueName]]
     * @return int Number of rows affected.
     */
    public function updateWhere(string $table, array $fields, array $params = []): int {
        if (empty($fields)) {
            error("Attempted to update with empty data in {$table}");
            return 0;
        }

        // Build SET part
        $setString = implode(' = ?, ', Arr::keys($fields)) . ' = ?';
        $values = Arr::values($fields);

        // Build WHERE part using your params logic
        $conditionString = '';
        if (isset($params['conditions'])) {
            $conditionString = ' WHERE ' . (is_array($params['conditions'])
                ? implode(' AND ', $params['conditions'])
                : $params['conditions']);
        }

        if (isset($params['bind'])) {
            $values = array_merge($values, $params['bind']);
        }

        $sql = "UPDATE {$table} SET {$setString}{$conditionString}";
        $this->query($sql, $values);

        return $this->count();
    }

    /**
     * Check if a value exists in a JSON or text-based column
     *
     * @param string $table The table name
     * @param string $column The column name (JSON or text-based)
     * @param mixed $value The value to search for
     * @return bool True if value exists, False otherwise
     */
    public function valueExistsInColumn(string $table, string $column, mixed $value): bool {
        $dbDriver = $this->_pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    
        if ($dbDriver === 'mysql') {
            $condition = "JSON_CONTAINS({$column}, ?)";
            $value = json_encode($value); // ✅ Fix: Ensure it's valid JSON
        } else {
            $condition = "{$column} LIKE ?";
            $value = '%"'.$value.'"%'; // Adjust value for SQLite string search
        }
    
        $query = "SELECT COUNT(*) as count FROM {$table} WHERE {$condition}";
        $result = $this->query($query, [$value])->first();
    
        return $result && isset($result->count) && $result->count > 0;
    }

}
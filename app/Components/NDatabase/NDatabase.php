<?php

namespace Components\NDatabase;

// phpcs:disable
use Components\NComponentManager\NComponentManager;
use Components\NConfig\NConfig;
use DateTime;
use DateTimeZone;
use Exception;
use NDatabase\NTimeZones;
use PDO;
use PDOException;
use PDOStatement;
use Throwable;
use function Components\NDatabase\pre;
use function Components\NDatabase\SQL_round_currency_store;
use function Components\NDatabase\SQLround_currency;
use function Components\NDatabase\SQLround_quantity;


// phpcs:enable

// phpcs:disable
/**
 * NDatabase is an abstract database engine.
 * This component can be used for following template routines:
 *   - run SQL queries
 *   - return different formats of SQL results
 *
 * @access  public
 * @name    NDatabase
 * @package NDatabase
 * @version 1.4
 *
 * Examples:
 * echo NDatabase::getOne("SELECT VERSION()");     // Get MySQL version
 */
// phpcs:enable

define('ND_FETCH_ROW_NUM', -1);
define('ND_FETCH_ROW_ASSOC', -2);
define('ND_FETCH_ASSOC', -3);
define('ND_FETCH_COL_NUM', -4);
define('ND_FETCH_ALL_NUM', -5);
define('ND_FETCH_ALL_ASSOC', -6);

class NDatabase
{
    /**
     * @var PDO object
     */
    private static $rootPDO;

    /**
     * @var NDatabase
     */
    private static $instance;

    /**
     * @var NDatabaseDestructor
     */
    private static $destructorInstance;
    private static $tryReconnect = 0;

    private static $reconnectCount = 1;

    private static $pdoConnections = [];

    public static $mainConnection = 'Database';

    public static $slaveConnections = ['DatabaseSlave'];

    public static $limitedConnection = 'DatabaseAnalytics';

    private static $lastConnectionName = 'Database';

    private static $lastTimeZone = 'SYSTEM';

    private static $transactionId;

    private static bool $disabledSlaveConnection = false;

    /**
     * Constructor
     *
     * @access public
     * @param  string $engine Configuration engine name
     * @global object NDatabase
     */
    public function __construct()
    {
        NDatabase::init();
    }

    /**
     * @return NDatabase
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new NDatabase('NDatabase');
        }
        return self::$instance;
    }

    /**
     * Reinitialize database connection
     */
    public static function reinit($connectionName = null)
    {
        self::closeConnection($connectionName);
        self::init($connectionName);
    }

    private static function getPDOByName($connecitonName)
    {
        if (empty(self::$pdoConnections[$connecitonName])) {
            self::init($connecitonName);
        }

        return self::$pdoConnections[$connecitonName];
    }

    private static function getMainPDO()
    {
        return self::getPDOByName(self::$mainConnection);
    }

    /**
     * Initialize database
     *
     * @return string DSN
     */
    private static function init($connectionName = null)
    {
        if (empty($connectionName)) {
            $connectionName = self::$mainConnection;
        }
        if (self::$destructorInstance == null) {
            self::$destructorInstance = new NDatabaseDestructor();
        }
        if (!empty(self::$pdoConnections[$connectionName])) {
            return false;
        }

        try {
            // MySQL через PDO_MYSQL
            $dbname   = NConfig::get("dbname", $connectionName);
            $host     = NConfig::get("host", $connectionName);
            $user     = NConfig::get("user", $connectionName);
            $pass     = NConfig::get("password", $connectionName);
            if (IS_DEFAULT_APPLICATION_CONFIG) {
                echo "*************************\n";

                if (defined('DOMAIN_NAME')) {
                    echo "NO DB CONFIG: " . DOMAIN_NAME . "\n";
                } else {
                    echo "NO DB CONFIG: DOMAIN_NAME not defined\n";
                }

                echo "*************************\n";
                exit(0);
            }

            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=UTF8", $user, $pass);

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            self::$pdoConnections[$connectionName] = $pdo;

            self::getStatement('SET NAMES utf8;', null, false, $connectionName);
            self::restoreTimeZone($connectionName);
        } catch (PDOException $e) {
            echo $e->getMessage();
            throw $e;
        }
    }

    /**
     * @param  $query
     * @param  null   $param
     * @param  bool   $skipBindParams
     * @param  string $connectionName
     * @return PDOStatement
     */
    public static function getStatement($query, $params = null, $skipBindParams = false, $connectionName = null)
    {
        if (empty($connectionName)) {
            $connectionName = self::$mainConnection;
        }

        $pdo = self::getPDOByName($connectionName);

        $sth = $pdo->prepare($query);

        if (!$skipBindParams) {
            if ($params) {
                if (!is_array($params)) {
                    $params = [$params];
                }
                foreach ($params as $key => $value) {
                    if (is_numeric($key)) {
                        $paramName = $key + 1;
                    } else {
                        $paramName = $key;
                    }
                    if (is_int($value)) {
                        $paramType = PDO::PARAM_INT;
                    } else {
                        $paramType = PDO::PARAM_STR;
                    }

                    $sth->bindValue($paramName, $value, $paramType);
                }
            }
            try {
                $sth->execute();
            } catch (Throwable $e) {
                if (self::isMysqlGoneAwayError($e) && empty(self::$transactionId)) {
                    if (self::$tryReconnect < self::$reconnectCount) {
                        self::$tryReconnect++;
                        self::$pdoConnections[$connectionName] = null;
                        self::init($connectionName);
                        return self::getStatement($query, $params, $skipBindParams, $connectionName);
                    }
                }
                throw $e;
            }
        }
        return $sth;
    }

    /**
     * @param  $query
     * @param  null $param
     * @return PDOStatement
     */
    public static function getStatementLC($query, $params = null)
    {
        $sth = self::getLimitedRightsConnection()->prepare($query);
        if ($params) {
            if (!is_array($params)) {
                $params = [$params];
            }
            foreach ($params as $key => $value) {
                if (is_numeric($key)) {
                    $paramName = $key + 1;
                } else {
                    $paramName = $key;
                }
                if (is_numeric($value)) {
                    $paramType = PDO::PARAM_INT;
                } else {
                    $paramType = PDO::PARAM_STR;
                }

                $sth->bindValue($paramName, $value, $paramType);
            }
        }

        $sth->execute();
        return $sth;
    }

    /**
     * @param  $query
     * @param  null $param
     * @param  bool $pre   debug query
     * @return bool
     */
    public static function query($query, $param = null, $pre = false, $skipDisableSlavePDO = false)
    {
        if (!$skipDisableSlavePDO) {
            self::disableSlaveConnection();
        }
        if ($pre) {
            self::pre($query, $param);
        }
        return self::getStatement($query, $param);
    }

    /**
     * @param  $query
     * @param  null $param
     * @return bool
     */
    public static function queryLC($query, $param = null)
    {
        return self::getStatementLC($query, $param);
    }

    /**
     * @param  $query
     * @param  null $param
     * @param  bool $pre   debug query
     * @return mixed Query result
     */
    public static function getOne($query, $param = null, $pre = false)
    {
        if ($pre) {
            self::pre($query, $param);
        }
        $sth = self::getStatement($query, $param, false, self::getConnectionName($query));
        $res = $sth->fetchColumn();
        $sth->closeCursor();
        return $res;
    }

    /**
     * @param  $query
     * @param  null $param
     * @param  bool $pre   debug query
     * @return mixed Query result
     */
    public static function getOneLC($query, $param = null, $pre = false)
    {
        if ($pre) {
            self::pre($query, $param);
        }
        $sth = self::getStatementLC($query, $param);
        $res = $sth->fetchColumn();
        $sth->closeCursor();
        return $res;
    }

    /**
     * @return int last inserted id
     */
    public static function getLastInsertId()
    {
        return (int) self::getMainPDO()->lastInsertId();
    }

    /**
     * @return int affected rows in last select query
     */
    public static function getTotal()
    {
        return (int) NDatabase::getOne('SELECT FOUND_ROWS()');
    }

    /**
     * @param  $table
     * @return int count rows in table
     */
    public static function getCount($table, $param = [])
    {
        return (int) NDatabase::getOne("SELECT count(1) from " . $table, $param);
    }

    /**
     * Get result from the query [row]
     *
     * @access public
     * @global object DB class
     * @global object DB Error
     * @param  string $query The SQL query
     * @param  array  $param Query's parameters
     * @param  bool   $pre   debug query
     * @return void Query result
     */
    public static function getRow($query, $param = null, $pre = false)
    {
        if ($pre) {
            self::pre($query, $param);
        }
        $sth = self::getStatement($query, $param, false, self::getConnectionName($query));
        $res = $sth->fetch(PDO::FETCH_NUM);
        $sth->closeCursor();
        return $res;
    }

    /**
     * Get result from the query [row]
     *
     * @access public
     * @param  string $query The SQL query
     * @param  array  $param Query's parameters
     * @param  bool   $pre   debug query
     * @return mixed
     */
    public static function getARow($query, $param = null, $pre = false)
    {
        if ($pre) {
            self::pre($query, $param);
        }
        $sth = self::getStatement($query, $param, false, self::getConnectionName($query));
        $res = $sth->fetch(PDO::FETCH_ASSOC);
        $sth->closeCursor();
        return $res;
    }

    /**
     * Get result from the query [row]
     *
     * @access public
     * @param  string $query The SQL query
     * @param  array  $param Query's parameters
     * @param  bool   $pre   debug query
     * @return mixed
     */
    public static function getARowLC($query, $param = null, $pre = false)
    {
        if ($pre) {
            self::pre($query, $param);
        }
        $sth = self::getStatementLC($query, $param);
        $res = $sth->fetch(PDO::FETCH_ASSOC);
        $sth->closeCursor();
        return $res;
    }

    /**
     * @param  $query
     * @param  null $param
     * @return array
     */
    public static function getCol($query, $param = null)
    {
        $sth = self::getStatement($query, $param, false, self::getConnectionName($query));
        $res = [];
        while (($row = $sth->fetchColumn()) !== false) {
            $res[] = $row;
        }
        $sth->closeCursor();
        return $res;
    }

    /**
     * @param  $query
     * @param  null $param
     * @return array
     */
    public static function getColLC($query, $param = null)
    {
        $sth = self::getStatementLC($query, $param);
        $res = [];
        while (($row = $sth->fetchColumn()) !== false) {
            $res[] = $row;
        }
        $sth->closeCursor();
        return $res;
    }

    /**
     * Get result from the query [all]
     *
     * @access public
     * @global object DB class
     * @global object DB Error
     * @param  string $query The SQL query
     * @param  array  $param Query's parameters
     * @param  bool   $pre   debug query
     * @return void Query result
     */
    public static function getAll($query, $param = null, $pre = false)
    {
        if ($pre) {
            self::pre($query, $param);
        }
        $sth = self::getStatement($query, $param, false, self::getConnectionName($query));
        $res = [];
        while ($row = $sth->fetch(PDO::FETCH_NUM)) {
            $res[] = $row;
        }
        $sth->closeCursor();
        return $res;
    }

    /**
     * Get result from the query [all]
     *
     * @access public
     * @global object DB class
     * @global object DB Error
     * @param  string $query The SQL query
     * @param  array  $param Query's parameters
     * @param  bool   $pre   debug query
     * @return array
     */
    public static function getAllAssoc($query, $param = null, $pre = false)
    {
        if ($pre) {
            self::pre($query, $param);
        }
        $sth = self::getStatement($query, $param, false, self::getConnectionName($query));
        $res = [];
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $res[] = $row;
        }
        $sth->closeCursor();
        return $res;
    }

    /**
     * Get result from the query [all]
     *
     * @access public
     * @global object DB class
     * @global object DB Error
     * @param  string $query The SQL query
     * @param  array  $param Query's parameters
     * @return array Query result
     */
    public static function getAllAssocLC($query, $param = null)
    {
        $sth = self::getStatementLC($query, $param);
        $res = [];
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $res[] = $row;
        }
        $sth->closeCursor();
        return $res;
    }

    /**
     * Shield the data for safely insert into SQL queries
     *
     * @access public
     * @param  string value
     * @param  boolean is shield needed for insert value in LIKE (add slashes also to %_)
     * @return string shielded value
     */
    public static function shield($val)
    {
        $val = is_string($val) ? $val : strval($val);
        $val = trim(
            self::getMainPDO()->quote(
                $val
            ),
            "'"
        );

        if (substr($val, -2) != "\\\\" and substr($val, -1) == "\\") {
            $val .= "'";
        }

        return $val;
    }

    public static function shieldIdsForIN($valuesIN)
    {
        $values = explode(',', $valuesIN);
        $safeValues = [];
        if (count($values)) {
            foreach ($values as $value) {
                $safeValues[] = (int) $value;
            }
            return implode(',', $safeValues);
        } else {
            return '0';
        }
    }

    public static function shieldFieldName($fieldName)
    {
        return preg_replace('![^a-zA-Z\\d_]*!', '', $fieldName);
    }

    public static function fetchRow(PDOStatement $statement)
    {
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public static function fetchAll(PDOStatement $statement)
    {
        $res = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $res[] = $row;
        }
        $statement->closeCursor();
        return $res;
    }

    public static function startTransaction()
    {
        self::disableSlaveConnection();
        self::$transactionId = md5(time());
        /*
            $data = [
                '$_SERVER' => $_SERVER,
                '$_REQUEST' => $_REQUEST,
                'trace' => debug_backtrace()
            ];
            self::query(
                "INSERT INTO temp_data SET create_date = NOW(), name = :name, data = :data",
                [':name' => self::$transactionId, ':data' => json_encode($data)]
            );
        */
        try {
            $transactionStarted = self::getMainPDO()->beginTransaction();
        } catch (Throwable $e) {
            if (self::isMysqlGoneAwayError($e)) {
                if (self::$tryReconnect < self::$reconnectCount) {
                    self::$tryReconnect++;
                    self::$pdoConnections[self::$mainConnection] = null;
                    self::init(self::$mainConnection);
                    self::startTransaction();
                    return;
                }
            }
            throw $e;
        }
        if (!$transactionStarted) {
            throw new Exception('Transaction not started');
        }
    }

    public static function startTransactionLC()
    {
        if (!self::getLimitedRightsConnection()->beginTransaction()) {
            throw new Exception('Limited connection transaction not started');
        }
    }
    public static function commitTransaction()
    {
        self::disableSlaveConnection();
        if (!self::getMainPDO()->commit()) {
            throw new Exception('Transaction not commited');
        }
        if (!empty(self::$transactionId)) {
           /* self::query(
                "DELETE FROM temp_data WHERE name = :name",
                [':name' => self::$transactionId]
            );*/
            self::$transactionId = '';
        }
    }
    public static function rollbackTransaction()
    {
        try {
            if (self::getMainPDO() and !self::getMainPDO()->rollBack()) {
                throw new Exception('Transaction not rollbacked');
            }
            if (!empty(self::$transactionId)) {
               /* self::query(
                    "DELETE FROM temp_data WHERE name = :name",
                    [':name' => self::$transactionId]
                );*/
                self::$transactionId = '';
            }
        } catch (Exception $e) {
            //
        }
    }
    public static function rollbackTransactionLC()
    {
        try {
            if (self::getLimitedRightsConnection()->rollBack()) {
                throw new Exception('Limited connection transaction not rollbacked');
            }
        } catch (Exception $e) {
            //
        }
    }

    public static function errorInfo()
    {
        return self::$pdoConnections[self::$lastConnectionName]->errorInfo();
    }

    public static function setTimeZone($timeZone = 'SYSTEM')
    {
        if (!NTimeZones::isTimeZoneValid($timeZone)) {
            $timeZone = 'SYSTEM';
        }
        self::$lastTimeZone = $timeZone;
        self::query("SET LOCAL time_zone = '{$timeZone}'", null, null, true);
        foreach (self::$pdoConnections as $connectionName => $pdo) {
            if ($pdo) {
                self::restoreTimeZone($connectionName);
            }
        }
    }
    public static function setTimeZoneByClinic($clinicId = 0)
    {
        $timeZone = self::getOne("SELECT time_zone FROM clinics WHERE id = :id", [':id' => $clinicId]);
        if (empty($timeZone)) {
            $timeZone = 'SYSTEM';
        }
        self::setTimeZone($timeZone);
    }

    public static function setFirstClinicTimeZoneIfSystem()
    {
        if (self::$lastTimeZone == 'SYSTEM') {
            self::setTimeZone(
                self::getOne(
                    "
                        SELECT
                            time_zone
                        FROM clinics
                        WHERE `status` = 'ACTIVE'
                        ORDER BY id
                        LIMIT 1;
                    "
                )
            );
        }
    }

    public static function setTimeZoneLC($timeZone = 'SYSTEM')
    {
        if (!NTimeZones::isTimeZoneValid($timeZone)) {
            $timeZone = 'SYSTEM';
        }
        self::queryLC("SET LOCAL time_zone = '{$timeZone}'");
    }

    public static function restoreTimeZone($connectionName)
    {
        $timeZone = self::$lastTimeZone;
        self::getStatement(
            "SET LOCAL time_zone = '{$timeZone}'",
            null,
            null,
            $connectionName
        );
    }

    /**
     * @return PDO
     */
    public static function getRootPDO(): PDO
    {
        if (is_null(self::$rootPDO)) {
            $dbname   = 'one';
            $host     = 'mysql';
            $rootUser = 'root';
            $rootPass = '123456';

            self::$rootPDO = new PDO("mysql:host={$host};dbname={$dbname};charset=UTF8", $rootUser, $rootPass);
            self::$rootPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$rootPDO;
    }
    private static function getLimitedConfig()
    {
        $config = [
            'dbname'    => NConfig::get("dbname", self::$limitedConnection),
            'host'      => NConfig::get("host", self::$limitedConnection),
            'user'      => NConfig::get("user", self::$limitedConnection),
            'password'  => NConfig::get("password", self::$limitedConnection)
        ];

        if (count(self::$slaveConnections) > 0 && $config['host'] == NConfig::get('host', self::$mainConnection)) {
            $config['host'] = NConfig::get('host', self::$slaveConnections[0]);
        }

        return $config;
    }

    /**
     * @return PDO
     */
    protected static function getLimitedRightsConnection()
    {
        if (!empty(self::$pdoConnections[self::$limitedConnection])) {
            return self::$pdoConnections[self::$limitedConnection];
        }

        try {
            $config = self::getLimitedConfig();
            $pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['dbname']};charset=UTF8",
                $config['user'],
                $config['password']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdoConnections[self::$limitedConnection] = $pdo;
            self::queryLC('SET NAMES utf8;');
            self::queryLC('SET SESSION max_join_size=16000000;');

            return $pdo;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        return false;
    }

    public static function closeConnection($connectionName = null)
    {
        if (empty($connectionName)) {
            $connectionName = self::$mainConnection;
        }
        self::$pdoConnections[$connectionName] = null;
    }

    public static function closeLimitedRightsConnection()
    {
        self::closeConnection(self::$limitedConnection);
    }

    /**
     * @param  array $data   result of NDatabase::getAllAssoc
     * @param  array $fields ('fieldName' => 'fieldType') // b - boolean, i - integer, f - float
     * @return array
     */
    public static function prettyTypeData(&$data = [], $fields = [])
    {
        if (!is_array($data) || !array_key_exists(0, $data) || !is_array($data[0])) {
            return $data;
        } else {
            foreach ($data as $i => $row) {
                foreach ($fields as $name => $type) {
                    switch ($type) {
                        case 'b':
                            $value = (bool)$row[$name];
                            break;
                        case 'i':
                            $value = (int)$row[$name];
                            break;
                        case 'f':
                            $value = (float)$row[$name];
                            break;
                        default:
                            $value = (string)$row[$name];
                    }
                    $data[$i][$name] = $value;
                }
            }
            return $data;
        }
    }

    public static function replaceQueryParams($query, $params = [])
    {
        $replacements = ['ROUNDCURRENCY', 'ROUNDCURRENCYSTORE', 'ROUNDQUANTITY'];

        foreach ($replacements as $pattern) {
            $results = [];
            preg_match_all('|' . $pattern . '\{.*\}|isU', $query, $results);

            foreach ($results[0] as $result) {
                $newResult = substr(str_replace($pattern . '{', '', $result), 0, -1);
                switch ($pattern) {
                    case 'ROUNDCURRENCY':
                        $newResult = SQLround_currency($newResult);
                        break;
                    case 'ROUNDCURRENCYSTORE':
                        $newResult = SQL_round_currency_store($newResult);
                        break;
                    case 'ROUNDQUANTITY':
                        $newResult = SQLround_quantity($newResult);
                        break;
                }

                $query = str_replace($result, $newResult, $query);
            }
        }

        foreach ($params as $paramName => $paramValue) {
            $query = str_replace("{{$paramName}}", "{$paramValue}", $query);
        }

        return $query;
    }

    public static function pre($query, $param)
    {
        if ($query) {
            if (is_array($param)) {
                if (array_key_exists(0, $param)) {
                    $index = 0;
                    $slashedQuery = $query;
                    $cnt = count($param);
                    while ($index < $cnt) {
                        $start = strpos($slashedQuery, '?');
                        if ($start !== false && array_key_exists($index, $param)) {
                            $slashedQuery = substr_replace($slashedQuery, "'{$param[$index]}'", $start, 1);
                        } else {
                            break;
                        }
                        $index++;
                    }
                    pre($slashedQuery);
                } else {
                    $slashedQuery = preg_replace('/(:(\w|\d|_)*)/is', '*|*$0*|*', $query);
                    foreach ($param as $key => $value) {
                        $slashedQuery = str_replace('*|*' . $key . '*|*', "'{$value}'", $slashedQuery);
                    }
                    $slashedQuery = str_replace('*|*:*|*', ':', $slashedQuery);
                    pre($slashedQuery);
                }
            } else {
                pre($query);
            }
        }
    }

    public static function killLimitedUserProcesses()
    {
        $config = self::getLimitedConfig();

        $stmt = self::getLimitedRightsConnection()->query("SHOW PROCESSLIST;");

        if ($stmt) {
            $processes = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($row['User'] == $config['user'] && $row['db'] == $config['dbname']) {
                    $processes[] = (int) $row['Id'];
                }
            }

            $stmt->closeCursor();

            foreach ($processes as $pid) {
                self::queryLC("KILL {$pid}")->execute();
            }
        }
    }

    public static function fixTablesCollations($echo = false)
    {
        $root = self::getRootPDO();
        $row = self::getARow(
            "SELECT DATABASE() AS db, @@character_set_database AS `charset`,
            @@collation_database AS `collation`"
        );
        $db = $row['db'];
        $tables = self::getAllAssoc("SHOW TABLE STATUS FROM `{$db}` WHERE `Collation` <> 'utf8_general_ci'");
        $cnt = sizeof($tables);
        if ($echo) {
            echo "Found broken tables {$cnt}\n";
        }
        foreach ($tables as $table) {
            if ($echo) {
                echo "\t{$table['Name']}\n";
            }
            $root->query(
                "ALTER TABLE `{$db}`.`{$table['Name']}` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;"
            );
        }
        if ($echo) {
            echo "DONE\n";
        }
    }

    /**
     * @var $logEntityRowsBufferCount int
     */
    private static $logEntityRowsBufferCount = 10;
    /**
     * @var $logEntityRows array()
     */
    private static $logEntityRows = [];
    /**
     * @var $logEntityValueRows array()
     */
    private static $logEntityValueRows = [];

    /**
     * @param  $userId    int
     * @param  $tableName string
     * @param  $tablePk   string
     * @param  $action    string
     * @return int
     */
    public static function logEntity($userId, $tableName, $tablePk, $action)
    {
        if (count(self::$logEntityRows) >= self::$logEntityRowsBufferCount) {
            self::clearLogEntityBuffer();
        }
        self::$logEntityRows[] = [$userId, $tableName, $tablePk, $action];
        return count(self::$logEntityRows) - 1;
    }

    /**
     * @param  $logId     int
     * @param  $fieldName string
     * @param  $oldValue  string
     * @param  $newValue  string
     * @return int
     */
    public static function logEntityValue($logId, $fieldName, $oldValue, $newValue)
    {
        self::$logEntityValueRows[] = [$logId, $fieldName, $oldValue, $newValue];
        return count(self::$logEntityValueRows) - 1;
    }

    private static function clearLogEntityBuffer()
    {
        if (sizeof(self::$logEntityRows) == 0) {
            return;
        }
        $idsMap = [];
        $values = [];
        $params = [];
        foreach (self::$logEntityRows as $oldLogId => $logEntity) {
            $idsMap[$oldLogId] = 0;
            $values[] = "(:userId{$oldLogId}, :tableName{$oldLogId}, :tablePk{$oldLogId}, :action{$oldLogId})";
            $params[':userId' . $oldLogId] = $logEntity[0];
            $params[':tableName' . $oldLogId] = $logEntity[1];
            $params[':tablePk' . $oldLogId] = $logEntity[2];
            $params[':action' . $oldLogId] = $logEntity[3];
        }

        $values = implode(',', $values);

        self::query("INSERT INTO change_log (user_id, table_name, table_pk, `action`) VALUES {$values}", $params);
        $firstLogId = self::getLastInsertId();

        if (sizeof(self::$logEntityValueRows) == 0) {
            return;
        }

        foreach ($idsMap as $oldLogId => $value) {
            $idsMap[$oldLogId] = $firstLogId++;
        }

        $values = [];
        $params = [];

        foreach (self::$logEntityValueRows as $i => $logValueEntity) {
            $values[] = "(:logId{$i}, :fieldName{$i}, :oldValue{$i}, :newValue{$i})";
            $params[':logId' . $i] = $idsMap[$logValueEntity[0]];
            $params[':fieldName' . $i] = $logValueEntity[1];
            $params[':oldValue' . $i] = $logValueEntity[2];
            $params[':newValue' . $i] = $logValueEntity[3];
        }

        $values = implode(',', $values);

        self::query(
            "INSERT INTO change_log_values (log_id, field_name, old_value, new_value) VALUES {$values}",
            $params
        );

        self::$logEntityRows = [];
        self::$logEntityValueRows = [];
    }
    public static function destruct()
    {
        self::clearLogEntityBuffer();
    }
    public static function getNow($format = '%d.%m.%Y %H:%i')
    {
        return self::getOne("SELECT DATE_FORMAT(NOW(), '{$format}')");
    }

    public static function createNowDateTime()
    {
        $format = '%Y-%m-%d %H:%i:%s';
        $date = self::getNow($format);

        return self::createDateTimeFromFormat($format, $date);
    }

    public static function createDateTimeFromFormat($format = '%Y-%m-%d %H:%i:%s', $datetimeStr = '')
    {
        $timestamp = self::getOne(
            "SELECT UNIX_TIMESTAMP(STR_TO_DATE(:datetime, :format))",
            [
                ':datetime' => $datetimeStr,
                ':format' => $format
            ]
        );

        $date = new DateTime();
        $date->setTimestamp($timestamp);
        try {
            $date->setTimezone(new DateTimeZone(self::$lastTimeZone));
        } catch (Throwable $e) {
        }
        return $date;
    }

    public static function setReconnectCount($cnt)
    {
        $cnt = (int) $cnt;
        self::$reconnectCount = $cnt;
    }

    public static function getDateFromTimestamp($timestamp)
    {
        return self::getOne('SELECT FROM_UNIXTIME(:timestamp)', [':timestamp' => $timestamp]);
    }

    public static function disableSlaveConnection()
    {
        self::$disabledSlaveConnection = true;
    }

    public static function enableSlaveConnection()
    {
        self::$disabledSlaveConnection = false;
    }

    public static function safeFromSlave($query)
    {
        return
            strpos($query, 'properties')
            || strpos($query, 'all_settings')
            || strpos($query, 'ip_access')
            || strpos($query, 'last_time')
//            || strpos($query, 'system_log')
            || strpos($query, 'tariffs');
    }

    public static function onlyFromMainConnection($query)
    {
        return
            defined('MIGRATION') || strpos($query, 'LAST_INSERT_ID()');
    }

    public static function onlyFromLastConnection($query)
    {
        return
            strpos($query, 'FOUND_ROWS()');
    }

    public static function fromSlave($query)
    {
        return
            !self::onlyFromMainConnection($query)
            && (
                self::safeFromSlave($query)
                || !self::$disabledSlaveConnection
            );
    }

    public static function getSlaveConnectionName()
    {
        return self::$slaveConnections[rand(0, count(self::$slaveConnections) - 1)];
    }

    public static function saveLastConnectionName($connectionName)
    {
        self::$lastConnectionName = $connectionName;
    }

    public static function getConnectionName($query)
    {
        if (self::onlyFromLastConnection($query)) {
            $connectionName = self::$lastConnectionName;
        } else {
            $connectionName = (self::fromSlave($query)) ? self::getSlaveConnectionName() : self::$mainConnection;
        }

        self::saveLastConnectionName($connectionName);
        return $connectionName;
    }

    public static function isTableExists($tableName)
    {
        $query = "show tables like '{$tableName}';";
        $table = self::getOne($query);

        return $table === $tableName;
    }

    public static function isColumnExists($tableName, $columnName)
    {
        $query = "show columns from {$tableName} like '{$columnName}';";
        $column = self::getOne($query);

        return $column === $columnName;
    }

    public static function isLockedTable($tableName)
    {
        return !empty(
            self::getARow(
                "SHOW OPEN TABLES WHERE `Table` LIKE '%{$tableName}%' AND In_use > 0"
            )
        );
    }

    public static function waitUnlockTable($tableName, $waitTimeMS = 5000): bool
    {
        $elapsed = 0;

        while ($elapsed < $waitTimeMS) {
            if (!self::isLockedTable($tableName)) {
                return true;
            }
            $elapsed += 500;
            usleep(500000);
        }

        return !self::isLockedTable($tableName);
    }

    public static function createParamsAndValuesFromArray(array $data, $paramPrefix = 'var'): array
    {
        return [
            0 => implode(',', array_map(function ($key) use ($paramPrefix) {
                return ':' . $paramPrefix . '_' . $key;
            }, array_keys($data))),
            1 => array_combine(array_map(function ($key) use ($paramPrefix) {
                return ':' . $paramPrefix . '_' . $key;
            }, array_keys($data)), array_values($data)),
        ];
    }

    private static function isMysqlGoneAwayError(Throwable $e): bool
    {
        return ($e->getMessage() == 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away');
    }

    public static function isTransactionStarted(): bool
    {
        return !empty(self::$transactionId);
    }
}

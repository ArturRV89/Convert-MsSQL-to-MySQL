<?php

namespace Components\MsDatabase;

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
use Pimple\Container as PimpleContainer;

class MsDatabase
{
    /**
     * @var PDO object
     */
    private static $rootPDO;

    /**
     * @var MsDatabase
     */
    private static $instance;

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
     * @return PDO|null
     */
    public function connectionToDb(): ?PDO
    {
        $dsn = getenv('DEFAULT_DSN');
        $username = getenv('DEFAULT_USERNAME');
        $password = getenv('DEFAULT_PASSWORD');
        $pdo = null;

        try {
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }

        return $pdo;
    }

    public function getRootPDOContainer()
    {
        $container = new PimpleContainer();
        $pdo = $this->connectionTodb();
        
        $container['db'] = function () use ($pdo) {
            return $pdo;
        };
        
        return $container['db'];
    }
}

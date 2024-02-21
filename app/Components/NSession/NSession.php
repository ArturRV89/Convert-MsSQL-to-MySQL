<?php

namespace  Components\NSession;

use Entity\DebugLog\DebugLog;
use App\Components\NDatabase\NDatabase;
//use const Components\NSession\DISABLE_SESSION;

/**
 * NSession is an abstract session engine.
 * This component can be used for following session routines:
 *   - get a session parameter
 *   - set a session parameter
 *   - remove a session parameter
 *   - remove all session parameters
 *   - destroy the session
 *
 * @access  public
 * @name    NSession
 * @package NSession
 * @version 1.0
 *
 * Examples:
 * NComponentManager::load("NSession"); // Load session component
 * NSession::set("a", 10);              // Set session key
 * $a = NSession::get("a");             // Get session key
 * NSession::remove("a");               // Remove session key
 * NSession::removeall();               // Remove all session keys
 * NSession::destroy();                 // Destroy the session
 */
class NSession
{
    public const MAX_TRY_COUNT = 30;
    /**
     * Initialize Session
     * Checks if register_globals is on - remove from globals session vars
     *
     * @access public
     */
    public static $id = '';

    public static function start()
    {
        if (self::disabled()) {
            return;
        }
        $copy = [];
        foreach ($GLOBALS as $k => $v) {
            $copy[$k] = $v;
        }
        self::startSession();
        foreach ($_SESSION as $k => $v) {
            unset($GLOBALS[$k]);
            if (isset($copy[$k])) {
                $GLOBALS[$k] = $copy[$k];
            }
        }
    }
    protected static function startSession($writeCloseSession = true)
    {
        if (self::disabled()) {
            return;
        }
        $tryCount = 0;

        while (self::MAX_TRY_COUNT > $tryCount) {
            try {
                if (!session_start() && empty(@session_id())) {
                    throw new \Exception('Session did not start.');
                }
                self::$id = session_id();
                if ($writeCloseSession) {
                    session_write_close();
                }
                return;
            } catch (\Throwable $e) {
                usleep(200000);
                $tryCount++;
                $message = $e->getMessage();
            }
        }
        throw new \Exception("Can`t create session: {$message}");
    }

    /**
     * Get Session parameter
     *
     * @access public
     * @param  string $aKey The HTTP key
     * @return mixed Session parameter
     */
    public static function get($aKey, $subKey = '', $tryCount = 2)
    {
        if (!self::disabled() && (!isset($_SESSION) || !session_id())) {
            NSession::start();
        }
        if (self::disabled()) {
            $_SESSION = $_SESSION ?? [];
        }
        $result = $_SESSION[$aKey] ?? null;
        if (is_array($result) && !empty($subKey)) {
            return $result[$subKey] ?? null;
        }
        if (!empty($result) || $tryCount <= 0) {
            return $result;
        }
        $needRepeatKeys = ['user_id', 'domain_name', 'clinic'];
        if (!in_array($aKey, $needRepeatKeys)) {
            return $result;
        }
        usleep(100);
        return NSession::get($aKey, $subKey, $tryCount - 1);
    }

    /**
     * Set Session parameter
     *
     * @access public
     * @param  string $aKey   The Session key
     * @param  string|array $aValue The Session value
     */
    public static function set($aKey, $aValue)
    {
        if (self::disabled()) {
            $_SESSION = $_SESSION ?? [];
            if (!$aValue === null) {
                self::remove($aKey);
            } else {
                $_SESSION[$aKey] = $aValue;
            }
            return;
        }
        if (!session_id()) {
            NSession::start();
        }
        self::startSession(false);
        if ($aValue !== null) {
            if ($aKey == 'clinic' && !empty($aValue['end_time']) && $aValue['end_time'] == '00:00') {
                $aValue['end_time'] = '23:59';
            }
            $_SESSION[$aKey] = $aValue;
        } elseif (isset($_SESSION[$aKey])) {
            NSession::remove($aKey);
        }
        session_write_close();
    }

    /**
     * UnSet Session parameter
     *
     * @access public
     * @param  string $aKey The Session key
     */
    public static function remove($aKey)
    {
        if (self::disabled()) {
            $_SESSION = $_SESSION ?? [];
            unset($_SESSION[$aKey]);
            return;
        }
        if (!session_id()) {
            NSession::start();
        }
        self::startSession(false);
        unset($_SESSION[$aKey]);
        session_write_close();
    }

    /**
     * UnSet all Session parameters
     *
     * @access public
     */
    public static function removeall()
    {
        if (self::disabled()) {
            $_SESSION = [];
            return;
        }
        if (!session_id()) {
            NSession::start();
        }

        self::startSession(false);
        $_SESSION = [];
        session_write_close();
    }

    /**
     * Destroy the session
     *
     * @access public
     */
    public static function destroy()
    {
        if (self::disabled()) {
            $_SESSION = [];
            return;
        }
        if (!session_id()) {
            NSession::start();
        }

        self::startSession(false);
        session_regenerate_id();
        session_destroy();
        session_write_close();
    }

    public static function &getRef($aKey)
    {
        if (!self::disabled() && !session_id()) {
            NSession::start();
        }
        return $_SESSION[$aKey];
    }

    public static function load($data)
    {
        foreach ($data as $key => $value) {
            self::set($key, $value);
        }
    }

    public static function setTimeZoneByCurrentUser()
    {
        $clinicId = (int)self::get('clinic', 'id');
        if ($clinicId) {
            NDatabase::setTimeZoneByClinic($clinicId);
        }
    }
    public static function destroySessionWhenMemcachedFailed()
    {
        if (self::disabled()) {
            return;
        }
        \Entity\VmSession\VMSession::getInstance()->destroyCurrentPHPSession();
        self::destroy();
    }

    public static function warningHandler($errno, $errstr)
    {
        if (self::disabled()) {
            return;
        }
        if (strpos($errstr, "Failed to write session lock") !== false) {
            (new DebugLog('NSession.php'))
                ->debug('Destroy Session. Failed to write session lock.');
            self::destroySessionWhenMemcachedFailed();
        }
    }

    private static function disabled(): bool
    {
        return defined('DISABLE_SESSION') && DISABLE_SESSION;
    }
}

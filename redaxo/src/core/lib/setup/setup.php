<?php

class rex_setup
{
    const MIN_PHP_VERSION = REX_MIN_PHP_VERSION;
    const MIN_MYSQL_VERSION = '5.0';

    static private $MIN_PHP_EXTENSIONS = array('session', 'pdo', 'pdo_mysql', 'pcre');

    /**
     * very basic setup steps, so everything is in place for our browser-based setup wizard.
     *
     * @param string $skinAddon
     * @param string $skinPlugin
     */
    static public function init($skinAddon = 'be_style', $skinPlugin = 'redaxo')
    {
        // initial purge all generated files
        rex_delete_cache();

        // delete backend session
        rex_backend_login::deleteSession();

        // copy alle media files of the current rex-version into redaxo_media
        rex_dir::copy(rex_path::core('assets'), rex_path::assets());

        // copy skins files/assets
        rex_dir::copy(rex_path::plugin($skinAddon, $skinPlugin, 'assets'), rex_path::pluginAssets($skinAddon, $skinPlugin, ''));
    }

    /**
     * checks environment related conditions
     *
     * @return array An array of error messages
     */
    static public function checkEnvironment()
    {
        $errors = array();

        // -------------------------- VERSIONSCHECK
        if (version_compare(phpversion(), self::MIN_PHP_VERSION, '<') == 1) {
            $errors[] = rex_i18n::msg('setup_301', phpversion(), self::MIN_PHP_VERSION);
        }

        // -------------------------- EXTENSION CHECK
        foreach (self::$MIN_PHP_EXTENSIONS as $extension) {
            if (!extension_loaded($extension))
                $errors[] = rex_i18n::msg('setup_302', $extension);
        }

        return $errors;
    }

    /**
     * checks permissions of all required filesystem resources
     *
     * @return array An array of error messages
     */
    static public function checkFilesystem()
    {
        // -------------------------- SCHREIBRECHTE
        $writables = array(
            rex_path::media(),
            rex_path::assets(),
            rex_path::cache(),
            rex_path::data(),
            rex_path::src()
        );

        $func = function ($dir) use (&$func) {
            if (!rex_dir::isWritable($dir)) {
                return array('setup_304' => array($dir));
            }
            $res = array();
            foreach (rex_finder::factory($dir) as $path => $file) {
                if ($file->isDir()) {
                    $res = array_merge_recursive($res, $func($path));
                } elseif (!$file->isWritable()) {
                    $res['setup_305'][] = $path;
                }
            }
            return $res;
        };

        $res = array();
        foreach ($writables as $dir) {
            if (@is_dir($dir)) {
                $res = array_merge_recursive($res, $func($dir));
            } else {
                $res['setup_306'][] = $dir;
            }
        }

        return $res;
    }

    /**
     * Checks the version of the connected database server.
     *
     * @param array   $config   of databaes configs
     * @param boolean $createDb Should the database be created, if it not exists.
     * @return string Error
     */
    static public function checkDb($config, $createDb)
    {
        $err = rex_sql::checkDbConnection($config['db'][1]['host'], $config['db'][1]['login'], $config['db'][1]['password'], $config['db'][1]['name'], $createDb);
        if ($err !== true) {
            return $err;
        }

        $serverVersion = rex_sql::getServerVersion();
        if (rex_string::compareVersions($serverVersion, self::MIN_MYSQL_VERSION, '<') == 1) {
            return rex_i18n::msg('setup_404', $serverVersion, self::MIN_MYSQL_VERSION);
        }
        return '';
    }
}

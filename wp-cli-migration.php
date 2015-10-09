<?php
/**
 * Plugin Name: Wp cli migration
 * Plugin URI: http://frojd.se
 * Description: Migration manager for wordpress
 * Version: 0.1
 * Author: Fröjd - Mikael Engström
 * Author URI: http://frojd.se
 * License: Fröjd Interactive AB (All Rights Reserved).
 */

namespace Frojd\Plugin\WpCliMigration;

class WpCliMigration {

    protected static $instance = null;

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected $pluginBaseDir;
    protected $commandsDir;

    private function __construct() {
        $this->pluginBaseDir = __DIR__;
        $this->commandsDir = $this->pluginBaseDir.'/commands';

        if (!defined('WP_CLI_MIGRATION_PATH')) {
            define('WP_CLI_MIGRATION_PATH', ABSPATH . 'migrations/');
        }
        
        $this->createMigrationFolderIfNotExists();

        $this->requireAllCommands();
    }

    private function createMigrationFolderIfNotExists() {
        if (!file_exists(WP_CLI_MIGRATION_PATH)) {
            try {
                mkdir(WP_CLI_MIGRATION_PATH, 0755, true);
            } catch (\Exception $e) {
                \WP_CLI::error(sprintf('Could not create migration folder: %s', $e->getMessage()));
            }
        }
    }

    private function requireAllCommands() {
        foreach (glob($this->commandsDir.'/*.php') as $file) {
            require_once $file;
        }
    }

    static public function getMigrationFolder() {
        return WP_CLI_MIGRATION_PATH;
    }
}

if ( defined('WP_CLI') && WP_CLI ) {
    WpCliMigration::getInstance();
}

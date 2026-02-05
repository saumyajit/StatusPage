<?php declare(strict_types = 1);

namespace Modules\StatusPageCompact;

use APP;
use CMenuItem;
use Zabbix\Core\CModule;

class Module extends CModule {

    /**
     * Initialize module.
     */
    public function init(): void {
        APP::Component()->get('script')->loadModule($this->getId());
    }

    /**
     * Get module name.
     */
    public function getName(): string {
        return _('Status Page');
    }

    /**
     * Get module version.
     */
    public function getVersion(): string {
        return '2.0.0';
    }

    /**
     * Get module author.
     */
    public function getAuthor(): string {
        return 'Custom Development';
    }

    /**
     * Get module description.
     */
    public function getDescription(): string {
        return _('Compact visual status page for large-scale deployments - shows host group status without labels');
    }

    /**
     * Get module menu items.
     */
    public function getMenuItems(): array {
        return [];
    }

    /**
     * Called on module enable.
     */
    public function onEnable(): void {
        // Module enable logic if needed
    }

    /**
     * Called on module disable.
     */
    public function onDisable(): void {
        // Module disable logic if needed
    }

    /**
     * Called on module install.
     */
    public function onInstall(): void {
        // Module install logic if needed
    }

    /**
     * Called on module uninstall.
     */
    public function onUninstall(): void {
        // Module uninstall logic if needed
    }
}

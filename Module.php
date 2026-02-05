<?php declare(strict_types = 1);

namespace Modules\StatusPageCompact;

use Zabbix\Core\CModule;

class Module extends CModule {

    /**
     * Initialize module.
     */
    public function init(): void {
        // Module initialization - leave empty for widget-only modules
    }

    /**
     * Get module name.
     */
    public function getName(): string {
        return 'Status Page';
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
        return 'Compact visual status page for large-scale deployments - shows host group status without labels';
    }
}

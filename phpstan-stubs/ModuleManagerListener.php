<?php

/**
 * PHPStan stub for ModuleManagerListener
 *
 * This class is dynamically loaded from each module's directory.
 * Each module implements its own ModuleManagerListener with these methods.
 */
class ModuleManagerListener
{
    /**
     * Get the namespace for the module
     */
    public static function getModuleNamespace(): string
    {
    }

    /**
     * Initialize and return the listener instance
     */
    public static function initListenerSelf(): self
    {
    }

    /**
     * Handle module manager actions (install, enable, disable, unregister)
     */
    public function moduleManagerAction(string $action, string $modId, string $currentValue): string
    {
    }
}

<?php

declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Install;

class Installer
{
    private const CONFIGURATION_KEYS = [
        // Account
        'SMAILY_SUBDOMAIN',
        'SMAILY_USERNAME',
        'SMAILY_PASSWORD',
        // Customer Sync
        'SMAILY_ENABLE_CUSTOMER_SYNC',
        'SMAILY_CUSTOMER_CRON_TOKEN',
        'SMAILY_SYNCRONIZE_ADDITIONAL',
        'SMAILY_OPTIN_ENABLED',
        'SMAILY_OPTIN_AUTORESPONDER',
        // Abandoned Cart
        'SMAILY_ENABLE_ABANDONED_CART',
        'SMAILY_CART_CRON_TOKEN',
        'SMAILY_CART_AUTORESPONDER',
        'SMAILY_ABANDONED_CART_TIME',
        'SMAILY_CART_SYNCRONIZE_ADDITIONAL',
        // RSS
        'SMAILY_RSS_CATEGORY_ID',
        'SMAILY_RSS_LIMIT',
        'SMAILY_RSS_SORT_BY',
        'SMAILY_RSS_SORT_ORDER',
    ];

    public function install(\Module $module): bool
    {
        if (!$this->createTables()
            || !$this->addDefaultConfiguration()
            || !$this->registerHooks($module)
        ) {
            return false;
        }

        return true;
    }

    public function uninstall(): bool
    {
        if (!$this->removeTables()
            || !$this->removeConfiguration()
        ) {
            return false;
        }

        return true;
    }

    private function createTables(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'smaily_cart (
            `id_smaily_cart` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `id_customer` INT UNSIGNED NULL ,
            `id_cart` INT UNSIGNED NULL ,
            `date_sent` DATETIME NOT NULL) ENGINE=' . _MYSQL_ENGINE_;

        return \Db::getInstance()->execute($sql);
    }

    private function removeTables(): bool
    {
        $sql = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'smaily_cart';

        return \Db::getInstance()->execute($sql);
    }

    private function registerHooks(\Module $module): bool
    {
        $hooks = [
            'actionCustomerAccountAdd',
            'displayFooterBefore',
            'displayLeftColumn',
            'displayRightColumn',
        ];

        return (bool) $module->registerHook($hooks);
    }

    private function addDefaultConfiguration(): bool
    {
        $defaults = [
            // Account
            'SMAILY_SUBDOMAIN' => '',
            'SMAILY_USERNAME' => '',
            'SMAILY_PASSWORD' => '',
            // Customer Sync
            'SMAILY_ENABLE_CUSTOMER_SYNC' => false,
            'SMAILY_CUSTOMER_CRON_TOKEN' => bin2hex(random_bytes(6)),
            'SMAILY_SYNCRONIZE_ADDITIONAL' => serialize([]),
            'SMAILY_OPTIN_ENABLED' => false,
            'SMAILY_OPTIN_AUTORESPONDER' => '',
            // Abandoned Cart
            'SMAILY_ENABLE_ABANDONED_CART' => 0,
            'SMAILY_CART_CRON_TOKEN' => bin2hex(random_bytes(6)),
            'SMAILY_CART_AUTORESPONDER' => '',
            'SMAILY_ABANDONED_CART_TIME' => 15,
            'SMAILY_CART_SYNCRONIZE_ADDITIONAL' => serialize([]),
            // RSS
            'SMAILY_RSS_CATEGORY_ID' => '',
            'SMAILY_RSS_LIMIT' => 50,
            'SMAILY_RSS_SORT_BY' => 'date_upd',
            'SMAILY_RSS_SORT_ORDER' => 'desc',
        ];

        foreach (self::CONFIGURATION_KEYS as $key) {
            if (!\Configuration::updateValue($key, $defaults[$key])) {
                return false;
            }
        }

        return true;
    }

    private function removeConfiguration(): bool
    {
        foreach (self::CONFIGURATION_KEYS as $key) {
            if (!\Configuration::deleteByName($key)) {
                return false;
            }
        }

        return true;
    }
}

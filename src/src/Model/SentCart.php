<?php

declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Model;

class SentCart extends \ObjectModel
{
    /**
     * @var int
     */
    public $id_smaily_cart;

    /**
     * @var int
     */
    public $id_customer;

    /**
     * @var int
     */
    public $id_cart;

    /**
     * @var string;
     */
    public $date_add;

    /**
     * @var string
     */
    public $date_upd;

    public static $definition = [
        'table' => 'smaily_cart',
        'primary' => 'id_smaily_cart',
        'multilang' => false,
        'fields' => [
            'id_customer' => ['type' => self::TYPE_INT],
            'id_cart' => ['type' => self::TYPE_INT],
            'date_add' => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
        ],
    ];
}

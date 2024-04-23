<?php

declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Model;

class AbandonedCart
{
    /**
     * @var int
     */
    public $cartID;
    /**
     * @var int
     */
    public $customerID;

    /**
     * @var string
     */
    public $dateUpdated;
    /**
     * @var string
     */
    public $firstName;
    /**
     * @var string
     */
    public $lastName;
    /**
     * @var string
     */
    public $email;

    /**
     * @var array;
     */
    public $products;
}

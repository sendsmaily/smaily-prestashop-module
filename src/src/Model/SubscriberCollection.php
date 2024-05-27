<?php
/**
 * 2024 Smaily
 *
 * NOTICE OF LICENSE
 *
 * Smaily for PrestaShop is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Smaily for PrestaShop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Smaily for PrestaShop. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Smaily <info@smaily.com>
 * @copyright 2024 Smaily
 * @license   GPL3
 */
declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriberCollection
{
    public function batchUnsubscribeByEmail(array $emails): bool
    {
        $query = 'UPDATE ' . _DB_PREFIX_ . 'customer SET newsletter=0 WHERE email IN (' .
        implode(
            ', ',
            array_map(
                function ($email) {
                    return "'" . pSQL($email) . "'";
                },
                $emails
            )
        ) . ')';

        return \Db::getInstance()->execute($query);
    }

    /**
     * Gets subscribers from DB.
     *
     * @return Subscriber[] Subscriber array
     */
    public function getSubscribers(int $limit, int $offset = 0): array
    {
        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from('customer', 'c');
        $sql->where('c.newsletter = 1');
        $sql->limit($limit, $offset);

        $result = \Db::getInstance()->executeS($sql);

        $subscribers = [];
        foreach ($result as $subscriber) {
            $s = new Subscriber();
            $s->email = $subscriber['email'];
            $s->firstName = $subscriber['firstname'];
            $s->lastName = $subscriber['lastname'];
            $s->birthDay = $subscriber['birthday'];
            $s->website = $subscriber['website'];

            $subscribers[] = $s;
        }

        return $subscribers;
    }
}

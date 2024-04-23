<?php

declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Model;

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

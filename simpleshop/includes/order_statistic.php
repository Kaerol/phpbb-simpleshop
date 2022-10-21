<?php

namespace kaerol\simpleshop\includes;

use Symfony\Component\DependencyInjection\Container;

class order_statistic
{
    /** @var \phpbb\db\driver\driver */
    protected  $db;

    /** @var \phpbb\user */
    protected  $user;

    /** @var \phpbb\language\language */
    protected  $language;

    public function __construct(
        Container $phpbb_container,
        \phpbb\db\driver\driver_interface $db,
        \phpbb\user $user,
        \phpbb\language\language $language
    ) {
        $this->db = $db;
        $this->language = $language;

        $this->core_users = $phpbb_container->getParameter('tables.core_users');
        $this->offer_item = $phpbb_container->getParameter('tables.simpleshop_sale_offer_item');
        $this->offer_item_order = $phpbb_container->getParameter('tables.simpleshop_sale_offer_item_order');
    }

    public function getStatisticWithLabels($sale_id, $user_id)
    {
        $allStatistic = $this->_getCurrentOrderAllStatistic($sale_id);
        $userStatistic = $this->_getCurrentOrderUserStatistic($sale_id, $user_id);
        $statistic = $this->_calculateStatistic($allStatistic, $userStatistic);

        foreach ($statistic as &$item) {
            $count = $item['count'];
            $user_count = $item['user_count'];

            $item['all_count_label'] = $this->language->lang('KAEROL_SIMPLESHOP_ORDER_ALL_LABEL', $count);
            $item['user_count_label'] = $this->language->lang('KAEROL_SIMPLESHOP_ORDER_USER_LABEL', $user_count);
        }

        return $statistic;
    }

    public function getCurrentOrderAllReport($sale_id)
    {
        $sql = 'SELECT oi.item_name as name, sum(soi1.count) as count 
				FROM forumsimpleshop_sale_offer_item_order soi1 
				INNER JOIN forumsimpleshop_sale_offer_item oi on oi.id = soi1.sale_offer_item_id 
				WHERE soi1.SALE_OFFER_ID = ' . $sale_id . ' GROUP by soi1.sale_offer_item_id order by oi.id';

        $dbResult = $this->db->sql_query($sql);
        $result = array();

        while ($row = $this->db->sql_fetchrow($dbResult)) {
            $result[] = ['name' => $row['name'], 'count' => $row['count']];
        }
        $this->db->sql_freeresult($dbResult);

        return $result;
    }

    public function getCurrentOrderPersonReport($sale_id)
    {
        $sql = 'SELECT u.username, oi.item_name as name, soi1.count as count 
					FROM ' . $this->offer_item_order . ' soi1 
					INNER JOIN ' . $this->offer_item . ' oi on oi.id = soi1.sale_offer_item_id 
					INNER JOIN ' . $this->core_users . ' u on u.user_id = soi1.user_id 
					WHERE soi1.SALE_OFFER_ID = ' . $sale_id . ' order by u.username';

        $dbResult = $this->db->sql_query($sql);
        $result = array();

        while ($row = $this->db->sql_fetchrow($dbResult)) {
            $result[] = ['username' => $row['username'], 'name' => $row['name'], 'count' => $row['count']];
        }
        $this->db->sql_freeresult($dbResult);

        return $result;
    }

    private function _getCurrentOrderAllStatistic($sale_id)
    {
        $sql = 'SELECT soi1.sale_offer_item_id, sum(soi1.count) as count FROM ' . $this->offer_item_order . ' soi1 
            WHERE soi1.SALE_OFFER_ID = ' . $sale_id . ' GROUP by soi1.sale_offer_item_id';

        $dbResult = $this->db->sql_query($sql);
        $id = -1;
        $result = array();

        while ($row = $this->db->sql_fetchrow($dbResult)) {
            $result[] = ['id' => $row['sale_offer_item_id'], 'count' => $row['count']];
        }
        $this->db->sql_freeresult($dbResult);

        return $result;
    }

    private function _getCurrentOrderUserStatistic($sale_id, $user_id)
    {
        $sql = 'SELECT soi1.sale_offer_item_id, sum(soi1.count) as count FROM ' . $this->offer_item_order . ' soi1 
            WHERE soi1.SALE_OFFER_ID = ' . $sale_id . ' and soi1.user_id = ' . $user_id . ' GROUP by soi1.sale_offer_item_id';

        $dbResult = $this->db->sql_query($sql);
        $id = -1;
        $result = array();

        while ($row = $this->db->sql_fetchrow($dbResult)) {
            $result[] = ['id' => $row['sale_offer_item_id'], 'count' => $row['count']];
        }
        $this->db->sql_freeresult($dbResult);

        return $result;
    }

    private function _calculateStatistic($all, $user)
    {
        foreach ($all as &$itemAll) {
            $itemAll['user_count'] = 0;
            foreach ($user as $itemUser) {
                if ($itemAll['id'] == $itemUser['id']) {
                    $itemAll['user_count'] = $itemUser['count'];
                    break;
                }
            }
        }

        return $all;
    }
}

<?php

namespace kaerol\simpleshop\controller;

use Symfony\Component\DependencyInjection\Container;

class order_an_item implements add_interface
{
    /* @var Container */
    protected $phpbb_container;

    /** @var \phpbb\auth\auth */
    protected $auth;

    /** @var \phpbb\config\config */
    protected $config;

    /** @var \phpbb\db\driver\driver */
    protected $db;

    /** @var \phpbb\controller\helper $controller_helper */
    protected $helper;

    /** @var \phpbb\notification\manager */
    protected $notification_manager;

    /** @var \phpbb\request\request */
    protected $request;

    /** @var \phpbb\user */
    protected $user;

    /** @var \phpbb\language\language */
    protected $language;

    /**
     * Constructor
     */

    public function __construct(
        Container $phpbb_container,
        \phpbb\auth\auth $auth,
        \phpbb\config\config $config,
        \phpbb\db\driver\driver_interface $db,
        \phpbb\controller\helper $helper,
        \phpbb\notification\manager $notification_manager,
        \phpbb\request\request $request,
        \phpbb\user $user,
        \phpbb\language\language $language
    ) {
        $this->auth = $auth;
        $this->config = $config;
        $this->helper = $helper;
        $this->db = $db;
        $this->notification_manager = $notification_manager;
        $this->request = $request;
        $this->user = $user;
        $this->language = $language;

        $this->offer_item_order = $phpbb_container->getParameter('tables.simpleshop_sale_offer_item_order');
    }

    public function add($topic_id, $sale_id)
    {
        //if ( $this->request->is_ajax() && $order_an_item )
        if ($this->request->is_ajax()) {
            $user_id = $this->request->variable('user_id', '');

            $items = $this->request->variable('items', [0 => ['id' => '', 'value' => '']], true);
            $sql = '';

            for (
                $i = 0;
                $i < count($items);
                $i++
            ) {
                $item_id = $items[$i]['id'];
                $item_value = $items[$i]['value'];

                if ($item_value == 0 || $item_value == '0') {
                    $sql = 'DELETE FROM ' . $this->offer_item_order . ' WHERE SALE_OFFER_ID = ' . $sale_id . ' AND SALE_OFFER_ITEM_ID = ' . $item_id . ' AND USER_ID = ' . $user_id;
                    $this->db->sql_query($sql);
                } else {

                    $user_buy_id = $this->_checkOrderItemExist($sale_id, $user_id, $item_id);

                    if ($user_buy_id == -1) {
                        $offer_item_order = array(
                            'TOPIC_ID'                =>    $topic_id,
                            'USER_ID'                =>    $user_id,
                            'SALE_OFFER_ID'            =>    $sale_id,
                            'SALE_OFFER_ITEM_ID'    =>    $item_id,
                            'COUNT'                    =>    $item_value,
                        );

                        $sql = 'INSERT INTO ' . $this->offer_item_order . ' ' . $this->db->sql_build_array('INSERT', $offer_item_order);
                        $this->db->sql_query($sql);
                    } else {
                        $sql = 'UPDATE ' . $this->offer_item_order . ' SET COUNT = ' . $item_value . ' WHERE ID = ' . $user_buy_id;
                        $this->db->sql_query($sql);
                    }
                }
            }

            $allStatistic = $this->_getCurrentOrderAllStatistic($sale_id);

            $userStatistic = $this->_getCurrentOrderUserStatistic($sale_id, $user_id);

            $statistic = $this->_calculateStatistic($allStatistic, $userStatistic);
            $statisticLabels = $this->_getStatisticWithLabels($statistic);

            $json_response = new \phpbb\json_response;
            $data_send = array(
                'success'             => true,
                'topic_id'             => $topic_id,
                'sale_id'            => $sale_id,
                'statistic'            => $statisticLabels,
            );

            return $json_response->send($data_send);
        }

        throw new \phpbb\exception\http_exception(500, 'GENERAL_ERROR');
    }

    private function _checkOrderItemExist($sale_id, $user_id, $item_id)
    {
        $sql = 'SELECT ID FROM ' . $this->offer_item_order . ' WHERE SALE_OFFER_ID = ' . $sale_id . ' AND SALE_OFFER_ITEM_ID = ' . $item_id . ' AND USER_ID = ' . $user_id;

        $result = $this->db->sql_query($sql);
        $id = -1;

        if ($result->num_rows != 0) {
            $row = $this->db->sql_fetchrow($result);
            $id = $row['ID'];
        }
        $this->db->sql_freeresult($result);

        return $id;
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

    private function _getStatisticWithLabels($statistic)
    {
        foreach ($statistic as &$item) {
            $count = $item['count'];
            $user_count = $item['user_count'];

            $item['count'] = $this->language->lang('KAEROL_SIMPLESHOP_ORDER_ALL_LABEL', $count);
            $item['user_count'] = $this->language->lang('KAEROL_SIMPLESHOP_ORDER_USER_LABEL', $user_count);
        }

        return $statistic;
    }
}

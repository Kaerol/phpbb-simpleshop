<?php

namespace kaerol\simpleshop\event;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use kaerol\simpleshop\includes\order_statistic;

class posting_listener implements EventSubscriberInterface
{

    /* @var Container */
    protected $phpbb_container;

    /** @var \phpbb\auth\auth */
    protected $auth;

    /** @var \phpbb\config\config */
    protected $config;

    /** @var \phpbb\db\driver\driver */
    protected $db;

    /** @var \phpbb\controller\helper */
    protected $helper;

    /** @var \phpbb\event\dispatcher_interface */
    protected $dispatcher;

    /** @var \phpbb\notification\manager */
    protected $notification_manager;

    /** @var \phpbb\request\request */
    protected $request;

    /** @var \phpbb\template\template */
    protected $template;

    /** @var \phpbb\user */
    protected $user;

    public function __construct(
        Container $phpbb_container,
        \phpbb\auth\auth $auth,
        \phpbb\config\config $config,
        \phpbb\db\driver\driver_interface $db,
        \phpbb\controller\helper $helper,
        \phpbb\event\dispatcher_interface $dispatcher,
        \phpbb\notification\manager $notification_manager,
        \phpbb\request\request $request,
        \phpbb\template\template $template,
        \phpbb\user $user,
        $order_statistic
    ) {
        $this->auth = $auth;
        $this->config = $config;
        $this->db = $db;
        $this->helper = $helper;
        $this->dispatcher = $dispatcher;
        $this->notification_manager = $notification_manager;
        $this->request = $request;
        $this->template = $template;
        $this->user = $user;
        $this->user->add_lang_ext('kaerol/simpleshop', 'simpleshop');
        $this->order_statistic = $order_statistic;

        $this->simpleshop_config = $phpbb_container->getParameter('tables.simpleshop_config');
        $this->simpleshop_sale_offer = $phpbb_container->getParameter('tables.simpleshop_sale_offer');
        $this->simpleshop_sale_offer_item = $phpbb_container->getParameter('tables.simpleshop_sale_offer_item');
        $this->simpleshop_sale_offer_item_order = $phpbb_container->getParameter('tables.simpleshop_sale_offer_item_order');
    }

    static public function getSubscribedEvents()
    {
        return array(
            'core.user_setup'                             => 'user_setup', // dane usera
            'core.submit_post_end'                        => 'submit_post_end', // zapisywanie posta
            'core.posting_modify_template_vars'           => 'posting_modify_template_vars', // modyfikowanie zmiennych przy edycji posta
            'core.viewtopic_assign_template_vars_before'  => 'viewtopic_assign_template_vars_before', // modyfikowanie zmiennych przy podgladzie topiku

            //'core.posting_modify_template_vars'			=> 'posting_modify_template',
            //'core.posting_topic_title_after'			=> 'posting_topic_title_after',
            //'core.user_setup' 							=> '_viewtopic_body_postrow_post_before',
            //'core.viewforum_modify_topics_data'			=> 'viewtopic_topic_title_after',
            //'core.viewtopic_modify_template_vars'		=> 'viewtopic_topic_title_after',
            //'core.viewonline_overwrite_location'			=> 'viewonline_page',
        );
    }

    public function user_setup($event)
    {

        $user_data = $event['user_data'];
        $this->user_id = $user_data['user_id'];

        $this->template->assign_var('USER_ID', $this->user_id);
    }

    public function posting_modify_template_vars($event)
    {
        $topic_id = $event['topic_id'];
        $template_data = $event['page_data'];
        $template_data['S_SHOW_SIMPLESHOP_PANEL_BOX'] = true;
        $event['page_data'] = $template_data;

        $sale_offer_id = $this->_getShopOfferId($topic_id);
        $offerExist = false;
        $offerOrdered = false;

        if ($sale_offer_id != -1) {
            //shop offer already exist
            $sale_offer_with_items = $this->_getShopOfferWithItems($topic_id);

            foreach ($sale_offer_with_items as $item) {
                $offerExist = true;

                $this->template->assign_vars(array(
                    'SALE_OFFER_TITLE'                => $item[1],
                    'SALE_OFFER_END_DATE'            => $item[2],
                ));

                $this->template->assign_block_vars('SALE_OFFER_ITEMS', array(
                    'item_id'         => $item[3],
                    'item_name'     => $item[4],
                ));
                $offerOrdered = $offerOrdered || ($item[5] > 0);
            }
        }
        $this->template->assign_var('S_SALE_OFFER_EXIST', $offerExist);
        $this->template->assign_var('S_SALE_OFFER_ORDERED', $offerOrdered);
    }

    public function viewtopic_assign_template_vars_before($event)
    {
        $topic_id = $event['topic_id'];
        $this->template->assign_var('S_SHOW_SIMPLESHOP_PANEL_BOX', true);

        $sale_offer_with_items = $this->_getShopOfferWithItems($topic_id);
        $this->template->assign_var('S_SALE_OFFER_EXIST', false);

        $event['S_SALE_OFFER_EXIST'] = false;
        $sale_id = $this->_getShopOfferId($topic_id);

        $statisticLabels = $this->order_statistic->getStatisticWithLabels($sale_id, $this->user_id);

        foreach ($sale_offer_with_items as $item) {
            $this->template->assign_var('S_SALE_OFFER_EXIST', true);

            $this->template->assign_vars(array(
                'SALE_OFFER_ID'                    => $item[0],
                'SALE_OFFER_TITLE'                => $item[1],
                'SALE_OFFER_END_DATE'            => $item[2],
            ));

            $all_count_label = '';
            $user_count_label = '';
            foreach ($statisticLabels as $statistic) {
                if ($item[3] == $statistic['id']) {
                    $all_count_label = $statistic['count'];
                    $user_count_label = $statistic['user_count'];
                    break;
                }
            }

            $this->template->assign_block_vars('SALE_OFFER_ITEMS', array(
                'item_id'            => $item[3],
                'item_name'          => $item[4],
                'all_count_label'   => $all_count_label,
                'user_count_label'   => $user_count_label,
            ));
        }

        $order_an_item_url = $this->helper->route('kaerol_simpleshop_order_an_item_controller', array('topic_id' => $topic_id, 'sale_id' => $sale_id, 'hash' => generate_link_hash('add_order')));

        $this->template->assign_var('AJAX_ORDER_AN_ITEM_URL', $order_an_item_url);

        $items_report_url = $this->helper->route('kaerol_simpleshop_sale_items_report_controller', array('topic_id' => $topic_id, 'sale_id' => $sale_id, 'hash' => generate_link_hash('items_report')));
        $this->template->assign_var('AJAX_ITEMS_REPORT_URL', $items_report_url);

        $person_report_url = $this->helper->route('kaerol_simpleshop_sale_person_report_controller', array('topic_id' => $topic_id, 'sale_id' => $sale_id, 'hash' => generate_link_hash('person_report')));
        $this->template->assign_var('AJAX_PERSON_REPORT_URL', $person_report_url);
    }

    public function submit_post_end($event)
    {
        $mode = $this->request->variable('mode', '');
        if ($mode != 'quote') {
            $topic_id = $event['data']['topic_id'];
            $sale_offer_id = $this->_getShopOfferId($topic_id);

            $sale_offer = $this->request->variable('sale_offer', '', true);
            $sale_offer_exist = $this->request->variable('sale_offer_exist', '');

            if ($sale_offer_exist || $sale_offer_exist === 'true') {
                //SKIPPED UPDATE OF EXISTING ORDERS
            } else {
                $sale_offer_collect_end_date     = $this->request->variable('sale_offer_collect_end_date', '0000-00-00');

                if ($sale_offer_id != -1) {
                    $sql_sale_offer = array(
                        'TITLE'                   =>    $sale_offer,
                        'END_DATE'                => ($sale_offer_collect_end_date) ? $sale_offer_collect_end_date : '0000-00-00',
                    );

                    $sql = 'UPDATE ' . $this->simpleshop_sale_offer . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_sale_offer) . ' WHERE id = ' . $sale_offer_id;
                    $this->db->sql_query($sql);

                    $sql = 'DELETE FROM ' . $this->simpleshop_sale_offer_item . ' WHERE sale_offer_id = ' . $sale_offer_id;
                    $this->db->sql_query($sql);
                } else {
                    $sql_sale_offer = array(
                        'TOPIC_ID'                =>    $topic_id,
                        'TITLE'                   =>    $sale_offer,
                        'END_DATE'                => ($sale_offer_collect_end_date) ? $sale_offer_collect_end_date : '0000-00-00',
                    );

                    $sql = 'INSERT INTO ' . $this->simpleshop_sale_offer . ' ' . $this->db->sql_build_array('INSERT', $sql_sale_offer);
                    $this->db->sql_query($sql);
                    $sale_offer_id = (int) $this->db->sql_nextid();
                }

                $sale_offer_item_text = $this->request->variable('sale_offer_item_text', '', true);
                $sale_offer_items = explode("\n", $sale_offer_item_text); // explode('\n', str_replace('\r', '', $sale_offer_item_text));

                for (
                    $i = 0;
                    $i < count($sale_offer_items);
                    ++$i
                ) {
                    $sql_sale_offer_items = array(
                        'SALE_OFFER_ID'                =>    $sale_offer_id,
                        'ITEM_NAME'                    =>    $sale_offer_items[$i],
                    );

                    $sql = 'INSERT INTO ' . $this->simpleshop_sale_offer_item . ' ' . $this->db->sql_build_array('INSERT', $sql_sale_offer_items);
                    $result = $this->db->sql_query($sql);
                }
            }
        }
    }

    private function _getShopOfferId($topic_id)
    {

        $sql = 'SELECT ID FROM ' . $this->simpleshop_sale_offer . ' WHERE TOPIC_ID = ' . $topic_id;
        $result = $this->db->sql_query($sql);
        $id = -1;

        if ($result->num_rows != 0) {
            $row = $this->db->sql_fetchrow($result);
            $id = $row['ID'];
        }
        $this->db->sql_freeresult($result);

        return $id;
    }

    private function _getShopOfferWithItems($topic_id)
    {
        $sql = 'SELECT so.id as id, so.title as title, so.end_date as end_date, soi.id as item_id, soi.item_name as item_name, soio.count as count
			FROM ' . $this->simpleshop_sale_offer . ' so 
			inner join ' . $this->simpleshop_sale_offer_item . ' soi on so.id = soi.sale_offer_id
			left outer join ' . $this->simpleshop_sale_offer_item_order . ' soio on soi.id = soio.sale_offer_item_id
			WHERE so.topic_id = ' . $topic_id . ' order by soi.id';

        $result = $this->db->sql_query($sql);

        $response = [];

        while ($row = $this->db->sql_fetchrow($result)) {
            $item = [];
            $item[] = $row['id'];
            $item[] = $row['title'];
            $item[] = $row['end_date'];
            $item[] = $row['item_id'];
            $item[] = $row['item_name'];
            $item[] = $row['count'] ?? 0;

            $response[] = $item;
        }
        $this->db->sql_freeresult($result);

        return $response;
    }
}

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

        $this->simpleshop_core_topic = $phpbb_container->getParameter('tables.simpleshop_core_topic');
        $this->simpleshop_core_posts = $phpbb_container->getParameter('tables.simpleshop_core_posts');
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

        $is_my_offer_id = $this->_isMyShopOffer($this->user_id, $topic_id);

        $this->template->assign_var('S_IS_LOCKED_ADDED_OR_EDIT', false);
        if (!$is_my_offer_id) {
            $this->template->assign_var('S_IS_LOCKED_ADDED_OR_EDIT', true);
        } else {
            $postData = $this->_getTopicPost($topic_id);

            if ($postData['count'] > 1) {
                $this->template->assign_var('S_IS_LOCKED_ADDED_OR_EDIT', true);
            }
            if ($topic_id == 0) {
                $this->template->assign_var('S_IS_LOCKED_ADDED_OR_EDIT', false);
            }
        }

        $sale_offer_id = $this->_getShopOfferId($topic_id);
        $offer_exist = false;
        $offer_ordered = false;

        if ($sale_offer_id != -1) {
            //shop offer already exist
            $sale_offer_with_items = $this->_getShopOfferWithItems($topic_id);

            foreach ($sale_offer_with_items as $item) {
                $offer_exist = true;

                $this->template->assign_vars(array(
                    'SALE_OFFER_TITLE'                => $item[1],
                    'SALE_OFFER_END_DATE'            => $item[2],
                ));

                $this->template->assign_block_vars('SALE_OFFER_ITEMS', array(
                    'item_id'         => $item[3],
                    'item_name'     => $item[4],
                ));
                $offer_ordered = $offer_ordered || ($item[5] > 0);
            }
        }
        $this->template->assign_var('S_SALE_OFFER_EXIST', $offer_exist);
        $this->template->assign_var('S_SALE_OFFER_ORDERED', $offer_ordered);
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
        $sale_offer_end_date = null;

        foreach ($sale_offer_with_items as $item) {
            $this->template->assign_var('S_SALE_OFFER_EXIST', true);

            $this->template->assign_vars(array(
                'SALE_OFFER_ID'                    => $item[0],
                'SALE_OFFER_TITLE'                => $item[1],
                'SALE_OFFER_END_DATE'            => $item[2],
            ));
            $sale_offer_end_date = $item[2];

            $all_count = '';
            $user_count = '';
            $all_count_label = '';
            $user_count_label = '';
            foreach ($statisticLabels as $statistic) {
                if ($item[3] == $statistic['id']) {
                    $all_count = $statistic['count'];
                    $user_count = $statistic['user_count'];
                    $all_count_label = $statistic['all_count_label'];
                    $user_count_label = $statistic['user_count_label'];
                    break;
                }
            }

            $this->template->assign_block_vars('SALE_OFFER_ITEMS', array(
                'item_id'           => $item[3],
                'item_name'         => $item[4],
                'all_count'         => $all_count,
                'user_count'        => $user_count,
                'all_count_label'   => $all_count_label,
                'user_count_label'  => $user_count_label,
            ));
        }

        $this->template->assign_var('S_SALE_OFFER_OPENED', $this->_isFuture($sale_offer_end_date));

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
        $topic_id = $event['data']['topic_id'];
        $post_id = $event['data']['post_id'];

        $postData = $this->_getTopicPost($topic_id);

        if ($mode != 'quote') {
            if ($postData['count'] <= 1) {

                $sale_offer_id = $this->_getShopOfferId($topic_id);

                $sale_offer = $this->request->variable('sale_offer', '', true);
                $sale_offer_exist = $this->request->variable('sale_offer_exist', '');

                if ($sale_offer_exist || $sale_offer_exist === 'true') {
                    //SKIPPED UPDATE OF EXISTING ORDERS
                } else {
                    if ($postData['postId'] <= $post_id) {
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
        $sql = 'SELECT so.id as id, so.title as title, so.end_date as end_date, soi.id as item_id, soi.item_name as item_name, count(soio.count) as count
			FROM ' . $this->simpleshop_sale_offer . ' so 
			inner join ' . $this->simpleshop_sale_offer_item . ' soi on so.id = soi.sale_offer_id
			left outer join ' . $this->simpleshop_sale_offer_item_order . ' soio on soi.id = soio.sale_offer_item_id
			WHERE so.topic_id = ' . $topic_id . '
            GROUP by so.id, so.title, so.end_date, soi.id, soi.item_name
            order by so.id';

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

    private function _isMyShopOffer($user_id, $topic_id)
    {
        $sql = 'SELECT * FROM ' . $this->simpleshop_sale_offer . ' so inner JOIN ' . $this->simpleshop_core_topic . ' ft ON ft.topic_id = so.topic_id where ft.topic_poster = ' . $user_id . ' and ' . $topic_id;

        $result = $this->db->sql_query($sql);

        if ($result->num_rows != 0) {
            $row = $this->db->sql_fetchrow($result);
            return true;
        }
        $this->db->sql_freeresult($result);

        return false;
    }


    private function _getTopicPost($topic_id)
    {
        $sql = 'SELECT fp.post_id, count(fp.post_id) as count FROM ' . $this->simpleshop_sale_offer . ' so inner JOIN ' . $this->simpleshop_core_topic . ' ft ON ft.topic_id = so.topic_id 
        inner JOIN ' . $this->simpleshop_core_posts . ' fp ON fp.topic_id = ft.topic_id where ft.topic_id = ' . $topic_id;

        $result = $this->db->sql_query($sql);
        $postId = -1;
        $count = -1;

        if ($result->num_rows != 0) {
            $row = $this->db->sql_fetchrow($result);
            $postId = $row['post_id'];
            $count = $row['count'];
        }
        $this->db->sql_freeresult($result);

        return [
            'postId' => $postId,
            'count' => $count
        ];
    }

    private function _isFuture($future_date)
    {
        $today = date("Y-m-d H:i:s");

        return ($future_date > $today);
    }
}

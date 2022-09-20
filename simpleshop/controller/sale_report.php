<?php

namespace kaerol\simpleshop\controller;

use Symfony\Component\DependencyInjection\Container;

class sale_report implements report_interface
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
        \phpbb\language\language $language,
        $order_statistic
    ) {
        $this->auth = $auth;
        $this->config = $config;
        $this->helper = $helper;
        $this->db = $db;
        $this->notification_manager = $notification_manager;
        $this->request = $request;
        $this->user = $user;
        $this->user->add_lang_ext('kaerol/simpleshop', 'simpleshop');
        $this->language = $language;
        $this->order_statistic = $order_statistic;

        $this->offer_item_order = $phpbb_container->getParameter('tables.simpleshop_sale_offer_item_order');
    }

    public function items_report($topic_id, $sale_id)
    {
        $report = $this->order_statistic->getCurrentOrderAllReport($sale_id);

        $out_title_html = $this->language->lang('KAEROL_SIMPLESHOP_ITEMS_REPORT_TITLE');
        $out_content_html = '<table border="1" width="100%" class="items_report">';
        $out_content_html .= '<tr><th>';
        $out_content_html .= $this->language->lang('KAEROL_SIMPLESHOP_REPORT_NAME_HEADER');
        $out_content_html .= '</th>';
        $out_content_html .= '<th>';
        $out_content_html .= $this->language->lang('KAEROL_SIMPLESHOP_REPORT_COUNT_HEADER');
        $out_content_html .= '</th>';
        $out_content_html .= '</th></tr>';

        foreach ($report as $row) {

            $out_content_html .= '<tr><td>';
            $out_content_html .= $row['name'];
            $out_content_html .= '</td>';
            $out_content_html .= '<td>';
            $out_content_html .= $row['count'];
            $out_content_html .= '</td>';
            $out_content_html .= '</td></tr>';
        }
        $out_content_html .= '</table>';

        $json_response = new \phpbb\json_response;
        $data_send = array(
            'success'           => true,
            'title'             => $out_title_html,
            'content'           => $out_content_html,
        );

        return $json_response->send($data_send);
    }

    public function person_report($topic_id, $sale_id)
    {

        $report = $this->order_statistic->getCurrentOrderPersonReport($sale_id);

        $out_title_html = $this->language->lang('KAEROL_SIMPLESHOP_PERSON_REPORT_TITLE');
        $out_content_html = '<table border="1" width="100%" class="person_report">';
        $out_content_html .= '<tr><th>';
        $out_content_html .= $this->language->lang('KAEROL_SIMPLESHOP_REPORT_USERNAME_HEADER');
        $out_content_html .= '</th>';
        $out_content_html .= '<th>';
        $out_content_html .= $this->language->lang('KAEROL_SIMPLESHOP_REPORT_NAME_HEADER');
        $out_content_html .= '</th>';
        $out_content_html .= '<th>';
        $out_content_html .= $this->language->lang('KAEROL_SIMPLESHOP_REPORT_COUNT_HEADER');
        $out_content_html .= '</th>';
        $out_content_html .= '</th></tr>';

        foreach ($report as $row) {

            $out_content_html .= '<tr><td>';
            $out_content_html .= $row['username'];
            $out_content_html .= '</td>';
            $out_content_html .= '<td>';
            $out_content_html .= $row['name'];
            $out_content_html .= '</td>';
            $out_content_html .= '<td>';
            $out_content_html .= $row['count'];
            $out_content_html .= '</td>';
            $out_content_html .= '</td></tr>';
        }
        $out_content_html .= '</table>';

        $json_response = new \phpbb\json_response;
        $data_send = array(
            'success'             => true,
            'title'             => $out_title_html,
            'content'            => $out_content_html,
        );

        return $json_response->send($data_send);
    }
}

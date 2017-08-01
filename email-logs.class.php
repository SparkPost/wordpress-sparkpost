<?php

namespace WPSparkPost;
// If ABSPATH is defined, we assume WP is calling us.
// Otherwise, this could be an illicit direct request.
if (!defined('ABSPATH')) exit();

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class SparkPostEmailLogs extends \WP_List_Table
{

    function __construct()
    {
        global $status, $page;
        //Set parent defaults
        parent::__construct(array(
            'singular' => 'log',
            'plural' => 'logs',
            'ajax' => false
        ));
    }

    public static function record_count()
    {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}sp_email_logs";

        return $wpdb->get_var($sql);
    }

    function get_logs($per_page = 10, $page = 1)
    {
        global $wpdb;

        $wpdb->show_errors();

        $sql = "SELECT * FROM {$wpdb->prefix}sp_email_logs ORDER BY `created_at` DESC LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page - 1) * $per_page;

        return $wpdb->get_results($sql, 'ARRAY_A');
    }

    function get_columns()
    {
        $columns = [
            'subject' => 'Subject',
            'wp_mail_args' => 'wp_mail Arguments',
            'sent_at' => 'Generated At',
            'content' => 'Request Data',
            'response' => 'Response Data'
        ];

        return $columns;
    }

    function column_subject($item)
    {
        return $item['subject'];
    }

    function column_sent_at($item)
    {
        return $item['created_at'];
    }

    function column_content($item)
    {
        return '<textarea style="width: 100%" rows="5">' . $item['content'] . '</textarea>';
    }

    function column_wp_mail_args($item)
    {
        return '<textarea style="width: 100%" rows="5">' . $item['wp_mail_args'] . '</textarea>';
    }

    function column_response($item)
    {
        return '<textarea style="width: 100%" rows="5">' . $item['response'] . '</textarea>';
    }

    function column_name($item)
    {

        return $item['name'];
    }

    function prepare_items()
    {
        $per_page = 10;
        $page = $this->get_pagenum();
        $columns = $this->get_columns();
        $this->_column_headers = array($columns);

        $this->items = $this->get_logs($per_page, $page);
        $total_items = $this->record_count();

        $this->set_pagination_args(array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page' => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
        ));

    }
}

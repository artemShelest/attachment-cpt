<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
	die;
}
require_once dirname( __FILE__ ) . '/includes/options.php';
ACPT_Options::delete_options();
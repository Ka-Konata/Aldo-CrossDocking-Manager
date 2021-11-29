<?php
/**
 * Plugin Name: Aldo's CrossDocking Manager
 * Plugin URI: https://github.com/Ka-Konata/Aldo-CrossDocking-Manager
 * Description: Get product information from Aldo's API and manage them for resale.
 * Version: 0.7.0
 * Author: Victor G. Ramos
 * Author URI: https://ka-konata.github.io/
**/

// Includes 
// acm-functions.php to access all the functions of the plugin or stop the script if the file is not found
require_once plugin_dir_path(__FILE__) . 'includes/acm-functions.php';

// acm-config-page.php insert a configuration page in the adminDashboard and a request handler
require_once plugin_dir_path(__FILE__) . 'includes/acm-config-page.php';
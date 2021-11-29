<?php
/*
 * All essencials functions needed to make the plugin works will be found here 
 * By Victor G. Ramos
 * Plugin: Aldo's CrossDocking Manager
 * Useful and indispensable functions:
 */


 
// Test Action
function acm_test_action() {
    //wp_schedule_single_event(time() + 120, 'acm_actualize_products_database_hook');
    //wp_clear_scheduled_hook('acm_actualize_products_database_hook');
    //acm_actualize_products_database();
    
    //acm_delete_all_products();
    //acm_move_all_products_to_trash();
    
    //$test_schedule = wp_get_schedule( 'acm_actualize_products_database_hook');
    //$test_url     = "https://abtus.kakiflly.repl.co/aldo-xml"; // (test server created by me)
    //$xml_products = simplexml_load_file($test_url);
    //echo '<pre style="z-index: 100000; background-color: blue; margin-top: 25px;" data-teste="aa">';
    //print_r($test_schedule);
    //echo '</pre>';
}
add_action('admin_notices', 'acm_test_action');


 
// Check if the plugin is already configured and add a notice error if it's not
function acm_check_config() {
	$acm_options = get_option( 'aldo_s_crossdocking_manager_option_name' );
	
    if($acm_options['cdigo_de_autenticao_0'] == false || $acm_options['chave_1'] == false || $acm_options['horrio_para_atualizao_2'] == false 
    || $acm_options['e_mail_4'] == false) {
        return false;
    }
    
    else {
        return true;
    }
}


function acm_not_configured() {
    //delete_option('aldo_s_crossdocking_manager_option_name');
    $acm_options = get_option( 'aldo_s_crossdocking_manager_option_name' );
    $href        = '/wp-admin/admin.php?page=aldo-s-crossdocking-manager';
 
    if (acm_check_config() == false) {
        echo  '<div class="notice notice-error">
                  <p>Aldo CD Manager | É necessário configurar o plugin <a href="/wp-admin/admin.php?page=aldo-s-crossdocking-manager">Aqui.</a></p>
               </div>';
    }
}
add_action('admin_notices', 'acm_not_configured');
 
 
// Add a scheduled event to actualize the database every day
// It only can add this event when someone access the site
// If it have past the planned time, this function will actualize the database by itself
add_action('init', 'acm_add_schedule');
function acm_add_schedule() 
{
    // Getting plugin's settings info
    $acm_options = get_option( 'aldo_s_crossdocking_manager_option_name' );
    $update_time = $acm_options['horrio_para_atualizao_2'];
    
    if (acm_check_config() == true) {
        $json_file   = file_get_contents(plugin_dir_path(__FILE__) . "/data.json");
        $json_data   = current(json_decode($json_file, true));
        
        $is_scheduled    = wp_next_scheduled('acm_actualize_products_database_hook');
        $sch_timestamp   = intval($update_time); // timestamp version for 5am
        $limit_time      = 19.30;
        $scheduled_time  = 5;
        
        list($today_year, $today_month, $today_day, $hour, $minute, $second) = preg_split( "([^0-9])", current_time( 'mysql' ) );
        $rigth_now       = $hour + ($minute / 100);
        $today           = intval("" . $today_day . $today_month . $today_year);
        $last_update     = intval("" . $json_data["day"] . $json_data["month"] . $json_data["year"]);
        
        if(!$is_scheduled && $last_update < $today && $rigth_now >= $scheduled_time && $rigth_now <= $limit_time) {
            // Do an update right now and plan another for the next day
            acm_actualize_products_database();
            wp_schedule_event($sch_timestamp, 'daily', 'acm_actualize_products_database_hook');
        }
        
        elseif (!$is_scheduled) {
            // Plan an update
            wp_schedule_event($sch_timestamp, 'daily', 'acm_actualize_products_database_hook');
        }
        
        $new_json_data = array(
            "last_update"  => array(
                "day"   => $today_day,
                "month" => $today_month,
                "year"  => $today_year,
            ),
            "last_product" => $json_data["last_product"]
        );
        
        $new_json = json_encode($new_json_data);
        file_put_contents(plugin_dir_path(__FILE__) . "/data.json", $new_json);
        
        // echo '<pre style="position: fixed; z-index: 100000; background-color: blue; margin-top: 25px;">';
        // print_r();
        // echo '</pre>';
    }
}



// Actualize this site's product database
add_action('acm_actualize_products_database_hook', 'acm_actualize_products_database');

function acm_actualize_products_database() 
{
    if(acm_check_config() == true) {
        $acm_options = get_option( 'aldo_s_crossdocking_manager_option_name' );
        $inicio      = $acm_options['enviar_inicio_5'];
        $email       = $acm_options['e_mail_4'];
        
        // step 1: Configure the site's time limit
        set_time_limit(50000);
    
        // step 2: Send notification
        if ($inicio) {
            acm_notification_init($email);
        }
        
        // step 3: Check old products
        $xml_products = simplexml_load_file('https://abtus.kakiflly.repl.co/aldo-xml');
        $xml_ids      = acm_get_xml_products_ids_list($xml_products);
        
        $args_data = array( 'post_type' => 'product', 'posts_per_page' => -1 );
        query_posts( $args_data );
        while ( have_posts() ) : the_post();
            global $product;
            if (!in_array(intval($product -> get_id()), $xml_ids)) {
                $product -> delete(true); 
                
            }
        endwhile;
        
        // step 4: Add new product or actualize the olds ones
        $json_file   = file_get_contents(plugin_dir_path(__FILE__) . "/data.json");
        $json_data   = current(json_decode($json_file, true));
        if ($json_data["last_product"] == 0) {
            acm_get_all_products();
        }
        
        // step 5: Change the site's time limit to default
        set_time_limit(60);
    }
}



// Make a request to Aldo's API and get ALL THE PRODUCTS
// Sections which add the products. Each one can run for 1 hour
add_action('acm_section_add_hook', 'acm_get_all_products');

function acm_get_all_products()
{
    $init  = time();
    $json_file   = file_get_contents(plugin_dir_path(__FILE__) . "/data.json");
    $json_data   = current(json_decode($json_file, true));
    
    // Getting plugin's settings info
    $acm_options          = get_option( 'aldo_s_crossdocking_manager_option_name' );
    $codigo_de_autenticao = $acm_options['cdigo_de_autenticao_0'];
    $chave                = $acm_options['chave_1'];
    $notification         = $acm_options['enviar_notificacao_3'];
    $email                = $acm_options['e_mail_4'];
    
    $url          = "https://webservice.aldo.com.br/asp.net/ferramentas/integracao.ashx?u=" . $codigo_de_autenticao . "&p=" . $chave;
    $test_url     = "https://abtus.kakiflly.repl.co/aldo-xml"; // (test server created by me)
    $exemple_url  = "https://www.aldo.com.br/portais/aldo-crazy-manager/arquivo_integracao_exemplo.xml";
    $xml_products = current(simplexml_load_file($test_url));
    
    $products_updated = array();
    $post_ids         = acm_get_all_products_ids();
    $count            = intval($json_data["last_product"]);
    
    while ($count < count($xml_products)) {
        
        $actual_product = $xml_products[$count];
        $count++;
        
        // Checking all registered products to find out if actual_Product already exist
        
        $new_product_id = acm_get_aldo_product_id($actual_product);
        if (in_array($new_product_id, $post_ids)) {
            // Edit some informations if they have changed
                  
            $post_id = acm_get_aldo_product_id($actual_product);
            acm_set_product_info($post_id, $actual_product, true);
            $products_updated[] = acm_get_aldo_product_id($actual_product);
        }
        
        // If the product dosen't exist yet, add it
        else {
            $user_id   = get_current_user_id();
            $post_info = array(
                'post_title'   => ucwords(strtolower($actual_product -> descricao)),
                'import_id'    => acm_get_aldo_product_id($actual_product),
                'post_content' => acm_get_product_aldo_description($actual_product),
                'post_name'    => $actual_product -> categoria,
                'post_type'    => "product",
                'post_status'  => "publish",
                'post_author'  => 1,
            );
            
            $post_id = wp_insert_post($post_info, $wp_error = false);
            set_post_thumbnail($post_id, acm_get_aldo_product_image($actual_product));
            acm_set_product_info($post_id, $actual_product);
            
            $products_updated[] = $post_id;
            $post_ids[]         = $post_id;
        }
        
        //Check if it has passed 1 hour
        $now = time();
        if ($now - $init >= 3300) {
            if ($count < count($xml_products)) {
                $json_data["last_product"] = $count;
                wp_schedule_single_event(time() + 60, 'acm_section_add_hook');
            }
            
            else {
                $json_data["last_product"] = 0;
                acm_notification_email($email, $products_updated, $init);
            }
            
            $new_json = json_encode($json_data);
            file_put_contents(plugin_dir_path(__FILE__) . "/data.json", $new_json);
            break;
        }
        
        else if ($count == count($xml_products)) {
            $json_data["last_product"] = 0;
            $new_json = json_encode($json_data);
            file_put_contents(plugin_dir_path(__FILE__) . "/data.json", $new_json);
            
            acm_notification_email($email, $products_updated, $init);
            set_time_limit(60);
        }
    }
}



// Will be used to check if one registered product still existing in Aldo's database
function acm_get_xml_products_ids_list($xml_products)
{
    $count = 0;
    $list  = array();
    foreach (current($xml_products) as $prod) {
        $list[] = acm_get_aldo_product_id($prod);
    }
    
    return $list;
}



// Get the product (coming from Aldo's API) id
function acm_get_aldo_product_id($actual_product) 
{
    $left_side      = substr($actual_product -> codigo, 0, -2);
    $right_side     = substr($actual_product -> codigo, -1);
    $codigo         = $left_side . $right_side;
    $new_product_id = intval($codigo);
    
    return $new_product_id;
}



// Split the "dimentions" string to get an usable valor and return it
function acm_get_aldo_product_dimentions($actual_product)
{
    $ALC = $actual_product -> dimensoes;
    $ALC = str_replace(' ', '', $ALC);
    $ALC = str_replace('-', '', $ALC);
    
    $pos_A = strpos($ALC, '(A)');
    $pos_L = strpos($ALC, '(L)');
    $pos_C = strpos($ALC, '(C)');
    
    $dimentions = array(
        'A' => floatval(substr($ALC, $pos_A + 3, $pos_L - 3)),
        'L' => floatval(substr($ALC, $pos_L + 3, $pos_C - $pos_L - 3)),
        'C' => floatval(substr($ALC, $pos_C + 3)),
    );
    
    return $dimentions;
}



// Insert the product's image and return its ID
function acm_get_aldo_product_image($actual_product)
{
    $image_url = strval($actual_product -> foto);
    $image_id = acm_upload_media($image_url);
    return $image_id;
}



// Make a complete product description
function acm_get_product_aldo_description($actual_product)
{
    $descricao_tectina = "<strong>Descrição Tecnica: </strong>"     . $actual_product -> descricao_tecnica;
    $unidade           = "<strong>Quantidade por Compra: </strong>" . $actual_product -> unidade;
    $tempo_garantia    = "<strong>Tempo de Garantia: </strong>"     .  $actual_product -> tempo_garantia;
    $procedimentos_rma = "<strong>Procedimentos: </strong>"         . $actual_product -> procedimentos_rma;
    
    $desc = "<p>" . $unidade ."</p>" . $descricao_tectina ."</p>" . "<p>" . $tempo_garantia ."</p>" . "<p>" . $procedimentos_rma ."</p>" . "<p>";
    return str_replace('\r\n', '', $desc);
}



// Verify if the product is in stock
function acm_get_product_aldo_stock($actual_product)
{
    $disponivel = $actual_product -> disponivel;
    
    if ($disponivel == "SIM") {
        $stock = "instock";
    }
    
    elseif ($disponivel == "NÃO") {
        $stock = "outofstock";
    }
    
    return $stock;
}



// Upload a image to the database
// This function isn't made by me. souce: https://stackoverflow.com/questions/11503646/woocommerce-create-product-by-code
function acm_upload_media($image_url){
    require_once(acm_get_path('wp-admin/includes/image.php'));
    require_once(acm_get_path('wp-admin/includes/file.php'));
    require_once(acm_get_path('wp-admin/includes/media.php'));
    $media = media_sideload_image($image_url,0);
    $attachments = get_posts(array(
        'post_type' => 'attachment',
        'post_status' => null,
        'post_parent' => 0,
        'orderby' => 'post_date',
        'order' => 'DESC'
    ));
    return $attachments[0]->ID;
}



// Pick up a product and add its informations or update it if it's is already avaiable
function acm_set_product_info($post_id, $actual_product, $actualize = false)
{
    update_post_meta( $post_id, '_price', str_replace(",", ".", $actual_product -> preco) );
    update_post_meta( $post_id, '_stock_status', acm_get_product_aldo_stock($actual_product) );
    
    
    if (!$actualize) {
        wp_set_object_terms( $post_id, ucwords(strtolower(strval($actual_product -> grupo_site))), 'product_cat' );
        wp_set_object_terms( $post_id, 'simple', 'product_type');

        update_post_meta( $post_id, '_stock', '' );
        update_post_meta( $post_id, '_regular_price', '');
        update_post_meta( $post_id, '_sale_price', '');
        update_post_meta( $post_id, '_visibility', 'visible' );
        update_post_meta( $post_id, '_manage_stock', "no" );
        update_post_meta( $post_id, '_downloadable', 'no');
        update_post_meta( $post_id, '_backorders', "no" );
        update_post_meta( $post_id, '_featured', "no" );
        update_post_meta( $post_id, '_virtual', 'no');
        update_post_meta( $post_id, '_height', acm_get_aldo_product_dimentions($actual_product)["A"] );
        update_post_meta( $post_id, '_width', acm_get_aldo_product_dimentions($actual_product)["L"] );
        update_post_meta( $post_id, '_length', acm_get_aldo_product_dimentions($actual_product)["C"]);
        update_post_meta( $post_id, '_weight', str_replace(",", ".", $actual_product -> peso) );
        update_post_meta( $post_id, '_product_attributes', array());
      //update_post_meta( $post_id, 'total_sales', '0');
      //update_post_meta( $post_id, '_sale_price_dates_from', "" );
      //update_post_meta( $post_id, '_sale_price_dates_to', "" );
      //update_post_meta( $post_id, '_sold_individually', "" );
      //update_post_meta( $post_id, '_purchase_note', "" );
      //update_post_meta( $post_id, '_sku', "");
      }
}



// Calc the diference between two numbers and return it the result as seconds
function acm_calc_seconds() 
{
    list($today_year, $today_month, $today_day, $hour, $minute, $second) = preg_split( "([^0-9])", current_time( 'mysql' ) );
    
    $predefined = 18000;
    $current_time_in_seconds = ($hour * 3600) + ($minute * 60);
    if($current_time_in_seconds <= $predefined) {
        $result = $predefined - $current_time_in_seconds;
    }
    
    else {
        $result = $current_time_in_seconds - $predefined;
    }
    return $result;
}



// Get a path ´from wp-admim´
function acm_get_path($path)
{
    $local_path = plugin_dir_path(__FILE__);
    $pos        = strpos($local_path, "wp-content");
    $real_path  = substr(substr_replace($local_path, $path, $pos, -1), 0, -1);
    return $real_path;
}



// Return a list with all already registred products
function acm_get_all_products_ids() {
   $all_ids = get_posts( array(
        'post_type' => 'product',
        'numberposts' => -1,
        'post_status' => 'publish',
        'fields' => 'ids',
   ) );
   
   return $all_ids;
}



// Send an notification in the init
function acm_notification_init($to) {
    $subject = 'Processo Iniciado';
    $body    = '<p>Horário da inicalização: ' . date('m/d/Y H:i:s', time()) . '</p>';
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail( $to, $subject, $body, $headers );
}



// Send an email to the configured e-mail to inform that the database was updated and a list with all the products which has been changed/added
function acm_notification_email($to, $products, $init) 
{
    $subject = 'Produtos Finalizado';
    $body    = '<p>Horário da atualização: ' . date('m/d/Y H:i:s', time()) . 
    '<span style="margin-left:8px;color:gray;background: #8080803b;padding: 3px;border-radius: 3px;">timestamp: ' . strval(time()) . '</span></p>
    <p>Finalizado às: ' . $init . '</p>Produtos atualizados (id):';
    
    foreach ($products as $p) {
        $body = $body . '<p style="margin: 0">' . $p . '</p>';
    }
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail( $to, $subject, $body, $headers );
}



// Move all the products to trash
function acm_move_all_products_to_trash() {
    $args_data = array( 'post_type' => 'product', 'posts_per_page' => -1 );
    query_posts( $args_data );
    while ( have_posts() ) : the_post();
        global $product;
        $id = strval($product -> get_id());
        wp_trash_post($id);
    endwhile;
}



// Delete PERMANENTLY all the products / clear the trash
function acm_delete_all_products() {
    $args_data = array( 'post_type' => 'product', 'posts_per_page' => -1 );
    query_posts( $args_data );
    while ( have_posts() ) : the_post();
        global $product;
        $product -> delete(true); 
    endwhile;
}



/*
 * add_action('acm_send_email_hook', 'acm_send_email');
 * function acm_send_email() {
 *     $to = 'towho@example.com';
 *     $subject = 'The subject';
 *     $body = 'The email body content';
 *     $headers = array('Content-Type: text/html; charset=UTF-8','From: My Site Name <yoursiteemail@gmail.com>');
 *      
 *     wp_mail( $to, $subject, $body, $headers );
 * }
 * 
 * // Add a custom time to the cron schedules
 * add_filter( 'cron_schedules', 'acm_custom_cron_schedule' );
 * function acm_custom_cron_schedule( $schedules ) {
 *     $schedules['per_minute'] = array(
 *         'interval' => 10, //acm_calc_seconds()
 *         'display'  => __( 'Every 24 hours' ),
 *     );
 *     return $schedules;
 * }
 
 * wp_clear_scheduled_hook('acm_send_email_hook');
 * wp_schedule_event(1634286600, 'per_minute', 'acm_send_email_hook'); // 1634286600
 */
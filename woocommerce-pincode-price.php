<?php
session_start();

/**
 * Plugin Name: Woocommerce Pincode Price
 * Plugin URI:  http://tajinstruments.in/
 * Description: Woocommerce price based on pincode
 * Version:     1.0.0
 * Author:      tajinstruments
 * Author URI:  http://tajinstruments.in/
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woocommerce-pincode-price
 * Domain Path: /languages
 */

/**
 * Register a custom menu page.
 */
function pincode_price_menu_page(){
    add_menu_page( 
        __( 'Pincode Price', 'textdomain' ),
        'Pincode Price',
        'manage_options',
        'pincode_price',
        'pincode_price',
        '',
        6
    ); 
}
add_action( 'admin_menu', 'pincode_price_menu_page' );
 

/**
 * Never worry about cache again!
 */
function my_load_scripts($hook) {
 
    wp_enqueue_script( 'dataTables', plugins_url( 'js/jquery.dataTables.min.js', __FILE__ ), array(), '1.0.0',true);
    wp_enqueue_style( 'dataTables',    plugins_url( 'css/jquery.dataTables.min.css',    __FILE__ ), false,   '1.0.0' );
 
}
add_action('admin_enqueue_scripts', 'my_load_scripts');

/**
 * Display a custom menu page
 */
function pincode_price(){ 

    if(isset($_POST['submit_excel'])){
        global $wpdb;
        require('spreadsheet-reader-master/SpreadsheetReader.php');


        if (file_exists($_FILES["file"]["tmp_name"])) {

            $allowed_image_extension = array(
                "csv",
                "xlsx",
                "xls"
            );
            

            $file_extension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);

            if (in_array($file_extension, $allowed_image_extension)) {
            
                $upload_dir = wp_upload_dir();
                $folder = $upload_dir['basedir'].'/pincode/';
                if (!file_exists($folder)) {
                    mkdir($folder, 0777, true);
                }

                $uploadFilePath = $folder.basename($_FILES['file']['name']);
                move_uploaded_file($_FILES['file']['tmp_name'], $uploadFilePath);

                $reader = new SpreadsheetReader($uploadFilePath);

                $table_name = $wpdb->prefix . "pincode"; 

                $delete = $wpdb->query("TRUNCATE TABLE $table_name");

                $skip = false;
                foreach ($reader as $row){
                    if(!$skip){ $skip = true; continue; }
                        $wpdb->insert( $table_name, array( 
                                        'pincode' => $row[0], 
                                        'city' => $row[1], 
                                        'state' => $row[2], 
                                        'price' => $row[3], 
                                    )
                            );
                }

                echo "Pincode list update successfully.";

            }else{
                echo "Sorry this file type now allowed.";
            }
        }else{
            echo "Please select file.";
        }
          
    }

    ?>
    <?php if($_REQUEST['page'] == 'pincode_price' && isset($_REQUEST['action'])){ 

        global $wpdb;
       
            if(isset($_POST['submit_pincode'])){

                if(isset($_REQUEST['pincode_id'])){
                    $sql = 'AND id NOT IN("'.$_REQUEST['pincode_id'].'")';
                }else{
                    $sql = '';
                }
                
                $table_name = $wpdb->prefix . "pincode"; 
                $pincode_number = $wpdb->get_row( "SELECT pincode FROM $table_name WHERE pincode = '".$_REQUEST['pincode_number']."' ".$sql." ", ARRAY_A);

                if (!empty($pincode_number)) {
                   echo "<p style='color:red;'>This pincode is already exist";
                }else{
                    
                    if ($_REQUEST['action'] == 'add_new_pincode') {
                        $wpdb->insert( $table_name, array( 
                                                'pincode' => $_REQUEST['pincode_number'], 
                                                'city' => $_REQUEST['pincode_city'], 
                                                'state' => $_REQUEST['pincode_state'], 
                                                'price' => $_REQUEST['pincode_price'], 
                                            )
                                    );
                    }
                    if ($_REQUEST['action'] == 'edit_pincode' && isset($_REQUEST['pincode_id'])) {
                                        $update_pincode = $wpdb->update(
                                                $table_name, 
                                                array(
                                                    'pincode' => $_REQUEST['pincode_number'],
                                                    'city' => $_REQUEST['pincode_city'],
                                                    'state' => $_REQUEST['pincode_state'],
                                                    'price' => $_REQUEST['pincode_price'],
                                                ), 
                                                array('id' => $_REQUEST['pincode_id'] )
                                            );
                    }
                        
                    header("Location: admin.php?page=pincode_price");

                }
            }
            if($_REQUEST['action'] == 'delete_pincode' && isset($_REQUEST['pincode_id'])){

                $table_name = $wpdb->prefix . "pincode"; 
                $delete_pincode = $wpdb->delete($table_name, array('id' => $_REQUEST['pincode_id'] ));

                header("Location: admin.php?page=pincode_price");
            }
        

        $pincode_value  = array();
           
        $table_name = $wpdb->prefix . "pincode"; 
        $pincode_value = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = '".$_REQUEST['pincode_id']."' ", ARRAY_A);
           


    ?>
        
    <div class="container">
        <h1>Add Pincode</h1>
         <div class="wrap"><a href="admin.php?page=pincode_price" class="button button-primary">Back To List</a></div>
        <form method="POST" action="" enctype="multipart/form-data">
            <table class="form-table" id="fieldset-shipping">
                <tbody>
                    <tr>
                        <th>
                            <label for="shipping_first_name">Pincode</label>
                        </th>
                        <td>
                            <input type="number" name="pincode_number" id="pincode_number" value="<?php echo (!empty($pincode_value) && $pincode_value['pincode'] != '') ? $pincode_value['pincode'] : '' ; ?>" class="regular-text" required="Pleas Enter Pincode">
                                <p class="description"></p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="pincode">City</label>
                        </th>
                        <td>
                            <input type="text" name="pincode_city" id="pincode_city" value="<?php echo (!empty($pincode_value) && $pincode_value['city'] != '') ? $pincode_value['city'] : '' ; ?>" class="regular-text" required="Pleas Enter City">
                                <p class="description"></p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="pincode">State</label>
                        </th>
                        <td>
                            <input type="text" name="pincode_state" id="pincode_state" value="<?php echo (!empty($pincode_value) && $pincode_value['state'] != '') ? $pincode_value['state'] : '' ; ?>" class="regular-text" required="Pleas Enter State">
                                <p class="description"></p>
                        </td>
                    </tr>
                     <tr>
                        <th>
                            <label for="shipping_first_name">Price</label>
                        </th>
                        <td>
                            <input type="text" name="pincode_price" id="pincode_price" value="<?php echo (!empty($pincode_value) && $pincode_value['price'] != '') ? $pincode_value['price'] : '' ; ?>" class="regular-text" required="Pleas enter price">
                                <p class="description"></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <button type="submit" name="submit_pincode" class="button button-primary">Save</button>
            </p>
        </form>
    </div>


    <?php }else{ ?>

    <div class="container">
        <h1>Excel Upload</h1>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label>Upload Excel File</label>
                <input type="file" name="file" class="form-control">
            </div>
            <div class="form-group">
                <button type="submit" name="submit_excel" class="btn btn-success">Upload</button>
            </div>
        </form>
    </div><br>
    <div class="container"><br>
        <h1>Only Single Pincode Upload</h1>
        <div class="wrap"><a href="admin.php?page=pincode_price&action=add_new_pincode" class="button button-primary">Add Pincode</a></div>
    </div><br>

    <table id="example" class="display" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Pincode</th>
                <th>City</th>
                <th>State</th>
                <th>Price</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            global $wpdb;
            $table_name = $wpdb->prefix . "pincode";
            $pincode_data = $wpdb->get_results( "SELECT * FROM $table_name",ARRAY_A );

            foreach ($pincode_data as $key => $value) { ?>
                <tr>
                    <td><?php echo $value['id']; ?></td>
                    <td><?php echo $value['pincode']; ?></td>
                    <td><?php echo $value['city']; ?></td>
                    <td><?php echo $value['state']; ?></td>
                    <td><?php echo $value['price']; ?></td>
                    <td><a href="admin.php?page=pincode_price&action=edit_pincode&pincode_id=<?php echo $value['id']; ?>">Edit</a> &nbsp;&nbsp;
                    <a href="admin.php?page=pincode_price&action=delete_pincode&pincode_id=<?php echo $value['id']; ?>" onclick="return confirm('Are you sure Delete this record?')">delete</a></td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <th>ID</th>
                <th>Pincode</th>
                <th>City</th>
                <th>State</th>
                <th>Price</th>
                <th>Action</th>
            </tr>
        </tfoot>
    </table>

<?php }
}

function intercept_wc_template($template, $template_name, $template_path) {
    if ($template_name == 'cart/cart-totals.php') {
        $template = plugin_dir_path( __FILE__ ).'woocommerce/'.$template_name; // 'the/path/of/your/plugin/template.php';
    }
    if ($template_name == 'cart/proceed-to-checkout-button.php') {
        $template = plugin_dir_path( __FILE__ ).'woocommerce/'.$template_name; // 'the/path/of/your/plugin/template.php';
    }
    return $template;
}

add_filter('woocommerce_locate_template', 'intercept_wc_template', 20, 3);


add_action('wp_footer','check_pincode_script');

function check_pincode_script(){ ?>
<script type="text/javascript">
    (function($){
        $(document).ready(function(){
            jQuery('#company_name_field').hide();
            jQuery('#gstin_field').hide();

            $('.check_pincode').click(function(){

                $('.not_avil_msg').hide();

                $elm = $('.shop_table');
                $elm.addClass( 'processing' ).block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });

                $.ajax({
                    type: 'POST',
                    url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                    dataType: 'json',
                    data: {action   : 'check_pincode',pincode:$('.pincode').val()},
                    success: function( response ) {
                        $elm.removeClass( 'processing' ).unblock();
                        location.reload();
                    },
                });
            });
           // $('#example').DataTable();
           jQuery('input#company_and_gst').change(function(){
             if (this.checked) {
                jQuery('#company_name_field').fadeIn();
                jQuery('#gstin_field').fadeIn();
             } else {
                jQuery('#company_name_field').fadeOut();
                jQuery('#gstin_field').fadeOut();
             }
              
          });
        });
    })(jQuery);
</script>
<?php }


add_action('admin_footer','admin_footer_script');

function admin_footer_script(){ ?>
<script type="text/javascript">
    (function($){
        $(document).ready(function(){
            $('#example').DataTable();
        });
    })(jQuery);
</script>
<?php }


add_action( 'wp_ajax_check_pincode', 'check_pincode' );
add_action( 'wp_ajax_nopriv_check_pincode', 'check_pincode' );

function check_pincode(){
    global $wpdb;
    $_SESSION['delivery'] = '';
    $_SESSION['pincode_avil_msg'] = '';
    $pincode = $_POST['pincode'];
    $table_name = $wpdb->prefix . "pincode"; 
    $pincode_data = $wpdb->get_row( "SELECT * FROM $table_name WHERE pincode = $pincode",ARRAY_A );

    if(!empty($pincode_data)){
        $_SESSION['shipping_pincode'] = $pincode_data['pincode'];

        if(WC()->cart->cart_contents_total <= 500){
            $_SESSION['shipping_charge'] = $pincode_data['price'];
        }else{
            if(isset($_SESSION['shipping_charge'])){
                unset($_SESSION['shipping_charge']);
            }    
        }

        $_SESSION['shipping_city'] = $pincode_data['city'];
        $_SESSION['shipping_state'] = $pincode_data['state'];
        $_SESSION['delivery'] = 'available';
        $_SESSION['pincode_avil_msg'] = 'success_msg';
    }else{
        $_SESSION['shipping_pincode'] = $pincode;
        if(isset($_SESSION['shipping_charge'])){
            unset($_SESSION['shipping_charge']);
        }
        if(isset($_SESSION['shipping_city'])){    
            unset($_SESSION['shipping_city']);
        }
        if(isset($_SESSION['shipping_state'])){    
            unset($_SESSION['shipping_state']);
        }
        $_SESSION['pincode_avil_msg'] = 'Delivery not available.';
        $_SESSION['delivery'] = 'na';
    }
    die;
}

add_action( 'woocommerce_cart_calculate_fees','woocommerce_custom_surcharge' );
function woocommerce_custom_surcharge() {
    global $woocommerce;
    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;
    $postcode = $woocommerce->customer->billing_postcode; //get postcode
    $country  = WC()->countries->countries[$woocommerce->customer->billing_country]; //To get country name by code
    $state    = WC()->countries->states[$woocommerce->customer->billing_country][$woocommerce->customer->billing_state]; //to get State name by state code

    if(isset($_SESSION['shipping_charge'])){
        $extra_charge = $_SESSION['shipping_charge']; //( $woocommerce->cart->cart_contents_total + 50 ); // * $percentage;   
        $woocommerce->cart->add_fee( 'Shipping Charge', $extra_charge, true, '' );
    }        
}

add_filter( 'woocommerce_form_field_args', 'autofill_checkout_field', 999, 3 );

function autofill_checkout_field($args, $key, $value){
        
    if($key == 'billing_city'){
        $args['custom_attributes']['readonly'] = "readonly";
    }

    if($key == 'billing_postcode'){
        $args['custom_attributes']['readonly'] = "readonly";    
    }

    if($key == 'billing_state'){
        $args['custom_attributes']['disabled'] = "disabled";    
    }

    return $args;
}

add_filter( 'woocommerce_checkout_get_value' , 'clear_checkout_fields' , 10, 2 );
function clear_checkout_fields( $value, $input ){
    
    $country_states_array = WC()->countries->get_states();

    if( $input == 'billing_city' ){
        $value = $_SESSION['shipping_city'];
    }

    if( $input == 'billing_postcode' ){
        $value = $_SESSION['shipping_pincode'];
    }

    if( $input == 'billing_state' ){
        $value = recursive_array_search(ucfirst(strtolower($_SESSION['shipping_state'])),$country_states_array);
    }

    return $value;
}

function skyverge_empty_cart_notice() {
    
    if ( WC()->cart->get_cart_contents_count() == 0 ) {
        if(isset($_SESSION['shipping_charge'])){
            unset($_SESSION['shipping_charge']);
        }
        if(isset($_SESSION['shipping_pincode'])){
            unset($_SESSION['shipping_pincode']);
        }
        if(isset($_SESSION['shipping_city'])){    
            unset($_SESSION['shipping_city']);
        }
        if(isset($_SESSION['shipping_state'])){    
            unset($_SESSION['shipping_state']);
        }
    }

}
// Add to cart page
add_action( 'woocommerce_check_cart_items', 'skyverge_empty_cart_notice' );

function get_price_by_pincode($pincode){
    global $wpdb;
    $table_name = $wpdb->prefix . "pincode"; 
    $pincode_data = $wpdb->get_row( "SELECT * FROM $table_name WHERE pincode = $pincode",ARRAY_A );
    return $pincode_data['price'];
}

function check_delivery_avail_by_pincode($pincode){
    global $wpdb;
    $table_name = $wpdb->prefix . "pincode"; 
    $pincode_data = $wpdb->get_row( "SELECT * FROM $table_name WHERE pincode = $pincode",ARRAY_A );
    if(empty($pincode_data)){
        return false;
    }
    return true;
}

//Disabling AJAX for Cart Page..
function cart_script_disabled(){
    wp_dequeue_script( 'wc-cart' );
}
add_action( 'wp_enqueue_scripts', 'cart_script_disabled',999,1 );

// remove a template redirect from within a custom plugin.
add_action( 'template_redirect', 'check_cart_price', 5 );
function check_cart_price(){
    if(is_cart() || is_checkout()){
        if ( WC()->cart->get_cart_contents_count() > 0 ) {
            if(WC()->cart->cart_contents_total <= 500 && isset($_SESSION['shipping_pincode']) && check_delivery_avail_by_pincode($_SESSION['shipping_pincode'])){
                $_SESSION['shipping_charge'] = get_price_by_pincode($_SESSION['shipping_pincode']);
            }else{
                if(isset($_SESSION['shipping_charge'])){
                    unset($_SESSION['shipping_charge']);
                }    
            }
        }
    }
}

add_action( 'wpo_wcpdf_after_order_data', 'wpo_wcpdf_shipping_day', 10, 2 );
function wpo_wcpdf_shipping_day ($template_type, $order) {
    if ($template_type == 'packing-slip' || $template_type == 'invoice') {
        if(get_post_meta( $order->get_id(), 'company_and_gst', true )){
            if(get_post_meta( $order->get_id(), 'company_name', true ) != ''){ ?>
                <tr class="">
                    <th>Company Name:</th>
                    <td><?php echo get_post_meta( $order->get_id(), 'company_name', true ); ?></td>
                </tr>
            <?php } 
            if(get_post_meta( $order->get_id(), 'gstin', true ) != ''){ ?>
                <tr class="">
                    <th>GSTIN No:</th>
                    <td><?php echo get_post_meta( $order->get_id(), 'gstin', true ); ?></td>
                </tr>
            <?php
            }
        }
    }
}


// function recursive_array_search($needle,$haystack) {
//     foreach($haystack as $key=>$value) {
//         $current_key=$key;
//         if($needle===$value OR (is_array($value) && recursive_array_search($needle,$value) !== false)) {
//             return $current_key;
//         }
//     }
//     return false;
// }

function recursive_array_search($needle, $haystack) {
    if(in_array($needle, $haystack)) {
        return array_search($needle, $haystack);
    }
    foreach($haystack as $key => $element) {
        if(is_array($element) && recursive_array_search($needle, $element)) {
            $searchReturn = recursive_array_search($needle, $element);
            if (is_array($searchReturn)) {
                array_unshift($searchReturn, $key);
                return $searchReturn;
            }
            return $searchReturn;
        }
    }
    return false;
}

add_action('woocommerce_after_order_notes', 'billing_country_hidden_field');
function billing_country_hidden_field($checkout){
    $country_states_array = WC()->countries->get_states();
    $billing_state = recursive_array_search(ucfirst(strtolower($_SESSION['shipping_state'])),$country_states_array);
    echo '<input type="hidden" class="input-hidden" name="billing_state"  value="'.$billing_state.'">';
}
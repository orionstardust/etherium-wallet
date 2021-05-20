<?php

/*
Plugin Name: Ether Wallet
Description: Wallet for Ether and ERC20 tokens for WordPress
Version: 1.0
Author: jjsiit
*/

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

global 
    $ETHER_WALLET_plugin_basename,
    $ETHER_WALLET_options,
    $ETHER_WALLET_plugin_dir,
    $ETHER_WALLET_plugin_url_path,
    $ETHER_WALLET_services,
    $ETHER_WALLET_amp_icons_css
;
if ( !function_exists( 'ETHER_WALLET_deactivate' ) ) {
    function ETHER_WALLET_deactivate()
    {
        if ( !current_user_can( 'activate_plugins' ) ) {
            return;
        }
        deactivate_plugins( plugin_basename( __FILE__ ) );
    }

}
                            
// ... Your plugin's main file logic ...
$ETHER_WALLET_plugin_basename = plugin_basename( dirname( __FILE__ ) );
$ETHER_WALLET_plugin_dir = untrailingslashit( plugin_dir_path( __FILE__ ) );
$ETHER_WALLET_plugin_url_path = untrailingslashit( plugin_dir_url( __FILE__ ) );
// HTTPS?
$ETHER_WALLET_plugin_url_path = ( is_ssl() ? str_replace( 'http:', 'https:', $ETHER_WALLET_plugin_url_path ) : $ETHER_WALLET_plugin_url_path );
// Set plugin options
$ETHER_WALLET_options = get_option( 'ether-wallet_options', array() );
function ETHER_WALLET_init()
{
    global  $ETHER_WALLET_plugin_dir, $ETHER_WALLET_plugin_basename, $ETHER_WALLET_options ;
    // Load the textdomain for translations
    load_plugin_textdomain( 'ether-wallet', false, $ETHER_WALLET_plugin_basename . '/languages/' );
}

add_filter( 'init', 'ETHER_WALLET_init' );
           
function ETHER_WALLET_form_shortcode( $attributes )
{
    //    global $ETHER_WALLET_options;
    //	$options = stripslashes_deep( $ETHER_WALLET_options );
    
    $js = '';
    $ret = "
    <div class='twbs' style='max-width: 400px;'>
        <div class='card'>
            <div class='card-body'>
                <h2 class='card-title'>Ether Wallet</h5>
                <div class='dropdown top-margin-20'>
                    <button class='btn btn-primary dropdown-toggle btn-block' type='button' id='providerBtn' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                        MainNet
                    </button>
                    <div class='dropdown-menu btn-block margin-0' aria-labelledby='dropdownMenuButton'>
                        <button class='dropdown-item' type='button' onclick='setProvider(0);'>MainNet</button>
                        <button class='dropdown-item' type='button' onclick='setProvider(1);'>TestNet</button>
                    </div>
                </div>
                <div class='input-group top-margin-15'>
                    <div class='input-group-prepend'>
                        <span class='input-group-text'>Account</span>
                    </div>
                    <input id='accountAddress' class='form-control'></input>
                    <span class='input-group-append'>
                        <button class='button btn btn-default btn-right btn-clipboard input-button' type='button'
                            data-clipboard-target='#accountAddress'
                            data-clipboard-action='copy'>
                            <i class='fa fa-clipboard' aria-hidden='true'></i>
                        </button>
                    </span>
                </div>

                <div class='input-group top-margin-10'>
                    <div class='input-group-prepend'>
                        <span class='input-group-text'>New Wallet</span>
                    </div>
                    <input id='userEntropy' class='form-control' placeholder='Type random text to generate entropy' size='80' type='text' />
                    <span class='input-group-append'>
                        <div class='btn-group' role='group'>
                            <button class='button btn btn-default btn-left d-md-inline ethereum-wallet-qr-scan-button input-button' type='button' 
                                    data-toggle='collapse' 
                                    onclick='newWallet()'
                                    role='button' aria-expanded='false' 
                                    aria-controls='ethereum-wallet-to-qr1' 
                                    title='Create New Wallet'>
                                <i class='fa fa-file' aria-hidden='true'></i>
                            </button>
                        </div>
                    </span>
                </div>

                <div class='input-group top-margin-10'>
                    <div class='input-group-prepend'>
                        <span class='input-group-text'>Restore Wallet</span>
                    </div>
                    <input id='seed' class='form-control' size='80' type='text' value=''/>
                    <span class='input-group-append'>
                        <div class='btn-group' role='group'>
                            <button class='button btn btn-default btn-left d-md-inline ethereum-wallet-qr-scan-button input-button' type='button' 
                                    data-toggle='collapse' 
                                    onclick='setSeed()'
                                    role='button' aria-expanded='false' 
                                    aria-controls='ethereum-wallet-to-qr1' 
                                    title='Restore wallet from Seed'>
                                <i class='fa fa-folder-open' aria-hidden='true'></i>
                            </button>
                        </div>
                    </span>
                </div>

                <div class='d-flex w-100 top-margin-15' role='group'>
                    <button class='button btn btn-default w-100' style='margin-right:5px' onclick='exportPrivateKey()'>Export Privatekey</button>
                    <button class='button btn btn-default w-100' style='margin-left:5px' onclick='showSeed()'>Show Seed</button>
                </div>
                <div class='form-group top-margin-15'>
                    <label class='control-label' for='addr'>Balance:</label>
                    <ul class='list-group' id='addr'>
                    </ul>
                </div>

                <div class='d-flex w-100 top-margin-15' role='group'>
                    <button class='button btn btn-default w-100' style='margin-right:5px' data-toggle='modal' data-target='#sendEtherModal'>Send Ether</button>
                    <button class='button btn btn-default w-100' style='margin-right:5px' data-toggle='modal' data-target='#sendTokenModal'>Send Token</button>
                </div>

                <div class='modal fade' id='sendEtherModal' tabindex='-1' role='dialog' aria-labelledby='sendEtherModalLabel' aria-hidden='true'>
                    <div class='modal-dialog modal-dialog-centered' role='document'>
                        <div class='modal-backdrop fade show' style='z-index:0'></div>
                        <div class='modal-content'>
                            <div class='modal-header'>
                                <h2 class='modal-title' id='sendEtherModalLabel'>Send Ether</h5>
                                <button type='button' class='close margin-0' data-dismiss='modal' aria-label='Close'>
                                <span aria-hidden='true'>&times;</span>
                                </button>
                            </div>
                            <div class='modal-body'>
                                <div class='form-group'>
                                    <label class='control-label margin-0' for='sendTo'>To: </label>
                                    <div class='input-group'>
                                        <input id='sendTo' size='40' type='text' class='form-control'/>
                                    </div>
                                    <div class='top-margin-15'>
                                        <label class='control-label margin-0' for='sendTo'>Amount: </label>
                                        <div class='input-group'>
                                            <input id='sendValueAmount' type='text' class='form-control'/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='modal-footer'>
                                <button id='ethereum-wallet-send-button' class='button btn btn-primary' onclick='sendEth()'>Send</button>
                                <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class='modal fade' id='sendTokenModal' tabindex='-1' role='dialog' aria-labelledby='sendTokenModalLabel' aria-hidden='true'>
                    <div class='modal-dialog modal-dialog-centered' role='document'>
                        <div class='modal-backdrop fade show' style='z-index:0'></div>
                        <div class='modal-content'>
                            <div class='modal-header'>
                                <h2 class='modal-title' id='sendTokenModalLabel'>Send Token</h5>
                                <button type='button' class='close margin-0' data-dismiss='modal' aria-label='Close'>
                                <span aria-hidden='true'>&times;</span>
                                </button>
                            </div>
                            <div class='modal-body'>
                                <div class='form-group top-margin-20'>
                                    <label class='control-label margin-0' for='contractAddr'>Function Call</label>
                                    <div class='input-group'>
                                        <input id='functionCaller' class='form-control'></input>
                                    </div>
                                    <div class='top-margin-15'>
                                        <label class='control-label margin-0' for='contractAddr'>Contract Address:</label>
                                        <div class='input-group' >
                                            <input id='contractAddr' class='form-control' size='40' type='text' />
                                        </div>
                                    </div>
                                    <div class='top-margin-15'>
                                        <label class='control-label margin-0' for='contractAbi'>Contract ABI:</label>
                                        <div class='input-group'>
                                            <input id='contractAbi' class='form-control' size='40' type='text' />
                                        </div>
                                    </div>
                                    <div class='top-margin-15'>
                                        <label class='control-label margin-0' for='functionName'>Function Name:</label>
                                        <div class='input-group'>
                                            <input id='functionName' class='form-control' size='20' type='text' />
                                        </div>
                                    </div>
                                    <div class='top-margin-15'>
                                        <label class='control-label margin-0' for='functionArgs'>Function Arguments:</label>
                                        <div class='input-group'>
                                            <input id='functionArgs' class='form-control' size='20' type='text' />
                                        </div>
                                    </div>
                                    <div class='top-margin-15'>
                                        <label class='control-label margin-0' for='sendValueAmount'>Value (Ether):</label>
                                        <div class='input-group'>
                                            <input id='sendValueAmount' class='form-control' type='text' />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='modal-footer'>
                                <button class='button btn btn-primary' onclick='functionCall()'>Call Function</button>
                                <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                
            </div>
        </div>
    </div>";
    ETHER_WALLET_enqueue_scripts_();
    wp_enqueue_script( 'jsQR' );
    return $js . str_replace( "\n", " ", str_replace( "\r", " ", str_replace( "\t", " ", $js . $ret ) ) );
}
                    
add_shortcode( 'ether-wallet-form', 'ETHER_WALLET_form_shortcode' );
                    
function ETHER_WALLET_enqueue_scripts_()
{
    wp_enqueue_style( 'ether-wallet' );
    //-------------------------//
    wp_enqueue_script( 'ether-wallet' );
    wp_enqueue_script( 'functions' );
}

function ETHER_WALLET_stylesheet()
{
    global  $ETHER_WALLET_plugin_url_path ;
    $deps = array(
        'font-awesome',
        'bootstrap-ether-wallet',
        'bootstrap-affix-ether-wallet'
    );
    $min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' );
    
    if ( !wp_style_is( 'font-awesome', 'queue' ) && !wp_style_is( 'font-awesome', 'done' ) ) {
        wp_dequeue_style( 'font-awesome' );
        wp_deregister_style( 'font-awesome' );
        wp_register_style(
            'font-awesome',
            $ETHER_WALLET_plugin_url_path . "/css/font-awesome{$min}.css",
            array(),
            '4.7.0'
        );
    }
    
    
    if ( !wp_style_is( 'bootstrap-ether-wallet', 'queue' ) && !wp_style_is( 'bootstrap-ether-wallet', 'done' ) ) {
        wp_dequeue_style( 'bootstrap-ether-wallet' );
        wp_deregister_style( 'bootstrap-ether-wallet' );
        wp_register_style(
            'bootstrap-ether-wallet',
            $ETHER_WALLET_plugin_url_path . "/css/bootstrap-ns{$min}.css",
            array(),
            '4.0.0'
        );
    }
    
    
    if ( !wp_style_is( 'bootstrap-affix-ether-wallet', 'queue' ) && !wp_style_is( 'bootstrap-affix-ether-wallet', 'done' ) ) {
        wp_dequeue_style( 'bootstrap-affix-ether-wallet' );
        wp_deregister_style( 'bootstrap-affix-ether-wallet' );
        wp_register_style(
            'bootstrap-affix-ether-wallet',
            $ETHER_WALLET_plugin_url_path . "/css/affix.css",
            array(),
            '3.3.7'
        );
    }
    
    
    if ( !wp_style_is( 'ether-wallet', 'queue' ) && !wp_style_is( 'ether-wallet', 'done' ) ) {
        wp_dequeue_style( 'ether-wallet' );
        wp_deregister_style( 'ether-wallet' );
        wp_register_style(
            'ether-wallet',
            $ETHER_WALLET_plugin_url_path . "/ether-wallet.css",
            $deps,
            '2.8.0'
        );
    }

}

add_action( 'wp_enqueue_scripts', 'ETHER_WALLET_stylesheet', 20 );
                   
function ETHER_WALLET_enqueue_script()
{
    global  $ETHER_WALLET_plugin_url_path, $ETHER_WALLET_options ;
    $min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' );
    
    if ( !wp_script_is( 'web3', 'queue' ) && !wp_script_is( 'web3', 'done' ) ) {
        wp_dequeue_script( 'web3' );
        wp_deregister_script( 'web3' );
        wp_register_script(
            'web3',
            $ETHER_WALLET_plugin_url_path . "/js/web3.js",
            array( 'jquery' ),
            '0.20.6'
        );
    }
    
    if ( !wp_script_is( 'popper', 'queue' ) && !wp_script_is( 'popper', 'done' ) ) {
        wp_dequeue_script( 'popper' );
        wp_deregister_script( 'popper' );
        wp_register_script(
            'popper',
            $ETHER_WALLET_plugin_url_path . "/js/popper.min.js",
            array( 'jquery' ),
            '1.14.6'
        );
    }
    
    
    if ( !wp_script_is( 'ether-wallet-bootstrap', 'queue' ) && !wp_script_is( 'ether-wallet-bootstrap', 'done' ) ) {
        wp_dequeue_script( 'ether-wallet-bootstrap' );
        wp_deregister_script( 'ether-wallet-bootstrap' );
        wp_register_script(
            'ether-wallet-bootstrap',
            $ETHER_WALLET_plugin_url_path . "/js/bootstrap.min.js",
            array( 'jquery', 'popper' ),
            '4.0.0'
        );
    }
    
    
    if ( !wp_script_is( 'ether-wallet-bootstrap-affix', 'queue' ) && !wp_script_is( 'ether-wallet-bootstrap-affix', 'done' ) ) {
        wp_dequeue_script( 'ether-wallet-bootstrap-affix' );
        wp_deregister_script( 'ether-wallet-bootstrap-affix' );
        wp_register_script(
            'ether-wallet-bootstrap-affix',
            $ETHER_WALLET_plugin_url_path . "/js/affix.js",
            array( 'ether-wallet-bootstrap' ),
            '3.3.7'
        );
    }
    
    
    if ( !wp_script_is( 'qrcode', 'queue' ) && !wp_script_is( 'qrcode', 'done' ) ) {
        wp_dequeue_script( 'qrcode' );
        wp_deregister_script( 'qrcode' );
        wp_register_script(
            'qrcode',
            //            $ETHER_WALLET_plugin_url_path . "/js/qrcode{$min}.js", array('ether-wallet-bootstrap-affix'), '2009'
            $ETHER_WALLET_plugin_url_path . "/js/qrcode.min.js",
            array( 'ether-wallet-bootstrap-affix' ),
            '2009'
        );
    }
    
    
    if ( !wp_script_is( 'jquery.qrcode', 'queue' ) && !wp_script_is( 'jquery.qrcode', 'done' ) ) {
        wp_dequeue_script( 'jquery.qrcode' );
        wp_deregister_script( 'jquery.qrcode' );
        wp_register_script(
            'jquery.qrcode',
            //            $ETHER_WALLET_plugin_url_path . "/js/jquery.qrcode{$min}.js", array('jquery', 'qrcode'), '1.0'
            $ETHER_WALLET_plugin_url_path . "/js/jquery.qrcode.min.js",
            array( 'jquery', 'qrcode' ),
            '1.0'
        );
    }
    
    // https://github.com/cozmo/jsQR
    
    if ( !wp_script_is( 'jsQR', 'queue' ) && !wp_script_is( 'jsQR', 'done' ) ) {
        wp_dequeue_script( 'jsQR' );
        wp_deregister_script( 'jsQR' );
        wp_register_script(
            'jsQR',
            $ETHER_WALLET_plugin_url_path . "/js/jsQR.min.js",
            array( 'ether-wallet-bootstrap-affix' ),
            '807b07357a35a2d16d8006bcc5426ce558d6b904'
        );
    }
    
    
    if ( !wp_script_is( 'clipboard', 'queue' ) && !wp_script_is( 'clipboard', 'done' ) ) {
        wp_dequeue_script( 'clipboard' );
        wp_deregister_script( 'clipboard' );
        wp_register_script(
            'clipboard',
            $ETHER_WALLET_plugin_url_path . "/js/clipboard.min.js",
            array(),
            '2.0.4'
        );
    }

    if ( !wp_script_is( 'functions', 'queue' ) && !wp_script_is( 'functions', 'done' ) ) {
        wp_dequeue_script( 'functions' );
        wp_deregister_script( 'functions' );
        wp_register_script(
            'functions',
            $ETHER_WALLET_plugin_url_path . "/functions.js",
            array('clipboard')
        );
    }

    if ( !wp_script_is( 'store', 'queue' ) && !wp_script_is( 'store', 'done' ) ) {
        wp_dequeue_script( 'store' );
        wp_deregister_script( 'store' );
        wp_register_script(
            'store',
            $ETHER_WALLET_plugin_url_path . "/js/store.legacy.min.js"
        );
    }

    if ( !wp_script_is( 'web3provider', 'queue' ) && !wp_script_is( 'web3provider', 'done' ) ) {
        wp_dequeue_script( 'web3provider' );
        wp_deregister_script( 'web3provider' );
        wp_register_script(
            'web3provider',
            $ETHER_WALLET_plugin_url_path . "/js/hooked-web3-provider.js"
        );
    }

    if ( !wp_script_is( 'async', 'queue' ) && !wp_script_is( 'async', 'done' ) ) {
        wp_dequeue_script( 'async' );
        wp_deregister_script( 'async' );
        wp_register_script(
            'async',
            $ETHER_WALLET_plugin_url_path . "/js/async.js"
        );
    }

    if ( !wp_script_is( 'ether-wallet', 'queue' ) && !wp_script_is( 'ether-wallet', 'done' ) ) {
        wp_dequeue_script( 'ether-wallet' );
        wp_deregister_script( 'ether-wallet' );
        wp_register_script(
            'ether-wallet',
            $ETHER_WALLET_plugin_url_path . "/dist/lightwallet.js",
            array( 'jquery', 'web3', 'web3provider', 'store', 'async')
        );
    }

    $provider = ( !empty($options['provider']) ? esc_attr( $options['provider'] ) : '' );
    wp_localize_script( 'ether-wallet', 'etherWallet', [
        'provider'              => esc_html( $provider ),
    ] );  
}
                    
add_action( 'wp_enqueue_scripts', 'ETHER_WALLET_enqueue_script' );
/**
 * Admin Options
 */
if ( is_admin() ) {
    include_once $ETHER_WALLET_plugin_dir . '/ether-wallet.admin.php';
}
function ETHER_WALLET_add_menu_link()
{
    $page = add_options_page(
        __( 'Ethereum Wallet Settings', 'ether-wallet' ),
        __( 'Ethereum Wallet', 'ether-wallet' ),
        'manage_options',
        'ether-wallet',
        'ETHER_WALLET_options_page'
    );
}

add_filter( 'admin_menu', 'ETHER_WALLET_add_menu_link' );
// Place in Option List on Settings > Plugins page
function ETHER_WALLET_actlinks( $links, $file )
{
    // Static so we don't call plugin_basename on every plugin row.
    static  $this_plugin ;
    if ( !$this_plugin ) {
        $this_plugin = plugin_basename( __FILE__ );
    }
    
    if ( $file == $this_plugin ) {
        $settings_link = '<a href="options-general.php?page=ether-wallet">' . __( 'Settings' ) . '</a>';
        array_unshift( $links, $settings_link );
        // before other links
    }
    
    return $links;
}

add_filter(
    'plugin_action_links',
    'ETHER_WALLET_actlinks',
    10,
    2
);
       
                    
// @see https://www.tipsandtricks-hq.com/adding-a-custom-column-to-the-users-table-in-wordpress-7378
add_action( 'manage_users_columns', 'ETHER_WALLET_modify_user_columns' );
function ETHER_WALLET_modify_user_columns( $column_headers )
{
    $column_headers['ether_wallet'] = __( 'Ethereum wallet', 'ether-wallet' );
    return $column_headers;
}
                    
add_action( 'admin_head', 'ETHER_WALLET_custom_admin_css' );
function ETHER_WALLET_custom_admin_css()
{
    echo  '<style>
.column-ethereum_wallet {width: 22%}
</style>' ;
}
                    
add_action(
    'manage_users_custom_column',
    'ETHER_WALLET_user_posts_count_column_content',
    10,
    3
);

function ETHER_WALLET_user_posts_count_column_content( $value, $column_name, $user_id )
{
    
    if ( 'ether_wallet' == $column_name ) {
        $address = ETHER_WALLET_get_wallet_address( $user_id );
        $addressPath = ETHER_WALLET_get_address_path( $address );
        $value = sprintf( '<a href="%1$s" target="_blank" rel="nofollow">%2$s</a>', $addressPath, $address );
    }
    
    return $value;
}

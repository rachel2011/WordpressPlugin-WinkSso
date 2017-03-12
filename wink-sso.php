<?php
/*
 * Plugin Name: Wink Single Sign On
 * Plugin URI: https://bitbucket.org/jrichardsii/wink-sso
 * Description: Interfaces with wink to allow anyone with a Wustl Key access
 * Version: 0.2.2
 * Author: John Richards
 * Author URI: publicaffairs.wustl.edu
 * Text Domain: wink-sso
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die( "Don't do that. Come here via WP-Admin like you know you should." );
}

include_once plugin_dir_path( __FILE__ ) . 'inc/settings.php'; // settings page

function wink_sso_sign_on( $atts ) {

	if( ! is_user_logged_in() ) {
		if (function_exists('shibboleth_session_initiator_url')) {
			$initiator_url = shibboleth_session_initiator_url( wp_login_url() . '?action=wink_sign_on' );
		}else {
			$initiator_url = wp_login_url( wp_login_url() . '?action=wink_sign_on');	
		}
		wp_redirect($initiator_url);
		die();
	}

	$options = get_option( 'wustl_wink_settings' );

	$users_values = explode( ';', $_SERVER[ $options['wustl_server_attribute'] ] );
	$accepted_values = explode( ',', $options['wustl_server_values'] );
	
	$access_denied = true;

	foreach ( $accepted_values as $val ) {
		if( in_array( trim( $val ), $users_values ) ) {
			$access_denied = false;
			break;
		}
	}

	if ( $access_denied ) {
		print '<pre> user vals:';
		print_r( $_REQUEST['action'] );
		print_r( $_SERVER );
		print_r( $users_values );
		print 'accepted vals:';
		print_r( $accepted_values );	
		die();

		wp_redirect( esc_url( $options['wustl_wink_denied_url'] ) ); 
		exit;
	}	

	$soapUrl = esc_html( $options['wustl_wink_sso_url'] ); // asmx URL of WSDL
	$token = esc_html( $options['wustl_wink_token'] );
	// echo '<pre>';
	// $curr = wp_get_current_user();
	// print_r(get_user_meta(get_current_user_id()));
	// print_r($_SERVER);

$accountname = $_SERVER[ 'sAMAccountName' ];
$email = $_SERVER[ 'email' ];
$first_name = $_SERVER[ 'givenName' ];
$last_name = $_SERVER[ 'sn' ];


    // xml post structure
	$xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sso="http://services.printable.com/1.0/sso" xmlns:sso1="http://www.printable.com/sso">
	<soapenv:Header/>
	   <soapenv:Body>
	      <sso:Authenticate>
	         <sso1:SSOMessage version="v1">
	            <PartnerCredentials>
	               <Token>' . $token . '</Token>
	            </PartnerCredentials>
	            <SingleSignOnRequest>
	               <UserCredentials>
	                  <ID type="NonPrintable">EXT_sample_wsuser1</ID>
	               </UserCredentials>
	               <Navigation>
	                  <StartPage>
	                     <PageName>Catalog</PageName>
	                  </StartPage>
	               </Navigation>
	            </SingleSignOnRequest>
	            <EditUserRequest Action="Auto">
	               <User>
	                  <ID type="NonPrintable">EXT_sample_wsuser1</ID>
	                  <Properties>
	                     <Login>' . $accountname . '</Login>
	                     <FirstName>' . $first_name . '</FirstName>
	                     <LastName>' . $last_name . '</LastName>
	                     <Email>' . $email . '</Email>
	                  </Properties>
	                  <Settings>
	                     <!--Zero or more repetitions:-->
	                     <Setting Name="User.DirectAccount" Value="true"/>
	                     <Setting Name="User.SeeOrdersFor" Value="NoneSelected"/>
	                     <Setting Name="User.ShowPaymentOptions" Value="false"/>
	                     <Setting Name="User.CalculateTax" Value="true"/>
	                     <Setting Name="User.CostCenterEditAllowed" Value="Granted"/>
	                     <Setting Name="User.CostCenterSelectionMode" Value="SearchMode"/>
	                     <Setting Name="User.OrderingPrivileges" Value="Purchaser"/>
	                  </Settings>
	                  <UserGroups SyncMode="Auto">
	                     <UserGroup type="NonPrintable">WashU</UserGroup>
	                  </UserGroups>
	               </User>
	            </EditUserRequest>
	         </sso1:SSOMessage>
	      </sso:Authenticate>
	   </soapenv:Body>
	</soapenv:Envelope>';


	$headers = array(
		"Content-type" => "text/xml;charset=\"utf-8\"",
		"Accept" => "text/xml",
		"Cache-Control" => "no-cache",
		"Pragma" => "no-cache",
		"SOAPAction" => "http://services.printable.com/1.0/sso/Authenticate", 
		"Content-length" => strlen($xml_post_string),
	); //SOAPAction: your op URL

	$url = $soapUrl;

	$args = array(
	    'blocking'    => true,
	    'headers'     => $headers,
	    'body'        => $xml_post_string,
	    'sslverify'   => true,
	); 

	$response = wp_remote_post( $url, $args );
	// Check for error
	if ( is_wp_error( $response ) ) {
		echo 'An unexpected error was encountered while contacting the W.Ink server. Wait a few minutes and try again. If problem persists, contact the site administrator.';
		die;
	}
	$body = wp_remote_retrieve_body( $response );
	// print '<pre>';
	// print_r( $response ); die();

	// converting
	$body1 = str_replace("<soap:Body>","",$body);
	$body2 = str_replace("</soap:Body>","",$body1);

	// convertingc to XML
	$parser = simplexml_load_string($body2);
	// use $parser to get your data out of XML response and to display it.

	wp_redirect( $parser->AuthenticateResponse->AuthenticateResult ); 
	exit;
}
add_action( 'login_form_wink_sign_on', 'wink_sso_sign_on', 9, 3);

function wink_sso_shortcode_button() {

	$options = get_option( 'wustl_wink_settings' );

	$formhtml = '<form action="' . wp_login_url() . '" method="post">
	<input type="hidden" name="action" value="wink_sign_on">
	<input type="submit" value="' . esc_html( $options['wustl_button_text'] ) . '" class="gform_button button">
	</form>';

	return $formhtml;
}
add_shortcode( 'wink-sso', 'wink_sso_shortcode_button' );

?>
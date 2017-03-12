<?php
/* functions for plugin settings
 */

function wustl_wink_add_admin_menu(  ) {
	add_options_page(
		'W.Ink Settings',
		'W.Ink',
		'manage_options',
		'wustl_wink_single_sign_on_settings',
		'wustl_wink_single_sign_on_settings_page'
	);
}
add_action( 'admin_menu', 'wustl_wink_add_admin_menu' );

function wustl_wink_settings_init(  ) {
	register_setting( 'pluginPage', 'wustl_wink_settings' );

	// Alert Mode & Feed Source Section
	add_settings_section(
		'wustl_wink_pluginPage_section',
		__( 'Alert Mode & Feed Source', 'wu-alert' ),
		'wustl_wink_settings_section_callback',
		'pluginPage'
	);
	
	add_settings_field(
		'wustl_wink_SSO_url',
		__( 'Wink SSO URL', 'wu-sso-url' ),
		'wustl_wink_sso_url_render',
		'pluginPage',
		'wustl_wink_pluginPage_section'
	);

	add_settings_field(
		'wustl_wink_denied_url',
		__( 'Wink denied URL', 'wu-denied-url' ),
		'wustl_wink_denied_url_render',
		'pluginPage',
		'wustl_wink_pluginPage_section'
	);

	add_settings_field(
		'wustl_wink_token',
		__( 'Wink token', 'wu-token' ),
		'wustl_wink_token_render',
		'pluginPage',
		'wustl_wink_pluginPage_section'
	);

	add_settings_field(
		'wustl_wink_button_text',
		__( 'Wink button text', 'wu-button-text' ),
		'wustl_wink_button_text_render',
		'pluginPage',
		'wustl_wink_pluginPage_section'
	);

	add_settings_field(
		'wustl_wink_server_attribute ',
		__( 'Wink server attribute', 'wu-server-attribute' ),
		'wustl_wink_server_attribute_render',
		'pluginPage',
		'wustl_wink_pluginPage_section'
	);

	add_settings_field(
		'wustl_wink_server_values ',
		__( 'Wink server values', 'wu-server-values' ),
		'wustl_wink_server_values_render',
		'pluginPage',
		'wustl_wink_pluginPage_section'
	);

}
add_action( 'admin_init', 'wustl_wink_settings_init' );

function wustl_wink_settings_section_callback() {
	return 'Settings for WINK\'s single sign on implementation';
}

function wustl_wink_sso_url_render() {
	$options = get_option( 'wustl_wink_settings' );
	?>
	<input type='text' name='wustl_wink_settings[wustl_wink_sso_url]' value='<?php echo esc_html( $options['wustl_wink_sso_url'] ); ?>' class="regular-text code">
	<p class="description">Enter the url to send SSO requests to.</p>
	<?php
}

function wustl_wink_denied_url_render() {
	$options = get_option( 'wustl_wink_settings' );
	?>
	<input type='text' name='wustl_wink_settings[wustl_wink_denied_url]' value='<?php echo esc_html( $options['wustl_wink_denied_url'] ); ?>' class="regular-text code">
	<p class="description">Enter the URL to send denied requests to.</p>
	<?php
}

function wustl_wink_token_render() {
	$options = get_option( 'wustl_wink_settings' );
	?>
	<input type='text' name='wustl_wink_settings[wustl_wink_token]' value='<?php echo esc_html( $options['wustl_wink_token'] ); ?>' class="regular-text code">
	<p class="description">Enter the token to be used for requests.</p>
	<?php
}

function wustl_wink_button_text_render() {
	$options = get_option( 'wustl_wink_settings' );
	?>
	<input type='text' name='wustl_wink_settings[wustl_button_text]' value='<?php echo esc_html( $options['wustl_button_text'] ); ?>' class="regular-text code">
	<p class="description">Enter the displayed button text.</p>
	<?php
}

function wustl_wink_server_attribute_render() {
	$options = get_option( 'wustl_wink_settings' );
	?>
	<input type='text' name='wustl_wink_settings[wustl_server_attribute]' value='<?php echo esc_html( $options['wustl_server_attribute'] ); ?>' class="regular-text code">
	<p class="description">Enter the shibboleth value to check against. (This attribute must be available via $_SERVER)</p>
	<?php
}

function wustl_wink_server_values_render() {
	$options = get_option( 'wustl_wink_settings' );
	?>
	<input type='text' name='wustl_wink_settings[wustl_server_values]' value='<?php echo esc_html( $options['wustl_server_values'] ); ?>' class="regular-text code">
	<p class="description">Enter a comma delimited list of accepted values.</p>
	<?php
}

function wustl_wink_single_sign_on_settings_page() { ?>
	<h1>W.Ink Settings</h1>
	<form action='options.php' method='post'>
		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>
	</form>
	<?php
}
?>
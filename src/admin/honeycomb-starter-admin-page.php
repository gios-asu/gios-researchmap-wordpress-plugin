<?php
namespace HoneycombStarter\Admin;
use Honeycomb\Wordpress\Hook;

// Avoid direct calls to this file
if ( ! defined( 'HONEYCOMB_STARTER_PLUGIN_VERSION' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}

/**
 * Honeycomb_Starter_Admin_Page
 * provides the WP Admin settings page
 */
class Honeycomb_Starter_Admin_Page extends Hook {
  use \HoneycombStarter\Options_Handler_Trait;

  public static $options_name = 'honeycomb-starter-options';
  public static $options_group = 'honeycomb-starter-options_group';
  public static $section_id = 'honeycomb-starter-section_id';
  public static $section_name = 'honeycomb-starter-section_name';
  public static $page_name = 'honeycomb-starter-admin-page';

  public static $setting_one_option_name = 'setting_one';
  public static $setting_two_option_name = 'setting_two';

 public function __construct( $version = '0.1' ) {
    parent::__construct( $version );

    $this->add_action( 'admin_menu', $this, 'admin_menu' );
    $this->add_action( 'admin_init', $this, 'admin_init' );

    // Set default options
    add_option(
        self::$options_name,
        array(
          self::$setting_one_option_name => 0,
          self::$setting_two_option_name => null,
        )
    );

    $this->define_hooks();
  }


  /**
   * Add filters and actions
   *
   * @override
   */
  public function define_hooks() {
    $this->add_action( 'admin_init', $this, 'admin_init' );
  }

  /**
   * Set up administrative fields
   */
  public function admin_init() {
    register_setting(
        self::$options_group,
        self::$options_name,
        array( $this, 'form_submit' )
    );

    add_settings_section(
        self::$section_id,
        'Honeycomb Starter Settings',
        array(
          $this,
          'print_section_info',
        ),
        self::$section_name
    );

    add_settings_field(
        self::$setting_one_option_name,
        'Setting One Example',
        array(
          $this,
          'setting_one_on_callback',
        ), // Callback
        self::$section_name,
        self::$section_id
    );

    add_settings_field(
        self::$setting_two_option_name,
        'Setting Two Example',
        array(
          $this,
          'setting_two_on_callback',
        ), // Callback
        self::$section_name,
        self::$section_id
    );
  }

  public function admin_menu() {
    $page_title = 'Honeycomb Starter Plugin Settings';
    $menu_title = 'Honeycomb Starter';
    $capability = 'manage_options';
    $path = plugin_dir_url( __FILE__ );

    add_options_page(
        'Settings Admin',
        'Honeycomb Starter Page',
        $capability,
        self::$page_name,
        array( $this, 'render_admin_page' )
    );

  }

  public function render_admin_page() {
    ?>
    <div class="wrap">
        <h1>Honeycomb Starter Settings</h1>
        <form method="post" action="options.php">
        <?php
            // This prints out all hidden setting fields
            settings_fields( self::$options_group );
            do_settings_sections( self::$section_name );
            submit_button();
        ?>
        </form>
    </div>
    <?php
  }


  /**
   * Print the section text
   */
  public function print_section_info() {
    print 'Enter your settings below:';
  }

  /**
   * Print the form section for the college code
   */
  public function setting_two_on_callback() {

    $value = $this->get_option_attribute_or_default(
        array(
          'name'      => self::$options_name,
          'attribute' => self::$setting_two_option_name,
          'default'   => '',
        )
    );

    $html = <<<HTML
    <input type="text" id="%s" name="%s[%s]" value="%s"/><br/>
    <em>This is the second example plugin setting stored in the WP options table. This example setting is named <b>"setting_two"</b>.</em>
HTML;

    printf(
        $html,
        self::$setting_two_option_name,
        self::$options_name,
        self::$setting_two_option_name,
        $value
    );
  }

  /**
   * Print the form section for the setting_one form element
   */
  public function setting_one_on_callback() {

    $value = $this->get_option_attribute_or_default(
        array(
          'name'      => self::$options_name,
          'attribute' => self::$setting_one_option_name,
          'default'   => '',
        )
    );

    $html = <<<HTML
    <input type="text" id="%s" name="%s[%s]" value="%s"/><br/>
    <em>This is the first example plugin setting stored in the WP options table. This example setting is named <b>"setting_one"</b>.</em>
HTML;

    printf(
        $html,
        self::$setting_one_option_name,
        self::$options_name,
        self::$setting_one_option_name,
        $value
    );
  }

  /**
   * Handle form submissions for validations
   */
  public function form_submit( $input ) {
    // intval the setting_one_option_name
    if ( isset( $input[ self::$setting_one_option_name ] ) ) {
      $input[ self::$setting_one_option_name ] = intval( $input[ self::$setting_one_option_name ] );
    }

    if ( isset( $input[ self::$setting_two_option_name ] ) ) {
      $input[ self::$setting_two_option_name ] = strtoupper( $input[ self::$setting_two_option_name ] );
    }

    return $input;
  }

}

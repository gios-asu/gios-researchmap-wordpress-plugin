<?php
namespace wpGiosMap\Shortcodes;
use Honeycomb\Wordpress\Hook;
use wpGiosMap\Admin\WP_GIOS_Map_Admin_Page;
use wpGiosMap\Options_Handler_Trait;


// Avoid direct calls to this file
if ( ! defined( 'WP_GIOS_MAP_PLUGIN_VERSION' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}

/**
 * WP_GIOS_Map_Shortcodes
 * provides the shortcode [hello-world]
 */
class WP_GIOS_Map_Shortcodes extends Hook {
  use Options_Handler_Trait;

  private $path_to_views;

  // const EXAMPLE_CLASS_CONSTANT  = 'Example Class Constant Value';

  public function __construct() {
    parent::__construct( 'wp-gios-map-shortcodes', WP_GIOS_MAP_PLUGIN_VERSION );
    $this->path_to_views = __DIR__ . '/../views/';
    $this->define_hooks();
  }

  /**
   * Register plugin functionality through WP hooks
   *
   * Uncomment action lines when that action is useful for your plugin
   */
  public function define_hooks() {
    $this->add_action( 'wp_enqueue_scripts', $this, 'wp_enqueue_scripts' );
    // $this->add_action( 'init', $this, 'setup_rewrites' );
    // $this->add_action( 'wp', $this, 'add_http_cache_header' );
    $this->add_action( 'wp_head', $this, 'add_mustache_tempate' );
    $this->add_shortcode( 'gios_map', $this, 'gios_map_shortcode_handler' );
  }

  /**
   * Shorthand view wrapper to make rendering a view using Nectary's factories easier in this plugin
   */
  private function view( $template_name ) {
    return new \Nectary\Factories\View_Factory( $template_name, $this->path_to_views );
  }

  /**
   * Do not cache any sensitive form data - ASU Web Application Security Standards
   */
  public function add_html_cache_header() {
    if ( $this->current_page_has_hello_world_shortcode() ) {
      echo '<meta http-equiv="Pragma" content="no-cache"/>
            <meta http-equiv="Expires" content="-1"/>
            <meta http-equiv="Cache-Control" content="no-store,no-cache" />';
    }
  }

  /**
   * Do not cache any sensitive form data - ASU Web Application Security Standards
   * This call back needs to hook after send_headers since we depend on the $post variable
   * and that is not populated at the time of send_headers.
   */
  public function add_http_cache_header() {
    if ( $this->current_page_has_hello_world_shortcode() ) {
      header( 'Cache-Control: no-Cache, no-Store, must-Revalidate' );
      header( 'Pragma: no-Cache' );
      header( 'Expires: 0' );
    }
  }

  /**
   * Returns true if the page is using the [gios_map] shortcode, else false
   *
   * Don't enqueue any scripts or stylesheets provided by this plugin,
   * unless we are actually rendering the shortcode
   */
  private function current_page_has_gios_map_shortcode() {
    global $post;
    return ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'gios_map' ) );
  }

  /**
   * Set up any url rewrites: Enable if needed
   *
   * WordPress requires that you tell it that you are using
   * additional parameters in the url.
   */
  // public function setup_rewrites() {
  //   add_rewrite_tag( '%param1%' , '([^&]+)' );
  //   add_rewrite_tag( '%param2%' , '([^&]+)' );
  // }

  /**
   * Enqueue CSS and JS
   * Hooks onto `wp_enqueue_scripts`.
   */
  public function wp_enqueue_scripts() {

    if ( $this->current_page_has_gios_map_shortcode() ) {
      // enqueue stylesheet
      $url_to_css_file = plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/css/gios-map-styles.css';
      wp_enqueue_style( $this->plugin_slug, $url_to_css_file, array(), $this->version );

      // enqueue javascript files
      $url_to_script = plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/js/raphael.min.js';
      wp_enqueue_script( 'raphael', $url_to_script, null, '1.0.0', false );

      $url_to_script = plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/js/jquery.mapael.min.js';
      wp_enqueue_script( 'jquery-mapael', $url_to_script, array( 'raphael' ), '1.0.0', false );

      $url_to_script = plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/js/world_countries_miller.min.js';
      wp_enqueue_script( 'world-map', $url_to_script, null, '1.0.0', false );

      $url_to_script = plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/js/mustache.min.js';
      wp_enqueue_script( 'mustache', $url_to_script, null, '1.0.0', false );

      $url_to_script = plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/js/gios-map.min.js';
      wp_enqueue_script( 'gios-map', $url_to_script, null, '1.0.0', false );


      /**
       * use the wp_localize_script() method to add a Javascript object to our page. The object
       * contains the image path variable set here. Our Javascript expects this object to exist
       * on page load.
       */
      $wp_urls_to_pass = array(
        'img_path' => plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/img'
        );
      wp_localize_script( 'gios-map', 'wpUrls', $wp_urls_to_pass );
    }
  }

  /**
   * gios_map_shortcode_handler( array|string $atts)
   *
   * This is the shortcode used to draw the research map. It supports (at this time),
   * one attribute, named 'disclaimer', that is used to toggle a <div> in the Handlebars
   * template. that <div> shows a text disclaimer under the map. Edit the file in
   * "/src/views/gios-map-shortcode/gios-map-display.handlebars" to change the text that
   * appears on screen.
   *
   * Also, I have commented out the use of some custom functions we wrote to provide default
   * attribute values. That is already possible through the use of Wordpress's shortcode_atts()
   * method, and I figure it's best to use the built-in stuff.
   *
   * By using shortcode_atts(), we can also provide the name of the shortcode to enable filtering
   * of the values. We use that here to convert the 'disclaimer' attribute to it's corresponding
   * boolean value. That way, setting 'disclaimer' to true/1/yes will all end up as TRUE.
   *
   * Fun note: using booleans (true/false) as shortcode attributes does not work. The mere presence
   * of the attribute, along with _any_ value, will evaluate to "true". Thus, entering 'disclaimer'=false
   * as your attribute will actually end up with 'disclaimer' equalling TRUE! That's why we're using the
   * boolean filter below. It uses wordpress's built-in validator where only 'false' ends up as false.
   *
   */
   public function gios_map_shortcode_handler( $atts, $content = "" ) {

    // process shortcode attributes, providing default values
    shortcode_atts(
      array( 'disclaimer' => false ),
      $atts,
      'gios_map'
    );

    // convert the disclaimer value to its corresponding boolean value
    $atts['disclaimer'] = filter_var( $atts['disclaimer'], FILTER_VALIDATE_BOOLEAN );

    // Leaving this here in case future side-effects require us to return to the old ways
    /*
      // if no attributes array exists, make an empty one
      if ( ! is_array( $atts ) ) {
        $atts = array();
      }

      // default to not showing the disclaimer
      ensure_default( $atts, 'disclaimer', false );
    */

    // pass the attributes to the view and return it for display
    $view_name = 'gios-map-shortcode.gios-map-display';
    $response = $this->view( $view_name )->add_data( $atts )->build();
    //$response = $this->view( $view_name )->build();
    return $response->content;
    }

    /**
     * add_mustache_template()
     *
     * I wanted to avoid having to copy/paste the template directly into WordPress (alongside
     * the shortcode), so I looked for the best way to get this non-javascript file into a
     * WordPress page. Turns out, the best/easiest way is to use the wp_head() hook, and just
     * echo the script text itself via a function.
     *
     * Wordpress is strict about what it will enqueue, and you can't enqueue a .mustache file,
     * nor can you enqueue a file with actual <script> tags, as required by mustache. So, I put
     * it here.
     */
    public function add_mustache_tempate() {
      echo '<script id="template" type="x-tmpl-mustache">
        {{#items}}
        <div class="project-box">
          <div class="row">
            <div class="col-xs-10 project-title">
              {{#slug}}
               <a href="{{slug}}" target="_blank">{{name}}</a>
              {{/slug}}
              {{^slug}}
                {{name}}
              {{/slug}}
            </div>
            <div class="col-xs-2 text-right">
              <img src="{{sdg}}" class="sdg-icon" />
            </div>
          </div>

          {{#description}}
            <div class="row">
              <div class="col-xs-12 project-description">
                <p>{{ description }}</p>
              </div>
            </div>
          {{/description}}
         </div>
        {{/items}}
      </script>';
    }
}

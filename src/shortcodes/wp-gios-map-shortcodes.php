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
 *
 * Provides the public-facing hooks, scripts, and other elements needed to display
 * the map, including:
 *
 * - The [gios_map] shortcode, and it's callback
 * - Enqueueing scripts and stylesheets on the map page
 * - Using the 'wp_head' hook to insert a Mustache template as a script in the header
 */
class WP_GIOS_Map_Shortcodes extends Hook {
  use Options_Handler_Trait;

  private $path_to_views;

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
   */
   public function gios_map_shortcode_handler( $atts, $content = "" ) {

    /* process shortcode attributes, providing default values. Please note: this uses
     * WordPress's built-in methods for handling shortcode attributes, ignoring calls
     * to our own functions.
     */
    shortcode_atts(
      array(
        'disclaimer'  => 'false',
        'disclaimer-text' => '(disclaimer not provided)',
        'title'       => '(title not provided)',
        'data-url'    => ''
      ),
      $atts,
      'gios_map'
    );

    // convert the disclaimer value to its corresponding boolean value
    $atts['disclaimer'] = filter_var( $atts['disclaimer'], FILTER_VALIDATE_BOOLEAN );

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
              {{#sdg}}
                <a href="https://sustainabledevelopment.un.org/sdg{{sdg}}" class="sdg-link" target="_blank"><img src="{{iconPath}}}" class="sdg-icon" /></a>
              {{/sdg}}
            </div>
          </div>

          {{#description}}
            <div class="row">
              <div class="col-xs-10 project-description">
                <p>{{{ description }}}</p>
              </div>
            </div>
          {{/description}}
         </div>
        {{/items}}
      </script>';
    }
}

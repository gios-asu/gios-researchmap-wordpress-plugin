# wp-gios-map
A WordPress plugin for displaying the GIOS research map

# Requirements
* PHP 5.4 or later
* jQuery 2.2.4 or later (2.2.4 was the version in the WordPress theme at the time of development)

# Overview
This plugin uses a collection of Javascript libraries, and a small chunk of HTML, to create a map of the world with plotted data "bubbles" at specified locations. As currently configured, the data bubbles represent GIOS research/activities around the world; these data points are pulled from a MySQL database specific to the map, and are requested via AJAX from an API server also created specially for the map.

That's an important thing to remember for this first version: it's built to purpose, and is not yet a user-friendly, general purpose plugin for easily dropping in an interactive map wherever you want. You've been warned.

# Tools Used
As mentioned above, the map uses a handful of Javascript libraries to do its work:
* jQuery  - the map plugin **does not** provide this; it uses the version from our theme
* [Raphael](http://dmitrybaranovskiy.github.io/raphael/) - a drawing library used to display graphics on an HTML `<canvas>` or `<svg>` element
* [jQuery Mapael](https://www.vincentbroute.fr/mapael/) - a jQuery plugin that wraps mapping-specific functionality around the bare Raphael library
* [Mustache.js](https://mustache.github.io) - a templating engine we use to display project names and details
* [FontAwesome](https://fontawesome.com) - Icons used, in our case, for the map zoom in/out/restore controls.

# Important Files
* `gios/gios-map.js`: this is our main Javascript file, which prepares and draws the map on screen. If you need to change the way the map works in any meaningful way, you'll be working on this file.
*  `assets/css/gios-map-styles.css`: The main stylesheet for the map. Controls most display options, with the notable exception of the actual country/border/bubble colors used by the map
* `src/views/gios-map-shortcode/gios-map-display.handlebars`: this is the HTML that will be injected into your Wordpress page when you use the `[gios_map]` short code.
* `src\shortcodes\wp-gios-map-shortcodes.php`: this is the central plugin file for the user-facing side of the map (aka the map itself). While you won't normally need to mess with this file, it does contain the mustache template for the details area show below the map (see notes below)

# Shortcodes
This plugin provides only one shortcode:
  * `[gios_map]` - Display the default map, with data from the default endpoint
  * You can toggle the visibility of a disclaimer sentence below the map by adding the attribute `disclaimer` to the code, as in `[gios_map disclaimer="true"]` or `[gios_map disclaimer="false"]`.
  * The disclaimer sentence itself can be found in the `gios-map-display.handlebars` file

# Notes
A list of miscellaneous notes that might be second nature to someone who made the map, but would need to be explained to someone else. Also, I forget sometimes.

### Changing The Version Number
When changing the version for this plugin, don't forget to update the following files:
* `wp-gios-map.php` - Relied upon by WordPress
* `package.json` - For NPM

### Wordpress
Set your Wordpress page to use the `containered template`. Without a `container` wrapped around the map, the various Bootstrap rows and columns in the map markup may not display correctly (specifically, certain margins on rows won't be applied, and information in the details area could be cut off. The map, itself, will look fine).

If you want to make sure that everything lines up, you can wrap a Bootstrap row/column combo around the map like this - while still using the containered template layout:

    <div class="row">
      <div class="col-12-xs">
        [gios_map]
      </div>
    </div>

_p.s. That's Bootstrap 3.3.7 there_

### Data
By default, the map will try to request its data, in JSON format, from a hard-coded API endpoint at: `https://researchmap.api.gios.asu.edu/v1/locations`. If you wanted to plot different data, you could:

* Hard-code your data into the jQuery Mapael object itself and comment out and/or delete the code that makes the AJAX call. This is, in fact, how all the examples on the Mapael page work, and is perfectly fine for smaller data sets.
* Change the Javascript to load the map data from a local file, and not from an AJAX call. This was how the map originally worked before we had an API server
* Create a new API endpoint for the data you want to show, and edit the main Javascript file accordingly.

### Details Template
The template we use to display the project details below the map can be found in the plugin code itself, in the `src\shortcodes\wp-gios-map-shortcodes.php` file, in a function called `add_mustache_template()`. If you ever need to modify the layout of the details area, you'll need to open that file, and edit the string found inside that function.

### Colors
The colors used on the map are set in both the `assets\css\styles.css` file and, because of the nature of jquery Mapael, the `gios\gios-map.js` file as well. Here's a breakdown of where you need to change certain things:

* **Countries/borders/bubbles** - `gios-map.js`. The elements that are actually parts of the SVG map are all controlled from the main Javascript file. We have put the most commonly changed elements near the top of the file, in a configuration object, so you don't need to hunt through the whole file to find them.
* **Water/map controls and title/tool-tips/details** - `styles.css`. Most everything around (and behind) the map is controlled by this CSS file. One special case is the water: the map **does not actually draw any of the oceans** - that's just background colors showing through. To change the color of the water, you would change the background color of the `#research-map` area that contains the map itself. If you want to set the entire page to match that background, you will also need to set the background color for `#content` to match `#research-map`.

/* Making the Map */
$( document ).ready( function() {

    /**************************************************************************
     * Configuration
     *
     * A configuration object to hold certain variables that I'm changing often
     * during development. I've added a few other variables as object properties
     * here, so that we have a global object we can access from multiple functions.
     *************************************************************************/
    var config = {
      // for the more/less links in article summaries
      "truncation": {
        "minimumRequired": 3,
        "showChar": 150,
        "ellipsestext": "...",
        "moretext": "[More]",
        "lesstext": "[Less]"
      },
      "colors": {
        // colors used by jquery-mapael when drawing the map
        "countries": "#EFF2EE",
        "countryHover": "#EFF2EE",
        "selectedCountryFill": "#0080FF",
        "borders": "#000",
        "bubbleFill": "#0080FF",
        "bubbleBorder": "#7F8E96",
        "bubbleHoverFill": "",
        "bubbleHoverBorder": "#0080FF",
        "textFill": "#FFF",
        "textBorder": "#000"
      },
      "zoom": {
        "detailMode": false
      },
      "dimensions": {
        // these are calculated by the afterInit() callback when the map is drawn
        // and are used to create the smaller 'page banner' sized map
        "initialHeight": 0,
        "smallHeight": 0,
        "scaleUp": false
      },
      "data": {
        "originalBubbles": {},
        "highlightedAreas": []
      },
      "images": {
        "sdgPath": "sdg-",
        "sdgFormat": ".png"
      },
      "title": {
        "default": "GIOS Research Map"
      }
    };

    /**************************************************************************
     * Drawing the Map
     *
     * jQuery-mapael initialization and configuration. Here we are setting the
     * default look/feel of the map, and - via the afterInit() callback - actually
     * plotting our "bubbles" on the map.
     *************************************************************************/
    $( '.map-container' ).mapael({
      map : {
        zoom: {
          enabled: true,
          touch: true,
          maxLevel: 30,
          animDuration: 500
        },
        name : 'world_countries_miller',
        cssClass: 'gios-research-map',
        defaultArea: {
          attrs: {
            stroke: config.colors.borders,
            fill: config.colors.countries,
            'stroke-width': 0.1
          },
          text: {
            content: "",
            attrs: {
              fill: config.colors.textFill,
              stroke: config.colors.textBorder,
              "stroke-width": 1,
              "font-size": 32.0,
            },
            position: "right"
          },
          attrsHover: {
            fill: config.colors.countries
          }
        },
        defaultPlot: {
          size: 15,
          attrs: {
            fill: config.colors.bubbleFill,
            'fill-opacity': 0.5,
            'stroke-width': 0
          },
          attrsHover: {
            fill: config.colors.bubbleHover,
            transform: "s1.25",
            animDuration: 100,
            "stroke-width": 2,
            stroke: config.colors.bubbleHoverBorder,
            'fill-opacity': 0.85
          },
          eventHandlers: {
            click: function( e, id, mapElem, textElem, elemOptions ){
              startDetailMode( elemOptions );
            }
          }
        },
        tooltip: {
          cssClass: "tool-tip",
          offset: {
            left: 0,
            top: 0
          }
        },
        afterInit: function() {

          // read the data file. On success, create tooltips, save the data, and update the map
          projectList = $.getJSON( 'https://researchmap.api.gios.asu.edu/v1/locations' ).done( function( response ) {
            $.each( response, function( key, value ) {
              value.tooltip = { content: value.name };
            });

          // save our original bubbles and then put them on the map
          config.data.originalBubbles = response;
          updateMap( config.data.originalBubbles );

          // replace the country name with the default title
          $( ".country-name" ).html( config.title.default);
          });
        }
      }
    });

    /**************************************************************************
     * Mapael Events/Hooks
     *
     * Event handlers and callbacks from the jQuery-Mapael object itself
     *************************************************************************/

    /**
     * Event Handler: afterZoom
     *
     * This is an event sent out every time the map's zoom level is changed,
     * whether by a user clicking buttons, or programmatically calling the
     * trigger( "zoom" ) method.
     */

    $( ".gios-research-map" ).on( "afterZoom", function( ) {
      if( config.zoom.detailMode ) {
        // if we just finished zooming in on a country, scale down the map container
        scaleDown();
      }else{
        if( config.dimensions.scaleUp ) {
          config.dimensions.scaleUp = false;
          scaleUp();
        }
      }
    });


    /**************************************************************************
     * Custom Events/Hooks
     *
     * Event handlers and callbacks we created ourselves.
     *************************************************************************/
    // zoom in button click
    $( ".map-zoom-in:not(map-control-disabled)" ).click( function() {
      $( ".gios-research-map" ).trigger( "zoom", { "level": "+5" } );
    });

    // zoom out button click
    $( ".map-zoom-out:not(map-control-disabled)" ).click( function() {
      $( ".gios-research-map" ).trigger( "zoom", { "level": "-5" } );
    });

    // reset zoom button click
    $( ".map-zoom-reset:not(map-control-disabled)" ).click( function() {
      resetZoom();
    });

    // details close button click
    $( ".details-close:not(map-control-disabled)" ).click( function() {
      $( '.details-close' ).addClass( "map-control-disabled" );
      endDetailMode();
    });

    /**************************************************************************
     * Custom Methods
     *
     * Methods we created to perform specific tasks that were not a part of
     * jQuery-Mapael
     *************************************************************************/

    /**
     * updateMap( object dataBubbles )
     *
     * Draws new plots (aka "bubbles") on the map, using a provided data object.
     */
    function updateMap( dataBubbles ) {
      // Updates just the "plots" (or "bubbles") on the map.
      $( ".gios-research-map" ).trigger( "update", [{
        "newPlots": dataBubbles,
        "animDuration": 500
      }]);
    }

    /**
     * clearAreas()
     *
     * To 'clear' any existing highlighted countries (aka 'areas'), we set the fill
     * back to the original color. To do this, we loop through our config.data.highlightedAreas
     * array (which will almost always hold only one item) and return it to the default
     * color. We also clear the country's on-screen text and call Mapael's update method.
     */
    function clearAreas() {
      // this will hold the new values for any country in the config.data.highlightedAreas array
      var updatedAreas = { areas: {} };

      // for each highlighted country, reset its settings
      config.data.highlightedAreas.forEach( function( item, index ) {
        updatedAreas.areas[ item ] = {
          attrs: {
            fill: config.colors.countries
          },
          attrsHover: {
            fill: config.colors.countries
          },
          text: {
            content: ""
          }
        };
      });

      // trigger an update to the map, passing our re-colored area(s)
      $( '.gios-research-map' ).trigger( 'update', [{
        mapOptions: updatedAreas,
        animDuration: 250
      }]);
    }


    /**
     * scaleUp()
     *
     * Returns the map SVG, and its container, to the original size
     */
    function scaleUp() {
      $( '.gios-research-map, .gios-research-map > svg' ).animate(
        { "height": config.dimensions.initialHeight+"px" },
        500,
        function() {
            $( ".overlay").fadeOut( "slow", function() {
              $( '[class*="map-zoom"]' ).removeClass( 'map-control-disabled' );
          });
        }
      );
    }

    /**
     * scaleDown()
     *
     * Scales down the SVG, and its container, into a header-like view for detail mode.
     */
    function scaleDown() {

      // save our SVG height, and the half-size measurement for later
      config.dimensions.initialHeight = $( '.gios-research-map > svg' ).height();
      config.dimensions.smallHeight = Math.floor( config.dimensions.initialHeight * 0.4 );

      $( '#research-map, #research-map > svg' ).animate(
        { "height": config.dimensions.smallHeight+"px" },
        500,
        function() {
          $( ".overlay").fadeIn( "slow", function() {
            $( '[class*="map-zoom"]' ).addClass( 'map-control-disabled' );
            $( '.details-close' ).removeClass( "map-control-disabled" );
          });
        });
    }

    /**
     * resetZoom()
     *
     * Resets the map zoom to the initial state. This will send the afterZoom
     * event, which we are listening for.
     */
    function resetZoom() {
      // return the map to the default zoom
      $( '.gios-research-map' ).trigger( 'zoom', {
        level: 0,
        longitude: 0,
        latitude: 0
      });
    }

    /**
     * clearDetails()
     *
     * Cleans up the details area by fading it out of view, and then deleting the HTML completely.
     */
    function clearDetails() {
      $( "#details" ).fadeOut( "slow" ).empty();
    }

    /**
     * clearBubbles()
     *
     * Removes all bubbles from the map using the jquery-mapael keyword 'all' in the list to be
     * deleted.
     */
    function clearBubbles() {
      // do stuff to clear all existing bubbles
      $( '.gios-research-map' ).trigger( 'update', {
        deletePlotKeys: 'all'
      });
    }

    /**
     * resetBubbles()
     *
     * We store the original bubble data from the JSON file at the time we read it.
     * This method updates the map with those original plots.
     */
    function resetBubbles() {
      // return map to its original bubbles
      $( '.gios-research-map' ).trigger( 'update', [{
        "newPlots": config.data.originalBubbles,
        "animDuration": 250
      }]);
    }

    /**
     * startDetailMode ( elemOptions )
     *
     * Puts us into detail mode - where the map is zoomed in on the selected country,
     * and scaled down to "header size". The "elemOptions" are the various jQuery-Mapael
     * properties of the plot/bubble that was selected.
     *
     * This method is a hot mess, but
     */
    function startDetailMode( elemOptions ) {
      // get the jquery-mapael info for the selected bubble
      var iso_code = elemOptions.iso;

      // set our internal detailMode variable
      config.zoom.detailMode = true;

      // get the lat/long of the selected bubble from the object the map gave us
      var zoomLat = elemOptions.latitude;
      var zoomLong = elemOptions.longitude;

      // set a default zoom level, for when the data file does not provide one
      var zoomLevel = 15.0;

      // push the id (which in our data file is the ISO 2-letter country code) onto our array
      config.data.highlightedAreas.push( iso_code );

      /**
       * create an object to hold the properties of this area (AKA country). We set the fill
       * color for both normal and hover modes to our default country highlight color
       */
      updatedOptions = { areas: {} };
      updatedOptions.areas[ iso_code ] = {
        attrs: {
          fill: config.colors.selectedCountryFill
        },
        attrsHover: {
          fill: config.colors.selectedCountryFill
        }
      };

      /**
       * Update the map, passing in our new options. This will color the selected country. We
       * are also telling the map to delete all the bubbles by using 'all' as the key for
       * plots to delete.
       */
      $( '.gios-research-map' ).trigger( 'update', [{
        mapOptions: updatedOptions,
        deletePlotKeys: 'all',
        animDuration: 100,
        afterUpdate: function() {
        }
      }]);

      // fade out any tool-tips on screen
      $( '.tool-tip' ).fadeOut();

      // if there is a zoom level in the data (from our file), use it to replace
      // our default - UNLESS it is zero
      if( typeof( elemOptions.zoom ) != 'undefined' &&  elemOptions.zoom > 0 ) {
        zoomLevel = elemOptions.zoom;
      }

      // tell the map to zoom in

      $( '.gios-research-map' ).trigger( 'zoom', {
        level: zoomLevel,
        longitude: zoomLong,
        latitude: zoomLat
      });

      // grab our template file from the page header, and our projects from the country
      // object the map gave us.
      var template = $( '#template' ).html();
      var projects = elemOptions.projects;

      // loop through the projects and build the image link
      $.each( projects, function( key, value ) {
        value.sdg = wpUrls.img_path + "/" + config.images.sdgPath + value.sdg + config.images.sdgFormat;
      });

      // Configure and render our template
      var data = { "country": elemOptions.name, "items": elemOptions.projects };
      Mustache.parse( template );
      var rendered = Mustache.render( template, data );
      $( '#details' ).html( rendered );

      /**
       * Truncate the project descriptions. Each description has a default class of '.more' in
       * the template. We loop through all those and truncate if needed, adding the classes needed
       * to show/hide text.
       */

      if( $( ".more" ).length  >= config.truncation.minimumRequired ) {
        $('.more').each(function() {
          // get the text
          var content = $(this).html();

          // if it's longer than the showChar limit, split it into two pieces
          if(content.length > config.truncation.showChar) {
            var c = content.substr(0, config.truncation.showChar);
            var h = content.substr(config.truncation.showChar, content.length - config.truncation.showChar);

            // combine the two pieces into the structure we need for hiding/showing
            var html = c + '<span class="moreellipses">' + config.truncation.ellipsestext+ '&nbsp;</span><span class="morecontent"><span>' + h + '</span>&nbsp;&nbsp;<a href="#" class="morelink">' + config.truncation.moretext + '</a></span>';

            // save the modified HTML
            $(this).html(html);
          }
        });
      }

      // show the details
      $( '.country-name' ).html( elemOptions.name );
      $( '#details' ).show();

    }

    function endDetailMode() {

      // disable the zoom buttons (except for the reset button)
      // $( '[class*="map-zoom"]' ).removeClass( 'map-control-disabled' );
      // $( '.details-close' ).addClass( "map-control-disabled" );

      config.zoom.detailMode = false;
      config.dimensions.scaleUp = true;
      $( ".country-name" ).html( config.title.default );

      $( ".overlay" ).fadeOut( "slow" );
      resetBubbles();
      clearAreas();
      clearDetails();
      resetZoom();
    }

    /**
     * Handles showing/hiding text when the more/less links are clicked. This is copy/paste from a JSFiddle,
     * and we might be able to improve on it. Essentially swaps out two blocks of text (the 'less' and 'more'
     * versions) when you click links.
     *
     * Note: we've attached the listener to the #details <div> itself because the content changes/disappears
     * all the time, but the <div> is always there. The event bubbles up to the .moreLink class on the paragraphs
     * to do the truncation work.
     */
    $("#details").on( "click", ".morelink", function() {

      if($(this).hasClass("less")) {
          $(this).removeClass("less");
          $(this).html(config.truncation.moretext);
      } else {
          $(this).addClass("less");
          $(this).html(config.truncation.lesstext);
      }

      // toggles the shortened view off, and the longer view on
      $(this).parent().prev().toggle();
      $(this).prev().toggle();

      return false;
    });


});
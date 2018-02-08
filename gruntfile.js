/**
 * gruntfile.js
 *
 */

module.exports = function( grunt ) {
  // configuration
  grunt.initConfig({
    pkg: grunt.file.readJSON( 'package.json' ),
    jshint: {
      gruntfile: [ 'gruntfile.js' ],
      map: [ 'gios/gios-map.js' ]
    },
    uglify: {
      build: {
        src: 'gios/gios-map.js',
        dest: 'assets/js/gios-map.min.js'
      }
    },
    csslint: {
      src: 'assets/css/gios-map-styles.css'
    },
    // Watching files for changes
    watch: {
      gruntfile: {
        // changes to the gruntfile make us lint the gruntfile itself
        files: [ 'gruntfile.js' ],
        tasks: [ 'jshint:gruntfile' ]
      },
      map: {
        // updating the gios-map.js file lints, minifies, and copies to /assets/js
        files: [ 'gios/gios-map.js' ],
        tasks: [ 'jshint:map', 'uglify' ]
      },
      css: {
        // changes to the CSS file triggers linting
        files: [ 'assets/css/gios-map-styles.css' ],
        tasks: [ 'csslint' ]
      }
    }
  });

  // load modules
  grunt.loadNpmTasks( 'grunt-contrib-jshint' );
  grunt.loadNpmTasks( 'grunt-contrib-uglify' );
  grunt.loadNpmTasks( 'grunt-contrib-copy' );
  grunt.loadNpmTasks( 'grunt-contrib-watch' );
  grunt.loadNpmTasks( 'grunt-contrib-csslint' );

  // define tasks
  grunt.registerTask( 'default', [ 'jshint', 'csslint' ] );
  grunt.registerTask( 'build', [ 'uglify' ] );
};

module.exports = function( grunt ) {
  // configuration
  grunt.initConfig({
    pkg: grunt.file.readJSON( 'package.json' ),
    jshint: {
      all: [ 'Gruntfile.js', 'gios/gios-map.js' ]
    },
    uglify: {
      build: {
        /* Remember, this is a minify AND COPY step */
        src: 'gios/gios-map.js',
        dest: 'assets/js/gios-map.min.js'
      }
    },
    csslint: {
      src: 'assets/css/gios-map-styles.css'
    },
    /*
    copy: {
        files: [
          {
            expand: true,
            cwd: 'gios',
            src: [ 'gios-map.js' ],
            dest: 'assets/js',
            filter: 'isFile'
          }
        ]
    },
    */
    watch: {
      js: {
        files: [ 'gios/gios-map.js', 'gruntfile.js' ],
        tasks: [ 'jshint', 'uglify:build']
      },
      css: {
        files: [ 'assets/css/gios-map-styles.css' ],
        tasks: [ 'csslint:dev' ]
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

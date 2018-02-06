module.exports = function( grunt ) {
  // configuration
  grunt.initConfig({
    pkg: grunt.file.readJSON( 'package.json' ),
    jshint: {
      dev: [ 'Gruntfile.js', 'gios/gios-map.js' ]
    },
    uglify: {
      options: {},
      build: {
        /* Remember, this is a minify AND COPY step */
        src: 'gios/gios-map.js',
        dest: 'assets/js/gios-map.min.js'
      }
    },
    csslint: {
      dev: {
        src: 'assets/css/styles.css'
      }
    },
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
    watch: {
      js: {
        files: [ 'gios/gios-map.js', 'gruntfile.js' ],
        tasks: [ 'jshint', 'uglify:build']
      },
      css: {
        files: [ 'src/css/styles.css' ],
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
  grunt.registerTask( 'default', [ 'jshint:dev', 'csslint:dev' ] );
  grunt.registerTask( 'build', [ 'uglify', 'copy:build' ] );
};

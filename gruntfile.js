module.exports = function( grunt ) {
  // configuration
  grunt.initConfig({
    pkg: grunt.file.readJSON( 'package.json' ),
    jshint: {
      dev: [ 'Gruntfile.js', 'src/js/gios-map.js' ]
    },
    uglify: {
      options: {},
      build: {
        /* Remember, this is a minify AND COPY step */
        src: 'src/js/<%= pkg.name %>.js',
        dest: 'src/js/<%= pkg.name %>.min.js'
      }
    },
    csslint: {
      dev: {
        src: 'src/css/styles.css'
      }
    },
    copy: {
      deps: {
        files: [
          {
            expand: true,
            cwd: 'node_modules/jquery/dist',
            src: 'jquery.min.js',
            dest: 'src/js',
            filter: 'isFile'
          },
          {
            expand: true,
            cwd: 'node_modules/raphael',
            src: 'raphael.min.js',
            dest: 'src/js',
            filter: 'isFile'
          },
          {
            expand: true,
            cwd: 'node_modules/jquery-mapael/js',
            src: 'jquery.mapael.min.js',
            dest: 'src/js',
            filter: 'isFile'
          },
          {
            expand: true,
            cwd: 'node_modules/jquery-mapael/js/maps',
            src: 'world_countries_miller.min.js',
            dest: 'src/js',
            filter: 'isFile'
          },
          {
            expand: true,
            cwd: 'node_modules/bulma/css',
            src: [ 'bulma.css', 'bulma.css.map' ],
            dest: 'src/css',
            filter: 'isFile'
          },
          {
            expand: true,
            cwd: 'node_modules/mustache',
            src: 'mustache.min.js',
            dest: 'src/js',
            filter: 'isFile'
          }
        ]
      },
      build: {
        files: [
          {
            expand: true,
            cwd: 'src',
            src: 'index.html',
            dest: 'dist',
            filter: 'isFile'
          },
          {
            expand: true,
            cwd: 'src/css',
            src: [ '*.css', '*.map' ],
            dest: 'dist/css',
            filter: 'isFile'
          },
          {
            expand: true,
            cwd: 'src/js',
            src: [ '*.min.js' ],
            dest: 'dist/js',
            filter: 'isFile'
          },
          {
            expand: true,
            cwd: 'src/data',
            src: '*.json',
            dest: 'dist/data',
            filter: 'isFile'
          },
          {
            expand: true,
            cwd: 'src/img',
            src: '*.png',
            dest: 'dist/img',
            filter: 'isFile'
          }
        ]
      }
    },
    rsync: {
      options: {
        args: ["--verbose"],
        ssh: true,
        recursive: true
      },
      dev: {
        options: {
          src: "dist/",
          dest: "ubuntu@52.41.34.193:/home/ubuntu/gios-map"
        }
      }
    },
    watch: {
      js: {
        files: [ 'src/js/gios-map.js', 'gruntfile.js' ],
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
  grunt.loadNpmTasks( 'grunt-rsync' );

  // define tasks
  grunt.registerTask( 'default', [ 'jshint:dev', 'csslint:dev' ] );
  grunt.registerTask( 'deps', [ 'copy:deps' ] );
  grunt.registerTask( 'build', [ 'uglify', 'copy:build' ] );
};
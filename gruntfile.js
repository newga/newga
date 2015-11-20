module.exports = function(grunt) {

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    sass: {
      css: {
        files: {
          'public_html/css/style.css': 'public_html/css/style.scss'
        }
      }
    },
    watch: {
        css: {
            files: ['public_html/css/*.scss'],
            tasks: ['sass'],
            options: {
                debounceDelay: 100,
                livereload: true
            }
        } 
    },
  });

  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-sass');
  
};
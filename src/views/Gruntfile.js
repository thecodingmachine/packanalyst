module.exports = function(grunt) {
    require('load-grunt-tasks')(grunt);
    require('time-grunt')(grunt);

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        less: {
            dev: {
                files: {
                    'css/styles.css': [
                        'src/less/style.less'
                    ]
                }
            },
            build: {
                files: {
                    'css/styles.min.css': [
                        'src/less/style.less'
                    ]
                },
                options: {
                    compress: true
                }
            }
        },
        watch: {
            less: {
                tasks: ['less:dev', 'autoprefixer:dev']
            }
        }
    });


    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', [
        'dev'
    ]);

    grunt.registerTask('dev', [
        'less:dev'
    ]);

    grunt.registerTask('build', [
        'less:build'
    ]);
};
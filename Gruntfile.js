/**
 * Gruntfile
 */

module.exports = function (grunt) {
    
    // Grunt configuration
    var config = {
        pkg: grunt.file.readJSON('package.json'),

        makepot: {
            target: {
                options: {
                    type: 'wp-plugin',
                    domainPath: '/languages'
                }
            }
        },

        copy: {
            main: {
                expand: true,
                src: [
                    '**',
                    '!.*',
                    '!.git/**',
                    '!README.md',
                    '!node_modules/**',
                    '!package.json',
                    '!package-lock.json',
                    '!Gruntfile.js',
                    '!composer.json',
                    '!composer.lock',
                    '!vendor/**',
                ],
                dest: './build'
            }
        },

        clean: ['./build']
    };

    //init grunt
    grunt.initConfig(config);

    //Load Tasks
    grunt.loadNpmTasks('grunt-wp-i18n');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-clean');

    //Register Tasks
    grunt.registerTask('default', ['makepot']);
    grunt.registerTask('build', ['default', 'clean', 'copy']);
};
module.exports = function (grunt) {
    grunt.initConfig({
        'preprocess' : {
            'mdr' : {
                files: {
                    'js/Mdr/Pages/lib/Mdr.Pages.js' : 'js/Mdr/Pages/src/build/Mdr.Pages.js',
                    'js/Mdr/Pages/Otc/lib/Mdr.Pages.Otc.js' : 'js/Mdr/Pages/Otc/src/build/Mdr.Pages.Otc.js',
                    'js/Mdr/Components/lib/Mdr.Components.js' : 'js/Mdr/Components/src/build/Mdr.Components.js',
                    'js/Mdr/Components/ImageCapture/lib/Mdr.Components.ImageCapture.js' : 'js/Mdr/Components/ImageCapture/src/build/Mdr.Components.ImageCapture.js',
                    'js/Mdr/Components/Messages/lib/Mdr.Components.Messages.js' : 'js/Mdr/Components/Messages/src/build/Mdr.Components.Messages.js'
                }
            }
        },

        'compile-handlebars' : {
            'otc-message-bus': {
                'template': 'js/events/messageBusTemplates/base.mustache',
                'partials' : 'js/events/messageBusTemplates/*.mustache',
                'templateData': ['js/events/otc-events.json'],
                'output' : ['www/scripts/Otc/OtcMessageBus.js'],
                'helpers': 'js/events/helpers/*.js'
            },
            'awaiting-exit-message-bus': {
                'template': 'js/events/messageBusTemplates/base.mustache',
                'partials' : 'js/events/messageBusTemplates/*.mustache',
                'templateData': ['js/events/awaiting-exit.json'],
                'output' : ['www/scripts/AwaitingExit/MessageBus.js'],
                'helpers': 'js/events/helpers/*.js'
            }
        },
        'less': {
            development: {
                options: {
                    paths: ["less/minder/source"]
                },
                files: [
                    {
                        expand: true,     // Enable dynamic expansion.
                        cwd: 'less/minder/themes/',      // Src matches are relative to this path.
                        src: ['*.less'], // Actual pattern(s) to match.
                        dest: 'www/style/themes/',   // Destination path prefix.
                        ext: '.css'   // Dest filepaths will have this extension.
                    }
                ]
            }
        },
        copy: {
            images: {
                files: [
                    {
                        expand: true,     // Enable dynamic expansion.
                        cwd: 'less/minder/source/',      // Src matches are relative to this path.
                        src: ['**/*.{png,jpg,gif}'], // Actual pattern(s) to match.
                        dest: 'www/style/themes/'   // Destination path prefix.
                    },
                    {
                        expand: true,     // Enable dynamic expansion.
                        cwd: 'less/minder/themes/',      // Src matches are relative to this path.
                        src: ['**/*.{png,jpg,gif}'], // Actual pattern(s) to match.
                        dest: 'www/style/themes/'   // Destination path prefix.
                    }
                ]
            },

            'mdr': {
                expand: true,
                flatten: true,
                src: 'js/Mdr/**/lib/*.js',
                dest: 'www/scripts/Mdr/'
            }
        },

        babel: {
            options: {
                modules: 'amd',
                sourceMap: true,
                ignore: [
                    '**/config.js',
                    '**/libs/*.js'
                ]
            },
            dist: {
                files: [
                    {
                        expand: true,
                        cwd: 'www/scripts/MinderNG/dev',
                        src: '**/*.js',
                        dest: 'www/scripts/MinderNG/tmp'
                    }
                ]
            }
        },

        requirejs: {
            app: {
                options: {
                    name: 'config',
                    baseUrl: './www/scripts/MinderNG/tmp',
                    out: 'www/scripts/MinderNG/build/config.js',
                    mainConfigFile: 'www/scripts/MinderNG/tmp/config.js',
                    removeCombined: true,
                    optimize: 'uglify2',
                    generateSourceMaps: true,
                    preserveLicenseComments: false,
                    findNestedDependencies: true,
                    useStrict: true
                }
            }
        }

    });

    grunt.loadNpmTasks('grunt-preprocess');
    grunt.loadNpmTasks('grunt-compile-handlebars');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-requirejs');
    grunt.loadNpmTasks('grunt-babel');

    grunt.registerTask('default', ['less', 'copy']);
    grunt.registerTask('MinderNG', ['requirejs']);
    grunt.registerTask('mdr', ['preprocess:mdr', 'copy:mdr']);
};
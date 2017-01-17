module.exports = function (grunt) {
    var pkgjson = require('./package.json');

    var config = {
        pkg: pkgjson,
        app: 'src',
        dist: 'dist'
    }

    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-image-resize');
    grunt.loadNpmTasks('grunt-image');

    var jpegRecompress = require('imagemin-jpeg-recompress');

    grunt.initConfig({
        config: config,
        pkg: config.pkg,

        image_resize: {
            stock: {
                options: {
                    width: 1920,
                    height: 1080,
                    overwrite: true
                },
                expand: true,
                cwd: 'public/stock/',
                src: '**/**/**/*.jpg',
                dest: 'public/stock/'
            }
        },

        imagemin: {
            png: {
                options: {
                    optimizationLevel: 7
                },
                files: [{
                    // Set to true to enable the following options…
                    expand: true,
                    // cwd is 'current working directory'
                    cwd: 'public/stock/',
                    src: ['**/**/**/*.png'],
                    // Could also match cwd line above. i.e. project-directory/img/
                    dest: 'public/stock/',
                    ext: '.png'
                }]
            },
            jpg: {
                options: {
                    progressive: true
                },
                files: [{
                    // Set to true to enable the following options…
                    expand: true,
                    // cwd is 'current working directory'
                    cwd: 'public/stock/',
                    src: ['**/**/**/*.jpg'],
                    // Could also match cwd. i.e. project-directory/img/
                    dest: 'public/stock/',
                    ext: '.jpg'
                }]
            }
        },

        image: {
          optimize: {
              options: {
                  pngquant: true,
                  optipng: true,
                  advpng: true,
                  zopflipng: true,
                  pngcrush: true,
                  pngout: true,
                  mozjpeg: true,
                  jpegRecompress: true,
                  jpegoptim: true,
                  gifsicle: true,
                  svgo: true
                },
            files: [{
                expand: true,
                cwd: 'public/stock/',
                src:['**/**/**/*.{png,jpg,gif}'],
                dest: 'public/stock/'
            }]
          }
        }

    });
    grunt.registerTask('default', [
        'image_resize',
		'imagemin',
		'image'
	]);
}

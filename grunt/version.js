/* jshint node:true */
//https://github.com/kswedberg/grunt-version
module.exports = {
    options: {
        pkg: {
            version: '<%= package.version %>'
        }
    },
    project: {
        src: [
            'package.json'
        ]
    },
    style: {
        options: {
            prefix: 'Version\\:\\s'
        },
        src: [
            'index.php',
            'css/media.css',
        ]
    },
    functions: {
        options: {
            prefix: 'VERSION\\s+=\\s+[\'"]'
        },
        src: [
            'classes/Visualizer/Plugin.php',
        ]
    }
};
/**
* @package     BlueAcorn/GreenPistachio
* @version     4.5.0
* @author      Blue Acorn, Inc. <code@blueacorn.com>
* @copyright   Copyright © 2016 Blue Acorn, Inc.
*/

'use strict';

var combo  = require('./combo'),
    themes = require('./themes'),
    _      = require('underscore');

var themeOptions = {};

_.each(themes, function(theme, name) {
    if(theme.grunt) {
        themeOptions[name + 'App'] = {
            files: [
                combo.themeFallbackApp(name)
            ],
            tasks: ['shell:cache']
        };

        themeOptions[name + 'Sass'] = {
            files: [
                combo.themeFallbackSass(name)
            ],
            tasks: ['sass:' + name + 'Dev', 'postcss:' + name + 'Dev'],
            options: {
                sourceMap: true
            }
        };

        themeOptions[name + 'Images'] = {
            files: [
                combo.autopath(name, 'skin') + 'src/**/*.{svg,png,gif,jpg}',
            ],
            tasks: ['concurrent:' + name + 'Images']
        };

        themeOptions[name + 'Js'] = {
            files: combo.themeFallbackJs(name),
            tasks: combo.themeTasksJs(name)
        };

        themeOptions[name + 'Livereload'] = {
            files: [
                combo.autopath(name, 'skin') + 'css/**/*.css'
            ],
            options: {
                livereload: true,
                sourceMap: true
            }
        };

    }
});

var watchOptions = {

};

module.exports = _.extend(themeOptions, watchOptions);

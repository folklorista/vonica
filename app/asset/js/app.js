/*global require*/
'use strict';

global._ = require('lodash');

/**
 * @ngdoc overview
 * @name vonica
 * @description
 * # vonica
 *
 * Main module of the application.
 */
var angular = require('angular');
require('./core');
require('./layout');
require('./reservation');

angular.module('vonica', [
    /*
     * Na pořadí nezáleží.
     */
    /*
     * Jádro aplikace
     */
    'vonica.core',

    /*
     * Moduly aplikace
     */
    'vonica.layout',
    'vonica.reservation'
]);

require('./bootstrap');

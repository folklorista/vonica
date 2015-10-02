'use strict';

require('restangular');
require('angular-bootstrap');
require('angular-translate');
require('oclazyload');
require('./partials');

var coreModule = require('angular').module('vonica.core', [
    require('angular-cookies'),
		'restangular',
		require('angular-ui-router'),
		require('angular-formly'),
    require('angular-formly-templates-bootstrap'),
		'ui.bootstrap',
		'oc.lazyLoad',
		'pascalprecht.translate',

    'partialsModule',
]);

angular.module("pascalprecht.translate").factory("$translateStaticFilesLoader", ["$q", "$http", function (a, b) {
	return function (c) {
	    if (!c || !angular.isString(c.prefix) || !angular.isString(c.suffix))
		throw new Error("Couldn't load static files, no prefix or suffix specified!");
	    var d = a.defer();
	    return b({url: [c.prefix, c.key, c.suffix].join(""), method: "GET", params: ""}).success(function (a) {
		d.resolve(a)
	    }).error(function () {
		d.reject(c.key)
	    }), d.promise
	}
}]);


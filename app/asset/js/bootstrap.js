'use strict';

var mainModule = require('angular').module('vonica');

mainModule.run(mainRun);

mainRun.$inject = ['$rootScope', '$state', 'formlyConfig', 'Restangular'];
function mainRun($rootScope, $state, formlyConfig, Restangular)
{

/**
 * Konfigurace základní url pro Restangular a formát příchozích dat
 */
mainModule.config(restangularConfig);

restangularConfig.$inject = ['RestangularProvider'];
function restangularConfig(RestangularProvider) {
	RestangularProvider.setBaseUrl('/api/v1/');

	// add a response intereceptor https://github.com/mgonto/restangular#my-response-is-actually-wrapped-with-some-metadata-how-do-i-get-the-data-in-that-case
	RestangularProvider.addResponseInterceptor(function (data, operation, what, url, response, deferred) {
		return data.data;
	});
}

/**
 * Konfigurace formly
 */
mainModule.config(formlyConfig);
formlyConfig.$inject = ['formlyConfigProvider'];
function formlyConfig(formlyConfigProvider) {
}

mainModule.config(languageConfig);
function languageConfig($translateProvider) {
	// Sanitize - https://angular-translate.github.io/docs/#/guide/19_security
	$translateProvider.useStaticFilesLoader({
		prefix: 'languages/translate-',
		suffix: '.json'
	}).fallbackLanguage('cz');
	$translateProvider.useSanitizeValueStrategy('escape');
}

mainModule.run(useTranslate);
routeConfig.$inject = ['$translate','localStorageService'];
function useTranslate($translate, localStorageService) {
	var lang = localStorageService.get("language");
	if(lang == null) {
		lang = 'cz';
	}
	$translate.use(lang);
}


/**
 * Route config
 */
mainModule.config(routeConfig);

routeConfig.$inject = ['$stateProvider', '$urlRouterProvider', '$ocLazyLoadProvider'];
function routeConfig($stateProvider, $urlRouterProvider, $ocLazyLoadProvider) {
	//$urlRouterProvider.otherwise('/app/reservation');
	//fix problem with unauthorized redirect in ui-router cause inifinite loop
	$urlRouterProvider.otherwise(function ($injector, $location) {
		var $state = $injector.get('$state');
		$state.go('app.reservation');
	});

	$stateProvider.
		// Main Layout Structure
		state('app', {
			abstract: true,
			url: '/',
			templateUrl: appHelper.templatePath('layout/app-body'),
			data: {
			},
			controller: function ($rootScope) {
				$rootScope.isMainPage = true;
			}
		})
	}
}

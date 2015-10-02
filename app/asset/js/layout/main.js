'use strict'

var layoutModule = require('angular').module('vonica.layout');
layoutModule.controller('MainCtrl', MainCtrl);

MainCtrl.$inject = ['$q', '$scope', '$rootScope', '$location', '$translate', '$state'];
function MainCtrl($q, $scope, $rootScope, $location, $translate, $state) {
	//var currentUser = Auth.getCurrentUser();

	console.debug($scope);
	console.debug($rootScope);
	console.debug($location);
	$rootScope.isMainPage = true;


	$scope.updateLanguage = function (lang)
	{
		$rootScope.$broadcast('changeLanguage', lang);
		$translate.use(lang);
		localStorageService.set("language", lang);
	}
	// Define Public Vars
	public_vars.$body = jQuery("body");
}

/**
 * Traverse tree until node containing the attr (used for find 'vm' object)
 * @param {type} node
 * @param {type} attr
 * @returns {OrderFormService.climb.node|Boolean}
 * @author skubal@b2a.cz
 */
function climb(node, attr) {
    if (attr in node) {
	return node[attr];
    } else if (node.$child === null) {
	return false;
    } else {
	return climb(node.$child, attr);
    }
}
;
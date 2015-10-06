/*global reservationRoute, require, appHelper*/
var reservationModule = require('angular').module('vonica.reservation');
reservationModule.config(reservationRoute);

reservationRoute.$inject = ['$stateProvider'];
function reservationRoute($stateProvider) {
	'use strict';

	$stateProvider.
		state('app.reservation', {
			url: '/',
			templateUrl: appHelper.templatePath('reservation/template/reservation'),
			controller: 'Reservation as vm',
			resolve: {
				deps: ['$ocLazyLoad', function ($ocLazyLoad) {
					return $ocLazyLoad.load([
					]);
				}]
			}
		}).

		state('app.reservation-list', {
			url: '/reservation-list',
			templateUrl: appHelper.templatePath('reservation/template/reservation.list'),
			controller: 'ReservationList as vm',
			resolve: {
				deps: ['$ocLazyLoad', function ($ocLazyLoad) {
					return $ocLazyLoad.load([
					]);
				}]
			}
		}).

		state('app.reservation-new', {
			url: '/reservation-new',
			templateUrl: appHelper.templatePath('reservation/template/reservation.detail'),
			controller: 'ReservationDetail as vm',
			resolve: {
				reservation: ['$q', function ($q) {
					return $q.when([]);
				}]
			}
		});
}
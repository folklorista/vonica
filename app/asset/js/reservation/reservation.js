/*global Reservation, require, angular, FormData*/
var reservationModule = require('angular').module('vonica.reservation');

reservationModule.controller('Reservation', Reservation);

/**
 * Reservation Controller
 * @type {string[]}
 */
Reservation.$inject = [
	'$state',
	'$stateParams',
	'$q',
	'$translate',
	'$scope',
	'Restangular'
];
function Reservation($state, $stateParams, $q, $translate, $scope, Restangular) {
	'use strict';

	/*jshint validthis: true */
	var vm = this;

	$scope.seats = [];

	// Set reserved and selected
	var reserved = [];
	$scope.selected = [];
	$scope.selected_count = 0;
	$scope.user = {
		'firstname': '',
		'lastname': '',
		'email': '@',
		'phone': '+420',
		'note': '',
		'seats': []
	};

	activate();

	/**
	 * Active controller
	 * @returns {Promise|*}
	 */
	function activate() {
		var promises = [getSeats(), getReserved()];
		return $q.all(promises).then(function () {
		});
	}

	/**
	 * Retrieve seats
	 * @returns {Promise}
	 */
	function getSeats() {
		return Restangular.all('api').all('v1').all('seat').getList({}).then(function(data) {
			$scope.seats = data;
			console.debug($scope.seats);
			return $scope.seats;
		}, function (data) {
			console.error('Nepodařilo se nahrát seznam sedadel');
		});
	}

	/**
	 * Retrieve reservations
	 * @returns {Promise}
	 */
	function getReserved() {
		return Restangular.all('api').all('v1').all('seat').all('reserved').getList({}).then(function(data) {
			angular.forEach(data, function(foo) {
				reserved.push(foo.id);
			});
			return reserved;
		}, function (data) {
			console.error('Nepodařilo se nahrát seznam rezervací');
		});
	}

	// seat onClick
	$scope.seatClicked = function(seatPos) {
		if ('reserved' == $scope.getStatus(seatPos)) {
			return false;
		}
		console.log("Selected Seat: " + seatPos);
		var index = $scope.selected.indexOf(seatPos);
		if(index != -1) {
				// seat already selected, remove
				$scope.selected.splice(index, 1)
		} else {
				// new seat, push
				$scope.selected.push(seatPos);
		}
		$scope.selected_count = $scope.selected.length;
	}

	// get seat status
	$scope.getStatus = function(seatPos) {
			if(reserved.indexOf(seatPos) > -1) {
					return 'reserved';
			} else if($scope.selected.indexOf(seatPos) > -1) {
					return 'selected';
			}
	}

	// clear selected
	$scope.clearSelected = function() {
			$scope.selected = [];
			$scope.selected_count = 0;
	}

	// show selected
	$scope.showSelected = function() {
			if($scope.selected.length > 0) {
					console.log("Selected Seats: \n" + $scope.selected);
					$http.post('/api/v1/reservation', $scope.user, {
						withCredentials: true,
						headers: {'Content-Type': 'application/json' },
						transformRequest: angular.identity
					}).then(function (data) {
						console.debug(data);
					}, function (data) {
						console.error(data);
					});
			} else {
					console.log("No seats selected!");
			}
	}
};

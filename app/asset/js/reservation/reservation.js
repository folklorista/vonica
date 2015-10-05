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
	'$http',
	'Restangular'
];
function Reservation($state, $stateParams, $q, $translate, $scope, $http, Restangular) {
	'use strict';

	/*jshint validthis: true */
	var vm = this;

	$scope.seats = [];

	// Set reserved and selected
	var reserved = [];
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
				reserved[foo.id] = foo.type;
			});
			return reserved;
		}, function (data) {
			windows.alert('Nepodařilo se nahrát seznam rezervací, zkuste prosím stránku obnovit stiskem klávesy F5');
		});
	}

	// seat onClick
	$scope.seatClicked = function(seatPos) {
		if (false !== $scope.getStatus(seatPos) && 'selected' !== $scope.getStatus(seatPos)) {
			return false;
		}
		var index = $scope.user.seats.indexOf(seatPos);
		if(index != -1) {
				// seat already selected, remove
				$scope.user.seats.splice(index, 1)
		} else {
				// new seat, push
				$scope.user.seats.push(seatPos);
		}
		$scope.selected_count = $scope.user.seats.length;
	}

	// get seat status
	$scope.getStatus = function(seatPos) {
			if(seatPos in reserved) {
				return reserved[seatPos];
			} else if($scope.user.seats.indexOf(seatPos) > -1) {
					return 'selected';
			} else {
				return false;
			}
	}

	// clear selected
	$scope.clearSelected = function() {
			$scope.user.seats = [];
			$scope.selected_count = 0;
	}

	// show selected
	$scope.showSelected = function() {
			if($scope.user.seats.length > 0) {
				$http.post('/api/v1/reservation', $scope.user, {
					headers: {'Content-Type': 'application/json' }
				}).then(function (data) {
					window.alert('Rezervace byla úspěšně vyřízena! Rezervace je platná 5 dní');
					getReserved();
				}, function (data) {
					window.alert('Rezervace se nezdařila, některá místa byla zřejmě zarezervována už jinou osobou.');
					$q.all([getReserved()]).then(function () {
						var selected = angular.copy($scope.user.seats);
						angular.forEach(selected, function(seatPos) {
							if (false !== $scope.getStatus(seatPos) && 'selected' !== $scope.getStatus(seatPos)) {
								// seat already selected, remove
								var index = $scope.user.seats.indexOf(seatPos);
								if(index != -1) {						
									// seat already selected, remove
									$scope.user.seats.splice(index, 1)
								}
							}
						});
					});					
				});
			} else {
				window.alert('Rezervace se nezdařila, protože systém!');
			}
	}
};

/*global Reservation, require, angular*/
var reservationModule = require('angular').module('vonica.reservation');

reservationModule.controller('ReservationList', ReservationList);

/**
 * Reservation Controller
 * @type {string[]}
 */
ReservationList.$inject = [
	'$state',
	'$stateParams',
	'$q',
	'$scope',
	'$http',
	'Restangular',
	'$location'
];
function ReservationList($state, $stateParams, $q, $scope, $http, Restangular, $location) {
	'use strict';

	/*jshint validthis: true */
	var vm = this;

	$scope.seats = [];

	// Set reserved and selected
	var reserved = [];
	$scope.user = {
		'firstname': '',
		'lastname': '',
		'email': '@',
		'phone': '+420',
		'note': '',
		'seats': []
	};

	$scope.reservations = [];
	$scope.timestamps = [];
	$scope.reservationId = null;
	$scope.getReservation = getReservation;

	activate();

	/**
	 * Active controller
	 * @returns {Promise|*}
	 */
	function activate() {
		var password = 'abc'; //window.prompt('Zadej heslo pro vstup do systému:');
		if (password === 'abc') {
			var promises = [getSeats(), getReserved(), getReservationList()];
			return $q.all(promises).then(function () {
			});
		} else {
			return false;
		}
	}

	/**
	 * Retrieve seats
	 * @returns {Promise}
	 */
	function getSeats() {
		return Restangular.all('api').all('v1').all('seat').getList({}).then(function(data) {
			$scope.seats = [];
			angular.forEach(data, function(foo) {
				$scope.seats[foo.id] = foo;
			});
			return $scope.seats;
		}, function (data) {
			console.error('Nepodařilo se nahrát seznam sedadel');
		});
	}

	/**
	 * Retrieve reserved seats
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

	/**
	 * Retrieve reservation list
	 * @returns {Promise}
	 */
	function getReservationList() {
		return Restangular.all('api').all('v1').all('reservation').getList({}).then(function(data) {
			$scope.reservations = data;
			return $scope.reservations;
		}, function (data) {
			windows.alert('Nepodařilo se nahrát seznam rezervací, zkuste prosím stránku obnovit stiskem klávesy F5');
		});
	}

	/**
	 * Retrieve reservation
	 * @returns {Promise}
	 */
	function getReservation(id) {
		return Restangular.all('api').all('v1').one('reservation', id).get().then(function(data) {
			$scope.user = data['user'];
			$scope.timestamps = data['timestamps'];
			$scope.reservationId = id;
			return $scope.user;
		}, function (data) {
			windows.alert('Nepodařilo se nahrát rezervaci, zkuste prosím stránku obnovit stiskem klávesy F5');
		});
	}

	// seat onClick
	$scope.seatClicked = function(seatPos) {
		if (false !== $scope.getStatus(seatPos)
						&& 'selected' !== $scope.getStatus(seatPos)
						&& 'paid' !== $scope.getStatus(seatPos)) {
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
	}

	// get seat status
	$scope.getStatus = function(seatPos) {
		if($scope.user.seats.indexOf(seatPos) > -1) {
			return 'selected';
		} else if(seatPos in reserved) {
			return reserved[seatPos];
		} else {
			return false;
		}
	}

	$scope.confirmReservation = function() {
		if (window.confirm('Opravdu chcete rezervaci převést na zaplacenou objednávku?')) {
			$http.put('/api/v1/reservation/' + $scope.reservationId, {}, {
				headers: {'Content-Type': 'application/json' }
			}).then(function (data) {
				window.alert('Objednávka byla úspěšně zaplacena');
				getReserved();
			}, function (data) {
				window.alert('Objednávka se nezdařila');
			});
		}
	}
	$scope.cancelReservation = function() {
		if (window.confirm('Smazat rrezervaci?')) {
			$http.delete('/api/v1/reservation/' + $scope.reservationId, {}, {
				headers: {'Content-Type': 'application/json' }
			}).then(function (data) {
				window.alert('Rezervace byla úspěšně zrušena');
				getReserved();
			}, function (data) {
				window.alert('Zrušení reervace se nezdařilo');
			});
		}
	}
};

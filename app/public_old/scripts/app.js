'use strict';

/**
 * @ngdoc overview
 * @name seatReservationApp
 * @description
 * # seatReservationApp
 *
 * Main module of the application.
 */
var angular = require('angular');

require('./core');

angular
  .module('seatReservationApp', [
    'SeatReservationApp.core'
  ]);

require('./bootstrap');
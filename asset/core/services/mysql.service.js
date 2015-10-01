'use strict';
var module = require('angular').module('SeatReservationApp.application');
module.factory('MysqlService', MysqlService)

MysqlService.$inject = ['mysql'];
function MysqlService(mysql) {

    MysqlService.factory('MysqlConnection', ['$http', function ($http) {
        var connection = mysql.createConnection({
          host     : 'localhost',
          user     : 'vonica',
          password : 'vonica',
          database : 'vonica'
        });

        connection.connect();

        return connection;
    }]);
};
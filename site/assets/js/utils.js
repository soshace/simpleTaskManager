var utils = angular.module('Utils', []);

utils.constant('USER_TYPES', {
    'CUSTOMER': 0,
    'PERFORMER': 1
});

utils.constant('FIELD', {
    'USERNAME': 'username',
    'PASSWORD': 'password'
});

utils.factory('UtilsFactory', function($rootScope, USER_TYPES) {
    var factory = {};
    factory.getUserTypeString = function(user) {
        if (user == undefined) {
            return '';
        }
        if (user.userType == USER_TYPES.CUSTOMER) {
            return 'customer';
        }
        if (user.userType == USER_TYPES.PERFORMER) {
            return 'performer';
        }
        return '';
    };

    factory.prepareData = function(data) {
        var result = '';
        for (var property in data) {
            if (data.hasOwnProperty(property)) {
                result += '&' + property + '=' + data[property];
            }
        }
        return result.substr(1);
    };

    factory.isLoggedIn = function() {
        return $rootScope.user != null;
    };

    return factory;
});
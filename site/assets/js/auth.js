var auth = angular.module('Authentication', ['Utils']);

auth.factory('AuthFactory', function ($http, $rootScope, USER_TYPES, UtilsFactory) {
    var factory = {};

    factory.signIn = function (user) {
        var promise = $http({
            method: 'POST',
            url: '/ajax/login_user.php',
            data: UtilsFactory.prepareData({
                username: user.username,
                password: user.password
            }),
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        });
        promise.success(function (data) {
            $rootScope.user = data;
        });
        return promise;
    };

    factory.signOut = function () {
        var promise = $http({
            method: 'POST',
            url: '/ajax/logout_user.php',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        });
        promise.success(function () {
            $rootScope.user = null;
        });
        return promise;
    };

    factory.checkAccess = function () {

        var promise = $http({
            method: 'GET',
            url: '/ajax/check_access.php',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        });

        promise.success(function (data) {
            $rootScope.user = data;
        });
    };

    return factory;
});
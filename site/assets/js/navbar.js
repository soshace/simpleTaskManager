//noinspection JSUnresolvedFunction
var navigation = angular.module('NavigationBar', ['Authentication', 'Utils', 'Modals']);

navigation.controller('NavigationBarController', function ($scope, $rootScope, toaster, $modal, AuthFactory, UtilsFactory) {

    $scope.user = $rootScope.user;

    $scope.userTypeName = UtilsFactory.getUserTypeString($scope.user);

    $scope.$watch(
        function() {
            return $rootScope.user;
        },
        function(newVal) {
            $scope.user = newVal;
            $scope.userTypeName = UtilsFactory.getUserTypeString(newVal);
        }
    );


    $scope.signIn = function () {
        var modalInstance = $modal.open({
            templateUrl: 'templates/modal/sign_in.html',
            controller: 'ModalSignInController',
            size: 'sm'
        });

        modalInstance.result.then(
            function () {
                toaster.success("Logged in");
            }
        );
    };

    $scope.signOut = function () {
        $scope.user.logged_in = false;
        AuthFactory.signOut();
        toaster.success("Logged out");
    };

    $scope.signUp = function () {
        var modalInstance = $modal.open({
            templateUrl: 'templates/modal/sign_up.html',
            controller: 'ModalSignUpController',
            size: 'sm'
        });

        modalInstance.result.then(
            function () {
                toaster.success("You are successfully registered");
            }
        );
    };
});

navigation.directive('navbar', function () {
    return {
        restrict: 'E',
        templateUrl: 'templates/navbar.html',
        controller: 'NavigationBarController'
    }
});
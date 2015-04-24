var app = angular.module('app', ['ngAnimate', 'toaster', 'ui.bootstrap', 'ui.router', 'NavigationBar', 'Utils']);

app.run(function (AuthFactory) {
    AuthFactory.checkAccess();
});

app.config(function ($stateProvider, $urlRouterProvider) {
    $urlRouterProvider.otherwise('/');
    $stateProvider
        .state('about', {
            url: '/',
            templateUrl: 'templates/about_content.html',
            controller: 'AboutController'
        })
        .state('tasks', {
            url: '/tasks',
            templateUrl: 'templates/tasks.html',
            controller: 'TasksController'
        });
});

app.controller('AboutController', function ($scope) {
    $scope.title = 'Labor Exchange test project';
    $scope.subTitle = 'Service which helps you to find a performer for your tasks';
});

app.controller('TasksController', function ($scope, $rootScope, $modal, RequestFactory, toaster, USER_TYPES) {
    $scope.title = 'Labor Exchange test project';
    $scope.subTitle = 'Service which helps you to find a performer for your tasks';

    $scope.user = $rootScope.user;

    $scope.$watch(
        function () {
            return $rootScope.user;
        },
        function (newVal) {
            $scope.user = newVal;
        }
    );

    $scope.USER_TYPES_CONST = USER_TYPES;

    $scope.tasks = {};

    $scope.hasMore = true;

    var minId = null;

    var updateMinId = function (task) {
        if (minId == null) {
            minId = task.taskId;
        }
        minId = Math.min(task.taskId, minId);
    };

    RequestFactory.getTasks()
        .success(function (data) {
            $scope.tasks = data;
            data.forEach(updateMinId);
        });

    $scope.deleteTask = function (task) {
        RequestFactory.deleteTask(task.taskId)
            .success(function (data) {
                var idx = $scope.tasks.indexOf(task);
                $scope.tasks.splice(idx, 1);
                $scope.user.wallet = data;
                toaster.success("You have deleted the task");
            })
            .error(function(){
                toaster.error("Something went wrong");
            });
    };

    $scope.completeTask = function (task) {
        RequestFactory.completeTask(task.taskId)
            .success(function (data) {
                $scope.user.wallet = data;
                var idx = $scope.tasks.indexOf(task);
                $scope.tasks.splice(idx, 1);
                toaster.success("You have completed the task");
            })
            .error(function (data) {
                if (data.reason == 'TaskDeleted') {
                    var idx = $scope.tasks.indexOf(task);
                    $scope.tasks.splice(idx, 1);
                    toaster.error("Sorry, but this task already deleted");
                }
            });
    };


    $scope.addTask = function () {
        var modalInstance = $modal.open({
            templateUrl: 'templates/modal/add_task.html',
            controller: 'ModalAddTaskController',
        });
        modalInstance.result.then(
            function (data) {
                updateMinId(data.task);
                $rootScope.user.wallet = data.wallet;
                $scope.tasks.unshift(data.task);
                toaster.success("Task added", "Your task has been completely added");
            });
    };

    $scope.addMoney = function () {
        var modalInstance = $modal.open({
            templateUrl: 'templates/modal/add_money.html',
            controller: 'ModalAddMoneyController',
            size: 'sm'
        });
        modalInstance.result.then(
            function (data) {
                $rootScope.user.wallet = data;

                toaster.success("You successfully added money");
            }
        );
    };

    $scope.loadMore = function () {
        RequestFactory.getTasks(minId)
            .success(function (data) {
                data.forEach(updateMinId);
                if (data.length == 0) {
                    $scope.hasMore = false;
                } else {
                    $scope.tasks = $scope.tasks.concat(data);
                }
            });
    };
});



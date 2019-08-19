App.controller('EnterprisepaymentController', function ($translate, $rootScope,$ionicPopup, $scope, $stateParams,
                                                                             Customer, SB, Dialog, SocialSharing, Modal,$timeout, $ionicLoading,
                                                                             Enterprisepaymentpro, $state, Application, $location, $window) {
    $scope.is_loading = true;
    $scope.homeView = false;
    $scope.value_id = Enterprisepaymentpro.value_id = $stateParams.value_id;
    
    /*Check web or mobile device*/
    if(Application.is_webview) {
      var url = $location.absUrl();
      if (url.indexOf("?") > -1) {
        $ionicLoading.show({
          template: 'Inprocess...',
        })
        var first_split = url.split('?');
        var second_split = first_split[1].split('&');

        if (second_split.length > 1) {
          var token_split = second_split[0].split('=');
          var tokenId = token_split[1];

          var payer_split = second_split[1].split('=');
          var payerId = payer_split[1];

          var amount = Enterprisepaymentpro.get_data('amount');
          var gid = Enterprisepaymentpro.get_data('paypalDetail_gid');
          var customer_id = Enterprisepaymentpro.get_data('customer_id');

          /*Response data after payment success*/
          $scope.tokenId = Enterprisepaymentpro.token = tokenId;
          $scope.payerId = Enterprisepaymentpro.payerId = payerId;
          $scope.meta_data = Enterprisepaymentpro.metaData = Enterprisepaymentpro.get_local('metaData');
          $scope.paymentCode = Enterprisepaymentpro.paymentCode = Enterprisepaymentpro.get_local('method_code');
          $scope.return_value_id = Enterprisepaymentpro.return_value_id = Enterprisepaymentpro.get_local('return_value_id');
          Enterprisepaymentpro.transaction($scope.value_id, amount, customer_id, 1, gid,1,$scope.return_value_id).success(function(data){
            if (data.success == 1) {
              Enterprisepaymentpro.unset_data('amount');
              Enterprisepaymentpro.unset_data('paypalDetail_gid');
              Enterprisepaymentpro.unset_data('customer_id');
              Enterprisepaymentpro.unset_data('metaData');
              Enterprisepaymentpro.unset_data('method_code');
              Enterprisepaymentpro.unset_data('return_value_id');
              $ionicLoading.hide();
              if (data.return_state.length > 0) {
                $state.go(data.return_state,{value_id: data.return_value_id}, {reload: true});
              } else {
                $location.path(BASE_PATH+'/'+data.return_url).search({transaction_id: Enterprisepaymentpro.payerId,customer_id: customer_id});
              }
            }
          });
        } else {
          $ionicLoading.hide();
          console.log('---- ----- ----- ---->');
          console.log('1');
          $state.go('enterprisepayment_process',{value_id: $scope.value_id, amount: Enterprisepaymentpro.get_data('amount'),return_value_id: $stateParams.value_id,order_id:456}, {reload: true});
        }
      }
    }

  	$scope.openpayment = function(isValid,amount){
        if(isValid){
          console.log('---- ----- ----- ---->');
          console.log('2');
          $state.go('enterprisepayment_process',{value_id: $scope.value_id, amount: amount,return_value_id: $scope.value_id ,order_id:456}, {reload: true});
        }
  	}
}).controller('EnterprisepaymentProcessController', function ($translate,$location,$ionicLoading,$state,$ocLazyLoad,$ionicLoading,$ionicModal,$rootScope, $scope, $stateParams,
                                                                             Customer, SB, Dialog, SocialSharing, Modal,
                                                                             Enterprisepaymentpro, $ionicPopup, $rootScope) {
    $scope.is_loading = true;
    $scope.value_id = Enterprisepaymentpro.value_id = $stateParams.value_id;
    $scope.meta_data = Enterprisepaymentpro.metaData = $stateParams.meta_data;
    $scope.return_value_id = Enterprisepaymentpro.return_value_id = $stateParams.return_value_id;
    
    Enterprisepaymentpro.set_local('return_value_id',$stateParams.return_value_id);

    Enterprisepaymentpro.set_local('metaData',$stateParams.meta_data);
  	$scope.amount = $stateParams.amount;
    $scope.order_id = $stateParams.order_id;
    $scope.is_logged_in = Customer.isLoggedIn();
    $scope.$on(SB.EVENTS.AUTH.loginSuccess, function() {
        $scope.is_logged_in = true; 
        $scope.getmethodid = Enterprisepaymentpro.get_local('method_gid');
        $scope.methodcode = Enterprisepaymentpro.get_local('method_code');
        if($scope.getmethodid){
          $scope.gotoTransaction($scope.getmethodid,$scope.methodcode);
        }
    });

    $scope.loadContent = function() {
      Enterprisepaymentpro.getMethods($scope.value_id).success(function(data){
        if (data.success == 1) {
            $scope.methods = data.methods;
            $scope.base_url = data.basepath;
            $scope.currency = data.currency;

            /*Load stripe.js*/
            $ocLazyLoad
              .load($scope.base_url+'/app/local/modules/Enterprisepayment/features/enterprisepayment/js/stripe.js')
              .then(function () {
            });
        }
      }).finally(function(){
          $scope.is_loading = false;
      });

      if ($stateParams.amount <= 0) {
        var alertPopup = $ionicPopup.alert({
            title: $translate.instant("Error"),
            template: $translate.instant("Please enter correct amount")
        });
        return;
      }
    }


    Enterprisepaymentpro.unset_local('method_gid');
    
  	$scope.gotoTransaction = function(method_gid,method_code){
        Enterprisepaymentpro.set_local('method_gid',method_gid);
        Enterprisepaymentpro.set_local('method_code',method_code);
        Enterprisepaymentpro.paymentCode = method_code;
        console.log(Enterprisepaymentpro.paymentCode);
        if (!Customer.isLoggedIn()) {
          $scope.login();
          return;
        }

        if (method_code == 'stripe') {
          $ionicLoading.show();
          $ionicModal.fromTemplateUrl('features/enterprisepayment/assets/templates/l1/stripe-form.html', {
            scope: $scope,
            animation: 'slide-in-up'
          }).then(function(modal) {
              $scope.stripeModel = modal;
              $scope.stripeModel.show();
              $ionicLoading.hide();
          });

          /*Stripe payment*/
          $scope.formstripe = function(isValid,formdata){
            if(isValid){
              $ionicLoading.show();
              Enterprisepaymentpro.getpublishkey(method_gid).success(function(data){
                if(data.success == 1){
                    $scope.publishablekey = data.publishkey;
                    $scope.secretekey = data.secretekey;
                    var PublishableKey = $scope.publishablekey; // Replace with your API publishable key
                    Stripe.setPublishableKey(PublishableKey);
                    Stripe.card.createToken(formdata, function stripeResponseHandler(status, response) {
                     var token = response.id;
                      $scope.amount = $stateParams.amount;
                      Enterprisepaymentpro.getstripe(token,$scope.amount,$scope.secretekey,method_gid,$scope.order_id,$scope.return_value_id).success(function(data){
                        if(data.success == 1){
                          $scope.return_url = data.return_url;
                          $scope.transaction_id = data.transaction_id;
                          Enterprisepaymentpro.token = $scope.transaction_id;
                          Enterprisepaymentpro.payerId = $scope.transaction_id;

                          $scope.stripeModel.hide();
                          $stateParams.order_id= $scope.order_id;
                          if (data.return_state.length > 0) {
                            $state.go(data.return_state,{value_id: data.return_value_id}, {reload: true});
                          } else {
                            $location.path(BASE_PATH+'/'+$scope.return_url).search({transaction_id: $scope.transaction_id,order_id:$scope.order_id,customer_id:Customer.id});
                          }
                         
                        }else{
                            $ionicPopup.alert({
                              title: $translate.instant('Error!'),
                              template: $translate.instant('Please enter correct credentials')
                            });
                        }
                      }).finally(function(){
                        $ionicLoading.hide();
                      });
                  });
                }
              });
            }
          }
        }
        
        if (method_code == 'paypal') {
          Enterprisepaymentpro.paypalPayment($scope.amount, method_gid,$scope.order_id,$scope.return_value_id);
        }

        if (method_code == 'cash') {
          Enterprisepaymentpro.postcashresponse($scope.amount, method_gid,$scope.return_value_id).success(function(data){
            if(data.success == 1){
              console.log(data);
              console.log('ooooooooooooooooooooooooooooooooooooooooooooo');
              console.log(data.return_state.length);
              $scope.return_url = data.return_url;
              Enterprisepaymentpro.token = data.token;
              Enterprisepaymentpro.payerId = data.token;
              if (data.return_state.length > 0) {

                $state.go(data.return_state,{value_id: data.return_value_id}, {reload: true});
              } else {
                $location.path(BASE_PATH+'/'+$scope.return_url).search({transaction_id: data.token,order_id:$scope.order_id,customer_id:Customer.id});
              }
             
            }
          });
        }

        if (method_code == 'bank_transfer') {
          $ionicLoading.show();
          $ionicModal.fromTemplateUrl('features/enterprisepayment/assets/templates/l1/bank-transfer.html', {
            scope: $scope,
            animation: 'slide-in-up'
          }).then(function(modal) {
              $scope.bankTransferModel = modal;
              $scope.bankTransferModel.show();
              $ionicLoading.hide();
          });
          Enterprisepaymentpro.getbanktransferform(method_gid).success(function(data){
            if(data.success == 1){
              $scope.bank_data = data.bank_data;
            }
          });
        }
        //payu latam
        if (method_code == 'payu_latam') {
          $ionicLoading.show();
          $ionicModal.fromTemplateUrl('features/enterprisepayment/assets/templates/l1/payu_latam.html', {
            scope: $scope,
            animation: 'slide-in-up'
          }).then(function(modal) {
              $scope.stripeModel = modal;
              $scope.stripeModel.show();
              $ionicLoading.hide();
          });

          /*Payu Latam payment*/
          $scope.payulatamformsubmit = function(isValid,formdata){
            if(isValid){
                $ionicLoading.show();
                //create token STEP -1
                Enterprisepaymentpro.getpayulatamtoken(method_gid, formdata).success(function(data){
                if (data.success == 1) {
                    $scope.payu_token = data.payu_latam_token;

                    //create payment id STEP -2
                    Enterprisepaymentpro.createpayulatampaymentid(method_gid,data.payu_latam_token, $scope.amount).success(function(data){
                        if (data.success == 1) {
                            $scope.payu_latam_payment_id = data.payu_latam_payment_id;
                            $scope.reconciliation_id = data.reconciliation_id;
                            
                            //create charge SETP - 3
                            Enterprisepaymentpro.createpayulatamchanrge(method_gid,$scope.payu_latam_payment_id,$scope.payu_token,$scope.reconciliation_id ).success(function(data){
                                if (data.success == 1) {
                                    //$scope.payu_token = data.token;
                                }else{
                                    $ionicPopup.alert({
                                        title: $translate.instant('Error!'),
                                        template: $translate.instant('Working .....')
                                    });
                                }
                             
                            });
                        }else{
                            $ionicPopup.alert({
                                title: $translate.instant('Error!'),
                                template: $translate.instant('Working....')
                            });
                        }
                     
                    });

                }else{
                    $ionicPopup.alert({
                        title: $translate.instant('Error!'),
                        template: $translate.instant('Unable to take payment')
                    });
                }
               /* if(data.success == 1){
                    $scope.publishablekey = data.publishkey;
                    $scope.secretekey = data.secretekey;
                    var PublishableKey = $scope.publishablekey; // Replace with your API publishable key
                    Stripe.setPublishableKey(PublishableKey);
                    Stripe.card.createToken(formdata, function stripeResponseHandler(status, response) {
                     var token = response.id;
                      $scope.amount = $stateParams.amount;
                      Enterprisepaymentpro.getstripe(token,$scope.amount,$scope.secretekey,method_gid,$scope.order_id).success(function(data){
                        if(data.success == 1){
                          $scope.return_url = data.return_url;
                          $scope.transaction_id = data.transaction_id;
                          Enterprisepaymentpro.token = $scope.transaction_id;
                          Enterprisepaymentpro.payerId = $scope.transaction_id;

                          $scope.stripeModel.hide();
                          $stateParams.order_id= $scope.order_id;
                          if (data.return_state.length > 0) {
                            $state.go(data.return_state,{value_id: data.return_value_id}, {reload: true});
                          } else {
                            $location.path(BASE_PATH+'/'+$scope.return_url).search({transaction_id: $scope.transaction_id,order_id:$scope.order_id,customer_id:Customer.id});
                          }
                         
                        }else{
                            $ionicPopup.alert({
                              title: $translate.instant('Error!'),
                              template: $translate.instant('Please enter correct credentials')
                            });
                        }
                      }).finally(function(){
                        $ionicLoading.hide();
                      });
                  });
                }*/
              });
            }else{
                $ionicPopup.alert({
                    title: $translate.instant('Error!'),
                    template: $translate.instant('Please enter correct credentials')
                });
            }
          }
        }
        
        //payu latam
  	}

    $scope.login = function() {
      Customer.loginModal($scope);
    };

    $scope.month = [1,2,3,4,5,6,7,8,9,10,11,12];

    var year = new Date().getFullYear();
    var range = [];
    range.push(year);
    for (var i = 1; i <= 19; i++) {
        range.push(year + i);
    }
    $scope.year = range;

    $scope.loadContent();

    $scope.bank_transfer_proceed = function(){
      $scope.getmethodid = Enterprisepaymentpro.get_local('method_gid');
      Enterprisepaymentpro.postbankresponse($scope.amount, $scope.getmethodid,$scope.return_value_id).success(function(data){
        console.log(data);
        if(data.success == 1){
          $scope.return_url = data.return_url;
          Enterprisepaymentpro.token = data.token;
          Enterprisepaymentpro.payerId = data.token;
          $ionicPopup.alert({
            title: $translate.instant('Success'),
            template: $translate.instant('Your payment have been done successfully')
          }).then(function() {
              $scope.bankTransferModel.hide();
              if (data.return_state.length > 0) {
                $state.go(data.return_state,{value_id: data.return_value_id}, {reload: true});
              } else {
                $location.path(BASE_PATH+'/'+$scope.return_url).search({transaction_id: data.token,order_id:$scope.order_id,customer_id:Customer.id});
              }
          });
        }
      });
    }
});


angular.module('starter').factory('Enterprisepaymentpro', function ($sbhttp, $pwaRequest, Application, $rootScope, $http, $ionicPopup, $translate, Loader, Dialog, Url, SB, $location, $ionicLoading, $window, $state, Customer,$ocLazyLoad,$ionicModal) {
    var factory = {};

    factory.value_id = null;
    factory.token = null;
    factory.payerId = null;
    factory.metaData = null;

    factory.get_local = function(key){
        return $window.localStorage.getItem(key) || null;
    }

    factory.set_local = function(key, id) {
        $window.localStorage.setItem(key, id);
    }

    factory.unset_local = function(key){
        $window.localStorage.removeItem(key);
    }

    factory.getMethods = function(value_id){
        var data = {};
        data.value = this.value_id;

        var url = Url.get("enterprisepayment/mobile_view/getmethods");
        return $http.post(url, data);
    }

    factory.paypalPayment = function (amount, gid,order_id,return_value_id) {
        var data = {};
        data.gid = gid;
        data.amount = amount;
        data.return_value_id = return_value_id;
        data.current_url = $location.url();
        data.value_id = factory.value_id;
        data.order_id = order_id;
        data.BASE_PATH = BASE_PATH;
        data.is_webview = Application.is_webview;

        Loader.show();

        var promise = $pwaRequest.post('enterprisepayment/mobile_view/paypalpayment', {
            data: data,
            cache: false
        });

        promise.then(function (result) {
            if (result.token_url == '&webview=1') {
                Dialog.alert('Error', 'Unable to process payment through paypal', 'OK', -1);
            } else {
                factory.populate(result, amount, order_id,return_value_id);
            }
            
            return result;
        }, function (error) {
            Dialog.alert('Error', error.message, 'OK', -1);
        }).then(function (result) {
            Loader.hide();

            return result;
        });

        return promise;
    }

    factory.populate = function(paypalDetail, amount, order_id,return_value_id){
        if (paypalDetail.success == 1) {
            if (!Application.is_webview) {
                $ionicLoading.show({content: 'Loading',animation: 'fade-in',showBackdrop: true,maxWidth: 200,showDelay: 0 });
                var browser = $window.open(paypalDetail.token_url, $rootScope.getTargetForLink(), 'location=yes');

                browser.addEventListener('loadstart', function(event) {
                    var newurl = event.url.split('/?__goto__=');
                    
                    var res = newurl[0].concat(newurl[1]);
                    if(/(confirm)/.test(event.url)) {
                        var url = event.url;
                        var first_split = url.split('?');
                        var second_split = first_split[1].split('&');
                        var token_split = second_split[0].split('=');

                        var tokenId = token_split[1];

                        var payer_split = second_split[1].split('=');

                        var payerId = payer_split[1];

                        if(tokenId !='' && payerId !='') {
                            factory.token = tokenId;
                            factory.payerId = payerId;
                            factory.transaction(this.value_id, amount, Customer.id, 1, paypalDetail.gid, order_id,return_value_id);
                            browser.close();
                            $ionicLoading.hide();
                            if (paypalDetail.return_state.length > 0) {
                                $state.go(paypalDetail.return_state,{value_id: paypalDetail.return_value_id}, {reload: true});
                            } else {
                                $location.path(BASE_PATH+'/'+paypalDetail.return_url).search({transaction_id: this.payerId,order_id:order_id,customer_id:Customer.id});
                            }
                           
                        } else {
                            var responseArray = {token: '', payer: ''};
                            factory.transaction(this.value_id, amount, Customer.id, 0, paypalDetail.gid, order_id,return_value_id);
                        }
                    } else if(/(cancel)/.test(event.url)) {
                        browser.close();
                        $ionicLoading.hide();
                    } 
                });
            } else {
                factory.set_data('amount', amount);
                factory.set_data('customer_id', Customer.id);
                factory.set_data('paypalDetail_gid', paypalDetail.gid);
                $window.location = paypalDetail.token_url;
            }
        }
    }

    factory.transaction = function(value_id, amount, customer_id, status, gid,order_id,return_value_id) {
        var data = {};
        data.token = this.token;
        data.payer = this.payerId;
        data.customer_id = customer_id;
        data.amount = amount;
        data.return_value_id = return_value_id;
        data.value = this.value_id;
        data.status = status;
        data.gid = gid;
        data.order_id = order_id;
        var url = Url.get("enterprisepayment/mobile_view/transaction");
        return $http.post(url, data);
    }

    factory.getpublishkey = function(id){
        var data = {};
        data.id = id;
        data.value = this.value_id;
        var url = Url.get("enterprisepayment/mobile_view/getpublishkey");
        return $http.post(url, data);
    }

    factory.getstripe = function(token,amount,secretkey,gid,order_id,return_value_id){
        var data = {};
        data.token = token;
        data.value = this.value_id;
        data.amount = amount;
        data.return_value_id = return_value_id;
        data.secretkey = secretkey;
        data.gid = gid;
        data.customer_id = Customer.id;
        data.order_id = order_id;
        var url = Url.get("enterprisepayment/mobile_view/stripepayment");
        return $http.post(url, data);
    }

    factory.getbanktransferform = function(gid){
        var data = {};
        data.gid = gid;
        data.value_id = this.value_id;
        var url = Url.get("enterprisepayment/mobile_view/getbanktransferform");
        return $http.post(url, data);
    }

    factory.postbankresponse = function(amount,gid,return_value_id){
        var data = {};
        data.return_value_id = return_value_id;
        data.amount = amount;
        data.gid = gid;
        data.customer_id = Customer.id;
        data.value_id = this.value_id;
        var url = Url.get("enterprisepayment/mobile_view/postbankresponse");
        return $http.post(url, data);
    }

    factory.postcashresponse = function(amount,gid,return_value_id){
        var data = {};
        data.return_value_id = return_value_id;
        data.amount = amount;
        data.gid = gid;
        data.customer_id = Customer.id;
        data.value_id = this.value_id;
        var url = Url.get("enterprisepayment/mobile_view/postcashresponse");
        return $http.post(url, data);
    }

    factory.getPaymentValueId = function(){
        var data = {};
        var url = Url.get("enterprisepayment/mobile_view/getpaymentvalueid");
        $http.post(url, data).then(function successCallback(response) {
            factory.enterprisepaymentValueId = response.data;
        }, function errorCallback(response) {
            console.log(response);
        });
    }

    factory.get_data = function(key) {
        return $window.localStorage.getItem(key) || null;
    };

    factory.set_data = function(key, id) {
        $window.localStorage.setItem(key, id);
    };

    factory.unset_data = function(key) {
        $window.localStorage.removeItem(key);
    };

     /****************PAYU LATAM**************************/
    factory.getpayulatamtoken = function(id,form_data){
        var data = {};
        data.id = id;
        data.form_data = form_data;
        data.value = this.value_id;
        var url = Url.get("enterprisepayment/mobile_view/getpayulatamtoken");
        return $http.post(url, data);
    }
     factory.createpayulatampaymentid = function(method_gid,create_payament, amount){
        var data = {};
        data.id = method_gid;
        data.amount = amount;
        data.token = create_payament;
        data.value = this.value_id;
        var url = Url.get("enterprisepayment/mobile_view/createpayulatampaymentid");
        return $http.post(url, data);
    }
    factory.createpayulatamchanrge = function(method_gid,payu_latam_payment_id, token,reconciliation_id){
        var data = {};
        data.id = method_gid;
        data.payu_latam_payment_id = payu_latam_payment_id;
        data.token = token;
         data.reconciliation_id = reconciliation_id;
        data.value = this.value_id;
        var url = Url.get("enterprisepayment/mobile_view/createpayulatamchanrge");
        return $http.post(url, data);
    }
   /****************END PAYU LATAM**************************/
    return factory;
});

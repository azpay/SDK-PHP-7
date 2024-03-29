<?php

namespace Gateway\API;

include_once "autoload.php";

use Exception as Exception;

try {
    $credential = new Credential("{{mechantID}}", "{{mechantKEY}}",
        Environment::SANDBOX);
    $gateway    = new Gateway($credential);
    $split      = [
        [
            "recipient"             => "d71c944b96a43b39c2b38fd6353b6a2",
            "liable"                => "true",
            "charge_processing_fee" => "true",
            "percentage"            => 10,
            "amount"                => 10,
        ],

    ];
    ### CREATE A NEW TRANSACTION
    $transaction = new Transaction();

    // Set ORDER
    $transaction->Order()
        ->setReference("ss")
        ->setTotalAmount(1000);

    // Set PAYMENT
    $transaction->Payment()
        ->setAcquirer(Acquirers::AZPAY)
        ->setCurrency(Currency::BRAZIL_BRAZILIAN_REAL_BRL)
        ->setCountry("BRA")
        ->setExpire("2021-09-17T23:00:00")
        ->setFine(0)
        ->setInterest(1.12)
        ->setInstructions("PIX: Anuidade do serviço")
        ->Split($split);

    // SET CUSTOMER
    $transaction->Customer()
        ->setCustomerIdentity("999999999")
        ->setName("Bruno")
        ->setAddress("Rua teste de varginha")
        ->setAddress2("Apartamento 23")
        ->setPostalCode("08742350")
        ->setCity("São Paulo")
        ->setState("SP")
        ->setCountry("BRASIL")
        ->setCpf("94127918012")
        ->setEmail("brunopaz@test.com");

    // Set URL RETURN
    $transaction->setUrlReturn("http://127.0.0.1:8989/return.php");

    // PROCESS - ACTION
    $response = $gateway->Pix($transaction);

    // REDIRECT IF NECESSARY (Debit uses)
    if ($response->isRedirect()) {
        print $response->getRedirectUrl();
        //$response->redirect();
    }

    // RESULTED
    if ($response->isAuthorized()) { // Action Authorized
        print "<br>RESULTED: ".$response->getStatus();
    } else { // Action Unauthorized
        print "<br>RESULTED:".$response->getStatus();
    }

    // REPORT
    $response = $gateway->Report($response->getTransactionID());
    print "<br>REPORTING: ".$response->getStatus();
    print "<br>INFO: <HR>";
    var_dump($response->getPixInfo()); // array

} catch (Exception $e) {
    print_r($e->getMessage());
}
<?php

require_once 'lib/nem-php/nem-php.php';
require_once 'lib/nem-php/keypair.php';

class HomeController {

    private $nemPhp = null;

    function setup($f3) {
        $env = $f3->get('env');
        $config = [
            'net'               => $env['SERVICE_TYPE'], //testnet or mijin
            'nis_address'       => $env['NIS'],
            'private'           => $env['WALLET_APP_PRIVATE_KEY'], //put your private key from nem wallet
            'public'            => $env['WALLET_APP_PUBLIC_KEY'], //put your public key from nem wallet
            'security_check'    => true //leave it true if you are not sure
        ];

        $this->nemPhp = new NemPhp($config);  //Get library
    }

    function index() {
        $f3 = Base::instance();
        $f3->set('page_title', "Home");
        $f3->set('content','templates/pages/welcome.htm');
        echo \Template::instance()->render('templates/layout/layout.htm');
    }

    function task1() {
        $f3 = Base::instance();
        $f3->set('page_title', "Requirements");
        $f3->set('content','templates/pages/task1.htm');
        echo \Template::instance()->render('templates/layout/layout.htm');
    }

    function task2() {
        $heartbeat = false;

        $f3 = Base::instance();
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $this->setup($f3);
            $heartbeat = $this->nemPhp->heartbit(); //Check if it's working
        }
        $f3->set('page_title', "Task 2: Configure and Heartbeat");
        $f3->set('content','templates/pages/task2.htm');
        $f3->set('heartbeat', $heartbeat);
        echo \Template::instance()->render('templates/layout/layout.htm');
    }

    function task3() {
        $status = false;

        $f3 = Base::instance();
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $this->setup($f3);
            $status = $this->nemPhp->status(); //Check if it's working
        }
        $f3->set('page_title', "Task 3: Configure and Status");
        $f3->set('content','templates/pages/task3.htm');
        $f3->set('status', $status);
        echo \Template::instance()->render('templates/layout/layout.htm');
    }

    function task4() {
        $result = false;

        $f3 = Base::instance();
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $this->setup($f3);

            switch($_POST['action']) {
            case "accountGet":
                $result = $this->nemPhp->accountGet(); //Check if it's working
                break;
            case "accountGetFromPublicKey":
                $nem_public_key = ($_POST['nem_public_key']) ? $_POST['nem_public_key'] : null;
                $result = $this->nemPhp->accountGetFromPublicKey($nem_public_key); //Check if it's working
                break;
            case "accountGetForwarded":
                $result = $this->nemPhp->accountGetForwarded(); //Check if it's working
                break;
            case "accountStatus":
                $result = $this->nemPhp->accountStatus(); //Check if it's working
                break;
            }
        }
        $f3->set('page_title', "Task 4: Get Account Information");
        $f3->set('content','templates/pages/task4.htm');
        $f3->set('result', $result);
        echo \Template::instance()->render('templates/layout/layout.htm');
    }

    function task5() {
        $result = false;

        $f3 = Base::instance();
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $this->setup($f3);

            $nem_address = ($_POST['nem_address']) ? $_POST['nem_address'] : null;
            $nem_hash = ($_POST['nem_hash']) ? $_POST['nem_hash'] : null;
            $nem_id = ($_POST['nem_id']) ? $_POST['nem_id'] : null;

            $result = $this->nemPhp->accountTransfersAll(
                $nem_address, //address - if ommitted, then address is taken from public key
                $nem_hash,  //Get 25 transactions that appeared directly before the transaction with paricular hash
                $nem_id //Get 25 transactions that appeared directly before the transaction with paricular id
            );
        }
        $f3->set('page_title', "Task 5: Fetch all transactions");
        $f3->set('content','templates/pages/task5.htm');
        $f3->set('result', $result);
        echo \Template::instance()->render('templates/layout/layout.htm');
    }

    function task6() {
        $result = false;

        $f3 = Base::instance();
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $this->setup($f3);

            $nem_address = ($_POST['nem_address']) ? $_POST['nem_address'] : null;
            $nem_hash = ($_POST['nem_hash']) ? $_POST['nem_hash'] : null;
            $nem_id = ($_POST['nem_id']) ? $_POST['nem_id'] : null;

            //Get 25 most recent transactions 
            $result = $this->nemPhp->accountTransfersIncoming(
                $nem_address, //address - if ommitted, then address is taken from public key
                $nem_hash,  //Get 25 transactions that appeared directly before the transaction with paricular hash
                $nem_id //Get 25 transactions that appeared directly before the transaction with paricular id
            );
        }
        $f3->set('page_title', "Task 6: Fetch incoming transactions");
        $f3->set('content','templates/pages/task6.htm');
        $f3->set('result', $result);
        echo \Template::instance()->render('templates/layout/layout.htm');
    }

    function task7() {
        $result = false;

        $f3 = Base::instance();
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $this->setup($f3);

            $nem_address = ($_POST['nem_address']) ? $_POST['nem_address'] : null;
            $nem_hash = ($_POST['nem_hash']) ? $_POST['nem_hash'] : null;
            $nem_id = ($_POST['nem_id']) ? $_POST['nem_id'] : null;

            //Get 25 most recent transactions 
            $result = $this->nemPhp->accountTransfersOutgoing(
                $nem_address, //address - if ommitted, then address is taken from public key
                $nem_hash,  //Get 25 transactions that appeared directly before the transaction with paricular hash
                $nem_id //Get 25 transactions that appeared directly before the transaction with paricular id
            );
        }

        $f3->set('page_title', "Task 7: Fetch outgoing transactions");
        $f3->set('content','templates/pages/task7.htm');
        $f3->set('result', $result);
        echo \Template::instance()->render('templates/layout/layout.htm');
    }

    function task8() {
        $result = false;

        $f3 = Base::instance();
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $this->setup($f3);

            $nem_address = ($_POST['nem_address']) ? $_POST['nem_address'] : null;
            
            //Get 25 most recent transactions 
            $result = $this->nemPhp->accountUnconfirmedTransactions(
                $nem_address //address - if ommitted, then address is taken from public key
            );
        }

        $f3->set('page_title', "Task 8: Fetch unconfirm transactions");
        $f3->set('content','templates/pages/task8.htm');
        $f3->set('result', $result);
        echo \Template::instance()->render('templates/layout/layout.htm');
    }

    function task9() {
        $result = false;

        $f3 = Base::instance();
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $this->setup($f3);

            $nem_hash = ($_POST['nem_hash']) ? $_POST['nem_hash'] : null;
            $decode = isset($_POST["decodeMessage"]);

            $result = $this->nemPhp->transactionGet(
                $nem_hash,
                $decode
            );
        }
        $f3->set('page_title', "Task 9: Get transaction");
        $f3->set('content','templates/pages/task9.htm');
        $f3->set('result', $result);
        echo \Template::instance()->render('templates/layout/layout.htm');
    }

    function task10() {
        $transaction = false;
        $fee = false;
        $result = false;

        $f3 = Base::instance();
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $this->setup($f3);

            $receiver = $_POST['receiver'];
            $amount = ($_POST['amount']) ? $_POST['amount'] : 0;
            $message = trim($_POST['message']);

            //Prepare transaction
            $this->nemPhp->prepareTransaction(
                $amount, //How much XEM to send 
                0, //Put higher fee if you want, otherwise leave it zero so minimum fee will be taken off
                $receiver, //adress where to send
                null,   //mosaics
                $message, // message
                false // secure message
            );

            //You can check your future transaction before sending
            $transaction = $this->nemPhp->transaction;

            //Or get estimated transaction fee in microXEM (actual amount in XEM will be divided vy 1000000)
            $fee = $this->nemPhp->transaction['fee'];

            if (isset($_POST['sendTransaction'])) {
                //And commit transaction to the network (you shoud almost immidiately hear 'dink' sound from you wallet
                $result = $this->nemPhp->announceTransaction();
            }
        }

        $f3->set('page_title', "Task 10: Fetch incoming transactions");
        $f3->set('content','templates/pages/task10.htm');
        $f3->set('transaction', $transaction);
        $f3->set('fee', $fee);
        $f3->set('result', $result);
        echo \Template::instance()->render('templates/layout/layout.htm');
    }

}
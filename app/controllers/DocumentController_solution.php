<?php

// TODO: require nem-php
require_once 'lib/nem-php/nem-php.php';
require_once 'lib/nem-php/keypair.php';

class DocumentController {

    private $DB = null;
    private $nemPhp = null;

    function setup($f3) {
        $databaseLocation = realpath(dirname(__FILE__) . '/../') . "/db/database.sqlite";

        if (!file_exists($databaseLocation)) {
            throw new Exception ("Database file ($databaseLocation) doest exist");
        }
        $this->DB = new DB\SQL('sqlite:' . $databaseLocation);

        // TODO: Initial NEM connection
        $env = $f3->get('env');
        $config = [
            'net'               => $env['SERVICE_TYPE'],
            'nis_address'       => $env['NIS'],
            'private'           => $env['WALLET_APP_PRIVATE_KEY'],
            'public'            => $env['WALLET_APP_PUBLIC_KEY'],
            'security_check'    => true
        ];

        $this->nemPhp = new NemPhp($config);
    }

    function getStorage() {
        return realpath(dirname(__FILE__) . '/../') . "/storage";
    }

    function index() {
        $f3 = Base::instance();

        $this->setup($f3);
        $docuemnts = $this->DB->exec('SELECT * FROM documents');

        $f3->set('page_title', "Document Management");
        $f3->set('page_subtitle', "Document Listing");
        $f3->set('documents', $docuemnts);
        $f3->set('content','templates/documents/index.htm');
        echo \Template::instance()->render('templates/layout/layout.htm');
    }

    function create() {
        $f3 = Base::instance();
        $f3->set('page_title', "Document Management");
        $f3->set('page_subtitle', "Document::Create");
        $f3->set('content','templates/documents/create.htm');
        echo \Template::instance()->render('templates/layout/layout.htm');
    }

    function store() {
        $f3 = Base::instance();
        $f3->set('page_title', "Document Management");
        $f3->set('page_subtitle', "Document::Create");
        
        $name = $_POST['name'];
        $description = $_POST['description'];
        $fileupload = $_FILES['fileupload'];
        
        
        if ($name == "" || $description == "" || sizeof($fileupload) == 0) {
            $f3->set('message','Please check your inputs.');    
            $f3->reroute($f3->get('SERVER.HTTP_REFERER'));
        }
        
        $this->setup($f3);

        // TODO: Insert send xem here
        $fileHash = hash_file("sha256", $fileupload['tmp_name']);
        $env = $f3->get('env');
        $receiver = $env['WALLET_DOC_ADDRESS'];
        $amount = 0;
        $message = $fileHash;
        
        // Prepare transaction
        $this->nemPhp->prepareTransaction(
            $amount, //How much XEM to send 
            0, //Put higher fee if you want, otherwise leave it zero so minimum fee will be taken off
            $receiver, //adress where to send
            null,   //mosaics
            $message, // message
            false // secure message
        );
        // And commit transaction to the network
        $result = $this->nemPhp->announceTransaction();

        if ($result['result']['message'] == "SUCCESS") {
            $nemHash = $result['result']['transactionHash']['data'];
        } else {
            $f3->set('message','ERROR ; ' . $result['result']['message']);    
            $f3->set('content','templates/documents/verifier.htm');
            echo \Template::instance()->render('templates/layout/layout.htm');
            return false;
        }
        
        $filename = time();
        $uploads_dir = $this->getStorage();
        $storeFile = "$uploads_dir/$filename";
        move_uploaded_file($fileupload['tmp_name'], $storeFile);
        $this->DB->exec(
            'INSERT INTO documents (name, description, filename, storage, fingerprint, nem_hash, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ? ,?, ?)',
            array(
                $name,                              // name
                $description,                       // description
                $fileupload['name'],                // filename
                $storeFile,                         // store file
                $fileHash,                          // fingerprint
                $nemHash,                           // nem_hash
                date('Y-m-d H:i:s'),                // created_at
                date('Y-m-d H:i:s')                 // updated_at
            )
        );
        $f3->reroute('/documents');
    }

    function verifier() {
        $f3 = Base::instance();
        $f3->set('page_title', "Document Management");
        $f3->set('page_subtitle', "Document::Verifier");
        $f3->set('content','templates/documents/verifier.htm');
        echo \Template::instance()->render('templates/layout/layout.htm');
    }

    function verify() {
        $f3 = Base::instance();

        $message = false;
        $documents = false;
        $fileupload = $_FILES['fileupload'];
        if (sizeof($fileupload) == 0 || $fileupload['tmp_name'] == "") {
            $f3->set('message','Your upload file is empty.');
        } else {
            $this->setup($f3);

            $fileHash = hash_file("sha256", $fileupload['tmp_name']);
            $nem_hash = $_POST['nem_hash'];

            // TODO: Get nem transaction by hash
            $result = $this->nemPhp->transactionGet(
                $nem_hash
            );

            // TODO: Compare fingerprint with xem message
            $validHash = false;
            if (isset($result['result']['transaction']['message']['payload'])) {
                $nem_fingerprint = $result['result']['transaction']['message']['payload'];
                $validHash = ($nem_fingerprint == $fileHash);
            }

            // TODO: filter nem transaction by hash
            if ($validHash) {
                $documents = $this->DB->exec(
                    'SELECT * FROM documents WHERE fingerprint=? AND nem_hash=?',
                    array(
                        $fileHash,
                        $nem_hash
                    )
                );
            } else {
                $f3->set('message', "ERROR: Invalid document");
            }
        }

        $f3->set('page_title', "Document Management");
        $f3->set('page_subtitle', "Document::Verifier");
        $f3->set('documents', $documents);
        $f3->set('content','templates/documents/verifier.htm');
        echo \Template::instance()->render('templates/layout/layout.htm');
    }

}
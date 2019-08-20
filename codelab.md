# Document Management - CodeLab

This is a simple document management program to allow you upload the document to server and calculate the fingerprint for verify the correct document.

Here you need to add feature to allow store the fingerprint to blockchain server. So that, other people cannot change the fingerprint from the database.

Before you start the codelab, please make sure you have copied ```/app/db/blank.sqlite``` to ```/app/db/database.sqlite```.


## Send XEM during upload document
1. Open ```app/controllers/DocumentController.php```
2. For ```// TODO: require nem-php```
```php
require_once 'lib/nem-php/nem-php.php';
require_once 'lib/nem-php/keypair.php';
```
3. Go to ```// TODO: Initial NEM connection```
```php
$env = $f3->get('env');
$config = [
    'net'               => $env['SERVICE_TYPE'],
    'nis_address'       => $env['NIS'],
    'private'           => $env['WALLET_APP_PRIVATE_KEY'],
    'public'            => $env['WALLET_APP_PUBLIC_KEY'],
    'security_check'    => true
];

$this->nemPhp = new NemPhp($config);
```
4. Go to ```// TODO: Insert send xem here```
```php
$env = $f3->get('env');
$receiver = $env['WALLET_DOC_ADDRESS'];
$amount = 0;
$message = $fileHash;
```
5. Go to ```// Prepare transaction```
```php
$this->nemPhp->prepareTransaction(
    $amount, //How much XEM to send 
    0, //Put higher fee if you want, otherwise leave it zero so minimum fee will be taken off
    $receiver, //adress where to send
    null,   //mosaics
    $fileHash, // message
    false // secure message
);
```
6. Go to ```// And commit transaction to the network```
```php
$result = $this->nemPhp->announceTransaction();

if ($result['result']['message'] == "SUCCESS") {
    $nemHash = $result['result']['transactionHash']['data'];
} else {
    $f3->set('message','ERROR ; ' . $result['result']['message']);    
    $f3->set('content','templates/documents/verifier.htm');
    echo \Template::instance()->render('templates/layout/layout.htm');
    return false;
}
```
7. Change the insert object
```php
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
```

## Change verifier UI
1. Open ```ui/templates/documents/verifier.htm```
2. Go to ```<!-- TODO: Insert nem hash input -->```
```html
<div class="form-group row">
    <label for="nem_hash" class="col-sm-2 col-form-label">NEM Hash</label>
    <div class="col-sm-10">
        <input type="text" class="form-control" name="nem_hash" id="nem_hash">
    </div>
</div>
```
3. Go to ```<!-- TODO: Print nem hash -->```
```html
<div class="row">
    <div class="col-md-2">NEM hash</div>
    <div class="col-md-10">
        <a href="http://testnet-explorer.nemtool.com/#/s_tx?hash={{ @document.nem_hash }}" target="_blank">{{ @document.nem_hash }}</a>
    </div>
</div>
```

## Change verifier
1. Open ```app/controllers/DocumentController.php```
2. Go to ```// TODO: Get nem transaction by hash```
```php
$result = $this->nemPhp->transactionGet(
    $nem_hash
);
```
3. Go to ```// TODO: Compare fingerprint with xem message```
```php
$validHash = false;
if (isset($result['result']['transaction']['message']['payload'])) {
    $nem_fingerprint = $result['result']['transaction']['message']['payload'];
    $validHash = ($nem_fingerprint == $fileHash);
}
```
4. Go to ```// TODO: filter nem transaction by hash```
```php
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
```
<?php
function array_key_exists_r($keys, $search_r) {
    if (!is_array($search_r)) return false;
    $keys_r = explode('|', $keys);
    foreach ($keys_r as $key) {
        if (!array_key_exists($key, $search_r)) return false;
    }
    return true;
}
class TransactionBuilder {
    private $type = null;
    private $version = null;
    private $signerlen = null;
    private $signer = null;
    private $fee = null;
    private $recipientlen = null;
    private $recipient = null;
    private $amount = null;
    private $timestamp = null;
    private $deadline = null;
    private $binary = "";
    private $message = array('payload' => '', 'type' => 1);
    private $decodedMessage = null;
    private $mosaics = false;
    public function __construct($input) {
        if (is_array($input)) {
            $this->setFromArray($input);
        }
    }
    public function setFromArray($input) {
        if (is_array($input) && array_key_exists_r('type|version|signer|recipient|amount', $input)) {
            $this->type = $input['type'];
            $this->version = $input['version'];
            $this->signer = $input['signer'];
            $this->recipient = $input['recipient'];
            $this->amount = $input['amount'];
            $this->timestamp = array_key_exists('timestamp', $input) ? $input['timestamp'] : timestamp2NEMTime(time());
            $this->deadline = array_key_exists('deadline', $input) ? $input['deadline'] : timestamp2NEMTime(time()) + 60 * 60 * 2;
            if (array_key_exists('fee', $input)) {
                $this->fee = $input["fee"];
            }
            if (array_key_exists('message', $input) && array_key_exists_r('type|payload', $input['message']) && $input['message']['type'] === 1 && ctype_xdigit($input['message']['payload']) && $this->decodedMessage = hex2bin($input['message']['payload'])) {
                $this->message = $input['message'];
            }
            if (array_key_exists('mosaics', $input)) {
                $this->mosaics = $input['mosaics'];
            }
            return true;
        } else {
            return false;
        }
    }
    private function build() {
        if (isset($this->type, $this->version, $this->signer, $this->recipient, $this->amount)) {
            if (!$this->fee) {
                $this->fee = calcFeeTransfer($this->amount);
                if ($this->message['payload']) {
                    $this->fee+= calcFeeMessage($this->decodedMessage);
                }
            }
            $this->binary = int2binary($this->type) . //Transaction type
            int2binary($this->version) . //Transaction version
            int2binary($this->timestamp) . //TimeStamp
            int2binary(strlen(hex2bin($this->signer))) . //Signer pubkey length ( always 32 )
            hex2bin($this->signer) . //Signer (sender) pubkey
            long2binary($this->fee) . //Transaction fee ( micro XEM )
            int2binary($this->deadline) . //Deadline
            int2binary(strlen($this->recipient)) . //Recipient address length ( always 40 )
            $this->recipient . //Recipient address
            long2binary($this->amount);
            if ($this->message['payload']) {
                $this->binary.= int2binary(8 + strlen($this->message['payload']) / 2) . int2binary($this->message['type']) . int2binary(strlen($this->message['payload']) / 2) . $this->decodedMessage;
            } else {
                $this->binary.= int2binary(0);
            }
            if (is_array($this->mosaics)) $this->binary.= $this->_mosaics($this->mosaics);
            return true;
        } else {
            return false;
        }
    }
    public function _mosaics($mosaics) {

        $binary = int2binary(sizeof($mosaics));
        foreach($mosaics as $mosaic) {
            $quantity = int2binary($mosaic['quantity']);
            $namespace_id = utf8_encode($mosaic['mosaicId']['namespaceId']);
            $namespace_id_len = int2binary(strlen($namespace_id));
            $name = utf8_encode($mosaic['mosaicId']['name']);
            $name_len = int2binary(strlen($name));
            $mosaic_id_structure = $namespace_id_len . $namespace_id . $name_len . $name;
            $mosaic_id_structure_len = int2binary(strlen($mosaic_id_structure));
            $mosaic_structure = $mosaic_id_structure_len . $mosaic_id_structure . $quantity;
            $mosaic_structure_len = int2binary(strlen($mosaic_structure));

            $binary .= $mosaic_structure_len;
            $binary .= $mosaic_id_structure_len;
            $binary .= $namespace_id_len;
            $binary .= $namespace_id;
            $binary .= $name_len;
            $binary .= $name;
            $binary .= $quantity;
        }

        return $binary;
    }

    public function getBinary() {
        $retval = $this->build();
        return $retval ? $this->binary : false;
    }
    public function getHex() {
        $retval = $this->build();
        return $retval ? bin2hex($this->binary) : false;
    }
}
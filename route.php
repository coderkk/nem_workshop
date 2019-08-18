<?php
if (!preg_match('/\.(?:ini|htaccess|cfg|env|sqlite|htm)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // serve the requested resource as-is.
}
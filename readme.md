# NEM Workshop

## Clone the repository
git clone https://github.com/coderkk/nem_workshop.git

## Before you start the server
1. Copy ```.env.exmaple.ini``` to ```.env.ini```
2. Copy ```/app/db/blank.sqlite``` to ```/app/db/database.sqlite```

## Configure PHP.ini
[PHP]

; Maximum execution time of each script, in seconds
; http://php.net/max-execution-time
; Note: This directive is hardcoded to 0 for the CLI SAPI
max_execution_time = 30

error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT

; Maximum allowed size for uploaded files.
; http://php.net/upload-max-filesize
upload_max_filesize = 8M

extension_dir = "D:\MY\Tools\Server\cmderPHP\cmderPHP\bin\php\php-7.3.7-x64\ext"

extension=curl
extension=fileinfo
extension=mbstring
extension=openssl
extension=pdo_sqlite
extension=sqlite3

## For XAMPP user:
Copy this folder to your ```xampp/htdocs``` folder

## For using php build-in webserver
run start-server.bat

or 

php -S 127.0.0.1:8080 -t .


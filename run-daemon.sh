#!/bin/bash
PHP=/home/developer/local/bin/php55
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

until `$PHP $DIR/unicron.php >> $DIR/logs/unicron.log 2>> $DIR/logs/unicron_error.log`; do
    echo "`date +'[%H:%M:%S %m-%d-%Y]'` Unicron crashed" >> $DIR/logs/unicron_error.log
    sleep 1
done

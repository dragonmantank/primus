#!/bin/bash
PHP=/home/developer/local/bin/php55
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

$PHP $DIR/unicron.php >> $DIR/logs/unicron.log 2>> $DIR/logs/unicron_error.log &

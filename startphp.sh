#!/bin/bash

THIS_SCRIPT="$0"

CODE_TIP=`dirname "$THIS_SCRIPT"`/public
CODE_TIP=`cd "$CODE_TIP"; pwd`
echo $CODE_TIP

/usr/bin/php -S localhost:8889 -t "$CODE_TIP" &

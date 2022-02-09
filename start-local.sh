#!/usr/bin/env bash
export XDEBUG_CONFIG="idekey=IDEA_DEBUG_STORE"
DB_HOST=127.0.0.1 DB_PORT=25433 php -S 127.0.0.1:9393 -t public

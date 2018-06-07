#!/bin/sh 

export PATH=/usr/local/php-5.2.17/bin:$PATH

echo "restart worker"

python stop_worker.py device_user_package
python start_worker.py device_user_package 5
python stop_worker.py sync_filter_cache
python start_worker.py sync_filter_cache 1
python stop_worker.py set_resolution
python start_worker.py set_resolution 5
python stop_worker.py device_user
python start_worker.py device_user 5
python stop_worker.py refresh_lack
python start_worker.py refresh_lack 5
python stop_worker.py update_lack
python start_worker.py update_lack 5
python stop_worker.py clear_html_cache
python start_worker.py clear_html_cache 1

echo "ok"
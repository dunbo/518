#-*- coding: utf-8 -*- 
import os, sys, glob, re; 

task_dir = os.path.split(os.path.realpath(__file__))[0];
worker_dir = task_dir + '/worker';

def get_worker_file(worker):
    if worker.find('@') == -1 :
        worker = re.sub("[^a-zA-Z_0-9]", "", worker);
    else:
        worker = worker.replace('@', '/');
    worker_file = '%s/%s_worker.php'% (worker_dir, worker);

    if not os.path.exists(worker_file):
        return False;
    else:
        return worker_file;

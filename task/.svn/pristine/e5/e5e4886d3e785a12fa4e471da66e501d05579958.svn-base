#-*- coding: utf-8 -*- 
from init import *

if __name__ == '__main__':
    for run_file in glob.glob(task_dir + '/*.run'):
        run_worker = os.path.basename(run_file).replace('.run', '');
        worker_file = get_worker_file(run_worker);
        if worker_file == False:
            continue;
        fd = os.popen('ps aux|grep "php %s"|grep -v "grep"'% (worker_file));
        if fd.read() == '':
            run_fp = open(run_file, 'r');
            num = int(run_fp.read());
            run_fp.close();
            os.system("python %s/start_worker.py '%s' '%s'"% (task_dir, run_worker, num));

        fd.close();

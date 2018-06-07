#-*- coding: utf-8 -*- 
from init import *

if __name__ == '__main__':
    if len(sys.argv) == 2:
        worker = str(sys.argv[1]);
        worker_file = get_worker_file(worker); 
        if worker_file == False:
            print '该worker %s 不存在'% (worker);
            sys.exit();
            
        os.system('kill -9 `ps aux|grep "php %s"|grep -v "grep"|awk \'{print $2}\'`'% (worker_file) );
        run_file = '%s/%s.run'% (task_dir, worker);
        if os.path.exists(run_file):
            os.remove(run_file);

    else:
        print '参数1 要停止的worker';
        sys.exit();

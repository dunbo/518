#-*- coding: utf-8 -*-
#python /data/www/wwwroot/new-wwwroot/task/start_worker.py zhuang@christmas_lottery 1 调用
from init import *

if __name__ == '__main__':
    if os.path.isfile('/usr/local/php-5.2.17/bin/php'):
        php = '/usr/local/php-5.2.17/bin/php'
    elif os.path.isfile('/usr/local/php-5.2.14/bin/php'):
        php = '/usr/local/php-5.2.14/bin/php'
    else:
        php = strip(os.popen('ls /usr/local/php-*/bin/php|tail -n 1'))
        if not os.path.isfile(php):
            php = 'php'
    if len(sys.argv) == 3:
        worker = str(sys.argv[1]);
        num = int(sys.argv[2]);
        if num == 0 or worker == '':
            print '参数1 要执行的worker \n 参数2 开启的进程数';
            sys.exit();

        worker_file = get_worker_file(worker);
        if worker_file == False:
            print '该worker %s 不存在'% (worker);
            sys.exit();
        for i in range(0, num):
            os.system('nohup %s %s > /tmp/%s_worker.%s.log 2>&1 &'% (php, worker_file, worker, i));

        run_file = '%s/%s.run'% (task_dir, worker);
        run_fp = open(run_file, 'w');
        run_fp.write(str(num));
        run_fp.close();

    else:
        print '参数1 要执行的worker \n 参数2 开启的进程数';
        sys.exit();

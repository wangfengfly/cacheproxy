多进程并行处理指定的函数任务:

具体用例请参考Demo.php
1. 为什么在每个子进程里面单独建立pdo连接？
因为如果子进程之间共享pdo连接的话，一个子进程先执行退出，会导致其他子进程查询失败。
2. 为什么curl会得到status code为0的情况？
https://www.cnblogs.com/snake-hand/archive/2013/06/09/3130076.html
因为如果子进程查询过快，对方服务器的端口被占满。

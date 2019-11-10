### 环境
+ 机器 192.168.1.103  和 192.168.145.128 
   
    CentOS Linux release 7.5.1804 (Core)
    
    mysql5.6
###### 搭建
    
   + linux环境请自行安装
   
   + mysql安装
   ```shell
[root@localhost ~]# wget http://repo.mysql.com/mysql-community-release-el7-5.noarch.rpm //下载yum源   
[root@localhost ~]# rpm -ivh mysql-community-release-el7-5.noarch.rpm  //更改yum源


[root@localhost ~]# yum repolist all | grep mysql   //查看是否成功
mysql-connectors-community/x86_64 MySQL Connectors Community     enabled:    131
mysql-connectors-community-source MySQL Connectors Community - S disabled
mysql-tools-community/x86_64      MySQL Tools Community          enabled:    100   //注意enable为当前指定版本
mysql-tools-community-source      MySQL Tools Community - Source disabled
mysql55-community/x86_64          MySQL 5.5 Community Server     disabled
mysql55-community-source          MySQL 5.5 Community Server - S disabled
mysql56-community/x86_64          MySQL 5.6 Community Server     enabled:    496   //注意enable为当前指定版本
mysql56-community-source          MySQL 5.6 Community Server - S disabled
mysql57-community-dmr/x86_64      MySQL 5.7 Community Server Dev disabled
mysql57-community-dmr-source      MySQL 5.7 Community Server Dev disabled



[root@localhost ~]# yum install mysql-community-server   //执行会自动检查，并下载相应依赖

[root@localhost ~]# systemctl start mysql  //启动

   ```
### 原理及优缺点

    mysql从3.23版本开始提供复制功能，复制主要是将主库的DDL和DML操作通过日志（binlog）记录下来，然后从库再执行一遍这些语句，
    来达到复制的效果。mysql支持一台主服务器同时向多台从服务器进行复制，从服务器同时也可以作为其他服务器的主服务器，实现链状的复制。
主库：

   + 开启二进制日志
   + 配置为一的server-id
   + 创建一个用于slave和master通信的账号
   
从库 
    
   + 配置主库唯一的server-id
   + 使用master分配的账号拉取master是的binlog文件
   + 启用salve服务
 
    
##### 优点
    
+ 如果主库出现异常无法提供服务，可以快速由从库来替代它，保证服务高可用性。
+ 可以实现读写分离，再主库上执行insert和update，在从库上执行select，来提高服务的吞吐。
+ 可以在从库进行备份。

##### 缺点

缺点显而易见的，由于是异步复制，所以会有延迟，对于实时性要求高的场景支持性不太好。


### 配置

#### 主库配置
mysql配置文件添加如下设置
```shell
[root@localhost ~]# vim /etc/my.cnf

    #主从复制配置
    innodb_flush_log_at_trx_commit=1
    sync_binlog=1
    ##需要备份的数据库
    binlog-do-db=orders
    ##不需要备份的数据库
    binlog-ignore-db=mysql
    #
    ##启动二进制文件
    log-bin=mysql-bin
    #
    ##服务器ID
    server-id=1
    #
    ## Disabling symbolic-links is recommended to prevent assorted security risks
    symbolic-links=0

```
登录mysql创建用户
```$xslt
[root@localhost ~]# mysql -uroot -p

mysql> CREATE USER salve@192.168.145.128 IDENTIFIED BY 'salve@123456';  //创建用户
Query OK, 0 rows affected (0.00 sec)

mysql> GRANT REPLICATION SLAVE ON *.* TO salve@192.168.145.128;   //分配权限
Query OK, 0 rows affected (0.00 sec)

mysql> FLUSH PRIVILEGES;        //刷新权限
Query OK, 0 rows affected (0.00 sec)


mysql> select host,user,password from user;  //查看是否创建成功
+-----------------------+-------+-------------------------------------------+
| host                  | user  | password                                  |
+-----------------------+-------+-------------------------------------------+
| localhost             | root  |                                           |
| localhost.localdomain | root  |                                           |
| 127.0.0.1             | root  |                                           |
| ::1                   | root  |                                           |
| localhost             |       |                                           |
| localhost.localdomain |       |                                           |
| 192.168.145.128       | salve | *BCEF75F7964A84DA9568D6397F47443C7EC771D7 |
+-----------------------+-------+-------------------------------------------+
7 rows in set (0.00 sec)


mysql> show master status;   //查看binlog文件名称和位置，后边从库配置需要用。
+------------------+----------+--------------+------------------+-------------------+
| File             | Position | Binlog_Do_DB | Binlog_Ignore_DB | Executed_Gtid_Set |
+------------------+----------+--------------+------------------+-------------------+
| mysql-bin.000002 |      120 | orders       | mysql            |                   |
+------------------+----------+--------------+------------------+-------------------+
1 row in set (0.00 sec)

```

#### 从库配置

mysql配置文件增加如下配置 
```$xslt
[root@localhost ~]# vim /etc/my.cnf

server-id=2 //这个值必须全局唯一
```

登录mysql服务器
```php  //设置同步配置
mysql> CHANGE MASTER TO 
    -> MASTER_HOST='192.168.1.103',
    -> MASTER_USER='salve',
    -> MASTER_PASSWORD='savle@123456',
    -> MASTER_LOG_FILE='mysql-bin.000002',
    -> MASTER_LOG_POS=120;
Query OK, 0 rows affected, 2 warnings (0.00 sec)

mysql> start slave;  //开启同步进程
Query OK, 0 rows affected (0.00 sec)
```

查看是否成功
```php
mysql> show slave status\G;
*************************** 1. row ***************************
               Slave_IO_State: Connecting to master
                  Master_Host: 192.168.1.103
                  Master_User: salve
                  Master_Port: 3306
                Connect_Retry: 60
              Master_Log_File: mysql-bin.000002
          Read_Master_Log_Pos: 120
               Relay_Log_File: mysqld-relay-bin.000002
                Relay_Log_Pos: 4
        Relay_Master_Log_File: mysql-bin.000002
             Slave_IO_Running: Connecting    //成功的话这里应该是Yes
            Slave_SQL_Running: Yes           //成功的话这里是Yes
              Replicate_Do_DB: 
          Replicate_Ignore_DB: 
           Replicate_Do_Table: 
       Replicate_Ignore_Table: 
      Replicate_Wild_Do_Table: 
  Replicate_Wild_Ignore_Table: 
                   Last_Errno: 0
                   Last_Error: 
                 Skip_Counter: 0
          Exec_Master_Log_Pos: 120
              Relay_Log_Space: 120
              Until_Condition: None
               Until_Log_File: 
                Until_Log_Pos: 0
           Master_SSL_Allowed: No
           Master_SSL_CA_File: 
           Master_SSL_CA_Path: 
              Master_SSL_Cert: 
            Master_SSL_Cipher: 
               Master_SSL_Key: 
        Seconds_Behind_Master: NULL
Master_SSL_Verify_Server_Cert: No
                Last_IO_Errno: 2003
                Last_IO_Error: error connecting to master 'salve@192.168.1.103:3306' - retry-time: 60  retries: 1
               Last_SQL_Errno: 0
               Last_SQL_Error: 
  Replicate_Ignore_Server_Ids: 
             Master_Server_Id: 0
                  Master_UUID: 
             Master_Info_File: /var/lib/mysql/master.info
                    SQL_Delay: 0
          SQL_Remaining_Delay: NULL
      Slave_SQL_Running_State: Slave has read all relay log; waiting for the slave I/O thread to update it
           Master_Retry_Count: 86400
                  Master_Bind: 
      Last_IO_Error_Timestamp: 191110 18:44:51
     Last_SQL_Error_Timestamp: 
               Master_SSL_Crl: 
           Master_SSL_Crlpath: 
           Retrieved_Gtid_Set: 
            Executed_Gtid_Set: 
                Auto_Position: 0
1 row in set (0.00 sec)

```
可以看到我这里并没有成功，下边排查一下原因，一般会有一下几种原因。
+ 网络不通
+ 账户密码错误
+ 防火墙
+ mysql配置文件问题
+ 连接服务器时语法
+ 主服务器mysql权限

下边一项一项检查。

   因为我这两台都是虚拟机，且都是采用桥接的方式上网，并且相互之间可以ping通，固排除网络问题。
   ```shell
    [root@localhost ~]# ping 192.168.1.103
    PING 192.168.1.103 (192.168.1.103) 56(84) bytes of data.
    64 bytes from 192.168.1.103: icmp_seq=1 ttl=128 time=0.538 ms
    64 bytes from 192.168.1.103: icmp_seq=2 ttl=128 time=0.668 ms
    64 bytes from 192.168.1.103: icmp_seq=3 ttl=128 time=0.370 ms
   ``` 
   检查权限，发现在从库服务器上无法连接到主库，问题应该出现在这里
   ```shell
   [root@localhost ~]# mysql -usalve -h192.168.1.103 -p
   Enter password: 
   ERROR 2003 (HY000): Can't connect to MySQL server on '192.168.1.103' (111)
   ```
   检查端口发现22可以连接，但是3306无法连接
   ```shell
   [root@localhost ~]# telnet 192.168.1.103 22
    Trying 192.168.1.103...
    Connected to 192.168.1.103.
    Escape character is '^]'.
    SSH-2.0-OpenSSH_7.4

    [root@localhost ~]# telnet 192.168.1.103 3306
    Trying 192.168.1.103...
    ^C
   ```
   查看主库服务器防火墙
   ```shell
   [root@localhost ~]# firewall-cmd --state
   running
   关掉防火墙
   [root@localhost ~]# systemctl stop firewalld.service
   [root@localhost ~]# systemctl disable firewalld.service
   Removed symlink /etc/systemd/system/multi-user.target.wants/firewalld.service.
   Removed symlink /etc/systemd/system/dbus-org.fedoraproject.FirewallD1.service.
   ```
   重启后再次查看
   ```shell
   mysql> show slave status\G
   *************************** 1. row ***************************
                  Slave_IO_State: Waiting for master to send event
                     Master_Host: 192.168.1.103
                     Master_User: salve
                     Master_Port: 3306
                   Connect_Retry: 60
                 Master_Log_File: mysql-bin.000004
             Read_Master_Log_Pos: 120
                  Relay_Log_File: mysqld-relay-bin.000002
                   Relay_Log_Pos: 283
           Relay_Master_Log_File: mysql-bin.000004
                Slave_IO_Running: Yes   //成功
               Slave_SQL_Running: Yes   //成功
                 Replicate_Do_DB: 
             Replicate_Ignore_DB: 
              Replicate_Do_Table: 
          Replicate_Ignore_Table: 
         Replicate_Wild_Do_Table: 
     Replicate_Wild_Ignore_Table: 
                      Last_Errno: 0
                      Last_Error: 
                    Skip_Counter: 0
             Exec_Master_Log_Pos: 120
                 Relay_Log_Space: 457
                 Until_Condition: None
                  Until_Log_File: 
                   Until_Log_Pos: 0
              Master_SSL_Allowed: No
              Master_SSL_CA_File: 
              Master_SSL_CA_Path: 
                 Master_SSL_Cert: 
               Master_SSL_Cipher: 
                  Master_SSL_Key: 
           Seconds_Behind_Master: 0
   Master_SSL_Verify_Server_Cert: No
                   Last_IO_Errno: 0
                   Last_IO_Error: 
                  Last_SQL_Errno: 0
                  Last_SQL_Error: 
     Replicate_Ignore_Server_Ids: 
                Master_Server_Id: 1
                     Master_UUID: 72b04edc-037b-11ea-bee5-000c294e3911
                Master_Info_File: /var/lib/mysql/master.info
                       SQL_Delay: 0
             SQL_Remaining_Delay: NULL
         Slave_SQL_Running_State: Slave has read all relay log; waiting for the slave I/O thread to update it
              Master_Retry_Count: 86400
                     Master_Bind: 
         Last_IO_Error_Timestamp: 
        Last_SQL_Error_Timestamp: 
                  Master_SSL_Crl: 
              Master_SSL_Crlpath: 
              Retrieved_Gtid_Set: 
               Executed_Gtid_Set: 
                   Auto_Position: 0
   1 row in set (0.00 sec)

   ```






































       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
           
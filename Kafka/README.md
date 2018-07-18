## Kafka

  * [install](install_kafka)
  * [online train](https://github.com/clotyxf/learningflow/tree/master/Kafka/online_train/kernel.py)


## Install Kafka
#### 1.安装 kafka [kafka.apache.org](http://kafka.apache.org/)
```
mkdir -p /usr/local/kafka_2.12-1.1.0/

cd /usr/local/kafka_2.12-1.1.0/

wget http://apache.claz.org/kafka/1.1.0/kafka_2.12-1.1.0.tgz

tar -xvf kafka_2.12-1.1.0.tgz
```

#### 2.复制配置文件到`kafka`目录下的`config`目录中:
```
%ls config/
cp config/ /usr/local/kafka_2.12-1.1.0/config/
```

#### 3.kafka服务运行:
3.1 启动 zookeeper
```
bin/zookeeper-server-start.sh config/zookeeper.properties
```
3.2 启动 server
```
bin/kafka-server-start.sh  config/server.properties
```
3.3 创建Topic
```
bin/kafka-topics.sh --create --zookeeper localhost:2181 --replication-factor 1 --partitions 1 --topic test
```
3.4 查看Topic
```
bin/kafka-topics.sh --list --zookeeper localhost:2181
```
3.5 测试消息接收发送-创建两个使用screen创建两个窗口分开运行如下命令:
```
bin/kafka-console-producer.sh --broker-list localhost:9092 --topic test
```
```
bin/kafka-console-consumer.sh --zookeeper localhost:2181 --topic test --from-beginning
```
#### 4. 使用`supervisord`运行
4.1 install
```
yum install supervisord
```
4.2修改配置文件 `vi /etc/supervisord.conf`
```
[include]
;files = supervisord.d/*.ini
files = /etc/supervisor/conf.d/*.conf
```

4.3 program配置 `vi /etc/supervisor/conf.d/kafka_server.conf`

```
[program:kafka-zookeeper-server]
command = bin/zookeeper-server-start.sh config/zookeeper.properties
autostart=true
autorestart=true
priority=5
stdout_events_enabled=true
stderr_events_enabled=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
directory=/usr/local/kafka_2.12-1.1.0/
stopsignal=QUIT


[program:kafka-server]
command = bin/kafka-server-start.sh  config/server.properties
autostart=true
autorestart=true
priority=5
stdout_events_enabled=true
stderr_events_enabled=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
directory=/usr/local/kafka_2.12-1.1.0/
stopsignal=QUIT
```
4.4 运行 `supervisord`
```
supervisord -c /etc/supervisord.conf
```

4.5 查看运行状态 `supervisorctl status` 输出如下内容:
```
kafka-server                     RUNNING   pid 5007, uptime 0:07:15
kafka-zookeeper-server           RUNNING   pid 5006, uptime 0:07:15
```
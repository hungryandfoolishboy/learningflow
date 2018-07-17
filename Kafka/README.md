## Kafka

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
bin/kafka-topics.sh --list --zookeeper localhost:2181
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

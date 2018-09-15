# Flume + KafKa

flume + kafka 实现收集服务器日志

  * [Kafka](https://github.com/clotyxf/learningflow/tree/master/Kafka/README.md)
  * [Flume](#flume)

## Flume

### 1.1 安装 [flume](https://flume.apache.org/download.html)

```linux
wget http://mirrors.hust.edu.cn/apache/flume/1.8.0/apache-flume-1.8.0-bin.tar.gz

tar -xvf apache-flume-1.8.0-bin.tar.gz

mv apache-flume-1.8.0-bin/ /usr/local/flume-1.8.0
```

### 1.2 配置`flume`

```bash
cd /usr/local/flume-1.8.0
```

1.2.1 配置`flume-env.sh`

```linux
cp conf/flume-env.sh.template conf/flume-env.sh

vi conf/flume-env.sh

# 添加 JAVA_HOME
export JAVA_HOME=/opt/java/jdk1.8.0_181
```

1.2.2 配置`properties`

>vi conf/flume-test-conf.properties

```vim
# a1可以看做是flume服务的名称，每个flume都由sources、channels和sinks三部分组成
# sources可以看做是数据源头、channels是中间转存的渠道、sinks是数据后面的去向
agent.sources = ng_log
agent.sinks = kfk_launcher
agent.channels = ch_launcher

# source
# 源头类型是TAILDIR，就可以实时监控以追加形式写入文件的日志
agent.sources.ng_log.type = TAILDIR
# positionFile记录所有监控的文件信息
agent.sources.ng_log.positionFile = /usr/local/flume-1.8.0/position/launcherclick/taildir_position.json
# 监控的文件组
agent.sources.ng_log.filegroups = f1
# 文件组包含的具体文件，也就是我们监控的文件
agent.sources.ng_log.filegroups.f1 = /data/sites/laravel/laravel5.6/storage/logs/.*

# 仅针对单个日志文件
#agent.sources.ng_log.type = exec
#agent.sources.ng_log.command = tail -F /data/sites/laravel/laravel5.6/storage/logs/laravel-2018-09-06.log


# interceptor
# 写kafka的topic即可
agent.sources.ng_log.interceptors = i1
agent.sources.ng_log.interceptors.i1.type=regex_filter
agent.sources.ng_log.interceptors.i1.regex = ^\\[\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}\].*\\[cloty\\].*
agent.sources.ng_log.interceptors.i1.excludeEvents = false


# channel
agent.channels.ch_launcher.type = memory
agent.channels.ch_launcher.capacity = 10000
agent.channels.ch_launcher.transactionCapacity = 1000

# kfk sink
# 指定sink类型是Kafka，说明日志最后要发送到Kafka
agent.sinks.kfk_launcher.type = org.apache.flume.sink.kafka.KafkaSink
# Kafka broker
agent.sinks.kfk_launcher.kafka.topic = cloty # kafka  topic
agent.sinks.kfk_launcher.kafka.bootstrap.servers = kafka-host:9092 # kafka 地址
agent.sinks.kfk_launcher.kafka.flumeBatchSize = 1
agent.sinks.kfk_launcher.kafka.producer.acks = 1
agent.sinks.kfk_launcher.kafka.producer.linger.ms = 1
agent.sinks.kfk_launcher.kafka.producer.compression.type = gzip
agent.sinks.k1.serializer.class=kafka.serializer.StringEncoder

# Bind the source and sink to the channel
agent.sources.ng_log.channels = ch_launcher
agent.sinks.kfk_launcher.channel = ch_launcher
```

### 1.3启动`Flume`

```
bin/flume-ng agent -c conf/ --conf-file conf/conf/flume-test-conf.properties --name agent -Dflume.root.logger=INFO,console
```

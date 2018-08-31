## hadoop

  * [install](install_hadoop)

## Install Hadoop
#### 1.安装 hadoop [hadoop.apache.org](http://hadoop.apache.org/releases.html)

```linux
cd /usr/local

wget http://mirrors.hust.edu.cn/apache/hadoop/common/hadoop-3.1.1/hadoop-3.1.1.tar.gz

tar -xvf hadoop-3.1.1.tar.gz

mv hadoop-3.1.1 hadoop
```

1.1下载java

```linux
cd /opt
wget 'http://download.oracle.com/otn-pub/java/jdk/8u181-b13/96a7b8442fe848ef90c96a2fad6ed6d1/jdk-8u181-linux-x64.tar.gz?AuthParam=1535687458_0404ee288d8da8b2e04f8e395f15a47a' -O jdk-8u181-linux-x64.tar.gz

mkdir java

tar -zxvf jdk-8u181-linux-x64.tar.gz -C java/

```

1.2配置java和hadoop
```
vi /etc/profile

export JAVA_HOME=/opt/java/jdk1.8.0_181
export JRE_HOME=/opt/java/jdk1.8.0_181/jre
export CLASSPATH=$:CLASSPATH:$JAVA_HOME/lib/
export PATH=$PATH:$JAVA_HOME/bin
export CLASSPATH=.:$JAVA_HOME/lib/dt.jar:$JAVA_HOME/lib/tools.jar:$JRE_HOME/lib/rt.jar:$JRE_HOME/lib
export PATH=$JAVA_HOME/bin:$JRE_HOME/bin:$PATH
export HADOOP_HOME=/usr/local/hadoop
export PATH=$PATH:$JAVA_HOME/bin:$JRE_HOME/bin:$HADOOP_HOME/bin
```

> `source /etc/profile` 更新Linux环境配置，可以通过`java -version` 验证是否成功

1.3更改hostname && hosts

```linux
vi /etc/hostname
master

vi /etc/hosts

#必须为内网地址
x.x.x.x master
#如部署集群 则需要加入集群主机名
```

1.4关闭防火墙

```linux
systemctl stop firewalld.service
systemctl disable firewalld.service
systemctl status firewalld.service 
```

1.5新建hadoop所需目录

```linux
mkdir  /home/hadoop
mkdir  /home/hadoop/tmp
mkdir  /home/hadoop/var
mkdir  /home/hadoop/dfs
mkdir  /home/hadoop/dfs/name
mkdir  /home/hadoop/dfs/data
```

1.6修改`etc/hadoop`中的一系列配置文件

> core-site.xml

```vi
<configuration>
       <property>
                <name>fs.defaultFS</name>
                <value>hdfs://master:9000</value>
       </property>
       <property>
                <name>io.file.buffer.size</name>
                <value>131072</value>
        </property>
       <property>
               <name>hadoop.tmp.dir</name>
               <value>file:/home/hadoop/tmp/</value>
               <description>A base for other temporary   directories.</description>
       </property>
</configuration>
```

>hadoop-env.sh

```vi
#export JAVA_HOME=${JAVA_HOME}
export JAVA_HOME=/opt/java/jdk1.8.0_181
```

>hdfs-site.xml

```vi
<configuration>
     <property>
             <name>dfs.namenode.name.dir</name>
             <value>file:///home/hadoop/dfs/name</value>
       </property>
      <property>
              <name>dfs.datanode.data.dir</name>
              <value>file:///home/hadoop/dfs/data</value>
       </property>
</configuration>
```

>mapred-site.xml

```vi
cp etc/hadoop/mapred-site.xml.template etc/hadoop/mapred-site.xml
<configuration>
    <property>
        <name>mapreduce.framework.name</name>
        <value>yarn</value>
    </property>
    <property>
        <name>mapreduce.jobhistory.address</name>
        <value>master:10020</value>
    </property>
    <property>
        <name>mapreduce.jobhistory.webapp.address</name>
        <value>master:19888</value>
    </property>
    <property>
        <name>mapreduce.jobtracker.http.address</name>
        <value>master:50030</value>
    </property>
    <property>
        <name>mapred.job.tracker</name>
        <value>master:9001</value>
    </property>
</configuration>
```

>slaves

```vi
# localhost
# 如果有配置cluster 填写主机名
server2
```

> yarn-site.xml

```vi
<configuration>
    <!-- Site specific YARN configuration properties -->
    <property>
        <name>yarn.nodemanager.aux-services</name>
        <value>mapreduce_shuffle</value>
    </property>
    <property>
        <name>yarn.nodemanager.aux-services.mapreduce.shuffle.class</name>
        <value>org.apache.hadoop.mapred.ShuffleHandler</value>
    </property>
    <property>
        <name>yarn.resourcemanager.hostname</name>
        <value>master</value>
    </property>
    <property>
        <name>yarn.resourcemanager.address</name>
        <value>master:8032</value>
    </property>
    <property>
        <name>yarn.resourcemanager.scheduler.address</name>
        <value>master:8030</value>
    </property>
    <property>
        <name>yarn.resourcemanager.resource-tracker.address</name>
        <value>master:8031</value>
    </property>
    <property>
        <name>yarn.resourcemanager.admin.address</name>
        <value>master:8033</value>
    </property>
    <property>
        <name>yarn.resourcemanager.webapp.address</name>
        <value>master:8088</value>
    </property>
</configuration>
```

#### 2.搭建集群

发送`dfs`内容给server1:

```linux
scp -r /home/hadoopdir/dfs/* slave1:/home/hadoopdir/dfs
```

发送`hadoop`文件给数据节点：

```linux
scp -r /usr/local/hadoop/* server1:/usr/local/hadoop/
```

2.1启动Hadoop

```linux
bin/hdfs namenode -format
sbin/start-dfs.sh
```

> 通过`jps` 命令查看是否运行正常

```linux
2736 NodeManager
2641 ResourceManager
2290 NameNode
2003 SecondaryNameNode
1849 DataNode
3306 Jps
```

如不存在 NameNode|DataNode 可以通过输出的log文件查看异常。若不存在`ResourceManager`，可以通过以下命令运行：

```linux
sbin/start-yarn.sh
```

可以登录网页查看：http：//master:50070 (查看live node)

查看yarn环境http：//master:8088
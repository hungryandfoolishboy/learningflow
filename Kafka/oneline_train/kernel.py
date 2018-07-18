from pyspark import SparkContext
from pyspark import SparkConf
from pyspark.streaming import StreamingContext
from pyspark.streaming.kafka import KafkaUtils
from pyspark.mllib.classification import StreamingLogisticRegressionWithSGD
from kafka import KafkaConsumer


################################################################################
####################USE pickle GET LATEST CHECKPOINT ##########################
################################################################################

oldsetOffset = 0
latestOffset = 0

class MingleModel:
    def __init__(self):
        pass

    def update(self):
        pass

class OnlineTrain(StreamingLogisticRegressionWithSGD):
    def __init__(self, *args, **kwargs):
        super(OnlineTrain, self).__init__(*args, **kwargs)
        self._model = MingleModel()
        self.oldsetOffset = oldsetOffset
    
    def trainOn(self, dstream):
        self._validate(dstream)

        def update(rdd):
            u_f = False # 是否更新checkpoints
            rdd = rdd.collect()
            for _, r in rdd:
                u_f = True
                self._model.update()

            if u_f and self.oldsetOffset < latestOffset:
                self.oldsetOffset = latestOffset
                ################################################################################
                ####################USE pickle SAVE LATEST CHECKPOINT ##########################
                ################################################################################
        dstream.foreachRDD(update)


offsetRanges = []
def store_offset_ranges(rdd):
    global offsetRanges
    offsetRanges = rdd.offsetRanges()
    return rdd

fromOffset=0
def get_latest_offset_ranges(rdd):
    """ get latest offset
    """
    global latestOffset
    global fromOffset
    latestOffset = offsetRanges[-1].untilOffset
    fromOffset = offsetRanges[-1].fromOffset

def start_direct_stream():
    sconf = SparkConf()
    sconf.set('spark.cores.max', 8)
    sc = SparkContext(appName='KafkaDirectWordCount', conf=sconf)
    ssc = StreamingContext(sc, 1)
    model = OnlineTrain()
    brokers = 'you kafka server url'
    topic = 'you topic'
    directKafkaStream = KafkaUtils.createDirectStream(
        ssc, [topic], kafkaParams={"metadata.broker.list": brokers})
    directKafkaStream.transform(store_offset_ranges).foreachRDD(get_latest_offset_ranges)
    model.trainOn(directKafkaStream)
    ssc.start()             # Start the computation
    ssc.awaitTermination()  # Wait for the computation to terminate

if __name__ == '__main__':
    start_direct_stream()
<?php

/*
|--------------------------------------------------------------------------
| 数据导出统一 Service TODO:使用队列
|--------------------------------------------------------------------------
 */

namespace App\Services;

use App\Services\Export\DataService as Builder;

class ExportService extends Builder
{
    
	/**
	 * 数据导出队列缓存数据
	 * @var string
	 */
	const EXPORTSERVICEQUEUE = 'export-queue-array';

	/**
	 * 数据导出完成进度
	 * @var string
	 */
	const EXPORTCOMPELTEPROCESS = 'export-process';


    /**
     * 执行导出作业
     * @author cloty
     * @datetime 2017-10-20T15:53:16+080
     * @version  1.0
     * @param    \Illuminate\Eloquent\Model $model
     * @param    string $method
     * @param    boolean $hasGroupBy
     * @return   boolean
     */
    public function handle($model, $method = '', $hasGroupBy = false)
    {
        $this->fileName = request('export_name');
        $requestData = request()->all();
        $url = request()->url();

        if (!empty($this->fileName) && in_array($this->fileName, $this->cacheQueueUrl())) {
            $this->page = (int) request('page', 1);

            if ($this->md5Sign($this->page) != request('sign')) {
                $this->completeSize = 100;
                $this->clearCacheProcess();
                return $this->finishJob('', 500);
            }

            $this->totalPage = $this->calCountPage($model, $hasGroupBy);
            $model = $this->initLimitModel($model);
            $initData = $this->initializeData($this->fileName);
            $result = $this->{$method}($model, $initData['exportService']);

            $requestData['page'] = $requestData['page'] + 1;
            $requestData['sign'] = $this->md5Sign($requestData['page']);
            $url = $url . '?' . http_build_query($requestData);

            return $this->finishJob($url);
        }

        $initData = $this->initializeData();

        $requestData['export_name'] = $initData['fileName'];
        $requestData['page'] = 1;
        $requestData['sign'] = $this->md5Sign($requestData['page']);
        $url = $url . '?' . http_build_query($requestData);

        return $this->finishJob($url);
    }

    /**
     * initialize data
     * @author cloty
     * @datetime 2017-10-17T13:42:37+080
     * @version  1.0
     * @return   [type] [description]
     */
    public function initializeData($fileName = '')
    {
        $exportService = app('export.csv');

        $exportService->setFileName($fileName);

        $this->fileName = $exportService->getFileName();

        if (empty($fileName)) {
            $this->setCacheQueueUrl();
        }

        $fileName = $exportService->getFileName();

        return compact('exportService', 'fileName');
    }

    /**
     * 执行导出任务 TODO:使用队列，现在的方式不安全
     * @author cloty
     * @datetime 2017-10-17T14:16:18+080
     * @version  1.0
     * @param    \Illuminate\Eloquent\Model $model
     * @param    array $initData
     * @param    string $method
     */
    public function startJob($model, $initData, $method)
    {
        // $job = (new \App\Jobs\ExportDataJob($model, $initData['fileName'], $method))->onQueue('queue');
        //
        // dispatch($job);
    }

    /**
     * 执行导出完成任务
     * @author cloty
     * @datetime 2017-10-17T14:19:27+080
     * @version  1.0
     * @param    string $url
     * @param    int $code
     * @return   return
     */
    public function finishJob($url, $code = 200)
    {
        return [
            'url' => $url,
            'fileName' => $this->fileName,
            'fileType' => 'csv',
            'fileCharset' => 'gbk',
            'completeSize' => $this->completeSize,
            'code' => $code
        ];
    }

    public function cacheQueueUrl()
    {
        return cache(EXPORTSERVICEQUEUE, []);
    }

    public function setCacheQueueUrl()
    {
        $data = $this->cacheQueueUrl();

        $data = array_merge($data, [$this->fileName]);

        cache([EXPORTSERVICEQUEUE => $data], $this->cacheTimeOut());
    }

    /**
     * 更新完成进度
     * @author cloty
     * @datetime 2017-10-20T15:36:53+080
     * @version  1.0
     * @param    string $fileName
     */
    public function completeProcess()
    {
        if ($this->totalPage == 0) {
            $this->completeSize = 100;
        } else {
            $this->completeSize = ceil(($this->page / $this->totalPage) * 100);
        }

        logger()->info('export-process-' . $this->page . '-' . $this->totalPage . '-' . $this->completeSize);

        if ($this->completeSize >= 100) {
            $this->clearCacheProcess();
        } else {
            cache([
                EXPORTCOMPELTEPROCESS . $this->fileName => $this->completeSize
            ], $this->cacheTimeOut());
        }

        ob_end_clean();
    }

    /**
     * 获取当前缓存进度
     * @author cloty
     * @datetime 2017-10-20T15:58:09+080
     * @version  1.0
     * @return   int
     */
    public function getCompleteProcess()
    {
        return cache(EXPORTCOMPELTEPROCESS . $this->fileName, 0);
    }

    /**
     * 清除已完成任务
     * @author cloty
     * @datetime 2017-10-20T15:58:57+080
     * @version  1.0
     * @return   [type] [description]
     */
    public function clearCacheProcess()
    {
        $data = $this->cacheQueueUrl();

        $data = array_diff($data, [$this->fileName]);

        cache([EXPORTSERVICEQUEUE => $data], $this->cacheTimeOut());

        \Cache::forget(EXPORTCOMPELTEPROCESS . $this->fileName);
    }

    /**
     * 缓存有效时间
     * @author cloty
     * @datetime 2017-10-20T15:46:50+080
     * @version  1.0
     * @return   [type] [description]
     */
    public function cacheTimeOut()
    {
        return $this->timeOut;
    }

    /**
     * init limit & skip the model
     * @author cloty
     * @datetime 2017-10-17T16:53:32+080
     * @version  1.0
     * @param    \Illuminate\Eloquent\Model $model
     * @return   \Illuminate\Eloquent\Model
     */
    public function initLimitModel($model)
    {
        if (is_array($model)) {
            return $model;
        }

        $page = $this->page - 1;
        $page = $page <= 0 ? 0 : $page;
        $offset = $this->limit * $page;

        $model = $model->limit($this->limit)->skip($offset);

        return $model;
    }

    /**
     * 计算总分页数
     * @author cloty
     * @datetime 2017-10-17T17:04:33+080
     * @version  1.0
     * @param    \Illuminate\Eloquent\Model|array $model
     * @return   int
     */
    public function calCountPage($model, $hasGroupBy = false)
    {
        if (is_array($model)) {
             return 1;
        }

        $count = clone($model);

        if ($hasGroupBy) {
            $count = $count->select(\DB::raw('count(*) as aggregate'))->get()->count();
        } else {
            $count = $count->count();
        }

        $totalPage =ceil($count / $this->limit);

        return $totalPage;
    }

    /**
     * md5Sign
     * @author cloty
     * @datetime 2017-10-23T13:50:17+080
     * @version  1.0
     * @param    int $page
     * @return   string
     */
    public function md5Sign($page)
    {
        $param = [
            'key' => env('APP_KEY', 'NWQTOwxUCUA1CbOS2od4575am0GbuvoP'),
            'fileName' => $this->fileName,
            'page' => $page
        ];

        $str = http_build_query($param);

        return md5($str);
    }

}

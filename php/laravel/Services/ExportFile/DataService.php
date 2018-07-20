<?php

namespace App\Services\Export;

class DataService
{
    /**
     * 导出文件名称
     *
     * @var string
     */
    public $fileName = '';

    /**
     * 导出分页大小限制
     *
     * @var int
     */
    protected $limit = 200;

    /**
     * 缓存有效时间
     *
     * @var int
     */
    protected $timeOut = 360;

    /**
     * 总分页数
     *
     * @var int
     */
    public $totalPage = 1;

    /**
     * 当前分页数
     *
     * @var int
     */
    public $page = 1;

    /**
     * 完成进度
     *
     * @var int
     */
    public $completeSize = 0;
    

    /**
     * 统计-导出
     * @author cloty
     * @datetime 2017-11-10T16:59:46+080
     * @version  1.0
     * @param    \Illuminate\Eloquent\Model $model
     * @param    object $exportService
     */
    public function exportStatistics($model, $exportService)
    {
        $headData = [
            'column',
        ];

        if ($this->page == 1) {
            $exportService->addRow($headData);
        }

        foreach ($model->get() as $k => $list) {
            $row = [
                $list->column_name,
            ];
            $exportService->addRow($row);
        }
        $this->completeProcess();
    }

}

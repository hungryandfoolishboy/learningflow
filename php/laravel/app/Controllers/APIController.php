<?php

/*
|--------------------------------------------------------------------------
| 内部公共调用接口
|--------------------------------------------------------------------------
 */

namespace xxxxxxxx;

class InternalController extends Controller
{
    public function downloadExport() {
        //导出excel
        if (!empty(request('export'))) {
            return (new \App\Services\ExportService())->handle($model_query, 'static');
        }

        if (request('export')) {
            return $data;
        }
    }

    /**
     * 下载系统导出文件
     * @author cloty
     * @datetime 2017-10-20T17:17:53+080
     * @version  1.0
     * @return   \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function downloadExportCsv()
    {
        $fileName = request('export_name');

        if (empty($fileName)) {
            return back()->with('error', '下载失败，文件不存在');
        }

        $exportService = app('export.csv');

        $exportService->setFileName($fileName);

        if (!$exportService->fileExist()) {
            return back()->with('error', '下载失败，文件不存在');
        }

        return $exportService->download();
    }
}

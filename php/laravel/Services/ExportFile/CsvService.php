<?php

namespace App\Services\Export;

use Exception;

class CsvService
{
    /**
     * file name
     * @var string
     */
    protected $fileName = '';

    /**
     * file path
     * @var string
     */
    public $filePath = null;

    /**
     * fopen file
     * @var pointer|null
     */
    protected $pointFile = null;

    /**
     * set export file name
     * @author cloty
     * @datetime 2017-10-17T10:32:34+080
     * @version  1.0
     * @param    string $fileName
     */
    public function setFileName($fileName = '')
    {
        if (!$fileName) {
            $fileName = 'export_csv_' . uniqid();
        }

        $this->fileName = $fileName;

        $this->setFilePath();
    }

    /**
     * get the file name
     * @author cloty
     * @datetime 2017-10-17T10:43:23+080
     * @version  1.0
     * @return  string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * set the file path. default: storage folder
     * @author cloty
     * @datetime 2017-10-17T10:43:46+080
     * @version  1.0
     * @return   string
     */
    protected function setFilePath()
    {
        $this->filePath = storage_path() . '/exports/' . $this->fileName . '.csv';
    }

    /**
     * get the file path
     * @author cloty
     * @datetime 2017-10-17T10:45:49+080
     * @version  1.0
     * @return   string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Determine if the file exists
     * @author cloty
     * @datetime 2017-10-16T18:49:10+080
     * @version  1.0
     * @param    string $filePath
     * @return   boolean
     */
    public function fileExist($filePath = '')
    {
        if (!$filePath) {
            $filePath = $this->filePath;
        }

        return file_exists($this->filePath);
    }

    /**
     * download file
     * @author cloty
     * @datetime 2017-10-17T11:38:15+080
     * @version  1.0
     * @param    string $filePath
     * @return   \Illuminate\Http\Response
     */
    public function download($filePath = '')
    {
        if (!$filePath) {
            $filePath = $this->filePath;
        }

        return response()->download($filePath);
    }

    /**
     * unlink file
     * @author cloty
     * @datetime 2017-10-17T11:00:26+080
     * @version  1.0
     * @return   boolean
     */
    public function unLinkFile()
    {
        if (!$this->fileExist()) {
            return false;
        }

        return unlink($this->filePath);
    }

    /**
     * write a row of data
     * @author cloty
     * @datetime 2017-10-17T11:02:25+080
     * @version  1.0
     * @param    array
     */
    public function addRow(array $data)
    {
        if (!$this->pointFile) {
            if (!$this->injectFile()) {
                throw new Exception("打开文件失败");
            }
        }

        foreach ($data as $key => $value) {
            $data[$key] = $this->charSetToGBK($value);
        }

        // TODO: 添加写入到文件 还是直接输出到浏览器
        //$this->write($data);
        fputcsv($this->pointFile, $data);
    }

    /**
     * Returns a file pointer resource on success, or FALSE on error.
     * @author cloty
     * @datetime 2017-10-17T11:08:49+080
     * @version  1.0
     * @return pointer|boolean
     */
    public function injectFile()
    {
        if (!is_null($this->pointFile)) {
            return $this->pointFile;
        }

        $this->pointFile = fopen($this->filePath, 'a+');

        return $this->pointFile;
    }

    /**
     * charset GBK
     * @author cloty
     * @datetime 2017-10-17T11:17:17+080
     * @version  1.0
     * @param    string $value
     * @return   string
     */
    public function charSetToGBK($value)
    {
         return mb_convert_encoding($value,'gbk', 'utf-8');
    }

    public function finish()
    {

    }

    /**
     *TODO:添加写入到文件 还是直接输出到浏览器
     */
    protected function write($data)
    {
		switch($this->exportTo) {
			case 'browser':
				echo $data;
				break;
			case 'string':
				$this->stringData .= $data;
				break;
			case 'file':
				fwrite($this->pointFile, $data);
				break;
		}
	}

    public function getExportProcess()
    {

    }

    public function getCompletedProgress()
    {

    }
}

<?php
namespace app\exception;

use Exception;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\Config;
use think\Log;
class Http extends Handle
{
    /**
     * Report or log an exception.
     *
     * @access public
     * @param  \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if (!$this->isIgnoreReport($exception)) {
            // 收集异常数据
            $data = [
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'message' => $this->getMessage($exception),
                'code'    => $this->getCode($exception),
            ];
            $log = "[{$data['code']}]{$data['message']}[{$data['file']}:{$data['line']}]";

            if (Config::get('record_trace')) {
                $log .= "\r\n" . $exception->getTraceAsString();
            }

            Log::record($log, 'error');
        }
    }
}
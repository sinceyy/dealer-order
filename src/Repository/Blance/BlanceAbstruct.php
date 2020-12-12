<?php


namespace YddOrder\Repository\Blance;

use think\Model;
use YddOrder\Model\Blance\StoreBlance;
use YddOrder\Model\Blance\StoreBlanceLog;

abstract class BlanceAbstruct implements BlanceInterface
{
    protected $model = null;

    //保存模型
    public function __construct()
    {
        if (is_null($this->model)) {
            $this->model = new StoreBlance;
        }
    }

    /**
     * 新增账户流水记录
     * @param float  $amount
     * @param int    $change_type
     * @param int    $data_id
     * @param int    $data_type
     * @param string $mark
     * @return bool
     */
    public static function addBlanceLog($amount, int $change_type, int $data_id, int $data_type, string $mark = '系统备注'): bool
    {
        $ad = StoreBlanceLog::create(compact('amount', 'change_type', 'data_id', 'data_type', 'mark'));
        return $ad ? true : false;
    }
}
<?php
declare(strict_types=1);

namespace YddOrder\Repository\Blance;

use think\Exception;
use YddOrder\Model\Blance\StoreBlanceLog;

class StoreBlanceRepository implements BlanceInterface
{

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

    /**
     * 新增金额
     * @param int    $data_id
     * @param int    $data_type
     * @param float  $money
     * @param string $mark
     * @return bool
     */
    public static function addBlance(int $data_id, int $data_type, $money = 0.00, string $mark = '业务新增金额'): bool
    {
        $model = StoreBlance::where(['data_id' => $data_id, 'data_type' => $data_type])->find();

        if ($model) {
            $up = $model::where(['data_id' => $data_id, 'data_type' => $data_type])->update(['blance' => sprintf('%.2f', $money)]);
            if ($up === false) throw new Exception('新增金额失败');
        } else {
            $ad = StoreBlance::create([
                'data_id'   => $data_id,
                'data_type' => $data_type,
                'blance'    => sprintf('%.2f', $money)
            ]);
            if ($ad === false) throw new Exception('新增金额失败');
        }
        return self::addBlanceLog(sprintf('%.2f', $money), 1, $data_id, $data_type, $mark);
    }

    /**
     * 减少金额
     * @param int    $data_id
     * @param int    $data_type
     * @param float  $money
     * @param string $mark
     * @return bool
     */
    public static function reduceBlance(int $data_id, int $data_type, float $money = 0.00, string $mark = '业务消费金额'): bool
    {
        $model = StoreBlance::where(['data_id' => $data_id, 'data_type' => $data_type])->find();

        if ($model) {
            $up = StoreBlance::where(['data_id' => $data_id, 'data_type' => $data_type])->update(['blance' => sprintf('%.2f', $money)]);
            if ($up === false) throw new Exception('减少金额失败');
        } else {
            $ad = StoreBlance::create([
                'data_id'   => $data_id,
                'data_type' => $data_type,
                'blance'    => sprintf('%.2f', $money)
            ]);
            if ($ad === false) throw new Exception('减少金额失败');
        }

        return self::addBlanceLog(sprintf('%.2f', $money), 2, $data_id, $data_type, $mark);
    }

}
<?php
declare(strict_types=1);

namespace YddOrder\Repository\Blance;

use think\Exception;
use YddOrder\Model\Blance\StoreBlance;
use YddOrder\Model\Blance\StoreBlanceLog;

class StoreBlanceRepository implements BlanceInterface
{

    /**
     * 新增账户流水记录
     * @param float  $amount
     * @param int    $change_type
     * @param int    $brand_id
     * @param int    $store_id
     * @param int    $clerk_id
     * @param string $mark
     * @return bool
     */
    public static function addBlanceLog($amount, int $change_type, int $brand_id, int $store_id, int $clerk_id, string $mark = '系统备注'): bool
    {
        $ad = StoreBlanceLog::create(compact('amount', 'change_type', 'mark', 'brand_id', 'store_id', 'clerk_id'));
        return $ad ? true : false;
    }

    /**
     * 新增金额
     * @param int    $clerk_id
     * @param int    $store_id
     * @param int    $brand_id
     * @param float  $money
     * @param string $mark
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function addBlance(int $clerk_id, int $store_id, int $brand_id, $money = 0.00, string $mark = '业务新增金额'): bool
    {
        $where  = [
            ['clerk_id' => $clerk_id, 'store_id' => $store_id, 'brand_id' => $brand_id]
        ];
        $blance = StoreBlance::where($where)->find();

        if ($blance) {
            $up = StoreBlance::update(['blance' => sprintf('%.2f', $money)], ['id' => $blance->id], 'blance');
            if ($up === false) throw new Exception('新增金额失败');
        } else {
            $ad = StoreBlance::create([
                'clerk_id' => $clerk_id,
                'store_id' => $store_id,
                'brand_id' => $brand_id,
                'blance'   => sprintf('%.2f', $money)
            ]);
            if ($ad === false) throw new Exception('新增金额失败');
        }
        return self::addBlanceLog(sprintf('%.2f', $money), 1, (int)$brand_id, (int)$store_id, (int)$clerk_id, $mark);
    }

    /**
     * 减少金额
     * @param int    $clerk_id
     * @param int    $store_id
     * @param int    $brand_id
     * @param float  $money
     * @param string $mark
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function reduceBlance(int $clerk_id, int $store_id, int $brand_id, float $money = 0.00, string $mark = '业务消费金额'): bool
    {
        $where  = [
            ['clerk_id' => $clerk_id, 'store_id' => $store_id, 'brand_id' => $brand_id]
        ];
        $blance = StoreBlance::where($where)->find();

        if ($blance) {
            $up = StoreBlance::update(['blance' => sprintf('%.2f', $money)], ['id' => $blance->id], 'blance');
            if ($up === false) throw new Exception('减少金额失败');
        } else {
            $ad = StoreBlance::create([
                'clerk_id' => $clerk_id,
                'store_id' => $store_id,
                'brand_id' => $brand_id,
                'blance'   => sprintf('%.2f', $money)
            ]);
            if ($ad === false) throw new Exception('减少金额失败');
        }

        return self::addBlanceLog(sprintf('%.2f', $money), 2, (int)$brand_id, (int)$store_id, (int)$clerk_id, $mark);
    }

}
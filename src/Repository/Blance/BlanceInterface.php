<?php
declare(strict_types=1);

namespace YddOrder\Repository\Blance;

interface BlanceInterface
{
    /**
     * 新增金额
     * @param int    $clerk_id
     * @param int    $store_id
     * @param int    $brand_id
     * @param float  $money
     * @param string $mark
     * @return bool
     */
    public static function addBlance(int $clerk_id, int $store_id, int $brand_id, $money = 0.00, string $mark = '业务新增金额'): bool;


    /**
     * 减少金额
     * @param int    $clerk_id
     * @param int    $store_id
     * @param int    $brand_id
     * @param float  $money
     * @param string $mark
     * @return bool
     */
    public static function reduceBlance(int $clerk_id, int $store_id, int $brand_id, float $money = 0.00, string $mark = '业务消费金额'): bool;

    /**
     * 新增账户流水记录
     * @param float  $amount
     * @param int    $change_type
     * @param int    $clerk_id
     * @param int    $brand_id
     * @param int    $store_id
     * @param string $mark
     * @return bool
     */
    public static function addBlanceLog($amount, int $change_type, int $brand_id, int $store_id, int $clerk_id, string $mark = '系统备注'): bool;
}
<?php


namespace YddOrder\Repository\Blance;


interface BlanceInterface
{
    /**
     * 新增金额
     * @param int    $data_id
     * @param int    $data_type
     * @param float  $money
     * @param string $mark
     * @return bool
     */
    public function addBlance(int $data_id, int $data_type, float $money = 0.00, string $mark = '业务新增金额'): bool;


    /**
     * 减少金额
     * @param int    $data_id
     * @param int    $data_type
     * @param float  $money
     * @param string $mark
     * @return bool
     */
    public function reduceBlance(int $data_id, int $data_type, float $money = 0.00, string $mark = '业务消费金额'): bool;

    /**
     * 新增账户流水记录
     * @param float  $amount
     * @param int    $change_type
     * @param int    $data_id
     * @param int    $data_type
     * @param string $mark
     * @return bool
     */
    public static function addBlanceLog($amount, int $change_type, int $data_id, int $data_type, string $mark = '系统备注'): bool;
}
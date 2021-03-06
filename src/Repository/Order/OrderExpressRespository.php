<?php
declare(strict_types=1);

namespace YddOrder\Repository\Order;


use think\Exception;
use think\Exception\InvalidArgumentException;
use YddOrder\Model\Order\OrderExtract;

final class OrderExpressRespository
{

    /**
     * 记录发货信息
     * @param array $data
     * @param int   $order_id
     * @return bool
     */
    public static function sendExtract(array $data, int $order_id): bool
    {
        //快递
        $express = OrderExtract::where(['order_id' => $order_id])->find();
        if ($express) {
            $express->express_id = $data['express_id'];
            $express->express_company = $data['express_company'];
            $express->express_no = $data['express_no'];
            $up = $express->save();
        } else {
            throw new Exception('该订单不是物流订单无法发货');
        }

        return $up === false ? false : true;
    }
}
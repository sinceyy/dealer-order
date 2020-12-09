<?php
declare(strict_types=1);

namespace YddOrder\Repository\Order;


use think\Exception\InvalidArgumentException;
use YddOrder\Model\Order\OrderExtract;

class OrderExpressRespository
{

    /**
     * 记录发货信息
     * @param array $data
     * @param array $condition
     * @return bool
     */
    public static function sendExtract(array $data, array $condition): bool
    {
        //快递
        $express = OrderExtract::where(['order_id' => $condition['id']])->find();
        if ($express) {
            $express->express_id = $data['express_id'];
            $express->express_company = $data['express_company'];
            $express->express_no = $data['express_no'];
            $up = $express->save();
        } else {
            throw new InvalidArgumentException('该订单不是物流订单无法发货');
        }

        return $up === false ? false : true;
    }
}
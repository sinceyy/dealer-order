<?php
declare(strict_types=1);

namespace YddOrder\Repository\Order;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\Model;
use YddOrder\Model\Order\Order;
use YddOrder\Model\Order\OrderExtract;
use YddOrder\Model\Order\OrderWriteLog;

class OrderWriteRespository
{

    /**
     * 记录核销信息
     * @param Order $order
     * @param int   $user_id
     * @param int   $write_type
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function setWriteLog(Order $order, int $user_id, int $write_type = 2): bool
    {
        //快递
        $log = OrderWriteLog::where(['order_id' => $order->id])->find();
        if ($log) {
            throw new Exception('该订单已核销，请勿重复核销');
        } else {
            $add = OrderWriteLog::create([
                'order_id'      => (int)$order->id,
                'order_no'      => (int)$order->order_no,
                'write_user_id' => (int)$user_id,
                'write_type'    => (int)$write_type
            ]);
        }

        return $add === false ? false : true;
    }

    /**
     * 修改核销订单为已核销
     * @param int $order_id
     * @return bool|Model|OrderExtract
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function upOrderWriteStatus(int $order_id): bool
    {
        //经销商给订单发货
        $up = Order::where(['order_id' => $order_id])->save([
            'order_status' => 2,
            'deliver_time' => time()
        ]);
        return $up === false ? false : true;
    }
}
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
     * 获取核销记录
     * @param array $conditions
     * @return \think\Paginator
     * @throws DbException
     */
    public static function getWriteLogList(array $conditions = []): \think\Paginator
    {
        return OrderWriteLog::with(['detail', 'orderUser', 'orderClerkUser', 'orderClerkSale', 'orderStoreClerkUser'])
            ->field('id,order_id,order_price,order_no,write_user_id,write_type,create_time')
            ->where(self::setWhere($conditions))
            ->order('id desc')->paginate([
                "page"      => $conditions['page'],
                'list_rows' => $conditions['limit'],
            ]);
    }

    /**
     * 记录核销信息
     * @param Order $order
     * @param int   $user_id
     * @param int   $store_id
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function setWriteLog(Order $order, int $user_id, int $store_id): bool
    {
        //快递
        $log = OrderWriteLog::where(['order_id' => $order->id])->find();
        if ($log) {
            $add = OrderWriteLog::where('order_id', $order->id)->update(['update_time' => time()]);
        } else {
            $add = OrderWriteLog::create([
                'order_id'      => (int)$order->id,
                'order_no'      => (int)$order->order_no,
                'order_price'   => sprintf('%.2f', $order->pay_price),
                'write_user_id' => (int)$user_id,
                'store_id'      => (int)$store_id
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
        $up = Order::where(['id' => $order_id])->update([
            'order_status' => 2,
            'deliver_time' => time()
        ]);
        return $up === false ? false : true;
    }

    /**
     * 设置条件
     * @param array $conditions
     * @return \Closure
     */
    private static function setWhere(array $conditions): \Closure
    {
        return function ($query) use ($conditions) {
            if (isset($conditions['keyword'])) $query->whereLike('order_no', $conditions['keyword'] . '%');
            if (isset($conditions['write_user_id'])) $query->where('write_user_id', $conditions['write_user_id']);
            if (isset($conditions['write_type'])) $query->where('write_type', $conditions['write_type']);
        };
    }
}
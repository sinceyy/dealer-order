<?php

declare(strict_types=1);

namespace YddOrder\Service\Order;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use YddOrder\Repository\Order\OrderRepository;

/**
 * 订单service
 * Class OrderService
 * @package YddOrder\Service\Order
 */
class OrderService
{
    /**
     * 订单列表
     * @param array $condition
     * @return array
     * @throws Exception
     */
    public function getList(array $condition): array
    {
        return OrderRepository::getOrderList($condition);
    }


    /**
     * 订单详情
     * @param array $condition
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function detail(array $condition): array
    {
        return OrderRepository::getOrderInfo($condition);
    }

    /**
     * 订单发货
     * @param array $params
     * @param array $condition
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function postDelivery(array $params, array $condition = []): array
    {
        $data = OrderRepository::getOrderInfo($condition);
        if (!$data)
            throw new \InvalidArgumentException("订单信息不存在");
        if ($data['pay_status'] != 1 || $data['delivery_status'] != 0) {
            throw new \InvalidArgumentException("订单号[{$data['order_no']}] 不满足发货条件!");
        }
        $params['delivery_status'] = 1;
        $params['delivery_time'] = time();
        $result = OrderRepository::orderUpdate($params, ['id' => (int)$data['id']]);
        return [$result];
    }


}
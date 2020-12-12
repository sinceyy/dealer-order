<?php

declare(strict_types=1);

namespace YddOrder\Service\Order;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\InvalidArgumentException;
use think\facade\Db;
use YddOrder\Model\Express\Express;
use YddOrder\OrderField\OrderFieldConstant;
use YddOrder\Repository\Order\OrderExpressRespository;
use YddOrder\Repository\Order\OrderRepository;
use YddOrder\Service\ServiceAbstruct;

/**
 * 订单service
 * Class OrderService
 * @package YddOrder\Service\Order
 */
class OrderService extends ServiceAbstruct
{
    /**
     * 订单列表
     * @param array $condition
     * @return array
     * @throws Exception
     */
    public function getList(array $condition): array
    {
        $list = OrderRepository::getOrderList($condition);
        if (isset($list['error'])) return self::returnErrorData($list['error']);
        return self::returnData($list);
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
     * 物流订单发货
     * @param array $params    [express_id,express_no,express_company,order_id,brand_id]
     * @param array $condition [order_id,brand_id]
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function postDelivery(array $params, array $condition = []): bool
    {
        $data = OrderRepository::getOrderInfo($condition);
        if (!$data)
            throw new \InvalidArgumentException("订单信息不存在");

        if ($data['pay_status'] != OrderFieldConstant::PAY_SUCCESS || $data['delivery_status'] != 0) {
            throw new \InvalidArgumentException("订单号[{$data['order_no']}] 不满足发货条件!");
        }

        //获取物流信息
        $express = Express::where(['id' => $params['express_id'], 'brand_id' => $condition['brand_id']])->find();

        if (!$express) {
            throw new \InvalidArgumentException("物流公司不存在!");
        }

        try {
            Db::startTrans();

            //更改订单状态
            $result = OrderRepository::orderUpdate([
                'delivery_time' => time(),
                'order_status'  => OrderFieldConstant::RECEIPT
            ], ['id' => (int)$data['id']]);

            //增加发货记录
            $extract = OrderExpressRespository::sendExtract([
                'express_id'      => (int)$params['express_id'],
                'express_no'      => $params['express_no'],
                'express_company' => $express->name
            ], ['order_id' => (int)$data->id]);

            if (!$result || !$extract) {
                throw new Exception('发货失败！');
            }
            Db::commit();
        } catch (Exception $exception) {
            Db::rollback();
            throw new InvalidArgumentException($exception->getMessage());
        }
        return true;
    }


}
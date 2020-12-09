<?php

declare(strict_types=1);

namespace YddOrder\Service\Refund;

use think\facade\Db;

/**
 * 订单退款服务类
 * Class RefundService
 * @package YddOrder\Service\Refund
 */
class RefundService
{
    /**
     * 执行订单退款
     * @param $order
     * @param null $money
     * @return array|false
     */
    public function execute(&$order, $money = null)
    {
        // 退款金额，如不指定则默认为订单实付款金额
        is_null($money) && $money = $order['pay_price'];
        //TODO 微信支付退款
        if ($order['pay_type'] == 1) {
            return $this->wxpay($order, $money);
        }
        return false;
    }

    /**
     * 微信支付退款
     */
    private function wxpay(&$order, $money)
    {
        //TODO
        $result = [];
        return $result;
    }


    /**
     * 确认收货并退款
     * @param $params
     * @param $brand_id
     * @return bool[]
     * @throws AuthenticationException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function receipt($params,$brand_id)
    {
        //退换货订单详情
        $list = OrderRefund::with(['orderMaster','orderProduct'])->where(['id'=>$params['id'],'brand_id'=>$brand_id])->find();
        if ($params['refund_money'] > min($list['orderMaster']['pay_price'], $list['orderProduct']['total_pay_price']))
            throw new \InvalidArgumentException("退款金额不能大于商品实付款金额");
        // 开启事务
        Db::startTrans();
        try {
            // 更新退货订单状态
            $data = [
                'refund_money' => $params['refund_money'],
                'is_receipt'   => 1,
                'status'       => 2
            ];
            OrderRefund::update($data);
            //TODO 执行原路退款
            $this->execute($order, $params['refund_money']);

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new AuthenticationException($e->getMessage());
        }
        return [true];
    }
}
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
     * 退换货订单列表
     * @param $params
     * @param $brand_id
     * @return mixed
     */
    public function getList($params, $brand_id)
    {
        $where = [];
        // 查询条件：订单号
        if ($params['order_no'] != '') {
            $where[] = ['order.order_no', 'like', '%' . $params['order_no'] . '%'];
        }
        // 处理状态
        if ($params['status'] != '') {
            $where[] = ['m.status', '=', $params['status']];
        }
        //列表显示条件
        if ($params['type']) {
            $wheres = ['m.type' => $params['type'], 'm.brand_id' => $brand_id];
        } else {
            $wheres = ['m.brand_id' => $brand_id];
        }
        return OrderRefund::alias('m')
            ->join('order', 'order.id = m.order_id')
            ->where($where)
            ->where($wheres)
            ->field('m.*,order.order_no')
            ->with(['orderMaster.address'])
            ->paginate($params['limit'], false, ["page" => $params["page"]])->toArray();
    }

    /**
     * 退换货订单详情
     * @param $params
     * @param $brand_id
     * @return mixed
     */
    public function detail($params, $brand_id)
    {
        $data = OrderRefund::alias('m')
            ->where(['m.id' => $params['id'], 'm.brand_id' => $brand_id])
            ->field('m.*,order.order_no')
            ->join('order', 'order.id = m.order_id')
            ->with(['orderMaster.address', 'user', 'orderProduct'])
            ->find();
        return [$data];
    }

    /**
     * 商家审核
     * @param $params
     * @param $brand_id
     * @return array
     * @throws AuthenticationException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function audit($params, $brand_id)
    {
        $list = OrderRefund::where(['id'=>$params['id'],'brand_id'=>$brand_id])->find();
        if ($params['is_agree'] == 2 && empty($params['refuse_desc'])) throw new \InvalidArgumentException("请输入拒绝原因");
        if ($params['is_agree'] == 1 && empty($params['address_id'])) throw new \InvalidArgumentException("请选择退货地址");
        // 开启事务
        Db::startTrans();
        try {
            // 拒绝申请, 标记售后单状态为已拒绝
            if ($params['is_agree'] == 2) {
                $params['status'] = 1;
                $data = OrderRefund::update($params);
            }
            // 同意换货申请, 标记售后单状态为已完成
            if ($params['is_agree'] == 1 && $list['type'] == 2) {
                $params['status'] = 2;
                OrderRefund::update($params);
            }
            // 同意售后申请, 记录退货地址
            if ($params['is_agree'] == 1) {
                $data = [
                    'address_id'      => $params['address_id'],
                    'order_refund_id' => $list['id'],
                    'brand_id'        => $brand_id,
                ];
                $data = OrderRefundAddress::create($data);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new AuthenticationException($e->getMessage());
        }
        return [$data];
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
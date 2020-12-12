<?php
declare(strict_types=1);

namespace YddOrder\Repository\Order;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Model;
use YddOrder\Model\Order\Order;
use Closure;
use think\db\exception\DbException;
use think\Exception;
use YddOrder\OrderField\OrderFieldConstant;

final class OrderRepository
{
    //定义筛选条件字段
    private static $conditions = [
        'order_no', //订单号
        'user_id',  //用户id
        'order_source',//订单来源
        'source_id',//来源id
        'brand_id',//品牌商id
        'clerk_id',//用户绑定的员工id
        'writeoff_id',//核销人id
        'pay_status',//支付状态
        'pay_type',//支付类型
        'delivery_type',//配送方式
        'order_type',//订单类型
        'is_settled',//结算状态
        'order_status'//订单状态
    ];

    /**
     * 订单筛选条件组装
     * all 全部订单 (不包含未支付订单)
     * pay 待付款
     * delivery 待发货（已支付，无发货时间，无退款）
     * receipt 待收货（已支付，已发货，无退款）
     * complete 已完成（已支付，有发货时间，不包含退款，不包含未结算））
     * refund 退款中（已支付，无发货时间 （仅可发货前退款））
     * refund_success 退款成功（已支付，退款时间不为空，无发货时间 （仅可发货前退款））
     * comment 待评价（已支付，未评价，（包含退款，包含未结算））
     * settled 未结算(已支付，已发货，未结算，待收货)
     * settled_success 已结算（已支付，已发货，待评价，已完成）
     */
    protected static $orderStatus = [
        'all'             => ['order_status' => ['>', 0]],
        'pay'             => ['pay_status' => OrderFieldConstant::PAY_SUCCESS],
        'delivery'        => ['pay_status' => OrderFieldConstant::PAY_SUCCESS, 'order_status' => OrderFieldConstant::DELIVERY, 'deliver_time' => 0, 'refund_time' => 0],
        'receipt'         => ['pay_status' => OrderFieldConstant::PAY_SUCCESS, 'order_status' => OrderFieldConstant::RECEIPT, 'deliver_time' => ['>', 0], 'refund_time' => 0],
        'complete'        => ['pay_status' => OrderFieldConstant::PAY_SUCCESS, 'order_status' => OrderFieldConstant::COMMENT, 'deliver_time' => ['>', 0], 'refund_time' => 0],
        'refund'          => ['pay_status' => OrderFieldConstant::PAY_SUCCESS, 'order_status' => OrderFieldConstant::REFUND, 'deliver_time' => 0],
        'refund_success'  => ['pay_status' => OrderFieldConstant::PAY_SUCCESS, 'order_status' => OrderFieldConstant::REFUND_SUCCESS, 'refund_time' => ['>', 0], 'deliver_time' => 0],
        'comment'         => ['pay_status' => OrderFieldConstant::PAY_SUCCESS, 'order_status' => OrderFieldConstant::COMMENT],
        'settled'         => ['pay_status' => OrderFieldConstant::PAY_SUCCESS, 'order_status' => ['in', [OrderFieldConstant::RECEIPT, OrderFieldConstant::REFUND]], 'deliver_time' => ['>', 0], 'is_settled' => OrderFieldConstant::SETTLED],
        'settled_success' => ['pay_status' => OrderFieldConstant::PAY_SUCCESS, 'order_status' => ['in', [OrderFieldConstant::COMMENT, OrderFieldConstant::COMPLETE]], 'deliver_time' => ['>', 0], 'is_settled' => OrderFieldConstant::SETTLED_SUCCESS]
    ];


    /**
     * 根据订单id获取订单
     * @param int $orderId
     * @return array|Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getInfoById(int $orderId)
    {
        return Order::where(['id' => $orderId])->find();
    }

    /**
     * 根据条件获取订单
     * @param array $condition
     * @return array|Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getInfoByWhere(array $condition)
    {
        return Order::where($condition)->find();
    }

    /**
     * 获取订单列表
     * @param array $condition
     * @return array
     * @throws Exception
     */
    public static function getOrderList(array $condition): array
    {
        // 订单列表
        try {
            $list = (new Order)->with(['detail', 'user', 'extract'])
                ->where(self::setCondition($condition))
                ->where(self::transferDataType($condition['datatype']))
                ->order('create_time desc')
                ->paginate(
                    [
                        "page"      => $condition['page'],
                        'list_rows' => $condition['limit'],
                    ]
                )->toArray();
        } catch (DbException $e) {
            return ['error' => $e->getMessage()];
        }
        return [
            'data'  => $list['data'],
            'total' => $list['total']
        ];
    }

    /**
     * 获取订单详情
     * @param array $condition
     * @return array|Model|null
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public static function getOrderInfo(array $condition)
    {
        return Order::with(['product', 'address', 'extract'])->where(self::setCondition($condition))->find();
    }

    /**
     * 修改订单
     * @param array $param
     * @param array $condition
     * @return bool|Order
     */
    public static function orderUpdate(array $param, array $condition = [])
    {
        return (new Order)->where($condition)->save($condition);
    }

    /**
     * @param array $condition
     * @return Closure
     */
    private static function setCondition(array $condition): Closure
    {
        return function ($query) use ($condition) {

            if ($condition['keyword'] != '') {
                $whereStr = 'order_no|user_id|extract.extract_name|extract.extract_phone|extract.phone|extract.name';
                $query->whereLike($whereStr, $condition['keyword'] . '%');
            }

            //根据订单创建时间
            if (isset($condition['start_time']) && isset($condition['end_time'])) {
                $query->whereTime('create_time', strtotime($condition['start_time']), strtotime($condition['end_time']) + 86400);
            }

            //根据订单来源筛选
            if (isset($condition['order_source'])) {
                $query->where('order_source', (int)$condition['order_source']);
            }

            //根据订单来源id筛选
            if (isset($condition['source_id'])) {
                if (is_array($condition['source_id'])) $query->whereIn('source_id', $condition['source_id']);
                if (is_int($condition['source_id'])) $query->where('source_id', (int)$condition['source_id']);
            }

            foreach ($condition as $k => $v) {
                //过滤time/source/keyword类型
                if (stristr($k, 'time') || stristr($k, 'source') || stristr($k, 'keyword')) {
                    continue;
                }
                if (in_array($k, self::$conditions)) {
                    if (is_string($v) && !empty($v)) {
                        $query->where($k, '=', $v);
                    } else if (is_int($v)) {
                        $query->where($k, '=', $v);
                    }
                }
            }
        };
    }

    /**
     * 转义数据类型条件
     * @param $dataType
     * @return array
     */
    private static function transferDataType($dataType): array
    {
        return self::$orderStatus[$dataType];
    }
}
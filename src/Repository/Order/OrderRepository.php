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

class OrderRepository
{

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
            $list = (new Order)->with(['product', 'address', 'user', 'extract'])
                ->where(self::setCondition($condition))
                ->where(self::transferDataType($condition['datatype']))
                ->order('create_time desc')
                ->paginate(
                    [
                        "page"      => $condition['page'],
                        'list_rows' => $condition['limit'],
                    ]
                );
        } catch (DbException $e) {
            throw new Exception($e->getMessage());
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
                $whereStr = 'order_no|user_id|address.phone|address.name|product.product_name|express_no|user.member_name|transaction_id';
                $query->whereLike($whereStr, $condition['keyword'] . '%');
            }
            if ($condition['start_time'] != '') {
                $query->where(['create_time', '>=', strtotime($condition['start_time'])]);
            }
            if ($condition['end_time'] != '') {
                $query->where(['create_time', '<=', strtotime($condition['end_time']) + 86400]);
            }
            if (isset($condition['store_name'])) {
                $query->where(['store_name', '=', $condition['store_name']]);
            }
            if (isset($condition['store_type'])) {
                $query->where(['store_type', '=', (int)$condition['store_type']]);
            }
            if (isset($condition['dealer_id'])) {
                $query->where(['dealer_id', '=', (int)$condition['dealer_id']]);
            }
            if (isset($condition['delivery_type'])) {
                $query->where(['delivery_type', '=', (int)$condition['delivery_type']]);
            }
            if (isset($condition['user_clerkid'])) {
                $query->where(['user_clerkid', '=', (int)$condition['user_clerkid']]);
            }
            if (isset($condition['store_id']) && is_array($condition['store_id'])) {
                $query->whereIn(['store_id', $condition['store_id']]);
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
        // 数据类型
        switch ($dataType){
            case "all":
                //全部订单
                $filter = [];
                break;
            case "pay":
                //待付款
                $filter = ['pay_status' => 0, 'order_status' => 0];
                break;
            case "delivery":
                //待发货
                $filter = [
                    'pay_status'      => 1,
                    'delivery_status' => 0,
                    'order_status'    => ['in', [1, 21]]
                ];
                break;
            case "receipt":
                //待收货
                $filter = [
                    'pay_status'      => 1,
                    'delivery_status' => 1,
                    'receipt_status'  => 0
                ];
                break;
            case "complete":
                //已完成
                $filter = ['order_status' => 3];
                break;
            case "refund":
                //退款中
                $filter = ['order_status' => 5];
                break;
            case "refund_success":
                //退款成功
                $filter = ['order_status' => 4];
                break;
            case "comment":
                //待评价
                $filter = ['is_comment' => 0, 'order_status' => 3];
                break;
            case "settled":
                //未结算
                $filter = ['is_settled' => 0, 'order_status' => 3];
                break;
            case "settled_success":
                $filter = ['is_settled' => 1, 'order_status' => 3];
                break;
            default:
                $filter = [];
                break;
        }

        return $filter;

        //php 8 新特性写法
//        $filter = match ($dataType) {
//            'all' => [],
//            'pay' => ['pay_status' => 0, 'order_status' => 0],
//            'delivery' => [
//                'pay_status'      => 1,
//                'delivery_status' => 0,
//                'order_status'    => ['in', [1, 21]]
//            ],
//            'receipt' => [
//                'pay_status'      => 1,
//                'delivery_status' => 1,
//                'receipt_status'  => 0
//            ],
//            'complete' => ['order_status' => 3],
//            'refund' => ['order_status' => 5],
//            'refund_success' => ['order_status' => 4],
//            'comment' => ['is_comment' => 0, 'order_status' => 3],
//            'settled' => ['is_settled' => 0, 'order_status' => 3],
//            'settled_success' => ['is_settled' => 1, 'order_status' => 3],
//        };

    }
}
<?php
declare(strict_types=1);

namespace YddOrder\Calculation;

use Exception;
use think\db\exception\DbException;
use YddOrder\Model\Order\Order;
use YddOrder\OrderField\OrderFieldConstant;
use YddOrder\Repository\Order\OrderSettlementRepository;

/**
 * 订单结算计算类
 * Class OrderSettlementCalculation
 * @package YddOrder\Calculation
 */
class OrderSettlementCalculation
{

    /**
     * 结算计算
     * @param Order $order
     * @param int   $user_id
     * @param int   $store_id
     * @return array
     * @throws Exception
     */
    public static function orderSettlement(Order $order): array
    {
        if ($order->pay_price > 0 && $order->source_id) {

            try {
                //结算比(千分单位)
                $proportion = self::getDealerConversionRatio($order);
                //组装备注内容
                $proStr = $proportion['proportionPrice'] > 0 ? '订单结算，扣除订单手续费￥' . $proportion['proportionPrice'] . '元' : '手续费为：' . $proportion['proportionPrice'];
                //组装结算记录数据
                return [
                    'order_id'     => (int)$order->id,
                    'order_no'     => (string)$order->order_no,
                    'price'        => sprintf('%.2f', $proportion['settlementMoney']),
                    'change_price' => sprintf('%.2f', $proportion['proportionPrice']),
                    'mark'         => $proStr
                ];

            } catch (DbException $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * 获取经销商对应的结算比
     * @param Order $order
     * @return array
     */
    private static function getDealerConversionRatio(Order $order): array
    {
        //获取结算比
        $proportion = OrderSettlementRepository::getDealerConversionRatio($order->brand_id);

        //计算手续费等信息
        if ($proportion > 0) {
            //结算后需要扣除的金额(后台设置为整数手续费，这里直接取千分，且毫厘四舍五入例如：0.125) 满五进一
            $proportionPrice = sprintf("%.2f", bcmul(sprintf("%.2f", $order->pay_price), sprintf("%.3f", $proportion / 1000), 3));
            $settlementMoney = sprintf("%.2f", bcsub($order->pay_price, $proportionPrice, 2));
        } else {
            $settlementMoney = sprintf('%.2f', $order->pay_price);
        }

        return compact('proportionPrice', 'settlementMoney');
    }

}
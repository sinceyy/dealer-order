<?php
declare(strict_types=1);

namespace YddOrder\OrderField;

/**
 * 订单字段定义
 * Class OrderField
 * @package YddOrder\OrderField
 */
class OrderFieldConstant
{
    //订单状态正常
    const SUCCESS = 1;
    //已支付
    const PAY_SUCCESS = 1;
    //未结算
    const SETTLED = 0;
    //已结算
    const SETTLED_SUCCESS = 1;
    //取消
    const CANCLE = 0;
    //待发货
    const DELIVERY = 1;
    //待收货
    const RECEIPT = 2;
    //待评价
    const COMMENT = 3;
    //已完成
    const COMPLETE = 4;
    //退款中
    const REFUND = 5;
    //退货退款（暂无退款）
    //已退款
    const REFUND_SUCCESS = 7;
    //经销商类型
    const ORDER_SOURCE_DEALER = 1;
    //门店类型
    const ORDER_SOURCE_STORE = 2;
    //物流配送
    const LOGISTICS = 1;
    //门店自提
    const RAISING = 2;

}
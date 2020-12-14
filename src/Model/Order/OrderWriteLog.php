<?php

declare(strict_types=1);

namespace YddOrder\Model\Order;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 订单model
 * Class Order
 * @package YddOrder\Model\Order
 */
class OrderWriteLog extends Model
{
    use SoftDelete;

    protected $connection = 'order';


    public function detail(): \think\model\relation\HasOne
    {
        return $this->hasOne(OrderDetail::class, 'order_id', 'order_id')->field('id,order_id,buy_num,total_pay_price,product_name,product_image,product_attr');
    }

    public function orderUser(): \think\model\relation\HasOne
    {
        return $this->hasOne(OrderExtract::class, 'order_id', 'order_id')->field('extract_name,extract_phone');
    }
}
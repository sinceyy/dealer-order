<?php

declare(strict_types=1);

namespace YddOrder\Model\Order;

use think\Model;
use think\model\concern\SoftDelete;
use YddOrder\Model\Dealer\Dealer;
use YddOrder\Model\Dealer\DealerSale;
use YddOrder\Model\Store\StoreClerk;

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
        return $this->hasOne(OrderExtract::class, 'order_id', 'order_id')->field('order_id,extract_name,extract_phone');
    }

    public function orderClerkUser(): \think\model\relation\HasOne
    {
        return $this->hasOne(Dealer::class, 'id', 'write_user_id')->field('id,dealer_name as write_name ');
    }

    public function orderStoreClerkUser(): \think\model\relation\HasOne
    {
        return $this->hasOne(StoreClerk::class, 'id', 'write_user_id')->field('id,clerk_name as write_name ');
    }

    public function orderClerkSale(): \think\model\relation\HasOne
    {
        return $this->hasOne(DealerSale::class, 'id', 'write_user_id')->field('id,sale_name as write_name ');
    }
}
<?php

declare(strict_types=1);

namespace YddOrder\Model\Order;

use think\Model;
use YddOrder\Model\Member\Member;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;
use think\model\relation\HasMany;
use think\model\relation\HasOne;

/**
 * 订单model
 * Class Order
 * @package YddOrder\Model\Order
 */
class Order extends Model
{
    use SoftDelete;

    protected $connection = 'brand';

    /**
     * 订单商品列表
     * @return HasMany
     */
    public function product(): HasMany
    {
        return $this->hasMany('OrderProduct');
    }

    /**
     * 关联订单收货地址表
     * @return HasOne
     */
    public function address(): HasOne
    {
        return $this->hasOne("OrderAddress");
    }

    /**
     * 关联订单收货地址表
     * @return HasOne
     */
    public function extract(): HasOne
    {
        return $this->hasOne("OrderExtract");
    }

    /**
     * 关联用户表
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(Member::class);
    }
}
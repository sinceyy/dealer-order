<?php

declare(strict_types=1);

namespace YddOrder\Model\Order;

use think\Model;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;
use think\model\relation\HasMany;
use think\model\relation\HasOne;

/**
 * 售后管理model
 * Class OrderRefund
 * @package YddOrder\Model\Order
 */
class OrderRefund extends Model
{
    use SoftDelete;

    /**
     * 关联用户表
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo("domain\\entity\\client\\Member",'user_id');
    }

    /**
     * 关联订单主表
     * @return BelongsTo
     */
    public function orderMaster(): BelongsTo
    {
        return $this->belongsTo('Order');
    }

    /**
     * 关联订单商品表
     * @return BelongsTo
     */
    public function orderProduct(): BelongsTo
    {
        return $this->belongsTo('OrderProduct');
    }

    /**
     * 关联图片记录表
     * @return HasMany
     */
    public function image(): HasMany
    {
        return $this->hasMany('OrderRefundImage');
    }

    /**
     * 关联物流公司表
     * @return BelongsTo
     */
    public function express(): BelongsTo
    {
        return $this->belongsTo('Express');
    }

    /**
     * 关联退货地址表
     * @return HasOne
     */
    public function address(): HasOne
    {
        return $this->hasOne('OrderRefundAddress');
    }
}
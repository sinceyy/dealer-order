<?php

declare(strict_types=1);

namespace YddOrder\Model\Order;

use think\Model;
use YddOrder\Model\Member\Member;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;

/**
 * 售后管理model
 * Class OrderRefund
 * @package YddOrder\Model\Order
 */
class OrderRefund extends Model
{
    use SoftDelete;

    protected $connection = 'order';

    protected $autoWriteTimestamp = true;

    /**
     * 关联用户表
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'user_id');
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
        return $this->belongsTo('OrderDetail');
    }

    /**
     * 关联秒杀订单商品表
     * @return BelongsTo
     */
    public function orderSekillProduct(): BelongsTo
    {
        return $this->belongsTo('OrderDetailSekill');
    }

}
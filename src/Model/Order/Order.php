<?php

declare(strict_types=1);

namespace YddOrder\Model\Order;

use think\Model;
use think\model\relation\BelongsTo;
use YddOrder\Model\Member\Member;
use think\model\concern\SoftDelete;
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

    protected $connection = 'order';

    protected $autoWriteTimestamp = true;

    protected $readonly = ['order_id', 'order_no'];

    /**
     * 订单详情列表
     * @return HasMany
     */
    public function detail(): HasMany
    {
        return $this->hasMany('OrderDetail');
    }

    /**
     * 关联订单结算
     * @return HasOne
     */
    public function settlement(): HasOne
    {
        return $this->hasOne('OrderSettlementLog');
    }

    /**
     * 秒杀订单详情列表
     * @return HasMany
     */
    public function detailSekill(): HasMany
    {
        return $this->hasMany('OrderDetailSekill');
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
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'user_id')->field('id,member_name,real_name,mobile');
    }
}
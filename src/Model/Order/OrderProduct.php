<?php

declare(strict_types=1);

namespace YddOrder\Model\Order;

use think\Model;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;

/**
 * 订单商品model
 * Class OrderProduct
 * @package YddOrder\Model\Order
 */
class OrderProduct extends Model
{
    use SoftDelete;

    /**
     * 订单商品图片列表
     * @return BelongsTo
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo('UploadFile', 'product_image_id', 'id');
    }
}
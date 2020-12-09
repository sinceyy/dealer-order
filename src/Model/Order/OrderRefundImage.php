<?php

declare(strict_types=1);

namespace YddOrder\Model\Order;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 退款图片
 * Class OrderRefundImage
 * @package app\model\order\order
 */
class OrderRefundImage extends Model
{
    use SoftDelete;

    protected $connection = 'brand';
}
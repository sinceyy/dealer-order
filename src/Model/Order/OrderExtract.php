<?php

declare(strict_types=1);

namespace YddOrder\Model\Order;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 自提收货人信息model
 * Class OrderExtract
 * @package app\model\order\order
 */
class OrderExtract extends Model
{
    use SoftDelete;

    protected $connection = 'brand';
}
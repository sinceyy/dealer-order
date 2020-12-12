<?php

declare(strict_types=1);

namespace YddOrder\Model\Store;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 门店用户model
 * Class Dealer
 * @package app\model
 */
class StoreClerk extends Model
{
    use SoftDelete;

    protected $connection = 'brand';
}
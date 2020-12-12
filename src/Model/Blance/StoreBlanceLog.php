<?php

declare(strict_types=1);

namespace YddOrder\Model\Blance;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 钱包记录model
 * Class Express
 * @package app\model\express
 */
class StoreBlanceLog extends Model
{
    use SoftDelete;

    protected $connection = 'brand';

    protected $autoWriteTimestamp = true;
}
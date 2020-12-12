<?php
declare(strict_types=1);

namespace YddOrder\OrderField;

/**
 * 订单字段定义
 * Class OrderField
 * @package YddOrder\OrderField
 */
class OrderField
{
    /**
     * 返回查询要的字段
     * @param string $field
     * @return string
     */
    public static function getOrderSelectField(string $field = ''): string
    {
        if ($field) return $field;
        return 'o.*,oe.extract_shop_id,oe.name,oe.phone,oe.extract_name,oe.extract_phone';
    }
}
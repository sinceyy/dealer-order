<?php


namespace YddOrder\Service;


abstract class ServiceAbstruct
{

    /**
     * 返回正常信息
     * @param array $data
     * @return array
     */
    public static function returnData(array $data = []): array
    {
        return ['code' => 200, 'data' => $data];
    }

    /**
     * 返回错误信息
     * @param string $msg
     * @param array  $data
     * @return array
     */
    public static function returnErrorData(string $msg = '', array $data = []): array
    {
        return ['code' => 200, 'data' => $data];
    }
}
<?php
declare(strict_types=1);

namespace YddOrder\Repository\Dealer;

use sunshine\model\dealer\Dealer;
use sunshine\repository\AbstractRepository;
use sunshine\util\tool\PassTools;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;

class DealerRepository extends AbstractRepository
{
    /**
     * 经销商列表
     * @param array $where
     * @param array $params
     * @param int $brand_id
     * @return \think\Collection
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function selectLists(array $where, array $params, int $brand_id): \think\Collection
    {
        return Dealer::where('brand_id', $brand_id)
            ->where($where)
            ->order('create_time', 'desc')
//            ->page((int)$params['page'], (int)$params['limit'])
            ->paginate([
                'limit'=>$params['limit'],
                'page'=>$params['page']
            ])
            ->select();
    }

    /**
     * 列表总数
     * @param int $brand_id
     * @param array $where
     * @return int
     */
    public static function countLists(int $brand_id, array $where)
    {
        return Dealer::where('brand_id', $brand_id)
            ->where($where)
            ->count();
    }

    /**
     * 根据id获取
     * @param int $brand_id
     * @param int $id
     * @return array|Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getInfoById(int $brand_id,int $id = 0)
    {
        return Dealer::with('dealerLevel')->where(['brand_id'=>$brand_id,'id' => $id])->find();
    }

    /**
     * 经销商禁用/启用
     * @param int $brand_id
     * @param array $data
     * @return bool
     */
    public static function enableStatus(int $brand_id,array $data): bool
    {
        $where = ['id' => $data['id'], 'brand_id' => $brand_id];
        $ad = Dealer::where($where)->save(['status' => $data['status']]) !== false;
        return $ad === false ? false : true;
    }

    /**
     * 创建数据
     * @param array $data
     * @return bool
     */
    public static function createDealer(array $data): bool
    {
        $ad = Dealer::create(self::setData($data));
        return $ad === false ? false : true;
    }


    /**
     * 设置经销商数据
     * @param array $params
     * @return array
     */
    public static function setData(array $params): array
    {
        return [
            'brand_id'         => $params['brand_id'],
            'dealer_sn'        => $params['dealer_sn'],
            'dealer_name'      => $params['dealer_name'],
            'level_id'         => $params['level_id'],
            'province'         => $params['province'],
            'city'             => $params['city'],
            'region'           => $params['region'],
            'address'          => $params['address'],
            'is_login'         => isset($params['is_login']) ? $params['is_login'] : 0,
            'parent_dealer_id' => isset($params['parent_dealer_id']) ? $params['parent_dealer_id'] : 0,
            'leader_name'      => $params['leader_name'],
            'leader_phone'     => $params['leader_phone'],
            'lng'              => $params['lng'],
            'lat'              => $params['lat'],
            'sale_area'        => json_encode($params['sale_area'] ?? []),
            'sale_area_text'   => json_encode($params['sale_area_text'] ?? []),
            'leader_wx'        => isset($params['leader_wx']) ? $params['leader_wx'] : '',
            'leader_qq'        => isset($params['leader_wx']) ? $params['leader_wx'] : '',
            'leader_email'     => isset($params['leader_email']) ? $params['leader_email'] : '',
            'password'         => PassTools::getNewPass('123456'),
        ];
    }
}
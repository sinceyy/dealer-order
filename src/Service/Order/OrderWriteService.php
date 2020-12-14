<?php
declare(strict_types=1);

namespace YddOrder\Service\Order;

use kernel\queue\factory\Settlement;
use think\exception\InvalidArgumentException;
use think\facade\Queue;
use YddOrder\OrderField\OrderFieldConstant;
use YddOrder\Repository\Dealer\DealerRepository;
use YddOrder\Repository\Order\OrderRepository;
use YddOrder\Repository\Store\StoreRepository;
use YddOrder\Repository\Order\OrderWriteRespository;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;

class OrderWriteService
{

    /**
     * 返回核销记录
     * @param array $params
     * @return \think\Paginator
     * @throws \think\db\exception\DbException
     */
    public static function getWriteOrderList(array $params): \think\Paginator
    {
        return OrderWriteRespository::getWriteLogList($params);
    }

    /**
     * 订单核销（核销码）
     * @param string $code
     * @param int    $user_id   （经销商用户id/门店用户id）
     * @param int    $user_type (1经销商用户2门店用户)
     * @return bool
     * @throws \Exception
     */
    public static function writeOff(string $code, int $user_id, int $user_type): bool
    {
        //获取订单信息
        $order = OrderRepository::getInfoByWhere(
            [
                'write_code'   => str_replace(' ', '', $code),
                'order_status' => OrderFieldConstant::SUCCESS,
                'pay_status'   => OrderFieldConstant::PAY_SUCCESS
            ]);

        //check 检查
        if (!$order) throw new InvalidArgumentException('暂未查询到此待核销订单');
        //检查是否自己订单
        $error = self::checkOrderIsMe($order, $user_id, $user_type);
        if (isset($error['error'])) throw new InvalidArgumentException($error['error']);

        try {
            //更改订单核销状态
            $upOrderStatus = OrderWriteRespository::upOrderWriteStatus($order->id);
            if (!$upOrderStatus) throw new InvalidArgumentException('订单信息修改失败！');
            //记录核销记录
            $write = OrderWriteRespository::setWriteLog($order, $user_id, $user_type);
            if (!$write) throw new InvalidArgumentException('订单信息记录新增失败！');
            //进行结算
            //$settlement = new OrderSettlementRepository;
            //操作成功则开始结算
            //$settlementOrder = $settlement($order, $user_id, $user_type)->handle()->upSettlementStatus()->addUserMoney()->settlementLog();
            //结算成功，提交事物
            if ($write && $upOrderStatus) {
                //进行结算(写入队列执行)
                Queue::push(Settlement::class, compact('code', 'user_id', 'user_type'));
                return true;
            }
            throw new \Exception('核销失败!');
        } catch (DbException $dbe) {
            throw new InvalidArgumentException($dbe->getMessage());
        } catch (Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        return true;
    }

    /**
     * 订单核销（自助核销）
     * @param array $codeArr
     * @param int   $user_id   （经销商用户id/门店用户id）
     * @param int   $user_type (1经销商用户2门店用户)
     * @return bool
     * @throws \Exception
     */
    public static function writeOffForSelf(array $codeArr, int $user_id, int $user_type): bool
    {
        $errorWrite = $orderArr = [];

        //循环检查数据完整性
        foreach ($codeArr as $k => $v) {
            //获取订单信息
            $order = OrderRepository::getInfoByWhere(
                [
                    'write_code'   => str_replace(' ', '', $v),
                    'order_status' => OrderFieldConstant::SUCCESS,
                    'pay_status'   => OrderFieldConstant::PAY_SUCCESS,
                    'order_source' => $user_type
                ]);

            //check 检查
            if (!$order) {
                throw new InvalidArgumentException("核销码：{$v} 暂未查询到此对应待核销订单");
            }
            //检查是否自己订单
            $error = self::checkOrderIsMe($order, $user_id, $user_type);
            if (isset($error['error'])) {
                //组装检查错误的部分数据
                $errorWrite['checkError'][] = '订单号：' . $order->order_no . $error['error'];
            } else {
                $orderArr[] = $order;
            }
        }
        //将检查错误的数据返回出去
        if (count($errorWrite['checkError'])) {
            throw new InvalidArgumentException(implode(',', $errorWrite['checkError']));
        } else if (count($orderArr)) {
            //处理订单核销
            foreach ($orderArr as $order) {
                try {
                    //更改订单核销状态
                    $upOrderStatus = OrderWriteRespository::upOrderWriteStatus($order->id);
                    if (!$upOrderStatus) throw new InvalidArgumentException('订单信息修改失败，订单号：' . $order->order_no);
                    //记录核销记录
                    $write = OrderWriteRespository::setWriteLog($order, $user_id, $user_type);
                    if (!$write) throw new InvalidArgumentException('订单信息记录新增失败，订单号：' . $order->order_no);
                    //结算任务放到队列执行
                    if ($write && $upOrderStatus) {
                        //进行结算(写入队列执行)
                        Queue::push(Settlement::class, compact('code', 'user_id', 'user_type'), (string)$order->order_no);
                    }
                    throw new \Exception('核销失败!');
                } catch (DbException $dbe) {
                    throw new InvalidArgumentException('订单号：' . $order->order_no . $dbe->getMessage());
                } catch (Exception $e) {
                    throw new InvalidArgumentException('订单号：' . $order->order_no . $e->getMessage());
                }
            }
        }

        return true;
    }


    /**
     * 检查是否所属自己或下级订单
     * @param     $order
     * @param int $user_id
     * @param int $user_type
     * @return bool|string[]
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    private static function checkOrderIsMe($order, int $user_id, int $user_type)
    {
        //经销商处理订单
        if ($user_type == 1) {
            //订单所属经销商门店
            if ($order->order_source == 1) {
                //检查订单是否是该经销商门店订单，或是该经销商旗下门店订单
                $info = DealerRepository::getInfoById($order->source_id);
                if (!$info) return ['error' => '暂未查询到该待核销订单所属门店信息'];
                if ($order->source_id != $user_id && $info->parent_dealer_id != $user_id) {
                    return ['error' => '暂未查询到该待核销订单所属经销商信息'];
                }
            } else if ($order->order_source == 2) { //订单所属经销商下门店
                $storeInfo = StoreRepository::getInfoById($order->source_id);
                if ($storeInfo->pid != $user_id) {
                    return ['error' => '暂未查询到该待核销订单所属经销商信息'];
                }
            } else {
                return ['error' => '订单信息错误，无法查询到此单，请确定是否属于当前账户下'];
            }
        } else if ($user_type == 2) {
            //订单所属经销商门店
            if ($order->order_source == 1) return ['error' => '暂未查询到该待核销订单所属门店信息'];
            if ($order->order_source == 2 && $order->source_id != $user_id) return ['error' => '暂未查询到该待核销订单所属门店信息'];
        }

        return true;
    }
}
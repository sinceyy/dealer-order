<?php
declare(strict_types=1);

namespace YddOrder\Service\Order;

use kernel\queue\factory\Settlement;
use think\exception\InvalidArgumentException;
use think\facade\Db;
use think\facade\Queue;
use YddOrder\Model\Order\Order;
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
     * @param int    $user_id   （门店用户id）
     * @param int    $store_id (门店id)
     * @return bool
     * @throws \Exception
     */
    public static function writeOff(string $code, int $user_id, int $store_id): bool
    {
        //获取订单信息
        $order = OrderRepository::getInfoByWhere(
            [
                'write_code'   => str_replace(' ', '', $code),
                'order_status' => OrderFieldConstant::SUCCESS,
                'pay_status'   => OrderFieldConstant::PAY_SUCCESS,
                'source_id'    => $store_id
            ]);

        //check 检查
        if (!$order) throw new InvalidArgumentException('暂未查询到此待核销订单');

        Order::newQuery()->transaction(function () use ($user_id, $order, $store_id, $code) {
            //更改订单核销状态
            $upOrderStatus = OrderWriteRespository::upOrderWriteStatus($order->id);
            if (!$upOrderStatus) throw new InvalidArgumentException('订单信息修改失败！');
            //记录核销记录
            $write = OrderWriteRespository::setWriteLog($order, $user_id, $store_id);
            if (!$write) throw new InvalidArgumentException('订单信息记录新增失败！');
            //进行结算
            //$settlement = new OrderSettlementRepository;
            //操作成功则开始结算
            //$settlementOrder = $settlement($order, $user_id, $user_type)->handle()->upSettlementStatus()->addUserMoney()->settlementLog();
            //结算成功，提交事物
            if ($write && $upOrderStatus) {
                //进行结算(写入队列执行)
                Queue::push(Settlement::class, compact('code', 'user_id', 'store_id'));
                return true;
            }
        });

        return true;
    }

    /**
     * 订单核销（自助核销）
     * @param array $codeArr
     * @param int   $user_id
     * @param int   $store_id （门店id）
     * @return bool
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws \Throwable
     * @throws \think\db\exception\DbException
     */
    public static function writeOffForSelf(array $codeArr, int $user_id, int $store_id): bool
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
                    'source_id'    => $store_id
                ]);

            //check 检查
            if (!$order) {
                $errorWrite['checkError'][] = "核销码：{$v} 暂未查询到此对应待核销订单";
            } else {
                $orderArr[] = $order;
            }
        }
        //将检查错误的数据返回出去
        if (count($errorWrite['checkError'])) {
            throw new InvalidArgumentException(implode(',', $errorWrite['checkError']));
        } else if (count($orderArr)) {

            Order::newQuery()->transaction(function () use ($user_id, $store_id, $order, $orderArr) {
                //处理订单核销
                foreach ($orderArr as $order) {
                    try {
                        //更改订单核销状态
                        $upOrderStatus = OrderWriteRespository::upOrderWriteStatus($order->id);
                        if (!$upOrderStatus) throw new InvalidArgumentException('订单信息修改失败，订单号：' . $order->order_no);
                        //记录核销记录
                        $write = OrderWriteRespository::setWriteLog($order, $user_id, $store_id);
                        if (!$write) throw new InvalidArgumentException('订单信息记录新增失败，订单号：' . $order->order_no);
                        //结算任务放到队列执行
                        if ($write && $upOrderStatus) {
                            //进行结算(写入队列执行)
                            Queue::push(Settlement::class, compact('code', 'user_id', 'store_id'), (string)$order->order_no);
                            return true;
                        }
                        throw new \Exception('核销失败!');
                    } catch (DbException $dbe) {
                        throw new InvalidArgumentException('订单号：' . $order->order_no . $dbe->getMessage());
                    } catch (Exception $e) {
                        throw new InvalidArgumentException('订单号：' . $order->order_no . $e->getMessage());
                    }
                }
            });
        }

        return true;
    }
}
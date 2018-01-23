<?php
namespace app\index\controller;
use app\Index\model\AiMl;
use think\Config;
use think\Env;
use think\Loader;
use think\Request;
use wechatsdk\wechatv2;
use Phpml\Classification\KNearestNeighbors;
class Index
{
    private static $config = [];
    private $req = null;
    use \traits\controller\Jump;
    public function __construct()
    {
        self::$config = [
            'host' => 'localhost',
            'port' => 5672,
            'vhost' => '/',
            'login' => 'guest',
            'password' => 'guest',
        ];
        $this->req = Request::instance();

    }
    public function index()
    {
        
    }

    private function mySort($a,$b)
    {

    public function used($uid)
    {
       
    }

    public function read()
    {
        
    }

    public function amqp()
    {
        global $argv;
        $routingKey = $argv[2];
        $message = 'Hello World!';
        $conn = new \AMQPConnection(self::$config);
        $conn->connect() or die('Could not connect.');
        $channel = new \AMQPChannel($conn);
        $exchange = new \AMQPExchange($channel);
        $exchange->setName('rand_routing');
        $exchange->setType(AMQP_EX_TYPE_DIRECT);
        $exchange->declareExchange();
        $queue = new \AMQPQueue($channel);
        //$queue->setName('each_distribute_queue');
        $queue->setFlags(AMQP_DURABLE);
        $queue->declareQueue();
        $exchange->publish($message,$routingKey);//exchange 将消息发送到路由
        var_dump("[x] Sent $message");
        $conn->disconnect();
    }

    public function receive()
    {
        global $argv;
        $conn = new \AMQPConnection(self::$config);
        $conn->connect() or die('Could not broker.');
        $channel = new \AMQPChannel($conn);
        $exchange = new \AMQPExchange($channel);
        $exchange->setName('rand_routing');
        $exchange->setType(AMQP_EX_TYPE_DIRECT);
        $exchange->declareExchange();
        $queue = new \AMQPQueue($channel);
        $queue->setFlags(AMQP_DURABLE);
        $queue->declareQueue();
        $severities = array_slice($argv, 2);
        foreach($severities as $severity) {
            $queue->bind($exchange->getName(),$severity);//绑定路由
        }

        echo '[*] Waiting for messages. To exit press CTRL+C';
        $queue->consume(array($this,'callback'));//消费队列信息
        $channel->qos(0,2);
        $conn->disconnect();
    }

    public function callback($envelope,$queue)
    {
        $msg = $envelope->getBody();
        echo " [x] Received:" . $msg.'\s';
        echo "routing key:".$envelope->getRoutingKey();
        //sleep(2);
        $queue->ack($envelope->getDeliveryTag());
    }

    public function mlDemo()
    {
        $samples = [[1, 3], [1, 4], [2, 4], [3, 1], [4, 1], [4, 2]];
        $labels = ['a', 'a', 'a', 'b', 'b', 'b'];
        $classifier = AiMl::getInstance();
        $classifier->train($samples ,$labels);
        echo $classifier->predict([3 ,2]);
    }

    public function counter()
    {
        $count = 1;
        return function() use(&$count){
            return $count++;
        };
    }

    private function countera(&$count)
    {
        return $count++;
    }

    public function outCounter()
    {
        $counter1 = $this->counter();
        $counter2 = $this->counter();

        for($i=1;$i<=3;$i++)
        {
            echo "counter1: " . $counter1() . "<br />";
        }

        for($i=1;$i<=3;$i++)
        {
            echo "counter2: " . $counter2() . "<br />";
        }
    }

    public function bubbleSort($arR){
        $arr = [75,38,64,12,32];
        for($i=0;$i<count($arr)-1;$i++)
        {
            for($j=0;$j<count($arr)-1-$i;$j++){
                if($arr[$j] > $arr[$j+1]){
                    $temp = $arr[$j+1];
                    $arr[$j+1] = $arr[$j];
                    $arr[$j] = $temp;
                }
            }
        }
        print_r($arr);
    }

    public function pcntlFork()
    {

    }

    public function annulusHash()
    {
        $config = array(
            '127.0.0.1:11211',
            '127.0.0.1:11212',
            '127.0.0.1:11213',
            '127.0.0.1:11214',
            '127.0.0.1:11215'
        );

        if (!$config) throw new Exception('Cache config NULL');
        $maxValue = pow(2,10);
        foreach ($config as $key => $value) {
            for ($i = 0; $i < $maxValue; $i++) {
                $node[sprintf("%u", crc32($value . '_' . $i))] = $value . '_' . $i;
            }
        }
        ksort($node);
        //print_r($node);
        //建立可以进行二分法查找的数组
        $j=0;
        foreach($node as $key=>$value){
            $newKeyNode[$j++] = $key;
        }
        //print_r($newKeyNode);
        //传入参与值
        for($k=0;$k<2;$k++) {
            $mcKey = 'ts_imb_userInfo_'.$k;
            $hashValue = sprintf('%u', crc32($mcKey));
            echo $hashValue."\n";
            //mc环形分布
            $index = $this->searchSectionMaxValue($hashValue, $newKeyNode);
            echo $index."\n";
            //echo $node[$newKeyNode[$index]]."\n";
        }
    }

    public function search()
    {
        for($i=0;$i<50;$i++){
            $arr[$i] = $i;
            if($i==20) $arr[$i] = 19;
        }
        ksort($arr);
        print_r($arr);
        $mcKey = 'ts_imb_userInfo_429823';
        $hashValue = sprintf('%u' ,crc32($mcKey));
        echo $this->dichotomySearch(20 ,$arr);
    }

    //while循环二分法查找
    public function dichotomySearch($search ,$arr)
    {
        $high = count($arr)-1;//最大key值
        $low = 0;
        if($search >= $arr[$high])
            return $high;
        if($search == $arr[$low])
            return $low;
        while($low <= $high){
            //取得数组的中间键值
            $mid = intval(($low+$high)/2);
            if($arr[$mid]==$search){
                return $mid; break;
            }elseif($arr[$mid] > $search){
                $high = $mid -1;
            }elseif($arr[$mid] < $search){
                $low = $mid+1;
            }
        }
        return false;
    }

    //递归二分法查找
    public function searchBinary($arr , $search ,$low ,$height)
    {
        if($height == 0){
            $height = count($arr)-1;
        }
        if($low <= $height){
            $mid = intval(($low+$height)/2);
            if($arr[$mid] == $search){
                return $mid;
            }elseif($arr[$mid] < $search){
                return $this->searchBinary($arr , $search ,$mid+1 ,$height);
            }else{
                return  $this->searchBinary($arr , $search ,$low ,$mid-1);
            }
        }
    }

    /**
     * @name 二分法查找最靠近区间
     * @param $search
     * @param $arr
     * @return bool|int
     */
    public function searchSectionMaxValue($search,$arr)
    {
        $high = count($arr)-1;//最大key值
        $low = 0;
        if($search >= $arr[$high])
            return $high;
        if($search == $arr[$low])
            return $low;
        while($low <= $high){
            //取得数组的中间键值
            $mid = intval(($low+$high)/2);
            if($arr[$mid]==$search){
                return $mid; break;
            }elseif($arr[$mid] > $search){
                $high = $mid -1;
            }elseif($arr[$mid] < $search){
                $low = $mid+1;
            }
        }

        if(abs($arr[$high] - $search) < abs($arr[$mid] - $search) ){//最大值与查找相值差的绝对值小于中间值与查找值的绝对值
            return $high;
        }else{
            return $mid;
        }
        return false;
    }

    /**
     * @name 生成二叉树
     * @param $array
     * @param $pid
     * @return array|string
     */
    public function buildTree($array ,$pid)
    {
        $newArr = '';
        foreach($array as $key=>$row)
        {
            if($row['parent_id'] == $pid){
                $row['parent_id'] = $this->buildTree($array ,$row['id']);
                $newArr[] = $row;
            }
        }
        return $newArr;
    }

    /**
     * @name 构建二叉树数据结构
     */
    public function getTree()
    {
        global $argv;
        $pid = $argv[2];
        $k = 7;
        $id = 0;
        for($i=65;$i<=90;$i++){
            $name = '';
            $parentId = 0;
            for($j=0;$j<$k;$j++){
                $name .=chr($i);
                $temp['id'] = ++$id;
                $temp['parent_id'] = ($j==0 && $i>65) ? $parentId : $temp['id']-1;
                $temp['name'] = $name;
                $array[] = $temp;
            }
        }
        $result = $this->buildTree($array ,$pid);
        print_r($result);
    }

}

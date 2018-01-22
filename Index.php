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
        $url = "http://www.imabo.net/?";
        $url .= (strpos($url ,"?") == false) ? '?mb_open_new_controller=1' : '&mb_open_new_controller=1';

        echo (microtime(TRUE));
        $path = "var/www/caifu/apps/landingpage/Lib/Model/VipWeiBoCommon818";
        //Config::load(APP_PATH.'config/config.php');
        echo Config::get('database.type');
        echo Config::get('app_namespace');
        $data = [
            9916=>['total'=>10.1,'name'=>'佛自在'],
            3424=>['total'=>10.2,'name'=>'佛自在2'],
            3423=>['total'=>10,'name'=>'佛自在3'],
        ];
        uasort($data,array($this,'mySort'));
        $diffTime = strtotime('+1 day', strtotime(date('Ymd')))-time();
        $demo = new \my\Test();
        $data = $demo->index();
        $demo2 = new \wechatsdk\wechat();
        $data2 = $demo2->index();
        Loader::import('wechatsdk.wechatv2',EXTEND_PATH,'.class.php');
        $demo3 = new wechatv2();
        $data3 = $demo3->index();
        $data = ['name'=>'tp5','url'=>'thinkphp.com'];

        return ['data'=>$data3,'code'=>0,'message'=>'操作完成','extra'=>Config::get('database.hostname'),'env'=>ENV::get('type'),'url'=>$url];
        //return $data.'<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_bd568ce7058a1091"></thinkad>';
    }

    private function mySort($a,$b)
    {
        if($a['total'] ==  $b['total']) return 0;
        return $a['total'] > $b['total'] ? -1 : 1;
    }

    public function used($uid)
    {
        $whiteUser = [4422280];
        echo $uid;
        if(!in_array($uid,$whiteUser)) {//by Bande
            return 1;
        }
    }

    public function read()
    {
        $data = [
            'TSLA',
            'AAPL',
            'AAP',
        ];
        echo json_encode($data);
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
        $maxValue = pow(2,4);
        foreach ($config as $key => $value) {
            for ($i = 0; $i < $maxValue; $i++) {
                $node[sprintf("%u", crc32($value . '_' . $i))] = $value . '_' . $i;
            }
        }
        ksort($node);
        print_r($node);
        //建立可以进行二分法查找的数组
        $j=0;
        foreach($node as $key=>$value){
            $newKeyNode[$j++] = $key;
        }
        print_r($newKeyNode);
        //传入参与值
        for($k=0;$k<2;$k++) {
            $mcKey = 'ts_imb_userInfo_429823_'.$k;
            $hashValue = sprintf('%u', crc32($mcKey));
            echo $hashValue."\n";
            //mc环形分布
            $index = $this->dichotomySearch($hashValue, $newKeyNode);
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
        //echo $this->searchBinary($arr ,49 ,0,0);


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
        return -1;
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

}

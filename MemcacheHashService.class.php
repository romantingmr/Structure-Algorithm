<?php

/**
 * Class MemcacheHashService
 * @name mc一致性hash映射算法
 * @author romantingmr
 * @version 20180119
 */
class MemcacheHashService extends Service{
	private $nodes = [];
	private $hKeyNodes = [];
	private $rdsObj = null;
	private $rdsDb = 12;
	private $mcHashKey = 'memcache_hash_nodes_key';

	public function __construct()
	{
		if($this->rdsObj == null) {
			require_cache(VENDOR_PATH . 'libs/redis/RedisLong.class.php');
			$this->rdsObj = RedisLong::getInstance();
		}
	}

	public function setHashMemConfig(){
		$this->rdsObj->select($this->rdsDb);
		$mcNodes = $this->rdsObj->get($this->mcHashKey);
		if($mcNodes){
			$this->nodes = json_decode($mcNodes ,true);
		}else {//定时任务初始化mc环形
			$config = C('MEMCACHE_HOST');
			if (empty($config))
				$config = ['host' => '127.0.0.1', 'port' => '11212', 'weight' => '100'];
			$maxId = pow(2, 5);
			foreach ($config as $key => $row) {
				for ($i = 0; $i < $maxId; $i++) {
					$this->nodes[sprintf('%u', crc32($row['host'] . ":" . $row['port'] . '_' . $i))] = $row['host'] . ":" . $row['port'] . '_' . $i;
				}
			}
			ksort($this->nodes);
			$this->rdsObj->set($this->mcHashKey ,json_encode($this->nodes));
		}
		//print_r($this->nodes);
		return $this;
    }

	public function preSearch()
	{
		$j = 0;
		if($this->nodes){
			foreach($this->nodes as $key=>$value){
				$this->hKeyNodes[$j++] = $key;
			}
		}
		//print_r($this->hKeyNodes);
		return $this;
	}

	public function setHitWhichHost($key)
	{
		$mcKey = sprintf('%u' ,crc32($key));
		$index = $this->dichotomySearch($mcKey);
		if(!empty($this->hKeyNodes[$index])){
			$hosts = explode(':',(explode("_" ,$this->nodes[$this->hKeyNodes[$index]])[0]));
			return ['host'=>$hosts[0] ,'port'=>$hosts[1]];
		}else{
			$hosts = C('MEMCACHE_HOST');
			return ['host'=>$hosts[0]['host'] ,'port'=>$hosts[0]['port']];//保证命中第一台服务端
		}
	}
	/**
	 * @name 二分法查找
	 * @param $search
	 * @return int
	 */
	public function dichotomySearch($search)
	{
		$low = 0;
		$high = count($this->hKeyNodes)-1;
		if($search >= $this->hKeyNodes[$high])
			return $high;
		if($search == $this->hKeyNodes[$low])
			return $low;
		while($low <= $high){
			$mid = intval(($low+$high)/2);
			if($search == $this->hKeyNodes[$mid]){
				return $mid;break;
			}elseif($search > $this->hKeyNodes[$mid]){
				$low = $mid + 1;
				//echo 'low->high:'.$low.':'.$high."\n";
			}else{
				$high = $mid - 1;
				//echo 'high->low:'.$high.':'.$low."\n";
			}
		}
		$diffValueOffsetHigh = abs($this->hKeyNodes[$high] - $search);
		$diffValueOffsetMid = abs($this->hKeyNodes[$mid] - $search);
		if($diffValueOffsetHigh < $diffValueOffsetMid) return $high;
		else return $mid;
	}

	//运行服务，系统服务自动运行
	public function run(){

	}
}
?>
<?php
namespace K360;

class Timer{
	
	const KEY = 0xF984;					//定时器的锁key，每个项目下的锁key必须不同
	private $time = 5000;				//定时器时间
	
	const T = 1;
	const F = 0;
	
	const SLEEP = 100;
	
	/**
	 * 构造函数
	 * @param number $time		定时器时间
	 */
	public function __construct($time=2000){
		set_time_limit(0);
		error_reporting(E_ALL);
		ob_implicit_flush();
		$this->time = $time;
	}
	
	/**
	 * 判断定时器是否启动
	 * @return boolean		如果已经启动返回true，没有启动返回false
	 */
	public static function isStart(){
		return (self::_lock() == self::T);
	}
	
	/**
	 * 关闭定时器
	 */
	public static function close(){
		self::_lock(self::F);
		usleep(self::SLEEP*1000);
	}
	
	/**
	 * 启动定时器
	 * @param unknown $url			定时器回调url
	 */
	public function run($url){
		try{
			//判断是否已经启动
			if(self::isStart()) return;
			self::_lock(self::T);
			$times = 0;
			while (true){
				//读取锁
				$lv = self::_lock();
				if($lv == self::F || !$lv)
					return ;
				if($times > $this->time){
					//curl访问
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_TIMEOUT, 1);
					curl_exec($ch);
					curl_close($ch);
					$times = 0;
				}
				//睡眠
				usleep(self::SLEEP*1000);
				$times += self::SLEEP;
			}
			self::_lock(self::F);
		}catch (\Exception $e){
			self::_lock(self::F);
		}
	}
	
	/**
	 * 自动启动定时器
	 * @param unknown $runUrl		定时器启动url
	 */
	public static function autoStatr($runUrl){
		//读取锁
		$lv = self::_lock();
		if($lv == self::T) return;
		//curl访问
		$ch = curl_init($runUrl);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_exec($ch);
		curl_close($ch);
	}
	
	
	/**
	 * 锁操作
	 * @param string $value				锁的值，如果不传，则为读取，否则为设置
	 * @return Ambigous <NULL, string>	如果是读取则返回锁值，否则返回null
	 */
	private static function _lock($value = null){
		$shmid = shmop_open(self::KEY, "c", 0777, 1);
		$buffer = null;
		if($value !== null){
			shmop_write($shmid, $value, 0);
		}else{
			$buffer = shmop_read($shmid, 0, 1);
		}
		shmop_close($shmid);
		return $buffer;
	}
	
}
<?php
namespace Home\Controller;
use Think\Controller;
use K360\MSG;


class TimerController extends Controller{
	
	public function timerCB(){
		//次数可以查询数据并发送给客户端
		//MSG::sendSamplePush("这是推送消息，人人可见");
// 		MSG::sendPush("哈哈，你中枪啦，是躺着的。。！！");
//		MSG::sendSampleRemind(1, "teacher", "老师好，");
		MSG::sendSamplePush("我来推送一条消息，这是推送的哦~~");
// 		MSG::sendSampleRemind(1, "teacher", "<a href='#'>老师是煞笔</a>，哈哈~~");
	}
	
	
	
}
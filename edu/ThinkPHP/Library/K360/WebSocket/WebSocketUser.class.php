<?php
namespace K360\WebSocket;

class WebSocketUser {

  public $socket;
  public $id;
  public $headers = array();
  public $handshake = false;
  
  public $utype = "";			//用户类型
  public $uid = "";				//用户数据库id

  public $handlingPartialPacket = false;
  public $partialBuffer = "";

  public $sendingContinuous = false;
  public $partialMessage = "";
  
  public $hasSentClose = false;

  function __construct($id, $socket) {
    $this->id = $id;
    $this->socket = $socket;
  }
}
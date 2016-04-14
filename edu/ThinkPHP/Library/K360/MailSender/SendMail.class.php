<?php
namespace K360\MailSender;

class SendMail{
	private $mail = array();
	private $emailConf = null;
	
	/**
	 * 从数据库读取邮箱数据
	 * @param $table	string		配置所在的数据库表格
	 * @throws \Exception
	 */
	public function __construct($table){
		$db = new \Think\Model($table);
		$this->emailConf = $db->field(array("server", "port", "addr", "user", "password", "username"))->find();
		//print_r($this->emailConf);
		if($this->emailConf === false){
			throw new \Exception("读取邮件配置信息错误");
		}
	}

	/**
	 * 添加邮件
	 * @param string $sendto				接收方邮箱
	 * @param string $subject				邮件主题
	 * @param string $body					邮件内容
	 * @param string $charset				邮件字符集
	 * @param string/Array $attachments		邮件附件，使用方法见附件用法啊
	 * @return Object		当前对象<br/>
	 * @internal <pre>支持接连调用，如：$mailSender->addMail("xxx@xxx.com", "subject 1", "text 1")->addMail("xxx@xxx.com", "subject 2", "text 2")->send();</pre><br />
	 * @internal <pre>邮件附件使用：
	 * 		(1) 直接使用一个文件："Public/test.txt"
	 * 		(2) 给出接收方显示的名字（如果我想把test.txt发送给对方，要让对方显示为“测试.txt”）：array("path"=>"Public/test.txt", "name"=>"测试.txt")，注意：array("path"=>"Public/test.txt")相当于"Public/test.txt"
	 * 		(3) 发送多个附件，将(1)和(2)的组合放到一个数组中即可：array("Public/test.txt", array("path"=>"Public/test.txt", "name"=>"测试.txt"), ...)
	 * </pre>
	 */
	public function addMail($sendto, $subject, $body, $charset="UTF-8", $attachments=null){
		$mail = new \K360\MailSender\PHPMailer();
		$mail->IsSMTP();                  // send via SMTP
		$mail->Host = $this->emailConf["server"];   // SMTP servers
		$mail->SMTPAuth = true;           // turn on SMTP authentication
		$mail->Port = $this->emailConf["port"];
		$mail->Username = $this->emailConf["user"];     // SMTP username  注意：普通邮件认证不需要加 @域名
		$mail->Password = $this->emailConf["password"]; // SMTP password
		$mail->From = $this->emailConf["addr"];      // 发件人邮箱
		$mail->FromName =  $this->emailConf["username"];  // 发件人
	
		$mail->CharSet = $charset;   // 这里指定字符集！
		$mail->Encoding = "base64";
		$mail->AddAddress($sendto,"username");  // 收件人邮箱和姓名
		$domainArr = explode("@", $this->emailConf["addr"]);
		$mail->AddReplyTo($this->emailConf["addr"],$domainArr[1]);
		if($attachments!=null){
			if(is_string($attachments)){
				$mail->AddAttachment($attachments);
			}else if($attachments["path"]){
				if($attachments["name"])
					$mail->AddAttachment($attachments["path"], $attachments["name"]);
				else $mail->AddAttachment($attachments["path"]);
			}else{
				foreach ($attachments as $v){
					if(is_string($v)){
						$mail->AddAttachment($attachments);
					}else if($v["path"]){
						if($v["name"])
							$mail->AddAttachment($v["path"], $v["name"]);
						else $mail->AddAttachment($v["path"]);
					}
				}
			}
		}
		//$mail->WordWrap = 50; // set word wrap 换行字数
		//$mail->AddAttachment("/var/tmp/file.tar.gz"); // attachment 附件
		//$mail->AddAttachment("/tmp/image.jpg", "new.jpg");
		$mail->IsHTML(true);  // send as HTML
		// 邮件主题
		$mail->Subject = $subject;
		// 邮件内容
		$mail->Body = $body;
		$mail->AltBody ="text/html";
		array_push($this->mail, $mail);
		return $this;
	}
	
	/**
	 * 发送邮件，将生成的邮件发送出去
	 * @return multitype:		发送时的错误信息，如果没有错误信息，则返回null
	 */
	public function send(){
		$failedMail = array();
		$i = 0;
		foreach ($this->mail as $v){
			if(!$v->Send()){
				array_push($failedMail, array("index"=>$i, "err"=>$v->ErrorInfo));
			}
			$i ++;
		}
		return (count($failedMail)==0) ? null : $failedMail;
	}
}

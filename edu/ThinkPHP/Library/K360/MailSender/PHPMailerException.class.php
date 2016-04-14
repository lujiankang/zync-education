<?php
namespace K360\MailSender;
/**
 * PHPMailer exception handler
 * @package PHPMailer
 */
class PHPMailerException extends \Exception
{
	/**
	 * Prettify error message output
	 * @return string
	 */
	public function errorMessage()
	{
		$errorMsg = '<strong>' . $this->getMessage() . "</strong><br />\n";
		return $errorMsg;
	}
}
?>
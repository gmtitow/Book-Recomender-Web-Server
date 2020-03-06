<?php

namespace App\Libs;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Phalcon\DI\FactoryDefault as DI;

class PHPMailerApp
{
    /**
     * @var PHPMailer
     */
    private $mail;

    private $config;

    private $currMessage;

    private $to;

    private $subject;

    public function __construct($config)
    {
        $this->config = $config;
        $this->mail = new PHPMailer(true);
    }

    public function createMessageFromView($path,$action,$params){
        $view = DI::getDefault()->getView();
        //$this->currMessage = $view->render($path, $params);
        ob_start();
        $view->partial($path, $params);
        $this->currMessage = ob_get_clean();

        foreach ($params as $param_name=>$param_value){
            $this->currMessage = str_replace('<$'.$param_name.'>',$param_value,$this->currMessage);
        }

        /*$this->currMessage .= "\n\r Активационный код = ".$params['activation'];
        $this->currMessage .= "\n\r Код для смены пароля = ".$params['resetcode'];
        $this->currMessage .= "\n\r Код подтверждения = ".$params['code'];*/
        SupportClass::writeMessageInLogFile('Текст письма:');
        SupportClass::writeMessageInLogFile($this->currMessage);

        return $this;
    }

    public function getMessage(){
        return $this->currMessage;
    }

    public function to($to){
        $this->to = $to;
        return $this;
    }

    public function subject($subject){
        $this->subject = $subject;
        return $this;
    }

    public function send(){
        try {
            //Server settings
            // Enable verbose debug output
            $this->mail->SMTPDebug = 2;
            $this->mail->Debugoutput = function ($str, $level) {
                file_put_contents(
                    BASE_PATH.'/app/logs/phpmailer.log',
                    date('Y-m-d H:i:s') . "\t" . $str."\r\n",
                    FILE_APPEND | LOCK_EX
                );
            };

            if($this->config['driver']!='smtp')
                throw new Exception('Currently only smtp is supported.');

            $this->mail->isSMTP();                                      // Set mailer to use SMTP
            $this->mail->Host = $this->config['host'];  // Specify main and backup SMTP servers
            //$this->mail->SMTPAuth = true;                               // Enable SMTP authentication                   // SMTP password
            $this->mail->SMTPSecure = $this->config['encryption'];
            $this->mail->SMTPAuth = ($this->config['auth'] ? true : false);
            $this->mail->Username = $this->config['username'];                 // SMTP username
            $this->mail->Password = $this->config['password'];                                // Enable TLS encryption, `ssl` also accepted
            $this->mail->Port = $this->config['port'];

            //Recipients
            $this->mail->setFrom($this->config['from']['email'], $this->config['from']['name']);
            $this->mail->addAddress($this->to);

            $this->mail->isHTML(true);
            $this->mail->Subject =  $this->subject;
            $this->mail->Body    = $this->currMessage;
            $this->mail->AltBody = $this->currMessage;
            $this->mail->CharSet = 'UTF-8';

            $this->mail->send();

            return true;
        } catch (Exception $e) {
            return 'Message could not be sent. Mailer Error: '. $this->mail->ErrorInfo;
        }
    }
}
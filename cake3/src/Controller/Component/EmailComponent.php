<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Mailer\Email;

/**
 * Class EmailComponent
 * @package App\Controller\Component
 */
class EmailComponent extends Component
{

  /**
   * 送信処理
   *
   * @param $senderMail
   * @param $senderName
   * @param $destinationMail
   * @param $title
   * @param $body
   */
  public function send($senderMail, $senderName, $destinationMail, $title, $body){
    $email = new Email('default');

    $email->from([$senderMail => $senderName])
          ->to($destinationMail)
          ->subject($title)
          ->send($body);
  }
}
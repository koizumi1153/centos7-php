<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\AqoursComponent;
use App\Controller\Component\LineComponent;
use App\Controller\Component\YouComponent;

class AqoursBlogShell extends Shell
{
  // アクセストークン
  protected $ACCESS_TOKEN = 'Fi3v81mkVQooM1wF9l2P4+aSWaYJFumNi4Vr3DwwMU1wSETxbTPn9HPDc64WCHujPM1XqLsPyN0oZuaIsJ6oqEYWsOl9U3gZXbbgJss8tfqPi0B/afR0kIt1pTmvM+kYCvAZEwqz5Cg7g5ecZ0hCBAdB04t89/1O/w1cDnyilFU=';

  public function initialize() {
    // component
    $this->Aqours  = new AqoursComponent(new ComponentRegistry());
    $this->Line   = new LineComponent(new ComponentRegistry());
    $this->You    = new YouComponent(new ComponentRegistry());
  }

  public function main()
  {

    $this->out('start task');

    $blogs = $this->Aqours->checkBlog();

    if(!empty($blogs)){
      // ユーザー取得
      $userCount = $this->You->getPushUsersCount();
      if ($userCount > 0) {
        $allPage = ceil($userCount / LINE_MULTI_USER);
        for ($page = 1; $page <= $allPage; $page++) {
          $user = $this->You->getPushUsers($page);
          $userIds = array_column($user, 'user_id');

          $messageData = $this->You->setPushBlogMessage($blogs);
          // PUSH
          if (count($messageData) > LINE_MESSAGE_COUNT) {
            $messages = array_chunk($messageData, LINE_MESSAGE_COUNT);
            foreach ($messages as $message) {
              $this->Line->sendPush(LINE_API_MULTI_URL, $this->ACCESS_TOKEN, $userIds, $message);
            }
          } else {
            $this->Line->sendPush(LINE_API_MULTI_URL, $this->ACCESS_TOKEN, $userIds, $messageData);
          }

        }
      }

    }

    $this->out('end task');
  }
}
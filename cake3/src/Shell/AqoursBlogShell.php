<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\AqoursComponent;
use App\Controller\Component\RakutenComponent;

class AqoursShell extends Shell
{

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
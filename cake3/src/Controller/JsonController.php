<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Json Controller
 *
 * Class YohaneController
 * @package App\Controller
 */
class JsonController extends AppController
{
  public function index(){
    $this->set('encode','');
    $this->set('decode','');
  }

  public function encode(){
    $this->render('index');
    $encode = "";
    if(isset($this->request->data['encode'])){
      $encode = json_encode($this->request->data['encode']);
    }

    $this->set('encode',$encode);
    $this->set('decode','');

  }

  public function decode(){
    $this->render('index');
    $decode = "";
    if(isset($this->request->data['decode'])){
      $decode = json_decode($this->request->data['decode']);
    }

    $this->set('encode','');
    $this->set('decode',$decode);

  }
}
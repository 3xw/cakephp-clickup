<?php
namespace Trois\Clickup\Model\Endpoint;

use Muffin\Webservice\Model\Endpoint;

class ClickUpEndpoint extends Endpoint
{
  public static function defaultConnectionName()
  {
    return 'click_up';
  }

  public function create(EntityInterface $resource, $options = [])
  {
    //toDo
  }
}

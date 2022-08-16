<?php
namespace Trois\Clickup\Model\Endpoint;

class UsersEndpoint extends ClickUpEndpoint
{
  public function initialize(array $config): void
  {
    parent::initialize($config);
    $this->setPrimaryKey('id');
    $this->setDisplayField('name');
    //$this->setWebservice('Space', new \App\Webservice\ClickUp\SpaceWebservice);
    //debug($this->getWebservice());
  }
}

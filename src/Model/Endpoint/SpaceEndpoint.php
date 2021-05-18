<?php
namespace Trois\Clickup\Model\Endpoint;

class SpaceEndpoint extends ClickUpEndpoint
{
  public function initialize(array $config)
  {
    parent::initialize($config);
    $this->primaryKey('id');
    $this->displayField('name');
    //$this->setWebservice('Space', new \App\Webservice\ClickUp\SpaceWebservice);
    //debug($this->getWebservice());
  }
}

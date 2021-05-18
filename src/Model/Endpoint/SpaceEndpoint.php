<?php
namespace Trois\Clickup\Model\Endpoint;

use Muffin\Webservice\Model\Endpoint;

class SpaceEndpoint extends Endpoint
{
  public function initialize(array $config)
  {
    parent::initialize($config);
    $this->primaryKey('id');
    $this->displayField('name');
    //$this->setWebservice('Space', new \App\Webservice\ClickUp\SpaceWebservice);
    debug($this->getWebservice());
  }
}

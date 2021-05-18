<?php

namespace Trois\Clickup\Webservice;

class SpaceWebservice extends ClickUpWebservice
{
  public function initialize()
  {
    parent::initialize();

    $this->addNestedResource('/api/v2/space/:spaceId', [
      'spaceId',
    ]);
  }
}

<?php

namespace Trois\Clickup\Webservice;

class SpacesWebservice extends ClickUpWebservice
{
  public function initialize(): void
  {
    parent::initialize();

    $this->addNestedResource('/api/v2/team/:teamId/space', [
      'teamId',
    ]);

    $this->addNestedResource('/api/v2/space/:spaceId', [
      'spaceId',
    ]);
  }
}

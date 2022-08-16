<?php

namespace Trois\Clickup\Webservice;

class UsersWebservice extends ClickUpWebservice
{
  public function initialize(): void
  {
    parent::initialize();

    $this->setEndpoint('user');

    $this->addNestedResource('/api/v2/team/:teamId/user/:userId', [
      'teamId','userId'
    ]);
  }
}

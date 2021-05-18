<?php

namespace Trois\Clickup\Webservice;

class TasksWebservice extends ClickUpWebservice
{
  public function initialize()
  {
    parent::initialize();

    $this->addNestedResource('/api/v2/list/:listId/task', [
      'listId',
    ]);

    $this->addNestedResource('/api/v2/task/:taskId', [
      'taskId',
    ]);
  }
}

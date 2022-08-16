<?php

namespace Trois\Clickup\Webservice;

class TasksWebservice extends ClickUpWebservice
{
  protected $_queryFilters = [
    'team_Id','order_by','reverse','subtasks','space_ids','project_ids','list_ids','statuses',
    'include_closed','assignees','tags','due_date_gt','due_date_lt','date_created_gt','date_created_lt','date_updated_gt','date_updated_lt','custom_fields'
  ];

  public function initialize(): void
  {
    parent::initialize();

    $this->setEndpoint('task');

    $this->addNestedResource('/api/v2/list/:listId/task', [
      'listId',
    ]);

    $this->addNestedResource('/api/v2/team/:teamId/task', [
      'teamId',
    ]);

    $this->addNestedResource('/api/v2/task/:taskId', [
      'taskId',
    ]);
  }
}

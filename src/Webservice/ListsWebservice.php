<?php

namespace Trois\Clickup\Webservice;

class ListsWebservice extends ClickUpWebservice
{
  public function initialize()
  {
    parent::initialize();

    $this->addNestedResource('/api/v2/list/:listId', [
      'listId',
    ]);

    $this->addNestedResource('/api/v2/folder/:folderId/list', [
      'folderId',
    ]);
  }
}

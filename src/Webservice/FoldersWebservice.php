<?php

namespace Trois\Clickup\Webservice;

class FoldersWebservice extends ClickUpWebservice
{
  public function initialize()
  {
    parent::initialize();

    $this->addNestedResource('/api/v2/space/:spaceId/folder', [
      'spaceId',
    ]);

    $this->addNestedResource('/api/v2/folder/:folderId', [
      'folderId',
    ]);
  }
}

<?php

namespace Trois\Clickup\Model\Endpoint\Schema;

use Muffin\Webservice\Model\Schema;

class ListSchema extends Schema
{
  /**
  * {@inheritDoc}
  */
  public function initialize(): void
  {
    parent::initialize();

    $this->addColumn('id', [
      'type' => 'integer',
      'primaryKey' => true
    ]);
    $this->addColumn('name', [
      'type' => 'string',
    ]);
  }
}

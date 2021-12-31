<?php

namespace Trois\Clickup\Webservice;

use Muffin\Webservice\Model\Endpoint;

class TeamsWebservice extends ClickUpWebservice
{
  public function initialize(): void
  {
    parent::initialize();

    $this->setEndpoint('team');

    $this->addNestedResource('/api/v2/team/:teamId', [
      'teamId',
    ]);
  }

  protected function _transformResults(Endpoint $endpoint, array $results)
  {
    if(!empty($results['team'])) $results = $results['team'];
    return parent::_transformResults($endpoint, $results);
  }
}

<?php

namespace Trois\Clickup\Webservice\Driver;

use Cake\Http\Client;
use Muffin\Webservice\Webservice\Driver\AbstractDriver;


class ClickUp extends AbstractDriver
{

  /**
  * {@inheritDoc}
  */
  public function initialize(): void
  {
    $this->setClient(new Client([
      'host' => 'api.clickup.com',
      'scheme' => 'https',
      'headers' => [
        'Authorization' => $this->getConfig('token'),
        'Content-Type' => 'application/json'
      ]
    ]));
  }
}

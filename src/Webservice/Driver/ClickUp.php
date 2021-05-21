<?php

namespace Trois\Clickup\Webservice\Driver;

use Cake\Network\Http\Client;
use Muffin\Webservice\AbstractDriver;


class ClickUp extends AbstractDriver
{

  /**
  * {@inheritDoc}
  */
  public function initialize()
  {
    $this->client(new Client([
      'host' => 'api.clickup.com',
      'scheme' => 'https',
      'headers' => [
        'Authorization' => $this->getConfig('token'),
        'Content-Type' => 'application/json'
      ]
    ]));
  }
}

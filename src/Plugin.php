<?php
declare(strict_types=1);

namespace Trois\Clickup;

use Cake\Core\BasePlugin;
use Cake\Routing\RouteBuilder;
use Cake\Core\PluginApplicationInterface;

class Plugin extends BasePlugin
{

  public function bootstrap(PluginApplicationInterface $app): void
  {
    parent::bootstrap($app);

    $app->addPlugin(\Muffin\Webservice\Plugin::class);
  }
}

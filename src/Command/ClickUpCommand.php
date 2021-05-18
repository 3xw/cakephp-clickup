<?php
namespace Trois\Clickup\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

class ClickUpCommand extends Command
{
  public function execute(Arguments $args, ConsoleIo $io)
  {
    $this->loadModel('Trois/Clickup.Space', 'Endpoint');
  }
}

<?php
namespace Trois\Clickup\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Table;
use Cake\Utility\Inflector;

class SyncWithClickUpBehavior extends Behavior
{
  protected $_defaultConfig = [
    'endpoint' => 'Folders',
    'joinTable' => [
      'modelName' => 'ClickupMatches',
      'foreignKey' => 'foreign_id',
      'clickupKey' => 'clickup_id',
      'conditions' => ['ClickupMatches.model' => 'Accounts'],
    ],
    'staticMatching' => [],
    'mapping' => [
      'name' => function($entity){ return "$entity->name - $entity->id"; },
    ],
    'delete' => false
  ];

  protected function getRelatedId(EntityInterface $entity)
  {
    // set
    $join = (object) $this->getConfig('joinTable');
    if(!$table = $this->{$join->modelName}) $table = $this->loadModel($join->modelName);
    $key = $entity->get($table->getPrimaryKey());
    $join->conditions["$join->modelName.$join->foreignKey"] = $key;

    // get
    $joinEntity = $table->find()->where($join->conditions)->firstOrFail();

    // return ID
    return $joinEntity->get($join->clickupKey);
  }

  public function getEndpoint($endpoint = null)
  {
    if(!$endpoint) $endpoint = $this->getConfig('endpoint');
    if($this->{$endpoint}) return $this->{$endpoint};
    return $this->loadModel("Trois/Clickup.$endpoint", 'Endpoint');
  }

  public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options)
  {
    if(empty($options['EnableClickUpSync'])) return;

    //$this->saveOrUpadteES($entity);
  }

  public function afterDelete(Event $event, EntityInterface $entity, ArrayObject $options)
  {
    // check
    if(empty($options['EnableClickUpSync'])) return;
    if(!$this->getConfig('delete')) return;

    // endpoint
    $endpointName = $this->getConfig('endpoint');
    $endpointId = Inflector::singularize(strtolower($endpointName)).'Id';

    // delete
    $this->getEndpoint()->delete($entity,['nested' => [$endpointId => $this->getRelatedId($entity)]]);
  }
}

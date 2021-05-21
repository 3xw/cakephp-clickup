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
  use \Cake\Datasource\ModelAwareTrait;

  protected $_defaultConfig = [
    'endpoint' => 'Folders',
    'joinTable' => [
      'modelName' => 'ClickupMatches',
      'foreignKey' => 'foreign_id',
      'clickupKey' => 'clickup_id',
      'conditions' => ['ClickupMatches.model' => 'Accounts'],
    ],
    'staticMatching' => [],
    'mapping' => [],
    'delete' => false
  ];

  public function patchResource($resource, EntityInterface $entity, ArrayObject $options)
  {
    $data = [];
    foreach($this->getConfig('staticMatching') as $field => $value) $data[$filed] = $value;
    foreach($this->getConfig('mapping') as $field => $mapping) $data[$field] = $this->getValueOrCallable($mapping, $entity);

    return $this->getEndpoint()->patchEntity($resource, $data, $options->getArrayCopy());
  }

  public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options)
  {
    if(empty($options['EnableClickUpSync'])) return;
    if(empty($options['nested'])) throw new \Exception('Need nested options to create records on ClickUp');

    // create empty
    $resource =  $this->getEndpoint()->newEntity();

    // check if one exists
    if(
      !$entity->isNew() &&
      $clickupId = $this->getClickupId($entity)
    ){
      if(
        $resourceExists = $this->getEndpoint()->find()
        ->where([$this->getNestedVarName($this->getConfig('endpoint')) => $clickupId])
        ->first()
      ) $resource = $resourceExists;
    }

    // warm
    $resource = $this->patchResource($resource, $entity, $options);
    $nested = $this->getNestedOptionsForResource($resource, $options['nested']);

    // save
    if(!$resource = $this->getEndpoint()->save($resource, ['nested' => $nested])) return;

    // set entity
    $entity->set('resource', $resource);

    // save relation
    if(!$clickupId)
    {
      $this->getJoinTable()->save($this->getJoinTable()->newEntity([
         'model' => $this->getTable()->getAlias(),
         'foreign_id' =>$entity->get($this->getTable()->getPrimaryKey()),
         'clickup_id' => $resource->id
       ]));
    }
  }

  public function afterDelete(Event $event, EntityInterface $entity, ArrayObject $options)
  {
    // check
    if(empty($options['EnableClickUpSync'])) return;
    if(!$this->getConfig('delete')) return;

    // related
    if(!$clickupId = $this->getClickupId($entity)) return;
    $nested = $this->getNestedOptions([$this->getConfig('endpoint') => $clickupId]);

    // delete
    $this->getEndpoint()->delete($entity, ['nested' => $nested]);
  }

  /* UTILS */
  public function getClickupId(EntityInterface $entity)
  {
    // get
    if(!$joinEntity = $this->getJoinEntity($entity)) return false;

    // return ID
    $join = (object) $this->getConfig('joinTable');
    return $joinEntity->get($join->clickupKey);
  }

  public function getEndpoint($endpoint = null)
  {
    if(!$endpoint) $endpoint = $this->getConfig('endpoint');
    if(property_exists($this, $endpoint)) return $this->{$endpoint};
    return $this->loadModel("Trois/Clickup.$endpoint", 'Endpoint');
  }

  public static function getValueOrCallable($value, ...$args)
  {
    if(is_callable($value)) return call_user_func_array($value, $args);
    else if(!empty($args) && is_subclass_of($args[0], 'Cake\Datasource\EntityInterface')) return $args[0]->{$value};
    else return $value;
  }

  protected function getJoinTable()
  {
    $mn = $this->getConfig('joinTable.modelName');
    if(!property_exists($this, $mn)) return $this->loadModel($mn);
    else $this->{$mn};
  }

  protected function getJoinEntity(EntityInterface $entity)
  {
    // set
    $join = (object) $this->getConfig('joinTable');
    $key = $entity->get($this->getTable()->getPrimaryKey());
    $join->conditions["$join->modelName.$join->foreignKey"] = $key;

    // get
    return $this->getJoinTable()->find()->where($join->conditions)->first();
  }

  protected function getNestedVarName($endpointName)
  {
    return Inflector::singularize(strtolower($endpointName)).'Id';
  }

  protected function getNestedOptions(array $nested)
  {
    $opt = [];
    foreach($nested as $endpointName => $id) $opt[$this->getNestedVarName($endpointName)] = $id;
    return $opt;
  }

  protected function getNestedOptionsForResource($resource, $nested = [])
  {
    if($resource->isNew()) return $nested;
    return [
      $this->getNestedVarName($this->getConfig('endpoint')) => $resource->id
    ];
  }
}

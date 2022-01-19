<?php
namespace Trois\Clickup\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use Trois\Clickup\Utility\ModelLoader;

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

  protected function loadModel($modelClass = null, $modelType = null)
  {
    return (new ModelLoader)->loadModel($modelClass, $modelType);
  }

  protected function patchResource($resource, EntityInterface $entity, ArrayObject $options)
  {
    $data = [];
    foreach($this->getConfig('staticMatching') as $field => $value) $data[$filed] = $value;
    foreach($this->getConfig('mapping') as $field => $mapping) $data[$field] = $this->getValueOrCallable($mapping, $entity);

    return $this->getEndpoint()->patchEntity($resource, $data, $options->getArrayCopy());
  }

  public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options)
  {

    if(empty($options['EnableClickUpSync'])) return;
    if(empty($options['EnableClickUpSync']['nested'])) throw new \Exception('Need nested options to create records on ClickUp');

    // create empty
    $clickupId = false;
    $resource =  $this->getEndpoint()->newEntity();

    // check if one exists
    if(
      !$entity->isNew() &&
      $clickupId = $this->getClickupId($entity, $options['EnableClickUpSync']['nested'])
    ){
      if(
        $resourceExists = $this->getEndpoint()->find()
        ->where([$this->getNestedVarName($this->getConfig('endpoint')) => $clickupId])
        ->first()
      ) $resource = $resourceExists;
    }

    // warm
    $resource = $this->patchResource($resource, $entity, $options);
    $nested = $this->getNestedOptionsForResource($resource, $options['EnableClickUpSync']['nested']);

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
    if(!$this->getConfig('delete')) return;
    if(empty($options['EnableClickUpSync'])) return;

    // related
    if(!$clickupId = $this->getClickupId($entity)) return;
    $nested = $this->getNestedOptions([$this->getConfig('endpoint') => $clickupId]);

    // delete
    $this->getEndpoint()->delete($entity, ['nested' => $nested]);
  }

  /* UTILS */
  public function getClickupId(EntityInterface $entity, $nested = null)
  {
    // get
    if(!$joinEntity = $this->getJoinEntity($entity))
    {
      if(!$nested) return false;
      if(!$joinEntity = $this->lookupOnClickUpAndCreate($entity, $nested)) return false;
    }

    // return ID
    $join = (object) $this->getConfig('joinTable');
    return $joinEntity->get($join->clickupKey);
  }

  public function lookupOnClickUpAndCreate(EntityInterface $entity, $nested)
  {
    $items = $this->getEndpoint()->find()->where($nested)->toArray();
    $entityId4Digi = sprintf('%04d', $entity->number);
    if($entityId4Digi == '0000') throw new \Exception("NUmber $entityId4Digi is not valid");


    foreach ($items as $itm)
    {
      $parts = explode(" ", trim($itm->name));
      if($entityId4Digi == sprintf('%04d',end($parts)))
      {
        if(
          !$joinEntity = $this->getJoinTable()->save($this->getJoinTable()->newEntity([
            'model' => $this->getTable()->getAlias(),
            'foreign_id' => $entity->id,
            'clickup_id' => $itm->id
          ]))
        ) throw new \Exception("Error Processing Request", 1);

        return $joinEntity;
      }
    }

    return false;
  }

  protected function getEndpoint($endpoint = null)
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
    if(property_exists($this, $mn)) return $this->{$mn};
    else return $this->loadModel($mn);
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

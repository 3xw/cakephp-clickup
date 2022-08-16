<?php

namespace Trois\Clickup\Webservice;

use Cake\Network\Http\Response;
use Cake\Utility\Hash;
use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Datasource\Query;
use Muffin\Webservice\Datasource\ResultSet;
use Muffin\Webservice\Webservice\Webservice;

/**
* Class GitHubWebservice
*
* @package CvoTechnologies\GitHub\Webservice
*/
class ClickUpWebservice extends Webservice
{

  protected $_queryFilters = ['id'];

  /**
  * Returns the base URL for this endpoint
  *
  * @return string Base URL
  */
  public function getBaseUrl()
  {
    return '/api/v2/' . $this->getEndpoint();
  }

  /**
  * {@inheritDoc}
  */
  protected function _executeReadQuery(Query $query, array $options = [])
  {
    $url = $this->getBaseUrl();

    $queryParameters = [];
    // Page number has been set, add to query parameters
    if ($query->clause('page')) {
      $queryParameters['page'] = $query->clause('page');
    }
    // Result limit has been set, add to query parameters
    if ($query->clause('limit')) {
      $queryParameters['per_page'] = $query->clause('limit');
    }

    $search = false;
    $searchParameters = [];
    if ($query->clause('where')) {
      foreach ($query->clause('where') as $field => $value) {
        if(in_array($field, $this->_queryFilters)) $queryParameters[$field] = $value;// is_array($value)? implode(",", $value): $value;
      }
    }

    // Check if this query could be requested using a nested resource.
    if ($nestedResource = $this->nestedResource($query->clause('where'))) $url = $nestedResource;

    /* @var Response $response */
    $response = $this->getDriver()->getClient()->get($url, $queryParameters);
    $results = $response->getJson();
    if (!$response->isOk())
    {
      debug($url);
      debug($response->getJson());
      throw new \Exception($response->getJson()['err']);
    }

    // Turn results into resources
    $resources = $this->_transformResults($query->getEndpoint(), $results);

    return new ResultSet($resources, count($resources));
  }

  protected function _transformResults(Endpoint $endpoint, array $results): array
  {
    $resources = [];
    if(!empty($results[$endpoint->getName()])) $results = $results[$endpoint->getName()];
    foreach ($results as $key =>$result)
    {
      if(!is_numeric($key)) return [$this->_transformResource($endpoint, $results)];
      $resources[] = $this->_transformResource($endpoint, $result);
    }

    return $resources;
  }

  protected function _executeCreateQuery(Query $query, array $options = [])
  {
    return $this->_write($query, $options);
  }

  protected function _executeUpdateQuery(Query $query, array $options = [])
  {
    return $this->_write($query, $options);
  }

  protected function _write(Query $query, array $options = [])
  {
    $url = $this->getBaseUrl();
    if (
    $query->getOptions() &&
    !empty($query->getOptions()['nested']) &&
    $nestedResource = $this->nestedResource($query->getOptions()['nested'])
    ) $url = $nestedResource;

    switch ($query->clause('action'))
    {
      case Query::ACTION_CREATE:
      $response = $this->getDriver()->getClient()->post($url, json_encode($query->set()));
      break;

      case Query::ACTION_UPDATE:
      $response = $this->getDriver()->getClient()->put($url, json_encode($query->set()));
      break;

      case Query::ACTION_DELETE:
      $response = $this->getDriver()->getClient()->delete($url);
      break;
    }

    if (!$response->isOk())
    {
      debug($response);
      debug($response->getStringBody());
      throw new \Exception($response->getJson()['err']);
    }

    return $this->_transformResource($query->getEndpoint(), $response->getJson());
  }

}

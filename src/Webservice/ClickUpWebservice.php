<?php

namespace Trois\Clickup\Webservice;

use Cake\Network\Http\Response;
use Cake\Utility\Hash;
use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Query;
use Muffin\Webservice\ResultSet;
use Muffin\Webservice\Webservice\Webservice;

/**
* Class GitHubWebservice
*
* @package CvoTechnologies\GitHub\Webservice
*/
class ClickUpWebservice extends Webservice
{

  /**
  * Returns the base URL for this endpoint
  *
  * @return string Base URL
  */
  public function getBaseUrl()
  {
    return '/api/v2/' . $this->endpoint();
  }

  /**
  * {@inheritDoc}
  */
  protected function _executeReadQuery(Query $query, array $options = [])
  {
    $url = $this->getBaseUrl();

    $queryParameters = [];
    // Page number has been set, add to query parameters
    if ($query->page()) {
      $queryParameters['page'] = $query->page();
    }
    // Result limit has been set, add to query parameters
    if ($query->limit()) {
      $queryParameters['per_page'] = $query->limit();
    }

    $search = false;
    $searchParameters = [];
    if ($query->clause('where')) {
      foreach ($query->clause('where') as $field => $value) {
        switch ($field) {
          case 'id':
          default:
          // Add the condition as search parameter
          $searchParameters[$field] = $value;

          // Mark this query as a search
          $search = true;
        }
      }
    }

    // Check if this query could be requested using a nested resource.
    if ($nestedResource = $this->nestedResource($query->clause('where'))) {
      $url = $nestedResource;

      // If this is the case turn search of
      $search = false;
    }

    if ($search) {
      $url = '/search' . $url;

      $q = [];
      foreach ($searchParameters as $parameter => $value) {
        $q[] = $parameter . ':' . $value;
      }

      $queryParameters['q'] = implode(' ', $q);
    }

    /* @var Response $response */
    $response = $this->driver()->client()->get($url, $queryParameters);
    $results = $response->getJson();
    if (!$response->isOk())
    {
      debug($response->getJson());
      throw new \Exception($response->getJson()['err']);
    }

    // Turn results into resources
    $resources = $this->_transformResults($query->endpoint(), $results);

    return new ResultSet($resources, count($resources));
  }

  protected function _transformResults(Endpoint $endpoint, array $results)
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
    $this->_write($query, $options);
  }

  protected function _executeUpdateQuery(Query $query, array $options = [])
  {
    $this->_write($query, $options);
  }

  protected function _write(Query $query, array $options = [])
  {
    $url = $this->getBaseUrl();
    if (
    $query->getOptions() &&
    !empty($query->getOptions()['nested']) &&
    $nestedResource = $this->nestedResource($query->getOptions()['nested'])
    ) $url = $nestedResource;

    switch ($query->action())
    {
      case Query::ACTION_CREATE:
      $response = $this->driver()->client()->post($url, $query->set());
      case Query::ACTION_UPDATE:
      $response = $this->driver()->client()->put($url, $query->set());
    }

    if (!$response->isOk())
    {
      debug($response);
      throw new \Exception($response->getJson()['err']);
    }

    return $this->_transformResource($query->endpoint(), $response->getJson());
  }

}

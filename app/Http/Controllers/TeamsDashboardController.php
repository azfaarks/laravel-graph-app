<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use App\TokenStore\TokenCache;

class TeamsDashboardController extends Controller
{
  public function dashboard()
  {
    $viewData = $this->loadViewData();

    // Get the access token from the cache
    $tokenCache = new TokenCache();
    $accessToken = $tokenCache->getAccessToken();

    // Create a Graph client
    $graph = new Graph();
    $graph->setAccessToken($accessToken);

   /* $queryParams = array(
      '$select' => 'subject,organizer,start,end',
      '$orderby' => 'createdDateTime DESC'
    );
*/
    // Append query parameters to the '/me/events' url
   // $getEventsUrl = '/me/events?'.http_build_query($queryParams);

   $queryParams = array(
    '$select' => 'id,resourceProvisioningOptions,displayName'
   );


   $getGroups = '/me/joinedTeams';
    $group = $graph->createRequest('GET', $getGroups)
      ->setReturnType(Model\Group::class)
      ->execute();

      $viewData['groups'] = $group;
      return view('dashboard', $viewData);  
    
    }
}
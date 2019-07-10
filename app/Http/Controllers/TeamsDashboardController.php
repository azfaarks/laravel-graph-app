<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use App\TokenStore\TokenCache;
class TeamsDashboardController extends Controller
{
  public $accessToken;
  public $url  = "https://graph.microsoft.com/beta/teams";


  public function dashboard()
  {
    $viewData = $this->loadViewData();
    // Get the access token from the cache
    $tokenCache = new TokenCache();
    $this->accessToken = $tokenCache->getAccessToken();
    // Create a Graph client
    $graph = new Graph();
    $graph->setAccessToken($this->accessToken);
   
    // Retrieving User Joined Groups
   $data  =  $this->getUserJoinedTeams($viewData,$graph);
      return view('dashboard', $data);  
    
    }

    

   function getUserJoinedTeams($viewData,$graph)
   {
    $queryParams = array(
      '$select' => 'id,resourceProvisioningOptions,displayName'
     );
     $getGroups = '/me/joinedTeams';
      $group = $graph->createRequest('GET', $getGroups)
        ->setReturnType(Model\Group::class)
        ->execute();
        $viewData['groups'] = $group;

        return $viewData;
   }

}
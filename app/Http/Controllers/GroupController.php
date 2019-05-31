<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use App\TokenStore\TokenCache;

class GroupController extends Controller
{
  public $accessToken;
   public function group()
  {
    $viewData = $this->loadViewData();

    // Get the access token from the cache
    $tokenCache = new TokenCache();
    $this->accessToken = $tokenCache->getAccessToken();
    //$this->createNewTeam($accessToken);

    // Create a Graph client
    $graph = new Graph();
    $graph->setAccessToken($this->accessToken);

    $queryParams = array(
      '$select' => 'subject,organizer,start,end',
      '$orderby' => 'createdDateTime DESC'
    );

    // Append query parameters to the '/me/events' url
    $getEventsUrl = '/me/events?' . http_build_query($queryParams);

    $events = $graph->createRequest('GET', $getEventsUrl)
      ->setReturnType(Model\Event::class)
      ->execute();
    $viewData['events'] = $events;
    return view('group',$viewData);
  }

  function createNewTeam($groupName,$accesstoken)
  {
    $url  = "https://graph.microsoft.com/beta/teams";
    // The data to send to the API
    $postData = array(

      "template@odata.bind" => "https://graph.microsoft.com/beta/teamsTemplates('standard')",
      "displayName" =>$groupName,
      "description" => "My Sample Team’s Description"
    );

    // Create the context for the request
    $context = stream_context_create(array(
      'http' => array(
        // http://www.php.net/manual/en/context.http.php
        'method' => 'POST',
        'header' => "Authorization: {$accesstoken}\r\n" .
          "Content-Type: application/json\r\n",
        'content' => json_encode($postData)
      )
    ));

    // Send the request
    $response = file_get_contents($url, FALSE, $context);

    $result=array();
    // Check for errors
    if ($response === FALSE) {
      $result = array("status"=>"failed");
      die('Error');
    }
    else
    {
      $result = array("status"=>"Group created successfully");

    }

    // Decode the response
    $responseData = json_decode($response, TRUE);

    

    return json_encode( $result );
    // Print the date from the respo
  }

  public function createNewGroup(Request $request)
  {
    $viewData = $this->loadViewData();

     $name = Input::get('group_name');

 
   // Get the access token from the cache
   $tokenCache = new TokenCache();
   $accessToken = $tokenCache->getAccessToken();
     // Create a Graph client
     $graph = new Graph();
     $graph->setAccessToken($this->accessToken);
 
    $response =  $this->createNewTeam($name,$accessToken);
     return view('group',$viewData)->with("data",  $response);
  }
}

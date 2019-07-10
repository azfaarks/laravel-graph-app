<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use App\TokenStore\TokenCache;
use function GuzzleHttp\json_encode;

class GroupController extends Controller
{
  public $accessToken;
  public $url  = "https://graph.microsoft.com/beta/teams";



  public function group()
  {
    $viewData = $this->loadViewData();


    //$this->createNewTeam($accessToken);

    // Create a Graph client
    $graph = new Graph();
    $graph->setAccessToken($this->getAccessToken());

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
    return view('group', $viewData);
  }


  function getAccessToken()
  {
    $tokenCache = new TokenCache();
    $accessToken = $tokenCache->getAccessToken();
    return $accessToken;
  }




  function createNewTeam($groupName, $groupDescription, $groupType,$owner, $accesstoken)
  {
    $url = $this->url;
    $bindingDataType = "";
    // The data to send to the API
    if ($groupType == "1")
      $bindingDataType = "https://graph.microsoft.com/beta/teamsTemplates('standard')";
    if ($groupType == "2")
      $bindingDataType = "https://graph.microsoft.com/beta/teamsTemplates('educationClass')";
    if ($groupType == "3")
      $bindingDataType = "https://graph.microsoft.com/beta/teamsTemplates('educationStaff')";
    if ($groupType == "4")
      $bindingDataType = "https://graph.microsoft.com/beta/teamsTemplates('educationProfessionalLearningCommunity')";

      $memberIDs = array();

      if($owner != null)
      {
        
        $response = $this->getMemberId($owner,$accesstoken);
        $encoded_response = json_decode( $response);

        array_push($memberIDs,"https://graph.microsoft.com/beta/users('$encoded_response->id')");
      
      //  "https://graph.microsoft.com/beta/users('$encoded_response->id')";

        $postData = array(

          "template@odata.bind" => $bindingDataType,
          "displayName" => $groupName,
          "description" => $groupDescription,
          "owners@odata.bind" => $memberIDs
        );
      }
      else 
      {
        $postData = array(

          "template@odata.bind" => $bindingDataType,
          "displayName" => $groupName,
          "description" => $groupDescription
        );
      }

 
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

    $result = array();
    // Check for errors
    if ($response === FALSE) {
      $result = array("status" => "failed to create new group");
      die('Error');
    } else {
      $result = array("status" => "Group created successfully");
    }
   
 
 // return $postData ;

    return json_encode($result);
     
  }

  public function createNewGroup(Request $request)
  {
    $viewData = $this->loadViewData();

    $name = Input::get('group_name');
    $groupType = $request->input('groupTypeSelect');
    $groupDescription = Input::get('new_group_description');

    // Get the access token from the cache

    // Create a Graph client
    // $graph = new Graph();
    //  $graph->setAccessToken($this->getAccessToken());

    if ($groupType != "") {
      $response =  $this->createNewTeam($name, $groupDescription, $groupType,null, $this->getAccessToken());
    } else {
      $response = "None Found";
    }

    //return view('group', $viewData)->with("data", json_decode($response, true));
    return view('group', $viewData)->with("data",  $response);
  }







  function searchGroupName($keyword, $accesstoken)
  {
    $url = "https://graph.microsoft.com/v1.0/me/joinedTeams?" . '$filter' . "=contains(displayName,'$keyword')";


    // Create the context for the request
    $context = stream_context_create(array(
      'http' => array(
        // http://www.php.net/manual/en/context.http.php
        'method' => 'GET',
        'header' => "Authorization: {$accesstoken}\r\n" .
          "Content-Type: application/json\r\n"
      )
    ));

    // Send the request
    $response = file_get_contents($url, FALSE, $context);

    return  $response;
  }


  function displaySearchGroup(Request $request)
  {
    $viewData = $this->loadViewData();

    $name = Input::get('search_group_name');


    // Get the access token from the cache
    $tokenCache = new TokenCache();
    $accessToken = $tokenCache->getAccessToken();
    // Create a Graph client
    $graph = new Graph();
    $graph->setAccessToken($this->getAccessToken());

    $response =  $this->searchGroupName($name, $this->getAccessToken());
    return view('dashboard', $viewData)->with("data",  $response);
  }


  function getMemberId($email, $accesstoken)
  {

    $url = "https://graph.microsoft.com/v1.0/users/" . $email;


    // Create the context for the request
    $context = stream_context_create(array(
      'http' => array(
        // http://www.php.net/manual/en/context.http.php
        'method' => 'GET',
        'header' => "Authorization: {$accesstoken}\r\n" .
          "Content-Type: application/json"
      )
    ));

    // Send the request
    $response = file_get_contents($url, FALSE, $context);


    return  $response;
  }



  function postUserToGroup($userId, $groupID, $accesstoken)
  {
    $url = "https://graph.microsoft.com/v1.0/groups/$groupID/members/" . '$ref';


    $json_String =  "{'@odata.id': 'https://graph.microsoft.com/v1.0/directoryObjects/$userId'}";


    // The data to send to the API


    // Create the context for the request
    $context = stream_context_create(array(
      'http' => array(
        'method' => 'POST',
        'header' => "Authorization: {$accesstoken}\r\n" .
          "Content-Type: application/json\r\n",
        'content' => $json_String
      )
    ));

    // Send the request
    $response = file_get_contents($url, FALSE, $context);

    $result = array();
    // Check for errors
    if ($response === FALSE) {
      $result = array("status" => "failed");
      die('Error');
    } else {
      $result = array("status" => "success");
    }



    return $result;
    // Print the date from the respo
  }

  function addMemberToGroup(Request $request)
  {
    $input = $request->all();
    $userIdsArray = [];
    foreach ($input['emails'] as $emailObj) {
      $response = $this->getMemberId($emailObj['email'], $this->getAccessToken());
      $encoded_response = json_decode($response);
      array_push($userIdsArray, $encoded_response->id);
    }
    foreach ($userIdsArray as $id) {
      $this->postUserToGroup($id, $input['id'], $this->getAccessToken());
    }

    return response()->json($userIdsArray);
  }



  public function createNewBulkGroup(Request $request)
  {
    // $viewData = $this->loadViewData();
    $input = $request->all();
    $response = [];
    $requestResponse =array();
    foreach ($input['groups']  as $obj) {
      //  array_push($response,$this->createNewTeam($obj['name'], $obj['description'], $obj['type'], $this->getAccessToken()));


      try {
        if(array_key_exists('name', $obj)){
          if(array_key_exists('description', $obj)){
            if(array_key_exists('type', $obj)){
              if(array_key_exists('owners', $obj))
              {


                $this->createNewTeam($obj['name'], $obj['description'], $obj['type'],$obj['owners'], $this->getAccessToken());
                $requestResponse  =  array("Status"=> "true","Group Name"=>$obj['name'],"msg" => "Group created successfully with owner","Owner"=>$obj['owners']);

              }
              else 
              {
               $this->createNewTeam($obj['name'], $obj['description'], $obj['type'],null, $this->getAccessToken());
             //   array_push($response,array( "status"=> "Could not find owners email addresses for ". $obj['name']));
             $requestResponse  =  array("Status"=> "true","Group Name"=>$obj['name'],"msg" => "Group created successfully");

              }

            }
            else
            {
               $requestResponse  =  array("Status"=> "false","Group Name"=>$obj['name'],"msg" => "Could not find Group type for ". $obj['name']);
            }
          }
          else
          {
             $requestResponse  =  array("Status"=> "false","Group Name"=>$obj['name'],"msg" => "Could not find Group description for ". $obj['name']);

          }
        }
        else
        {
           $requestResponse  =  array("Status"=> "false","Group Name"=>"not given","msg" => "Could not find Group name");
        }
        array_push($response,$requestResponse);


        //  array_push($response, "Tested for : " . $obj['name']);
      } catch (Exception $e) {
        array_push($response,array( "status"=> "Failed for" . $obj['name']));
      }
    }

    return response()->json($response);
  }


  function my_error_handler($errno,$errstr)
  {
  /* handle the issue */
  return true; // if you want to bypass php's default handler
  }
}

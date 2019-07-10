<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

 

Route::get('/', 'HomeController@welcome');
Route::get('/signin', 'AuthController@signin');
Route::get('/callback', 'AuthController@callback');
Route::get('/signout', 'AuthController@signout');
Route::get('/calendar', 'CalendarController@calendar');
Route::get('/dashboard', 'TeamsDashboardController@dashboard');
Route::get('/group', 'GroupController@group');
Route::post('/group', 'GroupController@createNewGroup');
 
Route::post('/dashboard', 'GroupController@displaySearchGroup');
Route::post('/createBulkGroup', 'GroupController@createNewBulkGroup');

Route::get('/users', 'UserController@user');
Route::get('/joinedTeams', 'UserController@getUserJoinedTeams');
Route::post('/updateGroup', 'UserController@addBulkMembersToGroup');
Route::post('/users', 'UserController@addMembersToGroup');




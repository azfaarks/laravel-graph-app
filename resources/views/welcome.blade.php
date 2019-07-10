@extends('layout')

@section('content')
<div class="jumbotron">
  <h1>ATMC - MS Team Manager</h1>
  <p class="lead">This app helps to manage Microsoft Teams</p>
  @if(isset($userName))
    <h4>Welcome {{ $userName }}!</h4>
    <p>Use the navigation bar at the top of the page to get started.</p>
  @else
    <a href="/signin" class="btn btn-primary btn-large">Click here to sign in</a>
  @endif
</div>



@endsection
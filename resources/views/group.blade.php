@extends('layout')

@section('content')
<h1>Groups</h1>
<form action="{{action('GroupController@createNewGroup')}}" method="post"> 
   <div class="form-group" >
    <label for="new_group_name">Enter Group Name</label>
    <input type="text" class="form-control" name="group_name" id="new_group_name" aria-describedby="new_group_help" placeholder="Enter group name here" required>
    <small id="new_group_help" class="form-text text-muted">Please enter your new group name here that you want to create in Teams.</small>
  </div>
   
   <button type="submit" class="btn btn-primary">Submit</button>
   {{csrf_field()}}

</form>
@isset($data)
<h1>{{$data}}</h1>
@endif

  
 
@endsection

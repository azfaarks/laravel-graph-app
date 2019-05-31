@extends('layout')

@section('content')
<h1>My Joined Groups </h1>
<table class="table">
  <thead>
    <tr>
      <th scope="col">Group Name</th>
      
    </tr>
  </thead>
  <tbody>
    @isset($groups)
      @foreach($groups as $group)
        <tr>
          <td>{{ $group->getDisplayName()}}</td>
      
        </tr>
      @endforeach
    @endif
  </tbody>
</table>
@endsection
@extends('layout')

@section('content')
<h1>Groups </h1>
<table class="table">
  <thead>
    <tr>
      <th scope="col">Group Name</th>
      <th scope="col">Subject</th>
      <th scope="col">Start</th>
      <th scope="col">End</th>
    </tr>
  </thead>
  <tbody>
    @isset($groups)
      @foreach($groups as $group)
        <tr>
          <td>{{ $group->getDisplayName()}}</td>
          <td></td>
          <td></td>
          <td> </td>
        </tr>
      @endforeach
    @endif
  </tbody>
</table>
@endsection
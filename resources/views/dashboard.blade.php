@extends('layout')

@section('content')


<h1>Search for Group</h1>
<form action="{{action('GroupController@displaySearchGroup')}}" method="post">
  <div class="form-group">
    <br>
    <input type="text" class="form-control" name="search_group_name" aria-describedby="search_group_help" placeholder="Enter group name here" required>

    <small id="search_group_help" class="form-text text-muted">Please enter group name that you want to search <br> <b>Note:</b> Search is case sensitive.</small>
  </div>

  <button type="submit" class="btn btn-primary">Submit</button>
  {{csrf_field()}}

</form>


<!-- Modal -->
<div class="modal fade" id="modalAddGroupMembers" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Member</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <div id="add-member-modal-content">
          <p id="p_group_id"></p>

          <div class="custom-file">

            <input type="file" id="excelfile" />
            <input type="button" id="viewfile" value="Add Members" onclick="ExportToTable()" />
            <br />
            <br />
            <table id="exceltable">
            </table>


          </div>

        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>

<br>
<br>
@isset($data)
<table class="table">
  <thead>
    <tr>
      <th scope="col">Group Name</th>

    </tr>
  </thead>

  <tbody>
    @php
    $decode_data = json_decode($data,true);
    $groups = $decode_data['value'];
    @endphp


    @isset($groups)
    @foreach($groups as $groupObject)
    <tr>
      <td id='<?php echo  $groupObject['id']; ?>'>{{$groupObject['displayName']}}</td>
    </tr>
    @endforeach
    @endif



  </tbody>
</table>
@endif

<?php

/*
-----------------------------------------------------------------------------
Display Joined Groups
-----------------------------------------------------------------------------

*/
?>
<script>
  var group_id;

  $('td').click(function(e) {
    group_id = e.target.id
    $('#modalAddGroupMembers').modal();
    $('#p_group_id').text(group_id);
  });

  function ExportToTable() {
    var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.xlsx|.xls)$/;
    /*Checks whether the file is a valid excel file*/
    if (regex.test($("#excelfile").val().toLowerCase())) {
      var xlsxflag = false; /*Flag for checking whether excel is .xls format or .xlsx format*/
      if ($("#excelfile").val().toLowerCase().indexOf(".xlsx") > 0) {
        xlsxflag = true;
      }
      /*Checks whether the browser supports HTML5*/
      if (typeof(FileReader) != "undefined") {
        var reader = new FileReader();
        reader.onload = function(e) {
          var data = e.target.result;
          /*Converts the excel data in to object*/
          if (xlsxflag) {
            var workbook = XLSX.read(data, {
              type: 'binary'
            });
          } else {
            var workbook = XLS.read(data, {
              type: 'binary'
            });
          }
          /*Gets all the sheetnames of excel in to a variable*/
          var sheet_name_list = workbook.SheetNames;

          var cnt = 0; /*This is used for restricting the script to consider only first sheet of excel*/
          sheet_name_list.forEach(function(y) {
            /*Iterate through all sheets*/
            /*Convert the cell value to Json*/
            if (xlsxflag) {
              var exceljson = XLSX.utils.sheet_to_json(workbook.Sheets[y]);
            } else {
              var exceljson = XLS.utils.sheet_to_row_object_array(workbook.Sheets[y]);
            }
            if (exceljson.length > 0 && cnt == 0) {

              console.log(exceljson);
              //   addMembersToGroup(group_id,exceljson);
            }
          });
          $('#exceltable').show();
        }
        if (xlsxflag) {
          /*If excel file is .xlsx extension than creates a Array Buffer from excel*/
          reader.readAsArrayBuffer($("#excelfile")[0].files[0]);
        } else {
          reader.readAsBinaryString($("#excelfile")[0].files[0]);
        }
      } else {
        alert("Sorry! Your browser does not support HTML5!");
      }
    } else {
      alert("Please upload a valid Excel file!");
    }
  }


  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  function addMembersToGroup(group_id, emailIDs) {
    var data = {
      id: group_id,
      emails: emailIDs
    };
    $.ajax({

      type: 'POST',

      url: "./updateGroup",

      data: data,

      success: function(data) {

        console.log(data);

      }

    });
  }
</script>
@endsection
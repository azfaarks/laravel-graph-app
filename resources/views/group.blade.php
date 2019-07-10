@extends('layout')

@section('content')
<h1>Groups</h1>
<br>

<ul class="nav nav-tabs" id="myTab" role="tablist">
   <li class="nav-item">
      <a class="nav-link active" id="new-group-tab" data-toggle="tab" href="#new-group" role="tab" aria-controls="new-group" aria-selected="true">New Group</a>
   </li>
   <li class="nav-item">
      <a class="nav-link" id="bulk-import-tab" data-toggle="tab" href="#bulk-import" role="tab" aria-controls="bulk-import" aria-selected="false">Bulk Import</a>
   </li>

</ul>
<div class="tab-content" id="myTabContent">

   <div class="tab-pane fade show active" id="new-group" role="tabpanel" aria-labelledby="new-group-tab">

      <form action="{{action('GroupController@createNewGroup')}}" method="post">
         <div class="form-group">
            <label for="new_group_name">Enter Group Name</label>
            <input type="text" class="form-control" name="group_name" id="new_group_name" aria-describedby="new_group_help" placeholder="Enter group name here" required>
         </div>
         <div class="form-group">
            <label for="new_group_name">Enter Group Description</label>
            <input type="text" class="form-control" name="group_description"" id=" new_group_description" aria-describedby="new_group_description_help" placeholder="Enter group description here" required>
         </div>
         <div class="form-group">
            <label for="groupTypeSelect">Type</label>
            <select name="groupTypeSelect" id="groupTypeSelect" class="form-control">
               <option value="1">Standard</option>
               <option value="2">Class Team</option>
               <option value="3">Staff Team</option>
               <option value="4">PLC team</option>

            </select>
         </div>
         <button type="submit" class="btn btn-primary">Submit</button>
         {{csrf_field()}}

      </form>

      @isset($data)

      <br>
      <div class="alert alert-primary" role="alert">
         {{$data}}
      </div>


      @endif
   </div>
   <div class="tab-pane fade" id="bulk-import" role="tabpanel" aria-labelledby="bulk-import-tab">



      <div class="custom-file">




         <div class="container">
            <div class="row" style="margin-top:20px">
               <br>

               <div class="col">
                  <div class="input-group mb-3">

                     <div class="custom-file">
                        <input type="file" class="custom-file-input" id="bulkImportFileGroup">

                        <label class="custom-file-label" for="bulkImportFileGroup">Choose file</label>
                     </div>

                  </div>
                  <button type="button" class="btn btn-primary" onclick="ExportToTable()">Create Groups</button>
               </div>
               <div class="col">
               </div>
            </div>
         </div>


         <!--
            <input type="file" id="excelfile" />
            <input type="button" id="viewfile" value="Add Members" onclick="ExportToTable()" />
            <br />
            <br />
            
-->

      </div>

   </div>


</div>

 

<div class="modal fade" id="div_ajax_loader" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Creating Groups</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <div id=""  ><img style="width: 100%" src="{{asset('img/ajax-loader.gif')}}" /></div>
      <p>Creating groups, Please do not refresh or close page. </p>
      </div>
      <div class="modal-footer">
        </div>
    </div>
  </div>
</div>


<script>
   $('#bulkImportFileGroup').on('change', function() {
      //get the file name
      var fileName = $(this).val();
      var cleanFileName = fileName.replace('C:\\fakepath\\', " ");
      //replace the "Choose a file" label
      $(this).next('.custom-file-label').html(cleanFileName);
   })



   //------------------------------------------------------------------------------------------------------------------



   function ExportToTable() {
      var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.xlsx|.xls)$/;
      /*Checks whether the file is a valid excel file*/
      if (regex.test($("#bulkImportFileGroup").val().toLowerCase())) {
         var xlsxflag = false; /*Flag for checking whether excel is .xls format or .xlsx format*/
         if ($("#bulkImportFileGroup").val().toLowerCase().indexOf(".xlsx") > 0) {
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

                     //   console.log(exceljson);


                     addBulkGroups(exceljson);
                  }
               });
            }
            if (xlsxflag) {
               /*If excel file is .xlsx extension than creates a Array Buffer from excel*/
               reader.readAsArrayBuffer($("#bulkImportFileGroup")[0].files[0]);
            } else {
               reader.readAsBinaryString($("#bulkImportFileGroup")[0].files[0]);
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

   function addBulkGroups(groupDetails) {
      var data = {
         groups: groupDetails
      };
      $.ajax({

         type: 'POST',

         url: "./createBulkGroup",

         data: data,
         beforeSend: function() {
         //   $('#div_ajax_loader').show();
            $('#div_ajax_loader').modal('show');

         },
         complete: function() {
           // $('#div_ajax_loader').hide();
            $('#div_ajax_loader').modal('hide');

         },
         success: function(e) {

            console.log(e);
            var currentdate = new Date();
            var datetime = currentdate.getDate() + "-" +
               (currentdate.getMonth() + 1) + "-" +
               currentdate.getFullYear() + " @ " +
               currentdate.getHours() + ":" +
               currentdate.getMinutes() + ":" +
               currentdate.getSeconds();
            JSONToCSVConvertor(e, "Group creation on " + datetime, true);
         },  error: function(data) {
        alert("Error while creating groups, Only Admin privileges can add owner to group");
      }

      });
   }




   function JSONToCSVConvertor(JSONData, ReportTitle, ShowLabel) {
      //If JSONData is not an object then JSON.parse will parse the JSON string in an Object
      var arrData = typeof JSONData != 'object' ? JSON.parse(JSONData) : JSONData;

      var CSV = '';
      //Set Report title in first row or line

      CSV += ReportTitle + '\r\n\n';

      //This condition will generate the Label/Header
      if (ShowLabel) {
         var row = "";

         //This loop will extract the label from 1st index of on array
         for (var index in arrData[0]) {

            //Now convert each value to string and comma-seprated
            row += index + ',';
         }

         row = row.slice(0, -1);

         //append Label row with line break
         CSV += row + '\r\n';
      }

      //1st loop is to extract each row
      for (var i = 0; i < arrData.length; i++) {
         var row = "";

         //2nd loop will extract each column and convert it in string comma-seprated
         for (var index in arrData[i]) {
            row += '"' + arrData[i][index] + '",';
         }

         row.slice(0, row.length - 1);

         //add a line break after each row
         CSV += row + '\r\n';
      }

      if (CSV == '') {
         alert("Invalid data");
         return;
      }

      //Generate a file name
      var fileName = "";
      //this will remove the blank-spaces from the title and replace it with an underscore
      fileName += ReportTitle.replace(/ /g, "_");

      //Initialize file format you want csv or xls
      var uri = 'data:text/csv;charset=utf-8,' + escape(CSV);

      // Now the little tricky part.
      // you can use either>> window.open(uri);
      // but this will not work in some browsers
      // or you will not get the correct file extension    

      //this trick will generate a temp <a /> tag
      var link = document.createElement("a");
      link.href = uri;

      //set the visibility hidden so it will not effect on your web-layout
      link.style = "visibility:hidden";
      link.download = fileName + ".csv";

      //this part will append the anchor tag and remove it after automatic click
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
   }
</script>

@endsection
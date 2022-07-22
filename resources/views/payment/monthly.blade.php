@extends('layouts.app')

@section('htmlheader_title')
      ክፍሊት
@endsection

@section('contentheader_title')
 ምሕደራ ወርሓዊ ክፍሊት
@endsection

@section('header-extra')
<style type="text/css">
  tr{
    cursor: pointer;
  }
</style>

@endsection
@section('main-content')
<div class="row">
        <div class="col-md-6 col-sm-6 col-xs-6">
                <div class="form-group col-md-12 col-sm-12 col-xs-12">                          
                    <!-- <div class="col-md-6 col-sm-6 col-xs-6">    
                        <select name="zone" id="zone" class="form-control" >
                            <option value=""selected disabled>~ዞባ ምረፅ~</option>
                            @foreach ($zobadatas as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div> -->
                    <!-- <div class="col-md-6 col-sm-6 col-xs-6">    
                        <select name="woreda" id="woreda" class="form-control">
                            <option value="">~ወረዳ ምረፅ~</option>
                        </select>
                    </div> -->
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-6">
            </div>  
    </div>    
<div class="box box-primary">
    <div class="box-header with-border">
        
        <div class=" ">
            {{ csrf_field() }}

            <div class="table-responsive text-center">
              <div class="pull-right" style="padding-bottom: 10px;">
                <button class="add-modal btn btn-success"
                    data-info="">
                    <span class="glyphicon glyphicon-tick"></span> ክፍሊት መዝግብ
                </button>
            </div>
                <table class="table table-borderless table-hover" id="table2">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select_all" value="">ኩሎም ምረፅ<th>
                        <th class="text-center">መ.ቑ</th>
                        <th class="text-center">ሽም ኣባል</th>                     
                        <th class="text-center">መሰረታዊ ውዳበ</th>
                        <th class="text-center">ዋህዮ</th>
                        <th class="text-center">ስራሕ ዘርፊ</th>
                        <th class="text-center">ዝተፃረየ ወርሓዊ መሃያ</th>    
                        <th class="text-center">ወርሓዊ ክፍሊት</th>    
                        <th class="text-center">መጨረሻ ክፍሊት ዝፈፀመሉ ወርሒን ዓመትን</th>
                    </tr>
                </thead>
            <tbody>
                         @foreach ($data as $mymonthly)
                           @if($mymonthly->hitsuy->occupation=="ሲቪል ሰርቫንት"||$mymonthly->hitsuy->occupation=="መምህር")
                          <tr>
                            <td><input style="display: inline;" type="checkbox" class="checkbox" name="check[]" value="{{{ $mymonthly->hitsuyID }}}"><td>
                            <td>{{ $mymonthly->hitsuyID }}</td>  
                            <td>{{ $mymonthly->hitsuy->name }} {{ $mymonthly->hitsuy->fname }} {{ $mymonthly->hitsuy->gfname }}</td>                                                      
                            <td>{{ $mwidabedata[$mymonthly->assignedWudabe] }}</td>
                            <td>{{ $wahiodata[$mymonthly->assignedWahio] }}</td> 
                            <td>{{ $mymonthly->hitsuy->occupation }}</td> 
                            <td>{{ $mymonthly->netSalary }}</td>
                            <td> {{ $collectionamount[$mymonthly->hitsuyID]}}</td>
                            <td>{{ $collectionmonth[$mymonthly->hitsuyID]}} {{$collectionyear[$mymonthly->hitsuyID] }}</td>
                          </tr>
                           @endif
                          @endforeach
                    </tbody>
                </table>
            </div>
	<div id="myModaladd" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title"></h4>

				</div>
				<div class="modal-body">
					<form class="form-horizontal" role="form">
                      <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="memeberID">መፍለይ ቑፅሪ ኣባላት  <span class="required">（*）</span>
                          </label>
                          <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" id="memeberID" required="required" class="form-control col-md-7 col-xs-12" readonly>
                          </div>
                      </div>  
		              <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="yearly">ዓመት<span class="required"></span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="yearly" required="required" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div> 
					  <div class="form-group">
				<label class="control-label col-md-3 col-sm-3 col-xs-3" for="monthly">ክፍሊት ወርሒ</label>
				<div class="col-md-6 col-sm-6 col-xs-12" >
				  <select class="form-control" id="monthly">
					<option selected disabled>~ወርሒ ይምረፁ~</option>
                    <option>መስከረም</option>
					<option>ጥቅምቲ</option>
					<option>ሕዳር</option>
					<option>ታሕሳስ</option>
					<option>ጥሪ</option>
					<option>ለካቲት</option>
					<option>መጋቢት</option>
					<option>ሚያዝያ </option>
					<option>ግንቦት</option>
					<option>ሰነ</option>
					<option>ሓምለ</option>
					<option>ነሓሰ</option>
				  </select>
				</div>
			  </div>
			  
						<p class="fname_error error text-center alert alert-danger hidden"></p>
								
											
					
					</form>
					
					<div class="modal-footer">
					<input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
						<button type="button" class="btn actionBtn">
							<span id="footer_action_button2" class='glyphicon'> </span>
						</button>
						<button type="button" class="btn btn-warning" data-dismiss="modal">
							<span class='glyphicon glyphicon-remove'></span> ዕፀው
						</button>
					</div>
				</div>
			</div>
		</div>
	  </div>
	</div>
   </div>
   </div>
  </div>
  </div>
@endsection

	@section('scripts-extra')
  <script type="text/javascript" src="js/jquery.calendars.js"></script> 
  <script type="text/javascript" src="js/jquery.calendars.plus.min.js"></script>
  <link rel="stylesheet" type="text/css" href="css/jquery.calendars.picker.css"> 
  <script type="text/javascript" src="js/jquery.plugin.min.js"></script> 
  <script type="text/javascript" src="js/jquery.calendars.picker.js"></script>
  <script type="text/javascript" src="js/jquery.calendars.ethiopian.min.js"></script>
	<script>
 
 
     $(document).ready(function() {
      $('#table2').DataTable({
    @include('layouts.partials.lang'),
    "order": []
  });
        } );
     var rows;
     $(document).ready(function(){

        //select all checkboxes
        $('#select_all').change(function(){
          var status=this.checked;
          $('.checkbox').each(function(){
            this.checked=status;
          });
        });
        //
        $('.checkbox').change(function(){
          if(this.checked==false){
            $('#select_all')[0].checked=false;
          }

          if($('.checkbox:checked').length==$('.checkbox').length){
            $('#select_all')[0].checked=true;
          }
          });

    }); 
     $('select[name="zone"]').on('change', function() {
            var stateID = $(this).val();                
            //instead of ዞባ ምረፅ => ኩለን ዞባታት value="" selected,
               if(stateID) {
                $.ajax({
                    url: 'myform/ajax/'+stateID,
                    type: "GET",
                    dataType: "json",
                    success:function(data) {

                        
                        $('select[name="woreda"]').empty();
                        $('select[name="woreda"]').append('<option value="'+ " " +'" selected disabled>'+ "~ወረዳ ምረፅ~" +'</option>');
                        $.each(data, function(key, value) {
                            $('select[name="woreda"]').append('<option value="'+ key +'">'+ value +'</option>');
                        });

                    }
                });
            }else{
                $('select[name="woreda"]').empty();
            }
            $('#table2').DataTable().column(0).search('^'+stateID,true,false).draw();
        });
     $('select[name="woreda"]').on('change', function() {
            var stateID = $(this).val();
            $('#table2').DataTable().column(0).search('^[0-9]{2}'+stateID,true,false).draw();   
        });


    $(document).on('click', '.edit-modal', function() {
        $('#footer_action_button').text(" Update");
        $('#footer_action_button').addClass('glyphicon-check');
        $('#footer_action_button').removeClass('glyphicon-trash');
        $('.actionBtn').addClass('btn-success');
        $('.actionBtn').removeClass('btn-danger');
        $('.actionBtn').removeClass('delete');
        $('.actionBtn').addClass('edit');
        $('.modal-title').text('Edit');
        $('.deleteContent').hide();
        $('.form-horizontal').show();
        var stuff = $(this).data('info').split(',');
        fillmodalData(stuff)
        $('#myModal').modal('show');
    });
    
    $(document).on('click', '.add-modal', function() {
       if($('.checkbox:checked').length==0){
            toastr.clear();
            toastr.warning("ዝተመረፀ ነገር የለን!! በይዘኦም ይምረፁ");
      }else{
            $('#footer_action_button2').text(" ኣቕምጥ");
            $('#footer_action_button2').addClass('glyphicon-check');
            $('#footer_action_button2').removeClass('glyphicon-trash');
            $('.actionBtn').addClass('btn-success');
            $('.actionBtn').removeClass('btn-danger');
            $('.actionBtn').removeClass('delete');
            $('.actionBtn').addClass('add');
            $('.modal-title').text('ወርሓዊ ክፍሊት መመዝገቢ ቕጥዒ');
            $('.deleteContent').hide();
            $('.form-horizontal').show();

            var idVals=[];
            rows = [];

            $('.checkbox').each(function(){
                // if(this.checked){
                  rows.push($(this).parent().parent().children()[9]);
                  var myVal=$(this).val();
                  idVals.push(myVal);            
                // }
            });

            $('#memeberID').val(JSON.stringify(idVals));        
            $('#myModaladd').modal('show');
      }  
    });
    $('.modal-footer').on('click', '.add', function() {
      $.ajax({
        type: 'post',
        url: 'storemonthly',
        data: {
          '_token': $('input[name=_token]').val(),        
          'memeberID': $('#memeberID').val(),
          'month': $('#monthly').val(),
          'year': $('#yearly').val()
        },

        success: function(data) {      
           if(data[0] == true){
              rows.forEach(function(t){
                  t.innerHTML = $("#monthly").val() + " " + $("#yearly").val();
              });
              $("#memeberID").val('');
              $('#monthly').prop('selectedIndex',0);
              $("#yearly").val('');
              $('#myModaladd').modal('hide');
            }
            else{
              if(Array.isArray(data[2]))
                  data[2] = data[2].join('<br>');
            }
          
            toastr.clear();
            toastr[data[1]](data[2]);
            if(data[0] == true){
              // window.location.reload();
            }

        },
        error:function(xhr,err,exception){
            alert(exception);
        }
      });
    });

    $(document).on('click', '.delete-modal', function() {
        $('#footer_action_button').text("Delete");
        $('#footer_action_button').removeClass('glyphicon-check');
        $('#footer_action_button').addClass('glyphicon-trash');
        $('.actionBtn').removeClass('btn-success');
        $('.actionBtn').addClass('btn-danger');
        $('.actionBtn').removeClass('edit');
        $('.actionBtn').addClass('delete');
        $('.modal-title').text('Delete');
        $('.deleteContent').show();
        $('.form-horizontal').hide();
        var stuff = $(this).data('info').split(',');
        
         
        $('.did').text(stuff[0]);
        
    
        $('.dname').html(stuff[1]);
        $('#myModal').modal('show');
    });

function fillmodalData(details){
     $('#fid').val(details[0]);
    $('#fname').val(details[1]);
    
}

    $('.modal-footer').on('click', '.edit', function() {
        $.ajax({
            type: 'post',
            url: 'zoneedit',
            data: {
                '_token': $('input[name=_token]').val(),                
                'id': $("#iid").val(),
                'fname': $('#fid').val(),
                'lname': $('#fname').val()
              
            },
            
            success: function(data) {
                if (data.errors){
                    $('#myModal').modal('show');
                    if(data.errors.fname) {
                        $('.fname_error').removeClass('hidden');
                        $('.fname_error').text("Code can't be empty !");
                    }
                    if(data.errors.lname) {
                        $('.lname_error').removeClass('hidden');
                        $('.lname_error').text("Name can't  be empty !");
                    }
                    
                    
                }
                 else {
                     
                     $('.error').addClass('hidden');
                $('.item' + data.zoneCode).replaceWith("<tr class='item" + data.zoneCode + "'> <td>" + data.zoneCode +
                        "</td><td>" + data.zoneName + "</td><td><button class='edit-modal btn btn-info' data-info='" + data.zoneCode+","+data.zoneName+"'><span class='glyphicon glyphicon-edit'></span> Edit</button> <button class='delete-modal btn btn-danger' data-info='" + data.zoneCode+","+data.zoneName+"' ><span class='glyphicon glyphicon-trash'></span> Delete</button></td></tr>");

                 }}
        });
    });
    $('.modal-footer').on('click', '.add', function() {
        $.ajax({
            type: 'post',
            url: 'zoneadd',
            data: {
                '_token': $('input[name=_token]').val(),                
                'fname': $('#fid2').val(),
                'lname': $('#fname2').val()
              
            },
            
            success: function(data) {
                 $('#products-list').append("<tr class='item" + data.zoneCode + "'> <td>" + data.zoneCode +
                        "</td><td>" + data.zoneName + "</td><td><button class='edit-modal btn btn-info' data-info='" + data.zoneCode+","+data.zoneName+"'><span class='glyphicon glyphicon-edit'></span> Edit</button> <button class='delete-modal btn btn-danger' data-info='" + data.zoneCode+","+data.zoneName+"' ><span class='glyphicon glyphicon-trash'></span> Delete</button></td></tr>");
               
            
                }
        });
    });
    $('.modal-footer').on('click', '.delete', function() {
        $.ajax({
            type: 'post',
            url: 'zonedelete',
            data: {
                '_token': $('input[name=_token]').val(),
                'id': $('.did').text()
                
            },
            
            success: function(data) {
              
                 $('.item' + $('.did').text()).remove();
                 
                  
            }
        });
    });
$(document).on('click', 'tbody tr', function() {
  var checkBox = $($(this).find('input')[0])
  checkBox.click();
});
$('tr').on('click', 'input[type="checkbox"]', function(e){
  e.stopPropagation();
});
</script>
<link rel="stylesheet" href="css/jquery.dataTables.min.css"></style>
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
@endsection

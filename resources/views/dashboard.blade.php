@extends('layouts.app')

@section('htmlheader_title')
    ህወሓት
@endsection

@section('contentheader_title')
    ዳሽ ቦርድ
    @if(array_search(Auth::user()->usertype, ['admin', 'zoneadmin', 'woredaadmin']) !== false)
      <div class="pull-right">
        <a href="#"><button name="createnew" id="announcemnt" class="btn btn-info"><span class="glyphicon glyphicon-plus"></span>ሓዱሽ ሓበሬታ መዝግብ</button></a>
      </div>
    @endif
@endsection

@section('main-content')
    <ul class="nav nav-tabs nav-justified" id="tabNavigation">
      <li class="active"><a href="#info" data-toggle="tab">ሓድሓደ ሓሳባት</a></li>
      <li><a href="#ann" data-toggle="tab">ሓበሬታ</a></li>
      <li><a href="#doc" data-toggle="tab">ዶክመንት</a></li>

    </ul>


    <div class="tab-content">
      <div class="tab-pane fade in active" id="ann">

       
      </div>
   


    <div class="tab-content">
      <div class="tab-pane fade in active" id="ann">
        @foreach ($data as $mydata)
        <div class="box">
            <div class="box-header with-border">
              <?php
                $area = ''; 
                if($mydata->area == 'all'){
                  $area = 'ቤት ፅሕፈት';
                  $label = "label-success";
                }
                if($mydata->area == 'zone'){
                  $area = 'ዞባ - '. $mydata->areaname;
                  $label = "label-warning";
                }
                if($mydata->area == 'woreda'){
                  $area = 'ወረዳ - '. $mydata->areaname;
                  $label = "label-danger";
                }
              ?>
                <input type="hidden" id="id" value="{{ $mydata->id }}">
                <h3 class="box-title">{{ $mydata->title }}&nbsp;&nbsp;&nbsp;<span class="label {{ $label }}">{{ $area }}</span></h3>
                @if(array_search(Auth::user()->usertype, ['admin', 'zoneadmin', 'woredaadmin']) !== false && Auth::user()->area == $mydata->code)
                  <span class="fa fa-trash pull-right deleteannouncement" style="cursor: pointer;"></span>
                  <span class="fa fa-edit pull-right editannouncement" style="cursor: pointer;"></span>
                @endif
            </div>
            <div class="box-body">
              <!--   You are logged in! -->
                <div class="row">
                  <div class="col-sm-12">
                      <p>
                        {{ $mydata->content }}
                      </p>
                  </div>
                  <!-- /.col -->
                </div>
                <!-- /.row -->
            </div><!-- /.box-body -->
        </div><!-- /.box -->
        @endforeach
      </div>
      <div class="tab-pane fade" id="doc">
        <br>
        <div class="box box-primary">
          <div class="box-header with-border">
              <div class="">
                  {{ csrf_field() }}
                  <div class="table-responsive text-center">
                      <table class="table table-borderless" id="table2">
                          <thead>
                              <tr>                       
                                  <th class="text-center">ሽም ፋይል</th>
                                  <th class="text-center">መብርሂ</th>
                                  <th class="text-center">መማረፂት</th>
                              </tr>
                          </thead>
                      <tbody  id="products-list" name="products-list">
                          @foreach($doc as $item)
                          <tr>
                              <td>{{ $item->title }}</td>
                              <td>{{ $item->description }}</td>
                              <td> <a href="download/{{ $item->title }}"> <button class="btn btn-primary"><span class="fa fa-download"></span>ኣውርድ</button> </a> 
                              @if (Auth::user()->usertype == 'admin')
                                <button class="btn btn-danger deleteDocument"><span class="fa fa-trash"></span>ኣጥፍእ</button>
                              </td>
                              @endif
                          </tr>
                          @endforeach
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
        </div>
      </div>

        <div class="tab-pane fade" id="info">
        <br>
        
        <div class="box box-primary">
          <div class="box-header with-border">
              <div class="">
                  {{ csrf_field() }}
                  <div class="table-responsive text-center">
                <table class="table table-borderless" id="table2">

         <tbody  id="products-list" name="products-list">
                         <div class="box">
       
        <div class="box-body">
          <!--   You are logged in! -->
            <div class="row">
              <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                  <span class="info-box-icon bg-aqua"><i class="ion ion-ios-gear-outline"></i></span>

                  <div class="info-box-content">
                    <span class="info-box-text">በዝሒ ኣባላት</span>
                    <span class="info-box-number">{{ $abalat}}<small></small></span>
                  </div>
                  <!-- /.info-box-content -->
                  
                </div>
                <!-- /.info-box -->
              </div>
              <!-- /.col -->
              <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                  <span class="info-box-icon bg-red"><i class="fa fa-google-plus"></i></span>

                  <div class="info-box-content">
                    <span class="info-box-text">ኮር ደገፍቲ</span>
                    <span class="info-box-number">{{$cordgefti}}</span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
              <!-- /.col -->

              <!-- fix for small devices only -->
              <div class="clearfix visible-sm-block"></div>

              <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                  <span class="info-box-icon bg-green"><i class="ion ion-ios-cart-outline"></i></span>

                  <div class="info-box-content">
                    <span class="info-box-text">ኣመራርሓ በዝሒ</span>
                    <span class="info-box-number">{{$amerarha}}</span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
              <!-- /.col -->
              <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                  <span class="info-box-icon bg-yellow"><i class="ion ion-ios-people-outline"></i></span>

                  <div class="info-box-content">
                    <span class="info-box-text">በዝሒ ክፍሊት</span>
                    <span class="info-box-number">{{$totalpayments}}</span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
              <!-- /.col -->
            </div>
            <!-- /.row -->

            <div class="row">
              <div class="col-md-12">
                <div class="box">
                  <div class="box-header with-border">
                    <h3 class="box-title">ወርሓዊ ፀብፃብ</h3>

                    <div class="box-tools pull-right">
                      <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                      </button>
                      <div class="btn-group">
                        <button type="button" class="btn btn-box-tool dropdown-toggle" data-toggle="dropdown">
                          <i class="fa fa-wrench"></i></button>
                        <ul class="dropdown-menu" role="menu">
                          <li><a href="#">Action</a></li>
                          <li><a href="#">Another action</a></li>
                          <li><a href="#">Something else here</a></li>
                          <li class="divider"></li>
                          <li><a href="#">Separated link</a></li>
                        </ul>
                      </div>
                      <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                    </div>
                  </div>
                  <!-- /.box-header -->
                  <div class="box-body">
                    <div class="row">
                      <div class="col-md-8">
                        <p class="text-center">
                          <strong>ሓደሽቲ ሕፁያት: 1 መስከረም, 2009 - 30 ነሓሰ, 2009</strong>
                        </p>

                        <div class="chart">
                          <!-- Sales Chart Canvas -->
                          <canvas id="salesChart" style="height: 180px;"></canvas>
                        </div>
                        <!-- /.chart-responsive -->
                      </div>
                      <!-- /.col -->
                      <div class="col-md-4">
                        <p class="text-center">
                          <strong>ዳሽ ቦርድ ብመልክዕ ግራፍ</strong>
                        </p>

                        <div class="progress-group">
                          <span class="progress-text"> ኣባላት ኣብዚ ሐዚ እዋን:
                          </span>
                          <span class="progress-number"><b>
                          
                          </b> {{$abalat}} </span>
                          <?php 
                            $abalatnow = (int)(($abalat/$totaltablevalue)*100);

                    
                           ?>

                          <div class="progress sm">
                            <div class="progress-bar progress-bar-aqua" style="width: <?php echo $abalatnow;?>%"></div>
                          </div>
                        </div>
                        <!-- //$ucounter*100.progress-group -->
                        <div class="progress-group">
                          <span class="progress-text">ኣባላት ቅድሚ 3 ወርሒ</span>
                          <span class="progress-number"><b>
                            
                          </b> {{$abalatbefore_threemonths}} </span>
                          <?php 
                            $abalatbeforethreemonth = (int)(($abalatbefore_threemonths/$totaltablevalue)*100);

                    
                           ?>
                          <div class="progress sm">
                            
                            <div class="progress-bar progress-bar-red" style="width:  <?php echo $abalatbeforethreemonth;?>%"></div>
                          </div>
                        </div>
                        <!-- /.progress-group -->
                        <div class="progress-group">
                          <span class="progress-text">ኣመራርሓ ቅድሚ 3 ወርሒ </span>
                          <span class="progress-number"><b>{{$amerarhabefore_threemonths}}</span>
                          <?php 
                            $amerarhabefore3 = (int)(($amerarhabefore_threemonths/$totaltablevalue)*100);

                          
                           ?>

                          <div class="progress sm">
                            <div class="progress-bar progress-bar-green" style="width: <?php echo $amerarhabefore3;?>%"></div>
                          </div>
                        </div>
                        <!-- /.progress-group -->
                        <div class="progress-group">
                          <span class="progress-text">ኣመራርሓ ኣብዚ ሐዚ እዋን</span>
                          <span class="progress-number"><b>{{$amerarha}}</b></span>
                           <?php 
                            $amerarhanow = (int)(($amerarha/$totaltablevalue)*100);

                    
                           ?>

                          <div class="progress sm">
                            <div class="progress-bar progress-bar-yellow" style="width: <?php echo $amerarhanow;?>%"></div>
                          </div>
                        </div>
                        <!-- /.progress-group -->
                      </div>
                      <!-- /.col -->
                    </div>
                    <!-- /.row -->
                  </div>
                  <!-- ./box-body -->
                  <div class="box-footer">
                    

                    </div>
                    <!-- /.row -->
                  </div>
                  <!-- /.box-footer -->
                </div>
                <!-- /.box -->
              </div>
              <!-- /.col -->
            </div>
            <!-- /.row -->
              </div><!-- /.box-body -->
            </div><!-- /.box -->
            
        </div>
        <!-- /.box-body -->
    </div>
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
        </div>
      </div>

    </div>
    <div id="announcemntModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                  <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">ሓዱሽ ሓበሬታ</h4>

                    </div>
                    <div class="modal-body">
                      <form class="form-horizontal" role="form">
                        <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                        <div class="form-group col-xs-12 col-sm-10">
                            <input type="text" class="form-control" placeholder="ርእሲ" name="title" id="title" value=""/>
                        </div>

                        <div class="form-group col-xs-12 col-sm-10">
                            <textarea class="form-control" placeholder="ዝርዝር ሓበሬታ" name="description" id="description" rows="10"></textarea>
                        </div>

                        <button type="button" class="btn btn-primary btn-block btn-flat announcemntbutton" id="createannouncemnt">ሓበሬታ መዝግብ</button>
                      </form>
                    </div>
                </div>
            </div>
        </div>
        <div id="deleteModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
    <!-- Modal content-->
            <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">×</button>
                  <h4 class="modal-title">ሰርዝ</h4>

              </div>
              <div class="modal-body">
                  <div class="deleteContent">
                    ሓበሬታ: <span id="deleteTitle"></span><br>
                      ብትክክል ክጠፍአ ይድለ ድዩ <span class="dname"></span> ?
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn actionBtn btn-danger delete">
                          <span id="deleteButton" class="glyphicon glyphicon-trash">ሰርዝ</span>
                      </button>
                      <button type="button" class="btn btn-warning" data-dismiss="modal">
                          <span class="glyphicon glyphicon-remove"></span> ዕፀው
                      </button>
                  </div>
              </div>
            </div>
          </div>
        </div>
        <div id="deleteDocumentModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">×</button>
                  <h4 class="modal-title">ሰርዝ</h4>

              </div>
              <div class="modal-body">
                  <div class="deleteContent">
                    ዶክመንት: <span id="documentTitle"></span><br>
                      ብትክክል ክጠፍአ ይድለ ድዩ <span class="dname"></span> ?
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn actionBtn btn-danger delete" id="deleteDocumentButton">
                          <span class="glyphicon glyphicon-trash">ሰርዝ</span>
                      </button>
                      <button type="button" class="btn btn-warning" data-dismiss="modal">
                          <span class="glyphicon glyphicon-remove"></span> ዕፀው
                      </button>
                  </div>
              </div>
            </div>
          </div>
        </div>

@endsection
@section('scripts-extra')
  <script type="text/javascript">

    $(document).ready(function() {
        $('#table2').DataTable({
            @include("layouts.partials.lang"),
            "order": []
        });
    });

    $(document).on('click', 'li a', function() {
      if($(this).html() == "ሓበሬታ"){
        $('button[name="createnew"]').html('<span class="glyphicon glyphicon-plus"></span>ሓዱሽ ሓበሬታ መዝግብ');
        $($('button[name="createnew"]').parent()).prop('href', '#');
        $('button[name="createnew"]').prop('id', 'announcemnt');
      }
      if($(this).html() == "ዶክመንት"){
        $('button[name="createnew"]').html('<span class="glyphicon glyphicon-plus"></span>ሓዱሽ ዶክመንት ኣእትው');
        $($('button[name="createnew"]').parent()).prop('href', 'newdocument');
        $('button[name="createnew"]').prop('id', 'document');
      }
    });
    $(document).on('click', '#announcemnt', function() {
        $('#title').val('');
        $('#description').val('');
        $('.announcemntbutton').prop('id', 'createannouncemnt');
        $('#announcemntModal').modal('show');
    });

    var title = null;
    var description = null;
    var id = null;
    var row = null;
    $(document).on('click', '.editannouncement', function() {
        id = $($(this).parent().children()[0]).val();
        title = $($(this).parent().children()[1]);
        description = $($($($($(this).parent().parent().children()[1]).children()[0]).children()[0]).children()[0]);
        $('#title').val(title.html().split('&')[0]);
        $('#description').val(description.html().trim());
        $('.announcemntbutton').prop('id', 'modifyannouncemnt');
        $('#announcemntModal').modal('show');
    });

    $(document).on('click', '.deleteDocument', function(){
      title = $($($(this).parent().parent()[0]).children()[0]).html();
      $('#documentTitle').html(title);
      row = $($($(this).parent().parent())[0]);
      $('#deleteDocumentModal').modal('show');
    });

    $(document).on('click', '.deleteannouncement', function() {
      id = $($(this).parent().children()[0]).val();
      title = $($(this).parent().children()[1]);
      $('#deleteTitle').html(title.html().split('&')[0]);
      row = $(this).parent().parent();
      $('#deleteModal').modal('show');
    });
    $(document).on('click', '#deleteDocumentButton', function() {
      $.ajax({
          type: 'post',
          url: 'deletedocument',
          data: {
            '_token': $('input[name=_token]').val(),
            'title': title,
          },
          success: function(data) {
            if(data[0] == true){
              $('#deleteDocumentModal').modal('hide');
            }
            else{
              if(Array.isArray(data[2]))
                      data[2] = data[2].join('<br>');
            }
            toastr.clear();
            toastr[data[1]](data[2]);
            if(data[0] == true){
              row.remove();
            }
          }
      });
    });
    $(document).on('click', '#deleteButton', function() {
      $.ajax({
          type: 'post',
          url: 'deleteannouncement',
          data: {
            '_token': $('input[name=_token]').val(),
            'id': id,
          },
          success: function(data) {
            if(data[0] == true){
              $('#deleteModal').modal('hide');
            }
            else{
              if(Array.isArray(data[2]))
                      data[2] = data[2].join('<br>');
            }
          
            toastr.clear();
            toastr[data[1]](data[2]);
            if(data[0] == true){
              row.remove();
            }
          }
      });
    });

    $(document).on('click', '#modifyannouncemnt', function() {
      $.ajax({
          type: 'post',
          url: 'editannouncement',
          data: {
              '_token': $('input[name=_token]').val(),
              'id': id,
              'title': $('#title').val(),
              'description': $('#description').val()
          },
          success: function(data) {
              if(data[0] == true){
                $('#announcemntModal').modal('hide');
              }
              else{
                if(Array.isArray(data[2]))
                        data[2] = data[2].join('<br>');
              }
            
              toastr.clear();
              toastr[data[1]](data[2]);
              if(data[0]==true){
                title.html($("#title").val()+'&'+title.html().split('&').splice(1).join('&'));
                description.html($('#description').val());
                $("#title").val('');
                $('#description').val('');
              }
          },
      });
    });

    $(document).on('click', '#createannouncemnt', function() {
      $.ajax({
          type: 'post',
          url: 'addnewannouncement',
          data: {
              '_token': $('input[name=_token]').val(),
              'title': $('#title').val(),
              'description': $('#description').val()
          },
          success: function(data) {
              if(data[0] == true){
                $('#announcemntModal').modal('hide');
              }
              else{
                if(Array.isArray(data[2]))
                        data[2] = data[2].join('<br>');
              }
            
              toastr.clear();
              toastr[data[1]](data[2]);
              if(data[0]==true){
                var area = '';
                var label = '';
                if(data[3] == 'all'){
                  area = 'ቤት ፅሕፈት';
                  label = "label-success";
                }
                if(data[3] == 'zone'){
                  area = 'ዞባ - '+ data[4];
                  label = "label-warning";
                }
                if(data[3] == 'woreda'){
                  area = 'ወረዳ - '+ data[4];
                  label = "label-danger";
                }
                var row = '<div class="box">\
                  <div class="box-header with-border">\
                  <input type="hidden" id="id" value="'+ data[5] +'">\
                      <h3 class="box-title">'+ $('#title').val() +'&nbsp;&nbsp;&nbsp;<span class="label ' + label + '">' + area + '</span></h3>\
                      <span class="fa fa-edit pull-right editannouncement" style="cursor: pointer;"></span>\
                  </div>\
                  <div class="box-body">\
                    <!--   You are logged in! -->\
                      <div class="row">\
                        <div class="col-sm-12">\
                            <p>\
                              ' + $('#description').val() + '\
                            </p>\
                        </div>\
                        <!-- /.col -->\
                      </div>\
                      <!-- /.row -->\
                  </div><!-- /.box-body -->\
              </div><!-- /.box --> ';
              $('#ann').prepend(row);
              $("#title").val('');
                $('#description').val('');
            }
          },
      });
    });
  </script>
  <link rel="stylesheet" href="css/jquery.dataTables.min.css"></style>
  <script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
@endsection

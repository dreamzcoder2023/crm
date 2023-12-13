@extends('layouts/contentNavbarLayout')

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<style>
  @media only screen and (max-width:320px){
    .aa{
      display:inline !important;
    }
  }
  .dataTables_wrapper {
    font-family: tahoma;
    font-size: 13px;
    clear: both;

}
.dataTables_length select {
  width: 120px;
}
.table-responsive{
  margin-top:5px;
  margin-left:5px;


}
  </style>
@section('title', 'Member')

@section('content')

<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
@if (session()->has('message'))

<script>
    $(function() {
      $('.success-msg').text("{{ session('message')}}");
      $('#walletsuccess').removeClass('fade');
      $('#walletsuccess').modal('show');
    });
</script>
@endif
@if(session()->has('msg'))
    <div class="alert alert-danger">
        {{ session()->get('msg') }}
    </div>
@endif
<div style="margin-top: 30px;">
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Labour </span>
</h4>
<div class="row" style="position:absolute; top:180px; right:50px ">
  <div class="col-md-12">
    @can('labour-create')
    <ul class="nav nav-pills flex-column flex-md-row mb-3">
      <li class="nav-item"><a class="nav-link active" href="{{route('labour-create')}}"><i class="bx bx-user me-1"></i> Add Labour</a></li>

    </ul>
    @endcan
  </div></div></div>

<!-- Basic Bootstrap Table -->
<div class="card" style="max-width: 1200px; margin: 40px auto; height:250px">
  <!-- <h5 class="card-header">Table Basic</h5> -->
  <div class="table-responsive text-nowrap">
    <table class="table" id="user_listing_table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Job Title</th>
          <th>Phone</th>
          <th>Salary</th>
          <th>Advance Amount</th>
          @canany(['labour-view','labour-edit','labour-delete'])
          <th>Action</th>
          @endcanany
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">

        @foreach($users as $user)
       <tr>
        <td>{{$loop->index+1}}</td>
        <td>{{$user->name}}</td>
        <td>{{$user->job_title}}</td>
        <td>{{$user->phone}}</td>
        <td>{{ $user->salary }}</td>
        <td>{{$user->advance_amt}}</td>
        @canany(['labour-view','labour-edit','labour-delete'])
        <td>
        @can(['labour-view'])
        <a  href="" data-id="{{$user->id}}" class=""><i class="bi bi-eye" style="font-size:24px"></i></a>
        @endcan
          @can(['labour-edit'])
        <a class=""  href="" ><i class="fa fa-edit" style="font-size:24px"></i></a>
        @endcan
        @can('labour-delete')
        <input type="hidden" value="{{$user->id}}" id="user_id">
        <a data-toggle="modal" href="javascript:void(0)" data-id="{{$user->id}}" class="deleteUser"><i class="fa fa-trash-o" style="font-size:24px; color:red"></i> </a><br/>
        @endcan
      </td>
      @endcanany
       </tr>
       @endforeach

      </tbody>
    </table>
  </div>
</div>
<!--/ Basic Bootstrap Table -->




<!--- modal popup for delete role started--->



  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-sm">

      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmation</h4>
        </div>
        <div class="modal-body">
          <p style="text-align: center;">Are you sure want to delete this?</p>
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-primary yes-delete" data-dismiss="modal">Yes</button>
          <button type="button" class="btn btn-danger no-delete" data-dismiss="modal">No</button>
        </div>
      </div>

    </div>
  </div>
  <div id="walletsuccess" class="modal fade" >
    <div class="modal-dialog modal-confirm modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <center><h4 class="modal-title">Success</h4>	</center>
        </div><hr>
        <div class="modal-body">
          <p class="text-center success-msg"></p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success btn-block" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>


<!-- modal popup for delete role ended -->
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
  $(document).ready(function() {
var data =  new DataTable('#user_listing_table', {
  "lengthMenu": [15, 25, 50, 100],
  processing: true,

});
});
$("document").ready(function(){
  var user = $('#user_id').val();
  console.log()
  var userid;
      setTimeout(function(){
       $("div.alert").remove();
    }, 5000 ); // 5 sec
    $("#user_listing_table").on("click", ".deleteUser", function(){

  userid = $(this).attr('data-id');
  $("#myModal").removeClass('fade');
  $("#myModal").modal('show');
});
$('.no-delete').click(function(){
  $("#myModal").addClass('fade');
  $("#myModal").modal('hide');
});
$('.yes-delete').click(function(){
console.log('userid',userid);
$("#myModal").modal('hide');
var url = '{{ route("user-delete",":id",) }}';
      url1 = url.replace(':id', userid);
      window.location.href=url1;
});
});
</script>

@endsection

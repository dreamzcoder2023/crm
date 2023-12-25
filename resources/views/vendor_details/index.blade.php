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
@section('title', 'List | HOUSE FIX - A DOCTOR FOR YOUR HOUSE')

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
  <span class="text-muted fw-light">Vendor Details</span>
</h4>
<div class="row" style="position:absolute; top:180px; right:50px ">
  <div class="col-md-12">
    @can('vendor-create')
    <ul class="nav nav-pills flex-column flex-md-row mb-3">
      <li class="nav-item"><a class="nav-link active" href="{{route('vendor-create')}}"><i class="bx bx-user me-1"></i> Add Vendor</a></li>

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
          <th>Vendor Name</th>
          <th>Phone</th>
          <th>Address</th>
          <th>Advance Amount</th>
          @canany(['vendor-edit','vendor-delete'])
          <th>Action</th>
          @endcanany
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">

        @foreach($users as $user)
       <tr>
        <td>{{$loop->index+1}}</td>
        <td>{{$user->name}}</td>
        <td>{{ $user->phone }}</td>
        <td>{{ $user->address }}</td>
        <td>{{ $user->advance_amt }}</td>
        @canany(['vendor-edit','vendor-delete'])
        <td>
        {{-- @can(['labour-view'])
        <a  href="" data-id="{{$user->id}}" class=""><i class="bi bi-eye" style="font-size:24px"></i></a>
        @endcan --}}
          @can(['vendor-edit'])
        <a class=""  href="{{ route('vendor-edit',$user->id) }}" ><i class="fa fa-edit" style="font-size:24px"></i></a>
        @endcan
        @can('vendor-delete')
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
  <div class="modal fade" id="myModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm">

      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmation</h4>
        </div><hr>
        <div class="modal-body">
          <p style="text-align: center;">Are you sure want to delete this?</p>
        </div><hr>
        <div class="modal-footer">
        <button type="button" class="btn btn-primary yes-delete" data-dismiss="modal">Yes</button>
          <button type="button" class="btn btn-danger no-delete" data-dismiss="modal">No</button>
        </div>
      </div>

    </div>
  </div>
  <div id="walletsuccess" class="modal fade"  data-backdrop="static" data-keyboard="false" >
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
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Salary Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
     <div class="loadingsalary"></div>
      </div>
      {{-- <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div> --}}
    </div>
  </div>
</div>
<!-- modal popup for salary details -->


<!-- modal popup for salary details -->
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

  $("#myModal").modal('show');
});
$('.no-delete').click(function(){

  $("#myModal").modal('hide');
});
$('.yes-delete').click(function(){
console.log('userid',userid);
$("#myModal").modal('hide');
var url = '{{ route("vendor-delete",":id",) }}';
      url1 = url.replace(':id', userid);
      window.location.href=url1;
});
$("#user_listing_table").on("click", "#click_salary", function(){
  var user = $(this).attr('data-user');
  console.log($(this).attr('data-user'));
  $.ajax
  $('#exampleModal').modal('show');
});
});

</script>

@endsection

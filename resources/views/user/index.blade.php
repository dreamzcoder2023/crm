@extends('layouts/contentNavbarLayout')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400&display=swap" rel="stylesheet">
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
    font-family: 'Source Sans 3', sans-serif;
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
.dropdown-toggle{
  width:146px !important;
}
.bs-caret::after{
  color:#f7f7f7 !important;
  content: "";
  display:none !important;
}
  </style>
@section('title', 'List | HOUSE FIX - A DOCTOR FOR YOUR HOUSE')

@section('content')

@if (session()->has('message'))

<script>
      $(function() {
            toastr.success('{{ session('message') }}', {
                timeOut: 1000,
                fadeOut: 1000,
            });
        });
</script>
@endif
@if(session()->has('msg'))
<script>
$(function() {
  toastr.error('{{ session('msg') }}', {
      timeOut: 1000,
      fadeOut: 1000,
  });
});
</script>
@endif
<div style="margin-top: 30px;">
<h4 class="fw-bold py-3 mb-4" style="margin-top:-49px;font-size:16px;color:black">
  <span class="fw-light">Member </span>
</h4>
<div class="row" style="position:absolute; top:90px; right:50px ">
  <div class="col-md-12">
    @can('user-create')
    <ul class="nav nav-pills flex-column flex-md-row mb-3">
      <li class="nav-item"><a class="nav-link active" href="{{route('user-create')}}"><i class="bx bx-user me-1"></i> Add Member</a></li>

    </ul>
    @endcan
  </div></div></div>

<!-- Basic Bootstrap Table -->
<div class="card" style="max-width: 1200px; margin: 40px auto;top:-26px;">
  <!-- <h5 class="card-header">Table Basic</h5> -->
  <div class="table-responsive text-nowrap" style="width:99%;">
    <table class="table" id="user_listing_table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Profile</th>
          <th>Name</th>
          <th>Job Title</th>
          <th>Email</th>
          <th>Phone</th>
          @role('Admin')
          <th>Wallet</th>
          <th>Unpaid Amount</th>
          @endrole
          @canany(['user-view','user-edit','user-delete'])
          <th>Action</th>
          @endcanany
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">

        @foreach($users as $user)
       <tr>
        <td>{{$loop->index+1}}</td>
        <td>@if($user->image != '' || $user->image != null) <img class="rounded float-left" src="public/images/{{ $user->image }}" width="30px"> @else <img class="rounded float-left" src="{{asset('assets/img/icons/gray-user-profile-icon.png')}}" width="30px">  @endif </td>
        <td>{{$user->first_name}} {{$user->last_name}}</td>
        <td>{{$user->job_title}}</td>
        <td>{{$user->email}}</td>
        <td>{{$user->phone}}</td>
        @role('Admin')
        <td>{{$user->wallet}}</td>
        <td>{{ App\Models\Expenses::where('user_id',$user->id)?->first()?->unpaid_amt }}</td>
        @endrole
        @canany(['user-view','user-edit','user-delete'])
        <td>
        @can(['user-view'])
        <a  href="{{route('user-show',$user->id)}}" data-id="{{$user->id}}" class=""><i class="bi bi-eye" style="font-size:20px"></i></a>
        @endcan
          @can(['user-edit'])
        <a class="" @if($role->model_id == Auth::user()->id) href="{{ route('user-edit',$user->id) }}" @else disabled @endif><i class="bi bi-pencil-square" style="font-size:20px;color:green"></i></a>
        @endcan
        @can('user-delete')
        <input type="hidden" value="{{$user->id}}" id="user_id">
        <a data-toggle="modal" href="javascript:void(0)" data-id="{{$user->id}}" class="deleteUser"><i class="bi bi-trash" style="font-size:20px; color:red"></i> </a><br/>

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

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
.dropdown-toggle{
  width:146px !important;
}
.bs-caret::after{
  color:#f7f7f7 !important;
  content: "";
  display:none !important;
}
.table-responsive{
  margin-top:5px;
  margin-left:5px;
}
table {
  width:50%;
  border-spacing: 0; /* Remove spacing between cells */
  border-collapse: collapse; /* Collapse cell borders */
}

td, th {
  padding: 5px; /* Reduce cell padding */
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
  <span class="fw-light">Project Details </span>
</h4>
<div class="row" style="position:absolute; top:90px; right:50px ">
  <div class="col-md-12">
    @can('project-create')
    <ul class="nav nav-pills flex-column flex-md-row mb-3">
      <li class="nav-item"><a class="nav-link active" href="{{route('project-create')}}"><i class="bi bi-tools me-1"></i> Add Project</a></li>

    </ul>
    @endcan
  </div></div></div>
<!-- Basic Bootstrap Table -->
<div class="card" style="max-width: 1200px; margin: 40px auto; height:569px;top:-32px;">
  <!-- <h5 class="card-header">Table Basic</h5> -->
  <div class="table-responsive text-nowrap" style="width: 99%;">
    <table class="table" id="project_listing_table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Project Name</th>
          <th>Client Name</th>
          <th>Advanced Amount</th>
          <th>Total Estimation</th>
          <th>Project Status</th>
          <th>Remaining </th>
          @can('expenses-view')
          <th>Expenses</th>
          @endcan
          @canany(['project-edit','project-delete','project-view'])
          <th>Action</th>
          @endcanany
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">

            @foreach($projects as $project)
        <tr>
            <td>{{ $loop->index+1}}</td>
            <td>{{$project->name}}</td>
            <td>{{$project->first_name}} {{$project->last_name}}</td>
            <td><b><span style="color:#ef6a0e">{{$project->advance_amt ? $project->advance_amt : '--'}}</span></b></td>
            <td><b><span style="color: green;">{{$project->total_amt}}</span></b></td>
            <td>@if($project->project_status == 0 )<button type="button" class="btn btn-success">Active</button>@else
<button type="button" class="btn btn-danger">De-active</button>@endif</td>
            <td><b><span style="color: red;">{{$project->profit}}</span></b></td>
            @can('expenses-view')
            <td><a class="" href="{{ route('project-show',$project->id) }}" data-toggle="tooltip" title="Expenses Detail" >View</a> </td>
            @endcan
             @canany(['project-edit','project-delete','project-view'])
            <td>
              @can('project-view')
            <a class="" href="{{ route('project-view',$project->id) }}" data-toggle="tooltip" title="Project Detail" ><i class="bi bi-info-circle" style="font-size:20px"></i></a>
            @endcan

            @can('project-edit')
            <a class="" href="{{ route('project-edit',$project->id) }}"><i class="fa fa-edit" style="font-size:20px"></i></a>
        @endcan
        @can('project-delete')
        @if(!in_array($project->id,$wallet) && !in_array($project->id,$expenses))
            <a data-toggle="modal" href="javascript:void(0)" data-id="{{$project->id}}" class="deleteProject"><i class="fa fa-trash-o" style="font-size:20px; color:red"></i> </a><br/>
           @endif
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

<p class="text-end" style="margin-top: 33px; margin-right: 147px; font-size: medium;">
    <span class="d-inline"><b>Total Advance Amount:</b> <b><span style="color:#ef6a0e">{{$sum}}</span></b></span>
    <span class="d-inline ms-3"><b>Total  Amount:</b> <b><span style="color: green;">{{$total}}</span></b></span>
    <span class="d-inline ms-3"><b>Total Remaining Amount:</b> <b><span style="color: red;">{{$remaining}}</span></b></span>

</p>
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
var data =  new DataTable('#project_listing_table', {
  "lengthMenu": [15, 50, 100],
  processing: true,

});
});
$("document").ready(function(){
  var projectid;
      setTimeout(function(){
       $("div.alert").remove();
    }, 5000 ); // 5 secs
    $("#project_listing_table").on("click", ".deleteProject", function(){

  projectid = $(this).attr('data-id');
  $("#myModal").removeClass('fade');
  $("#myModal").modal('show');
});
$('.no-delete').click(function(){
  $("#myModal").addClass('fade');
  $("#myModal").modal('hide');
});
$('.yes-delete').click(function(){
console.log('projectid',projectid);
$("#myModal").modal('hide');
var url = '{{ route("project-delete",":id",) }}';
      url1 = url.replace(':id', projectid);
      window.location.href=url1;
});
});
</script>

@endsection

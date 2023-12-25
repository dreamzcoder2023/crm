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
@section('title', 'View | HOUSE FIX - A DOCTOR FOR YOUR HOUSE')

@section('content')
<!-- <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Forms/</span></h4> -->
<!-- Basic Layout & Basic with Icons -->
<div class="container">
    <div class="row" style="background-color: #f0f0f0;">
        <div class="col-md-3 border-end"> <!-- Reduced the column size -->
            <h5 class="fw-bold py-3 mb-4">
                @if($user->image != '' || $user->image != null)
                    <input type="hidden" name="image_status" value="{{ $user->image }}">
                    <div style="width: 100px; height: 100px; border-radius: 50%; overflow: hidden; display: flex; justify-content: center; align-items: center;"> <!-- Centering using flexbox -->
                        <img src="{{url('public/images/'.$user->image)}}" width="100%" height="100%" style="object-fit: cover;"> <!-- Maintain aspect ratio and fill the circular container -->
                    </div>
                    <br/><br/>
                @endif{{$user->first_name}} {{$user->last_name}}
            </h5>
        </div>
        <div class="col-md-9 border-start"> <!-- Adjusted the column size -->
            <div style="margin-top: 20px;">
                <b>Total working hours<small>(last 30 days)</small>: {{$hours}}</b>
                <!-- Add working hours content here -->
            </div>
        </div>
    </div>
</div>


        <div class="row" style="position:absolute; top:100px; right:50px;  ">
  <div class="col-md-12">
        <ul class="nav nav-pills flex-column flex-md-row mb-3">
      <li class="nav-item"><a class="nav-link active" href="{{route('user-index')}}"><i class="bx me-1"></i> Back </a></li>

    </ul>
  </div></div>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<ul class="nav nav-tabs">
   <li  class="active"><a href="#general-info" data-toggle="tab">General Info</a></li>
   <li ><a href="#job-info" data-toggle="tab">Job Info</a></li>
   @can('expenses-history')
   <li ><a href="#expenses-info" data-toggle="tab">Expenses</a></li>
   @endcan
   <li><a href="#attendance-info" data-toggle="tab">Attendance</a></li>
</ul>
<div class="tab-content">
   <div class="tab-pane active" id="general-info">
      <div class="row">
        <div class="col-xl">
          <div class="card mb-4" style="margin-top:30px;">
            <div class="card-header d-flex justify-content-between align-items-center">

            </div>

            <div class="card-body">
              <div class="row">
                  <div class="col-6"><div class="mb-3">
                  <label class="form-label" for="basic-default-fullname">First Name :</label> {{$user->first_name}}
                </div>
                <div class="mb-3">
                  <label class="form-label" for="basic-default-company">Last Name :</label> {{$user->last_name}}

                </div>
                <div class="mb-3">
                  <label class="form-label" for="basic-default-email">Email :</label> {{$user->email}}
                    </div>
              </div>
                  <div class="col-6"><div class="mb-3">
                  <label class="form-label" for="basic-default-phone">Phone Number :</label> {{$user->phone}}
                    </div>
                <div class="mb-3">
                  <label class="form-label" for="basic-default-message">Gender :</label> @if($user->gender == 1) Male @elseif($user->gender == 2) Female @else Others @endif<br>
                  </div>

                <div class="mb-3">

                  <label class="form-label" for="basic-default-phone">Roles : </label> {{$user->role_name}}
                </div>

              </div>

              </div>
            </div>
          </div>
        </div>
      </div>
</div>
<div class="tab-pane" id="job-info">
    <div class="row">

      <div class="col-xl">
        <div class="card mb-4" style="margin-top:30px;">
          <div class="card-header d-flex justify-content-between align-items-center">

          </div>

          <div class="card-body">
            <div class="row">
                <div class="col-6"><div class="mb-3">
                <label class="form-label" for="basic-default-phone">Job Title :</label>  {{$user->job_title}}
              </div>
              <div class="mb-3">
              <label class="form-label" for="basic-default-phone">Salary : </label>  {{$user->salary}}

              </div>


              </div>
                <div class="col-6"><div class="mb-3">
                <label class="form-label" for="datetimepicker1">Date of joining :</label>  {{$user->date_of_joining}}<br>

              <div class="mb-3">
              <label class="form-label" for="basic-default-phone">Upload Government Document :</label> @if($user->government_image != '' && $user->government_image != null) <a href="{{url('public/images/'.$user->government_image) }}" target="_blank">View
                              </a>@else -- @endif

              </div>

            </div>

            </div>



          </div>
        </div>
      </div>

    </div>
</div></div>
<div class="tab-pane" id="expenses-info">
<div class="card" style="max-width: 1200px; margin: 22px auto; height:250px">
  <!-- <h5 class="card-header">Table Basic</h5> -->
  <div class="table-responsive text-nowrap">
    <table class="table" id="unpaid_expenses_listing_table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Category Name</th>
          <th>Project Name</th>
          <th>Amount</th>

          <th>Image</th>
          <th>Payment Mode</th>
          <th>Description</th>
          <th>Paid</th>
          <th>Unpaid</th>
          <th>Advanced Amount</th>
          @role('Admin')
          <th>Added By</th>

          <th>Edited By</th>
          @endrole
          <th>Paid date</th>

        </tr>
      </thead>
      <tbody class="table-border-bottom-0">

        @foreach($expenses as $expense)
       <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{$expense->category_name ? $expense->category_name : '--'}}</td>
        <td>{{$expense->project_name ? $expense->project_name : '--'}}</td>
        <td>{{$expense->amount}}</td>
        <td>@if($expense->image != '' || $expense->image != null) <img src={{url('public/images/'.$expense->image) }}" width="50px"> @endif</td>
        <td>{{$expense->payment_name}}</td>
        <td>{{$expense->description? $expense->description : '--'}}</td>
        <td><span style="color: green;">{{$expense->paid_amt}}</td>
        <td>@if($expense->unpaid_amt !=0)<a href="{{ route('unpaidex-create',$expense->id) }}"  style="color:red">{{$expense->unpaid_amt}}</a> @else {{$expense->unpaid_amt}}@endif</td>
        <td>{{$expense->extra_amt}}</td>
        @role('Admin')
        <td>{{$expense->first.''.$expense->last}}</td>
        <td>{{$expense->first_name.''.$expense->last_name}}</td>
        @endrole
        @if(!empty($unpaid_date))
        <td>{{$unpaid_date->updated_at}}</td>
        @else<td>{{$expense->created_at}}</td>
        @endif

       </tr>
       @endforeach

      </tbody>
    </table>
  </div>
</div>
</div>
<div class="tab-pane" id="attendance-info">
<div class="card" style="margin-top: 0px;">
  <!-- <h5 class="card-header">Table Basic</h5> -->
  <div class="table-responsive text-nowrap">
    <table class="table" id="attendance_listing_table">
      <thead>
        <tr>

          <th>Login</th>
          <th>Logout</th>
          <th>Duration</th>

        </tr>
      </thead>
      <tbody class="table-border-bottom-0">

        @foreach($attendance as $attendance)
       <tr>
        <td>{{$attendance->created_at ? $attendance->created_at : '--'}}</td>
        <td>{{$attendance->updated_at ? $attendance->updated_at : '--'}}</td>
        <td>{{$attendance->duration}}</td>

       </tr>
       @endforeach

      </tbody>
    </table>
  </div>
</div>
</div>
</div>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
  $(document).ready(function() {
var data =  new DataTable('#unpaid_expenses_listing_table', {
  "lengthMenu": [15, 50, 100],
  processing: true,

});
});
$(document).ready(function(){
  var data = new DataTable('#attendance_listing_table',{
    "lengthMenu": [15, 50, 100],
    processing: true,
  });
});

</script>
@endsection

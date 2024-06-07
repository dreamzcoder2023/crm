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
table {
  width:50%;
  border-spacing: 0; /* Remove spacing between cells */
  border-collapse: collapse; /* Collapse cell borders */
}

td, th {
  padding: 5px; /* Reduce cell padding */
}

  </style>
@section('title', 'Show | HOUSE FIX - A DOCTOR FOR YOUR HOUSE')

@section('content')


@if(session()->has('message'))
    <div class="alert alert-success">
        {{ session()->get('message') }}
    </div>
@endif
@if(session()->has('msg'))
    <div class="alert alert-danger">
        {{ session()->get('msg') }}
    </div>
@endif
<div style="margin-top: 30px;">
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Expenses Details </span>
</h4>
<div class="row" style="position:absolute; top:160px; right:50px ">
  <div class="col-md-12">
    <ul class="nav nav-pills flex-column flex-md-row mb-3">
      <li class="nav-item"><a class="nav-link active" href="{{route('project-index')}}"> Back</a></li>

    </ul>
  </div></div></div>
<!-- Basic Bootstrap Table -->
<div class="card" style="max-width: 1200px; margin: 40px auto; height:250px">
  <!-- <h5 class="card-header">Table Basic</h5> -->
  <div class="table-responsive text-nowrap">
    <table class="table" id="show_expense_listing_table">
      <thead>

        <tr>
          <th>ID</th>
          <th>Category Name</th>
          <th>Amount</th>
          <th>Paid Amount</th>
          <th>Unpaid Amount</th>
          <th>Description</th>
          <th>Received Date</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">

        @foreach($expenses as $expense)
       <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{$expense->category_name ? $expense->category_name : '--'}}</td>
        <td><b><span style="color:#ef6a0e">{{$expense->amount}}</span></b></td>
        <td><b><span style="color: green;">{{$expense->paid_amt}}</span></b></td>
        <td> <b><span style="color: red;">{{$expense->unpaid_amt}}</span></b></td>
        <td>{{$expense->description? $expense->description : '--'}}</td>
        <td>{{$expense->current_date}}</td>
       </tr>
       @endforeach

      </tbody>
    </table>
  </div>
</div>
<!--/ Basic Bootstrap Table -->


<p class="text-end" style="margin-top: 53px; margin-right: 147px; font-size: medium;">
    <span class="d-inline"><b>Total Advance Amount:</b> <b><span style="color:#ef6a0e">{{App\Models\Expenses::where('project_id',$project_id)?->sum('amount')}}</span></b></span>
    <span class="d-inline ms-3"><b>Total paid Amount:</b> <b><span style="color: green;">{{App\Models\Expenses::where('project_id',$project_id)?->sum('paid_amt')}}</span></b></span>
    <span class="d-inline ms-3"><b>Total unpaid Amount:</b> <b><span style="color: red;">{{App\Models\Expenses::where('project_id',$project_id)?->sum('unpaid_amt')}}</span></b></span>

</p>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
  $(document).ready(function() {
var data =  new DataTable('#show_expense_listing_table', {
  "lengthMenu": [15, 50, 100],
  processing: true,

});
});
</script>
@endsection

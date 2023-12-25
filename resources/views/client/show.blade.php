@extends('layouts/contentNavbarLayout')

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

@section('title', 'View | HOUSE FIX - A DOCTOR FOR YOUR HOUSE')
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
  <span class="text-muted fw-light">Project Details </span>
</h4>
<div class="row" style="position:absolute; top:160px; right:50px ">
  <div class="col-md-12">
    <ul class="nav nav-pills flex-column flex-md-row mb-3">
      <li class="nav-item"><a class="nav-link active" href="{{route('client-index')}}"> Back</a></li>

    </ul>
  </div></div></div>
  <div class="card">
    <div class="card-header">
        <div class="container text-center">
            <div class="row aa">

                <div class="col">
                    <label >from</label>
                    <input type="date" class="form-control bb" id="from_date" name="from_date"
                        value="{{$from_date}}"
                       >
                </div>
                <div class="col">
                    <label>to</label>
                    <input type="date" class="form-control " id="to_date" name="to_date"
                        value="{{$to_date}}"
                         >
                </div>
                <div class="col">
                    <a href="{{route('client-show',$client_id)}}"><img src="{{asset('assets/img/icons/clearfilter.png')}}"
                            alt="slack" class="me-3" height="40" width="40"></a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Basic Bootstrap Table -->
<div class="card" style="max-width: 1200px; margin: 22px auto; height:250px">
<input type="hidden" name="client_id" id="client_id" value="{{$client_id}}">
  <!-- <h5 class="card-header">Table Basic</h5> -->
  <div class="table-responsive text-nowrap">
    <table class="table" id="show_expense_listing_table">
      <thead>

        <tr>
          <th>ID</th>
          <th>Project Name</th>
          <th>Advanced Amount</th>
          <th>Total Amount</th>
          <th>Remaining</th>
          <!-- <th>Payment Mode</th>  -->
          <th>Project Status</th>
          <th>Start Date</th>
          <th>End Date</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">

        @foreach($projects as $project)
       <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{$project->name ? $project->name : '--'}}</td>
        <td><b><span style="color:#ef6a0e">{{$project->advance_amt}}</span></b></td>
        <td><b><span style="color: green;">{{$project->total_amt}}</span></b></td>
        <td><b><span style="color: red;">{{$project->profit}}</span></b></td>
        <!-- <td>{{$project->payment_name}}</td> -->
        <td>@if($project->project_status == 0 )<button type="button" class="btn btn-success">Active</button>@else
<button type="button" class="btn btn-danger">De-active</button>@endif</td>
<td>{{$project->start_date}}</td>
<td>{{$project->end_date}}</td>
       </tr>
       @endforeach

      </tbody>
    </table>
  </div>
</div>
<!--/ Basic Bootstrap Table -->

<p class="text-end" style="margin-top: 53px; margin-right: 147px; font-size: medium;">
    <span class="d-inline"><b>Total Advanced Amount:</b> <b><span style="color:#ef6a0e">{{$sum}}</span></b></span>
    <span class="d-inline ms-3"><b>Total Amount:</b> <b><span style="color: green;">{{$total}}</span></b></span>
    <span class="d-inline ms-3"><b>Total Remaining Amount:</b> <b><span style="color: red;">{{$remaining}}</span></b></span>

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
$(document).ready(function(){
          $('#unpaid-popup').modal('hide');

          var from_date=[];
          var end_date=[];
        var user = $('#client_id').val();

      $('#from_date').change(function(){



        from_date=$('#from_date').val();
        end_date = $('#to_date').val();
       console.log(from_date);
       if(from_date != ''){
         reset_table(from_date,end_date);
       }
     });
     $('#to_date').change(function(){


        from_date=$('#from_date').val();
        end_date = $('#to_date').val();
       console.log(end_date);
       if(end_date != ''){
         reset_table(from_date,end_date);
       }
     });
     function reset_table(from_date,to_date){

    from_date = from_date;
    end_date = to_date;
    var url = '{{ route("client-show",":id") }}';
    url1 = url.replace(':id', user);
      window.location.href=url1+'?from_date='+from_date+'&to_date='+to_date;
   }

  });
</script>
@endsection

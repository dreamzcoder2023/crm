@extends('layouts/contentNavbarLayout')

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.1/css/bootstrap-select.css">

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
.dropdown-toggle{
  width:146px !important;
}

.bs-caret::after{
  color:#f7f7f7 !important;
  content: "";
  display:none !important;
}
  </style>
@section('title','List | HOUSE FIX - A DOCTOR FOR YOUR HOUSE')

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
@if (session()->has('transfer-popup'))
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

<script>
    $(function() {
            toastr.success('{{ session('transfer-popup') }}', {
                timeOut: 1000,
                fadeOut: 1000,
            });
        });
</script>
@endif
<div style="margin-top: 30px;">
<h4 class="fw-bold py-3 mb-4" style="margin-top: -55px;font-size:13px;">
  <span class="fw-light" style="color: black;">Transfer History </span>
</h4>
<div class="row" style="position:absolute; top:180px; right:50px ">
  <div class="col-md-12">
    <!-- @can('transfer-create') -->
    <!-- <ul class="nav nav-pills flex-column flex-md-row mb-3">
      <li class="nav-item"><a class="nav-link active" href="{{route('transfer-create')}}"><i class="bi bi-currency-exchange me-1"></i> Add Transfer</a></li>

    </ul> -->
    <!-- @endcan -->
  </div></div></div>
  <div class="card">
    <div class="card-header">
        <div class="container text-center">
            <div class="row aa">

            @role('Admin')
                <div class="col-md-2">
                    <select class="form-control selectpicker" name="user_id" id="user_id" data-live-search="true">
                        <option value="">Select Member</option>
                        @foreach($user as $user)
                        <option value="{{$user->id}}"{{$user->id == $user_filter ? 'selected' : ''}}>{{$user->first_name}} {{$user->last_name}} - {{$user->name}}</option>
                        @endforeach
                    </select>
                </div>
               @endrole
                <div class="col-md-4">
                   <h6> <span>From:&nbsp;&nbsp;</span>
                    <input type="date" class="form-control bb" id="from_date" name="from_date"
                        value="{{$from_date}}" style="width: 145px;display:initial;"></h6>

                </div>
                <div class="col-md-4">
                    <h6><span>To:</span>
                    <input type="date" class="form-control " id="to_date" name="to_date"
                        value="{{$to_date1}}" style="width: 145px;display:initial;"></h6>

                </div>
                <div class="col-md-2">
                    <a href="{{route('transfer-history')}}"><img src="{{asset('assets/img/icons/clearfilter.png')}}"
                            alt="slack" class="me-3" height="25" width="25"></a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Basic Bootstrap Table -->
<div class="card"  style="max-width: 1200px; top:10px; height:550px">
  <!-- <h5 class="card-header">Table Basic</h5> -->
  <div class="table-responsive text-nowrap">
    <table class="table" id="transfer_listing_table">
      <thead>
        <tr>
          <th>ID</th>
          @role('Admin')
          <th>From Member Name</th>
          @endrole
          <th>To Member Name</th>
          <th>Amount</th>
          <th>Payment Mode</th>
          <th>Description</th>
          <th>Received Date</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">

        @foreach($transfers as $transfer)
       <tr>
        <td>{{ $loop->index+1}}</td>
        @role('Admin')
        <td>{{$transfer->firstname}} {{$transfer->lastname}}</td>
        @endrole
        <td>{{$transfer->first_name}} {{$transfer->last_name}}</td>
        <td>{{$transfer->amount}}</td>
        <td>{{$transfer->payment_name}}</td>
        <td>{{$transfer->description? $transfer->description : '--'}}</td>
        <td>{{$transfer->current_date}}</td>
       </tr>
       @endforeach

      </tbody>
    </table>
  </div>
</div>
<!--/ Basic Bootstrap Table -->





<p class='text-end' style="margin-top:30px;
    margin-right: 249px;
    font-size: medium;"><b>Total Amount:</b>{{$role == 1 ? App\Models\Transfer::sum('amount') : App\Models\Transfer::where('user_id',Auth::user()->id)->sum('amount')}}
<br/>&nbsp;</p>
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
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.1/js/bootstrap-select.js"></script>
    <script>
        $(document).ready(function() {
            $('.selectpicker').selectpicker();
        });
  $(document).ready(function() {
var data =  new DataTable('#transfer_listing_table', {
  "lengthMenu": [15, 50, 100],
  processing: true,

});
});
$(document).ready(function(){
          $('#unpaid-popup').modal('hide');
          var user =[];
          var from_date=[];
          var end_date=[];


      $('#user_id').change(function(){

         user =$('#user_id').find(":selected").val();

         from_date=$('#from_date').val();
         end_date = $('#to_date').val();
        console.log(user);
        if(user != ''){
          reset_table(from_date,end_date,user);
        }
      });
      $('#from_date').change(function(){

        user =$('#user_id').find(":selected").val();

        from_date=$('#from_date').val();
        end_date = $('#to_date').val();
       console.log(from_date);
       if(from_date != ''){
         reset_table(from_date,end_date,user);
       }
     });
     $('#to_date').change(function(){

        user =$('#user_id').find(":selected").val();

        from_date=$('#from_date').val();
        end_date = $('#to_date').val();
       console.log(end_date);
       if(end_date != ''){
         reset_table(from_date,end_date,user);
       }
     });
     function reset_table(from_date,to_date,user){

    from_date = from_date;
    end_date = to_date;
    var url = '{{ route("transfer-history") }}';
      window.location.href=url+'?from_date='+from_date+'&to_date='+to_date+'&user_id='+user;
   }

  });

</script>
@endsection

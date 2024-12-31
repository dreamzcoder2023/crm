@extends('layouts/contentNavbarLayout')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
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
#unpaid_expenses_listing_table th,
#unpaid_expenses_listing_table td {
  width: 10%; /* Adjust the width as needed */
  font-size: 13px; /* Adjust the font size as needed */
  white-space: nowrap; /* Prevent text from wrapping */
  text-overflow: ellipsis; /* Add ellipsis for long text */
  overflow: hidden; /* Hide overflowing content */
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

@if (session()->has('expenses-popup'))
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

<script>
    $(function() {
    toastr.success('{{session('expenses-popup')}}',{
      timeOut:1000,
      fadeOut:1000,
    });
    });
</script>
@endif
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
<div style="margin-top:-32px ;">

<h4 class="fw-bold py-3 mb-4">
  <span class="fw-light" style="color: black;font-size:16px;">Unpaid Expenses History </span>

</h4>

<div class="row" style="position:absolute;  right:50px ">
  <div class="col-md-12">

    <!-- @can('transfer-create') -->
    <!-- <ul class="nav nav-pills flex-column flex-md-row mb-3">
      <li class="nav-item"><a class="nav-link active" href="{{route('transfer-create')}}"><i class="bi bi-currency-exchange me-1"></i> Add Transfer</a></li>

    </ul> -->
    <!-- @endcan -->
  </div></div></div>

  <div class="card" style="top:-28px;">

    <div class="card-header">
        <div class="container text-center">
          <div style="float: right"> <!-- Reduce the column size from 1 to 2 -->
            <a href="{{route('unpaid-history')}}"><img src="{{asset('assets/img/icons/clearfilter.png')}}"
              alt="slack" class="me-3" height="25" width="25"></a>
              <button type="button" class="btn btn-light" id="unpaidexpense-export" ><img src="{{ asset('assets/img/icons/excel.png') }}" style="height: 25px;width:25px;" alt=""></button>
              <button type="button" class="btn btn-light" id="unpaidexpense-pdf" ><img src="{{ asset('assets/img/icons/file.png') }}" style="height: 25px;width:25px;" alt=""></button>

            </div>
            <div class="row aa">



            <div class="col-md-2">
              <select class="form-group selectpicker" name="category_id" id="category_id"
                  data-live-search="true" style="width:50px;">
                  <option value="">Select category</option>
                  @foreach ($category as $category)
                      <option
                          value="{{ $category->id }}"{{ $category->id == $category_filter ? 'selected' : '' }}>
                          {{ $category->name }}</option>
                  @endforeach
              </select>
          </div>
          <div class="col-md-2"><select class="form-group selectpicker" name="project_id"
                  id="project_id" data-live-search="true">
                  <option value="">Select Project</option>
                  @foreach ($project as $project)
                      <option
                          value="{{ $project->id }}"{{ $project->id == $project_filter ? 'selected' : '' }}>
                          {{ $project->name }}</option>
                  @endforeach
              </select></div>
          @role('Admin') <div class="col-md-2"><select class="form-group selectpicker"
                      name="user_id" id="user_id" data-live-search="true">
                      <option value="">Select Member</option>
                      @foreach ($user as $user)
                          <option
                              value="{{ $user->id }}"{{ $user->id == $user_filter ? 'selected' : '' }}>
                              {{ $user->first_name }} {{ $user->last_name }} -
                              {{ $user->name }}</option>
                      @endforeach
              </select></div> @endrole

                <div class="col-md-3"> <!-- Reduce the column size from 1 to 2 -->
                  <span> <label>From:&nbsp;</label>
                    <input type="date" class="form-control bb" id="from_date" name="from_date"
                        value="{{ $from_date }}" style="width: 144px;display:initial;"></span>
                </div>
                <div class="col-md-3"> <!-- Reduce the column size from 1 to 2 -->
                    <label>To</label>
                    <input type="date" class="form-control" id="to_date" name="to_date"
                        value="{{ $to_date1 }}" style="width: 144px;display:initial;">
                </div>





            </div>
        </div>
    </div>
</div>
<!-- Basic Bootstrap Table -->
<div class="card" style="max-width: 1200px; top:-11px; height:516px">
  <!-- <h5 class="card-header">Table Basic</h5> -->
  <div class="table-responsive text-nowrap" >
    <table class="table" id="unpaid_expenses_listing_table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Paid date</th>
          <th>Category <br/> Name</th>
          <th>Project Name</th>
          <th>Amount</th>
          <th>Paid</th>
          <th>Unpaid</th>
          <th>Advanced <br/> Amount</th>
          <th>Description</th>
          <th>Image</th>
          <th>Payment Mode</th>


          @role('Admin')
          <th>Added By</th>

          <th>Edited By</th>
          @endrole

          @canany(['expenses-edit','expenses-delete'])
          <th>Action</th>
          @endcanany
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">

        @foreach($expenses as $expense)
       <tr>
        <td>{{ $loop->index+1}}</td>
      <td>{{\Carbon\Carbon::parse($expense->current_date)->format('d-m-Y ')}} <br/>{{\Carbon\Carbon::parse($expense->current_date)->format('h:i A ')}}</td>

        <td>{{$expense->category_name ? $expense->category_name : '--'}}</td>
        <td>{{$expense->project_name ? $expense->project_name : '--'}}</td>
        <td><b><span style="color:#ef6a0e">{{$expense->amount}}</span></b></td>
        <td><b><span style="color: green;">{{$expense->paid_amt}}</span></b></td>
        <td>@if($expense->unpaid_amt !=0)<b><a href="{{ route('unpaidex-create',$expense->id) }}"  style="color:red">{{$expense->unpaid_amt}}</a></b> @else <b><span style="color:red"> {{$expense->unpaid_amt}}</span></b>@endif</td>
        <td><b><span style="color: #840eef;">{{$expense->extra_amt}}</span></b></td>
        <td>{{$expense->description? $expense->description : '--'}}</td>
        <td>@if($expense->image != '' || $expense->image != null) <img src="public/images/{{ $expense->image }}" width="50px"> @endif</td>
        <td>{{$expense->payment_name}}</td>


        @role('Admin')
        <td>{{$expense->first.''.$expense->last}}</td>
        <td>{{$expense->first_name.''.$expense->last_name}}</td>
        @endrole

         @canany(['expenses-unpaid edit'])
       
        @can('expenses-unpaid edit')
        <td>
        <a class="" href="{{ route('unpaid-create',$expense->id) }}"><i class="bi bi-pencil-square" style="font-size:24px;color:green"></i></a>
      </td>
        @endcan
      
      @endcanany
       </tr>
       @endforeach

      </tbody>
    </table>
  </div>
</div>
<!--/ Basic Bootstrap Table -->



<p class="text-end" style="margin-top: 6px; margin-right: 147px; font-size: medium;">
    <span class="d-inline"><b>Total Amount:</b> <b><span style="color:#ef6a0e">{{$sum}}</span></b></span>
    <span class="d-inline ms-3"><b>Total Paid Amount:</b> <b><span style="color: green;">{{$paid_amt}}</span></b></span>
    <span class="d-inline ms-3"><b>Total Unpaid Amount:</b> <b><span style="color: red;">{{$unpaid_amt}}</span></b></span>
    <span class="d-inline ms-3"><b>Total Advanced Amount:</b><b><span style="color: #840eef;"> {{$advanced_amt}}</span></b></span>
</p>
<!--- modal popup for transfer -->
<div class="modal fade" id="unpaid-popup" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" >
    <div class="modal-dialog d-flex justify-content-center">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="unpaid_title">Unpaid Details</h5>
                <button type="button" class="btn-close unpaid-close" data-mdb-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
               <div class="unpaidform"></div>
            </div>
        </div>
    </div>
</div>
<!--- modal popup for transfer -->

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
  <div class="modal fade" id="myModal_reason" role="dialog">
    <div class="modal-dialog modal-sm">

      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Reason</h4><hr>
        </div>
        <div class="modal-body">
        <input  type="text"  id="reason" name="reason" class="form-control" placeholder="Enter reason"  value=""/>
              <label id="reason-error" class="error"  style="color:red" for="basic-default-email">Reason is required</label>
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-primary yes-reason" data-dismiss="modal">Submit</button>
          <button type="button" class="btn btn-danger no-reason" data-dismiss="modal">cancel</button>
        </div>
      </div>

    </div>
  </div>

<!-- modal popup for delete role ended -->

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
          $(document).ready(function() {
           // $('.selectpicker').selectpicker();
        });
        $('#category_id').select2({
        placeholder: "Select",
        allowClear: true,
        width: '100%',
    });
    $('#project_id').select2({
        placeholder: "Select",
        allowClear: true,
        width: '100%',
    });
    $('#user_id').select2({
        placeholder: "Select",
        allowClear: true,
        width: '100%',
    });
  $(document).ready(function() {
var data =  new DataTable('#unpaid_expenses_listing_table', {
  "lengthMenu": [15, 50, 100],
  processing: true,

});
});
      $("document").ready(function(){
  var roleid; var user;
      setTimeout(function(){
       $("div.alert").remove();
    }, 5000 ); // 5 secs
    $("#unpaid_expenses_listing_table").on("click", ".deleteExpense", function(){

  roleid = $(this).attr('data-id');
  user = $(this).attr('data-user');
  $("#myModal").removeClass('fade');
  $("#myModal").modal('show');
});
$('.no-delete').click(function(){
  $("#myModal").addClass('fade');
  $("#myModal").modal('hide');
});
$('.no-reason').click(function(){
  $("#myModal_reason").addClass('fade');
  $("#myModal_reason").modal('hide');
});
$('.yes-delete').click(function(){
console.log('roleid',roleid);
$('#myModal').modal('hide');
$('#myModal_reason').modal('show');
// $("#myModal").modal('hide');
// var url = '{{ route("expenses-delete",":id",) }}';
//       url1 = url.replace(':id', roleid);
//       window.location.href=url1;
});
$('.yes-reason').click(function(){
console.log('roleid',roleid);
var reason = $('#reason').val();
console.log('reason',reason);
if(reason == ''){
$('#reason-error').removeClass('hide');
}else{
  $('#reason-error').addClass('hide');
  $("#myModal_reason").modal('hide');
var url = '{{ route("unpaid-delete") }}';

      window.location.href=url+'?id='+roleid+'&reason='+reason+'&user='+user;
}
});
});
      $(document).ready(function(){
          $('#unpaid-popup').modal('hide');
      });
      $(document).ready(function(){
          $('#unpaid-popup').modal('hide');
      });
          // var category=[];
          // var amount =[];
          // var project=[];
          // var user =[];
          // var from_date=[];
          // var end_date=[];


      $('#category_id').change(function(){

       var project =$('#project_id').find(":selected").val();
        var category =$('#category_id').find(":selected").val();
       var  user =$('#user_id').find(":selected").val();
         var amount =$('#amount_id').find(":selected").val();
        var from_date=$('#from_date').val();
        var end_date = $('#to_date').val();
        if(category != ''){
          reset_table(from_date,end_date,category,project,user);
        }
      });
      // $('#amount_id').change(function(){

      //   amount =$('#amount_id').find(":selected").val();
      //   project =$('#project_id').find(":selected").val();
      //    category =$('#category_id').find(":selected").val();
      //    user =$('#user_id').find(":selected").val();
      //    from_date=$('#from_date').val();
      //    end_date = $('#to_date').val();
      //   if(amount != ''){
      //     reset_table(from_date,end_date,category,project,user,amount);
      //   }
      // });
      $('#project_id').change(function(){

        var project =$('#project_id').find(":selected").val();
        var category =$('#category_id').find(":selected").val();
        var user =$('#user_id').find(":selected").val();
        var amount =$('#amount_id').find(":selected").val();
        var from_date=$('#from_date').val();
        var end_date = $('#to_date').val();
        console.log('project',project);
        console.log('category',category);
        console.log('from_date',from_date);

        if(project != ''){
          reset_table(from_date,end_date,category,project,user);
        }
      });
      $('#user_id').change(function(){

        var user =$('#user_id').find(":selected").val();
        var project =$('#project_id').find(":selected").val();
        var category =$('#category_id').find(":selected").val();
        var amount =$('#amount_id').find(":selected").val();
        var from_date=$('#from_date').val();
        var end_date = $('#to_date').val();
        console.log(user);
        if(user != ''){
          reset_table(from_date,end_date,category,project,user);
        }
      });
      $('#from_date').change(function(){

       var user =$('#user_id').find(":selected").val();
       var project =$('#project_id').find(":selected").val();
      var  category =$('#category_id').find(":selected").val();
       var amount =$('#amount_id').find(":selected").val();
       var from_date=$('#from_date').val();
       var end_date = $('#to_date').val();
       console.log(from_date);
       if(from_date != ''){
         reset_table(from_date,end_date,category,project,user);
       }
     });
     $('#to_date').change(function(){

      var  user =$('#user_id').find(":selected").val();
      var  project =$('#project_id').find(":selected").val();
       var category =$('#category_id').find(":selected").val();
      var  amount =$('#amount_id').find(":selected").val();
      var  from_date=$('#from_date').val();
      var  end_date = $('#to_date').val();
       console.log(end_date);
       if(end_date != ''){
         reset_table(from_date,end_date,category,project,user);
       }
     });
     function reset_table(from_date,to_date,category,project,user){
    console.log('category',category);
    from_date = from_date;
    end_date = to_date;
    var url = '{{ route("unpaid-history") }}';
      window.location.href=url+'?from_date='+from_date+'&to_date='+to_date+'&category_id='+category+'&project_id='+project+'&user_id='+user;
   }

//  });
  $('#unpaidexpense-export').click(function(){
    console.log('test');
    var user =$('#user_id').find(":selected").val();
      var  project =$('#project_id').find(":selected").val();
      var  category =$('#category_id').find(":selected").val();

     var   from_date=$('#from_date').val();
       var end_date = $('#to_date').val();
    var url = '{{ route("unpaidexpenses-export") }}';
      window.location.href=url+'?from_date='+from_date+'&to_date='+end_date+'&category_id='+category+'&project_id='+project+'&user_id='+user;
  });
  $('#unpaidexpense-pdf').click(function(){
    console.log('test1');
    var user =$('#user_id').find(":selected").val();
      var  project =$('#project_id').find(":selected").val();
      var  category =$('#category_id').find(":selected").val();

     var   from_date=$('#from_date').val();
       var end_date = $('#to_date').val();
    var url = '{{ route("unpaidexpenses-pdf") }}';
      window.location.href=url+'?from_date='+from_date+'&to_date='+end_date+'&category_id='+category+'&project_id='+project+'&user_id='+user;
  });
    </script>
@endsection

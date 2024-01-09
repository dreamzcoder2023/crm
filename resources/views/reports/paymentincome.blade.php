@extends('layouts/contentNavbarLayout')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
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
.dropdown-toggle{
  width:146px !important;
}
.bs-caret::after{
  color:#f7f7f7 !important;
  content: "";
  display:none !important;
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
@section('title', 'Report | HOUSE FIX - A DOCTOR FOR YOUR HOUSE')

@section('content')
<div style="margin-top: 30px;">
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Payment Income </span>
</h4>
</div>
<div class="card">
    <div class="card-header">
        <div class="container text-center">
            <div class="row aa">
                <div class="col">
                <select class="form-control" name="user_id" id="user_id">
                        <option value="">Select Client</option>
                        @foreach($user as $user)
                        <option value="{{$user->id}}"{{$user->id == $user_filter ? 'selected' : ''}}>{{$user->first_name}} {{$user->last_name}} - {{$user->name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col">
                    <label >from</label>
                    <input type="date" class="form-control bb" id="from_date" name="from_date"
                        value="{{$from_date}}"
                       >
                </div>
                <div class="col">
                    <label>to</label>
                    <input type="date" class="form-control " id="to_date" name="to_date"
                        value="{{$to_date1}}"
                         >
                </div>
                <div class="col-1"> <!-- Reduce the column size from 1 to 2 -->
                    <button type="button" class="btn btn-success" id="paymentincome-export">Excel</button>
                </div>
                <div class="col-1"> <!-- Reduce the column size from 1 to 2 -->
                    <button type="button" class="btn btn-primary" id="paymentincome-pdf">Pdf</button>
                </div>
                <div class="col">
                    <a href="{{route('payment-income',$id)}}"><img src="{{asset('assets/img/icons/clearfilter.png')}}"
                            alt="slack" class="me-3" height="40" width="40"></a>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- Basic Bootstrap Table -->
<div class="card" style="max-width: 1200px; margin: 22px auto; height:250px">
  <!-- <h5 class="card-header">Table Basic</h5> -->
  <input type="hidden" name="id" value="{{$id}}" id="id">
  <div class="table-responsive text-nowrap">
    <table class="table" id="payment_income_listing_table">
      <thead>
        <tr>
            <th>ID</th>
          <th>Client Name</th>
          <th>Received Amount</th>
          <th>Payment Mode</th>
          <th>Decription</th>
          <th>Stage</th>
          <th>Received Date</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">

        @foreach($project as $project)
       <tr>
       <td>{{ $loop->index+1}}</td>
        <td> {{$project->first_name}} {{$project->last_name}}</td>
        <td>{{$project->amount}}</td>
        <td>{{$project->payment_name}}</td>
        <td>{{$project->description}}</td>
        <td>{{$project->stage_name}}</td>
        <td>{{$project->current_date}}</td>
       </tr>
       @endforeach

      </tbody>
    </table>
  </div>
</div>
<!--/ Basic Bootstrap Table -->

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
  $(document).ready(function() {
var data =  new DataTable('#payment_income_listing_table', {
  "lengthMenu": [15, 50, 100],
  processing: true,

});
});
$(document).ready(function(){
          $('#unpaid-popup').modal('hide');
          var category=[];
          var amount =[];
          var project=[];
          var user =[];
          var from_date=[];
          var end_date=[];


      // $('#category_id').change(function(){

      //   project =$('#project_id').find(":selected").val();
      //    category =$('#category_id').find(":selected").val();
      //    user =$('#user_id').find(":selected").val();
      //    amount =$('#amount_id').find(":selected").val();
      //    from_date=$('#from_date').val();
      //    end_date = $('#to_date').val();
      //   if(category != ''){
      //     reset_table(from_date,end_date,category,project,user);
      //   }
      // });
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
      // $('#project_id').change(function(){

      //    project =$('#project_id').find(":selected").val();
      //    category =$('#category_id').find(":selected").val();
      //    user =$('#user_id').find(":selected").val();
      //    amount =$('#amount_id').find(":selected").val();
      //    from_date=$('#from_date').val();
      //    end_date = $('#to_date').val();
      //   console.log('project',project);
      //   console.log('category',category);
      //   console.log('from_date',from_date);

      //   if(project != ''){
      //     reset_table(from_date,end_date,category,project,user);
      //   }
      // });
      $('#user_id').change(function(){

         user =$('#user_id').find(":selected").val();
         project =$('#project_id').find(":selected").val();
         category =$('#category_id').find(":selected").val();
         amount =$('#amount_id').find(":selected").val();
         from_date=$('#from_date').val();
         end_date = $('#to_date').val();
        console.log(user);
        if(user != ''){
          reset_table(from_date,end_date,user);
        }
      });
      $('#from_date').change(function(){

        user =$('#user_id').find(":selected").val();
        project =$('#project_id').find(":selected").val();
        category =$('#category_id').find(":selected").val();
        amount =$('#amount_id').find(":selected").val();
        from_date=$('#from_date').val();
        end_date = $('#to_date').val();
       console.log(from_date);
       if(from_date != ''){
         reset_table(from_date,end_date,user);
       }
     });
     $('#to_date').change(function(){

        user =$('#user_id').find(":selected").val();
        project =$('#project_id').find(":selected").val();
        category =$('#category_id').find(":selected").val();
        amount =$('#amount_id').find(":selected").val();
        from_date=$('#from_date').val();
        end_date = $('#to_date').val();
       console.log(end_date);
       if(end_date != ''){
         reset_table(from_date,end_date,user);
       }
     });
     function reset_table(from_date,to_date,user){
    console.log('category',category);
    from_date = from_date;
    end_date = to_date;
    var id = $('#id').val();
    var url = '{{ route("payment-income",":id") }}';
    var url1 =  url.replace(':id', id);
      window.location.href=url1+'?from_date='+from_date+'&to_date='+to_date+'&category_id='+category+'&project_id='+project+'&user_id='+user;
   }

  });
  $('#paymentincome-export').click(function(){
    console.log('test');
    var user =$('#user_id').find(":selected").val();
    var id = $('#id').val();

     var   from_date=$('#from_date').val();
       var end_date = $('#to_date').val();
    var url = '{{ route("paymentincome-export") }}';
      window.location.href=url+'?id='+id+'&from_date='+from_date+'&to_date='+to_date+'&user_id='+user;
  });
  $('#paymentincome-pdf').click(function(){
    console.log('test1');
    var user =$('#user_id').find(":selected").val();
    var id = $('#id').val();

     var   from_date=$('#from_date').val();
       var end_date = $('#to_date').val();
    var url = '{{ route("paymentincome-pdf") }}';
      window.location.href=url+'?id='+id+'&from_date='+from_date+'&to_date='+to_date+'&user_id='+user;
  });
</script>

@endsection

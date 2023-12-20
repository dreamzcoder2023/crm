<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<!-- Basic Bootstrap Table -->
<div class="card" >
  <!-- <h5 class="card-header">Table Basic</h5> -->
  <div class="table-responsive text-nowrap">
    <table class="table" id="user_listing_table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Week</th>
          <th>Unpaid Amount</th>
          <th>Advance Amount</th>

        </tr>
      </thead>
      <tbody class="table-border-bottom-0">

        {{-- @foreach($users as $user)
       <tr>
        <td>{{$loop->index+1}}</td>
        <td>{{$user->week}}</td>
        <td>{{$user->unpaid_amt}}</td>
        <td>{{$user->extra_amt}}</td>

       </tr>
       @endforeach --}}

      </tbody>
    </table>
  </div>
</div>
<!--/ Basic Bootstrap Table -->




<!--- modal popup for delete role started--->




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


</script>



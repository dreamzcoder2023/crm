<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Include DataTables CSS and JS from CDN -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>

<!-- Basic Bootstrap Table -->
<style>
  .preloader {
    width: 100%;
    height: 100vh;
    background-color: rgba(255, 255, 255, 0.2);
    position: fixed;
    top: 0;
    left: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    backdrop-filter: blur(15px);
  }

  .loader {
    width: 15px;
    aspect-ratio: 1;
    border-radius: 50%;
    animation: l5 1s infinite linear alternate;
  }
</style>

<div class="card">
  <div class="table-responsive text-nowrap">
    <table class="table" id="user_listing_table">
      <thead>
        <tr>
          <th>Week</th>
          <th>Unpaid Amount</th>
          <th>Advance Amount</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach($start_labour_date as $user)
        @foreach($user['records'] as $record)
        <tr>
          <td><a href="javascript:void(0);" class="click_project_detail" data-start_date ="{{ $record->week_start_date }}"  data-end_date ="{{ $record->week_end_date }}" >{{$record->week_start_date}} - {{ $record->week_end_date }}</a></td>
          <td>{{ $record->unpaid_amt}}</td>
          <td>{{$record->advance_amt}}</td>
        </tr>
        @endforeach
        @endforeach
      </tbody>
    </table>
  </div>
</div>
<div class="modal fade" id="labour_project_popup" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
  <div class="modal-dialog modal-lg ">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalToggleLabel">Project details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="labour_loadingproject"></div>
      </div>

    </div>
  </div>
</div>
<!-- DataTables initialization -->
<script>
  $(document).ready(function () {
    var table = $('#user_listing_table').DataTable({
      "lengthMenu": [15, 25, 50, 100],
      "processing": true,
      "paging": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false,
    });

    table.on('processing.dt', function (e, settings, processing) {
      if (processing) {
        $('.preloader').fadeIn();
      } else {
        $('.preloader').fadeOut();
      }
    });
  });
  $('.click_project_detail').click(function(){
    var start_date = $(this).attr(data-start_date);
    var end_date = $(this).attr(data-end_date);
    $('.preloader').css('display','block');
    $.ajax({
    type:"get",
    url:"{{route('labour-expenses-index')}}",
    dataType:'json',
    success:function(html){
      console.log(html);

      $('.labour_loadingsalary').html(html);
      $('.preloader').css('display','none');
      $('#labour_total_popup').modal('show');
    }


  });
  });
</script>

@extends('layouts/contentNavbarLayout')

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
    integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<style>
    @media only screen and (max-width:320px) {
        .aa {
            display: inline !important;
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

    .table-responsive {
        margin-top: 5px;
        margin-left: 5px;


    }

    .cards {
        box-shadow: 0 2px 4px rgba(0, 0, 20, .08), 0 1px 2px rgba(0, 0, 20, .08);
        border: 0;
        border-radius: 0.5rem;
        width: 300px;
    }
    .icon-shape{
      display: inline-flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    vertical-align: middle;
    }
    .bg-light-primary {
    background-color:#b2d6e5 !important;
}
.icon-md {
    width: 2.5rem;
    height: 2.5rem;
    line-height: 2.5rem;
}
.dataTables_filter{
  text-align: center !important;
}
.pagination{
  justify-content: center !important;
  margin-left:-50px !important;
}
div.dataTables_wrapper div.dataTables_length select {
  width:60px !important;
}

@media (max-width: 767px) {
    .rows {
        display: block !important;
        margin-bottom:20px !important;
    }
    .cards{
      margin-bottom:30px !important;
    }
}
</style>

@section('title', 'Labour')

@section('content')

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    @if (session()->has('message'))
        <script>
            $(function() {
                $('.success-msg').text("{{ session('message') }}");
                $('#walletsuccess').removeClass('fade');
                $('#walletsuccess').modal('show');
            });
        </script>
    @endif
    @if (session()->has('msg'))
        <div class="alert alert-danger">
            {{ session()->get('msg') }}
        </div>
    @endif
    <div style="margin-top: 30px;">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Project Details </span>
        </h4>
    </div>
    <div class="row rows">
        <div class="col-4">
            <div class="card cards">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="mb-0">Project Name</h4>
                        </div>
                        <div class="icon-shape icon-md bg-light-primary text-primary rounded-2"><svg
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="18" height="18"
                                fill="currentColor">
                                <path
                                    d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v8A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-8A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1h-3zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5zm1.886 6.914L15 7.151V12.5a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5V7.15l6.614 1.764a1.5 1.5 0 0 0 .772 0zM1.5 4h13a.5.5 0 0 1 .5.5v1.616L8.129 7.948a.5.5 0 0 1-.258 0L1 6.116V4.5a.5.5 0 0 1 .5-.5z">
                                </path>
                            </svg></div>
                    </div>
                    <div>
                        <h3 class="fw-bold">{{ $project->project_name }}</h3>
                        {{-- <p class="mb-0"><span classname="text-dark me-2">2</span> Completed</p> --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card cards">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="mb-0">Unpaid Amount</h4>
                        </div>
                        <div class="icon-shape icon-md bg-light-primary text-primary rounded-2"><svg
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="18" height="18"
                                fill="currentColor">
                                <path
                                    d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v8A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-8A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1h-3zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5zm1.886 6.914L15 7.151V12.5a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5V7.15l6.614 1.764a1.5 1.5 0 0 0 .772 0zM1.5 4h13a.5.5 0 0 1 .5.5v1.616L8.129 7.948a.5.5 0 0 1-.258 0L1 6.116V4.5a.5.5 0 0 1 .5-.5z">
                                </path>
                            </svg></div>
                    </div>
                    <div>
                        <h3 class="fw-bold">{{ $project->unpaid_amt }}</h3>
                        {{-- <p class="mb-0"><span classname="text-dark me-2">2</span> Completed</p> --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card cards">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="mb-0">Advance Name</h4>
                        </div>
                        <div class="icon-shape icon-md bg-light-primary text-primary rounded-2"><svg
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="18" height="18"
                                fill="currentColor">
                                <path
                                    d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v8A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-8A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1h-3zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5zm1.886 6.914L15 7.151V12.5a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5V7.15l6.614 1.764a1.5 1.5 0 0 0 .772 0zM1.5 4h13a.5.5 0 0 1 .5.5v1.616L8.129 7.948a.5.5 0 0 1-.258 0L1 6.116V4.5a.5.5 0 0 1 .5-.5z">
                                </path>
                            </svg></div>
                    </div>
                    <div>
                        <h3 class="fw-bold">{{ $project->advance_amt }}</h3>
                        {{-- <p class="mb-0"><span classname="text-dark me-2">2</span> Completed</p> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Basic Bootstrap Table -->
    <div class="card" style="max-width: 1200px; margin: 40px auto; height:250px">
        <!-- <h5 class="card-header">Table Basic</h5> -->
        <div class="table-responsive text-nowrap">

            <table class="table" id="user_listing_table">

                <thead>
                    <tr>
                        <th><input type="checkbox" id="select_all" ></th>
                        <th>Name</th>
                        <th>Salary</th>
                        <th>Unpaid Amount</th>
                        <th>Advance Amount</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">

                    @foreach ($labour as $labour)
                        <tr>
                            <td><input type="checkbox" name="labour_id[]" class="days" id="{{ $labour->labour_id }}"
                                    value="{{ $labour->labour_id }}"></td>
                            <td><a style="text-decoration: none" href="javascript:void(0)" class="labour_details_weekly"
                                    style="cursor:pointer" data-start_week="{{ $start_date }}"
                                    data-end_week="{{ $end_date }}"
                                    data-labour_id="{{ $labour->labour_id }}">{{ $labour->labour_name }}</a></td>
                            <td>{{ $labour->amount }} </td>
                            <td>{{ $labour->unpaid_amt }}
                            <td>{{ $labour->advance_amt }}</td>
                        </tr>
                    @endforeach

                </tbody>

            </table>

        </div>
    </div>
    <button class="btn btn-primary" id="advance_submit" disabled>Submit</button>
    <!--/ Basic Bootstrap Table -->


    <!-- modal popup for delete role ended -->
    <div class="modal fade" id="labour_weeklypopup" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Labour Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="loadingsalary"></div>

            </div>
        </div>
    </div>
    <!-- modal popup for salary details -->


    <!-- modal popup for salary details -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            var data = new DataTable('#user_listing_table', {
                "lengthMenu": [15, 25, 50, 100],
                processing: true,

            });
        });

        $('#select_all').on('click', function() {
            if (this.checked) {
                $('.days').each(function() {
                    this.checked = true;
                });
                $('#advance_submit').prop('disabled',false);
            } else {
                $('.days').each(function() {
                    this.checked = false;
                });
                $('#advance_submit').prop('disabled',true);
            }
        });
        $('.days').on('click', function() {
          console.log($('.days:checked').length);

          if($('.days:checked').length != 0){
            $('#advance_submit').prop('disabled',false);
          }else{
            $('#advance_submit').prop('disabled',true);
          }
            if ($('.days:checked').length == $('.days').length) {
                $('#select_all').prop('checked', true);

            } else {
                $('#select_all').prop('checked', false);
            }
        });
        $('.labour_details_weekly').click(function() {
            var start_date = $(this).attr('data-start_week');
            var end_date = $(this).attr('data-end_week');
            var labour_id = $(this).attr('data-labour_id');
            $('.preloader').css('display', 'block');
            $.ajax({
                type: "get",
                url: "{{ route('labour-expenses-labour') }}",
                data: {
                    start_date: start_date,
                    end_date: end_date,
                    labour_id: labour_id
                },
                dataType: 'json',
                success: function(html) {
                    console.log(html);

                    $('.loadingsalary').html(html);
                    $('.preloader').css('display', 'none');
                    $('#labour_weeklypopup').modal('show');
                }
            });
        });
        $('#advance_submit').click(function(){
          var val = [];
        $('.days:checked').each(function(i){
          val[i] = $(this).val();
        });
        console.log('val',val);
        $.ajax({
                type: "get",
                url: "{{ route('labour-expenses-labour') }}",
                data: {
                    labour_id: val,
                    end_date: end_date,
                    labour_id: labour_id
                },
                dataType: 'json',
                success: function(html) {
                    console.log(html);

                    $('.loadingsalary').html(html);
                    $('.preloader').css('display', 'none');
                    $('#labour_weeklypopup').modal('show');
                }
            });
        });
    </script>

@endsection

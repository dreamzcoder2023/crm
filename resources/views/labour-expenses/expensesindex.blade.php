@extends('layouts/contentNavbarLayout')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
    integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/css/bootstrap-select.min.css">



<style>
    #expenses_listing_table th,
    #expenses_listing_table td {
        width: 10%;
        /* Adjust the width as needed */
        font-size: 13px;
        /* Adjust the font size as needed */
        white-space: nowrap;
        /* Prevent text from wrapping */
        text-overflow: ellipsis;
        /* Add ellipsis for long text */
        overflow: hidden;
        /* Hide overflowing content */
    }

    .bootstrap-select {
        max-width: 150px;
    }


    /* Customize the styling further if needed */

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

    table {
        width: 50%;
        border-spacing: 0;
        /* Remove spacing between cells */
        border-collapse: collapse;
        /* Collapse cell borders */
    }

    td,
    th {
        padding: 5px;
        /* Reduce cell padding */
    }
</style>
@section('title', 'List | HOUSE FIX - A DOCTOR FOR YOUR HOUSE')

@section('content')

    @if (session()->has('expenses-popup'))
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"
            integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

        <script>
            $(function() {
                $('.success-msg').text('Expenses Updated Successfully')
                $('#walletsuccess').removeClass('fade');
                $('#walletsuccess').modal('show');
            });
        </script>
    @endif
    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session()->get('message') }}
        </div>
    @endif
    @if (session()->has('msg'))
        <div class="alert alert-danger">
            {{ session()->get('msg') }}
        </div>
    @endif

    <div class="card" style="margin-top: 10px;">
        <div class="card-header">
            <div class="container justify-content-start">
                <div class="row aa">
                    <div class="col-4 col-lg-2">
                        <select class="form-group selectpicker" name="category_id" id="category_id" data-live-search="true">
                            <option value="">Select category</option>
                            @foreach ($category as $category)
                                <option
                                    value="{{ $category->id }}"{{ $category->id == $category_filter ? 'selected' : '' }}>
                                    {{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-2">
                        <select class="form-group selectpicker" name="project_id" id="project_id">
                            <option value="">Select Project</option>
                            @foreach ($project as $project)
                                <option value="{{ $project->id }}"{{ $project->id == $project_filter ? 'selected' : '' }}>
                                    {{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @role('Admin')
                        <div class="col-2">
                            <select class="form-group selectpicker" name="user_id" id="user_id">
                                <option value="">Select Member</option>
                                @foreach ($user as $user)
                                    <option value="{{ $user->id }}"{{ $user->id == $user_filter ? 'selected' : '' }}>
                                        {{ $user->first_name }} {{ $user->last_name }} - {{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endrole

                    <div class="col-2"> <!-- Reduce the column size from 1 to 2 -->
                        <label>From</label>
                        <input type="date" class="form-control bb" id="from_date" name="from_date"
                            value="{{ $from_date }}">
                    </div>
                    <div class="col-2"> <!-- Reduce the column size from 1 to 2 -->
                        <label>To</label>
                        <input type="date" class="form-control" id="to_date" name="to_date"
                            value="{{ $to_date1 }}">
                    </div>
                    <div class="col-1"> <!-- Reduce the column size from 1 to 2 -->
                        <a href="{{ route('expenses-history') }}" class="me-3">
                            <img src="{{ asset('assets/img/icons/clearfilter.png') }}" alt="clear filter" height="30"
                                width="30">
                        </a>
                    </div>
                    <div class="col-1"> <!-- Reduce the column size from 1 to 2 -->
                        <button type="button" class="btn btn-success" id="expense-export">Excel</button>
                    </div>
                    <div class="col-1"> <!-- Reduce the column size from 1 to 2 -->
                        <button type="button" class="btn btn-primary" id="expense-pdf">Pdf</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Basic Bootstrap Table -->
    <div class="card " style="max-width: 1200px; margin: 22px auto; height:450px">
        <!-- <h5 class="card-header">Table Basic</h5> -->
        <div class="table-responsive text-nowrap" style="padding:20px;">
            <table class="table " id="expenses_listing_table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Paid date</th>
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

                        @role('Admin')
                            <th>Action</th>
                        @endrole
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">

                    @foreach ($expenses as $expense)
                        <tr>
                            <td>{{ $loop->index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($expense->current_date)->format('d-m-Y h:i A') }}</td>

                            <td>{{ $expense->category_name ? $expense->category_name : '--' }}</td>
                            <td>{{ $expense->project_name ? $expense->project_name : '--' }}</td>
                            <td><b><span style="color:#ef6a0e">{{ $expense->amount }}</span></b></td>
                            <td>
                                @if ($expense->image != '' || $expense->image != null)
                                    <a href="{{ url('public/images/' . $expense->image) }}" target="_blank">View</a>
                                @else
                                    --
                                @endif
                            <td>{{ $expense->payment_name }}</td>
                            <td>{{ $expense->description ? $expense->description : '--' }}</td>
                            <td><b><span style="color: green;">{{ $expense->paid_amt }}</span></b></td>
                            <td>
                                @if ($expense->unpaid_amt != 0)
                                    <b><a href="{{ route('unpaidex-create', $expense->id) }}"
                                        style="color:red">{{ $expense->unpaid_amt }}</a></b> @else<b>
                                        <p style="color:red">{{ $expense->unpaid_amt }}</p>
                                    </b>
                                @endif
                            </td>
                            <td><b><span style="color:#840eef;">{{ $expense->extra_amt }}</span></b></td>
                            @role('Admin')
                                <td>{{ $expense->first . '' . $expense->last }}</td>
                                <td>{{ $expense->first_name . '' . $expense->last_name }}</td>
                            @endrole


                            <td>
                                @can('expenses-edit')
                                    <a class="" href="{{ route('expenses-edit', $expense->id) }}"><i class="fa fa-edit"
                                            style="font-size:24px"></i></a>
                                @endcan
                                @can('expenses-delete')
                                    <a data-toggle="modal" href="javascript:void(0)" data-user="{{ $expense->user_id }}"
                                        data-id="{{ $expense->id }}" class="deleteExpense"><i class="fa fa-trash-o"
                                            style="font-size:24px; color:red"></i> </a><br />
                                @endcan
                            </td>

                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>

    <!--/ Basic Bootstrap Table -->

    <p class="text-end" style="margin-top: 53px; margin-right: 147px; font-size: medium;">
        <span class="d-inline"><b>Total Amount:</b> <b><span style="color:#ef6a0e">{{ $sum }}</span></b></span>
        <span class="d-inline ms-3"><b>Total Paid Amount:</b> <b><span
                    style="color: green;">{{ $paid_amt }}</span></b></span>
        <span class="d-inline ms-3"><b>Total Unpaid Amount:</b> <b><span
                    style="color: red;">{{ $unpaid_amt }}</span></b></span>
        <span class="d-inline ms-3"><b>Total Advanced Amount:</b><b><span style="color: #840eef;">
                    {{ $advanced_amt }}</span></b></span>
    </p>

    <!--- modal popup for transfer -->
    <div class="modal fade" id="unpaid-popup" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog d-flex justify-content-center">
            <div class="modal-content w-75">
                <div class="modal-header">
                    <h5 class="modal-title" id="unpaid_title">Unpaid Details</h5>
                    <button type="button" class="btn-close unpaid-close" data-mdb-dismiss="modal"
                        aria-label="Close"></button>
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
                    <h4 class="modal-title">Reason</h4>
                    <hr>
                </div>
                <div class="modal-body">
                    <input type="text" id="reason" name="reason" class="form-control" placeholder="Enter reason"
                        value="" />
                    <label id="reason-error" class="error" style="color:red" for="basic-default-email">Reason is
                        required</label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary yes-reason" data-dismiss="modal">Submit</button>
                    <button type="button" class="btn btn-danger no-reason" data-dismiss="modal">cancel</button>
                </div>
            </div>

        </div>
    </div>

    <!-- modal popup for delete role ended -->

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous">
    </script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.selectpicker').selectpicker();
        });
        $(document).ready(function() {
            var data = new DataTable('#expenses_listing_table', {
                "lengthMenu": [15, 50, 100],
                processing: true,

            });
        });
        $("document").ready(function() {
            var roleid;
            var user;
            $('.error').addClass('hide');
            setTimeout(function() {
                $("div.alert").remove();
            }, 5000); // 5 secs
            $("#expenses_listing_table").on("click", ".deleteExpense", function() {
                roleid = $(this).attr('data-id');
                user = $(this).attr('data-user');
                $("#myModal").removeClass('fade');
                $("#myModal").modal('show');
            });
            $('.no-delete').click(function() {
                $("#myModal").addClass('fade');
                $("#myModal").modal('hide');
            });
            $('.no-reason').click(function() {
                $("#myModal_reason").addClass('fade');
                $("#myModal_reason").modal('hide');
            });
            $('.yes-delete').click(function() {
                console.log('roleid', roleid);
                $('#myModal').modal('hide');
                $('#myModal_reason').modal('show');
                // $("#myModal").modal('hide');
                // var url = '{{ route('expenses-delete', ':id') }}';
                //       url1 = url.replace(':id', roleid);
                //       window.location.href=url1;
            });
            $('.yes-reason').click(function() {
                console.log('roleid', roleid);
                var reason = $('#reason').val();
                console.log('reason', reason);
                if (reason == '') {
                    $('#reason-error').removeClass('hide');
                } else {
                    $('#reason-error').addClass('hide');
                    $("#myModal_reason").modal('hide');
                    var url = '{{ route('expenses-delete') }}';

                    window.location.href = url + '?id=' + roleid + '&reason=' + reason + '&user=' + user;
                }
            });
        });
        $(document).ready(function() {
            $('#unpaid-popup').modal('hide');
            var category = [];
            var amount = [];
            var project = [];
            var user = [];
            var from_date = [];
            var end_date = [];


            $('#category_id').change(function() {

                project = $('#project_id').find(":selected").val();
                category = $('#category_id').find(":selected").val();
                user = $('#user_id').find(":selected").val();
                // amount =$('#amount_id').find(":selected").val();
                from_date = $('#from_date').val();
                end_date = $('#to_date').val();
                if (category != '') {
                    reset_table(from_date, end_date, category, project, user, );
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
            $('#project_id').change(function() {

                project = $('#project_id').find(":selected").val();
                category = $('#category_id').find(":selected").val();
                user = $('#user_id').find(":selected").val();
                // amount =$('#amount_id').find(":selected").val();
                from_date = $('#from_date').val();
                end_date = $('#to_date').val();
                console.log('project', project);
                console.log('category', category);
                console.log('from_date', from_date);

                if (project != '') {
                    reset_table(from_date, end_date, category, project, user);
                }
            });
            $('#user_id').change(function() {

                user = $('#user_id').find(":selected").val();
                project = $('#project_id').find(":selected").val();
                category = $('#category_id').find(":selected").val();
                //amount =$('#amount_id').find(":selected").val();
                from_date = $('#from_date').val();
                end_date = $('#to_date').val();
                console.log(user);
                if (user != '') {
                    reset_table(from_date, end_date, category, project, user);
                }
            });
            $('#from_date').change(function() {

                user = $('#user_id').find(":selected").val();
                project = $('#project_id').find(":selected").val();
                category = $('#category_id').find(":selected").val();
                // amount =$('#amount_id').find(":selected").val();
                from_date = $('#from_date').val();
                end_date = $('#to_date').val();
                console.log(from_date);
                if (from_date != '') {
                    reset_table(from_date, end_date, category, project, user);
                }
            });
            $('#to_date').change(function() {

                user = $('#user_id').find(":selected").val();
                project = $('#project_id').find(":selected").val();
                category = $('#category_id').find(":selected").val();
                //amount =$('#amount_id').find(":selected").val();
                from_date = $('#from_date').val();
                end_date = $('#to_date').val();
                console.log(end_date);
                if (end_date != '') {
                    reset_table(from_date, end_date, category, project, user);
                }
            });

            function reset_table(from_date, to_date, category, project, user) {
                console.log('category', category);
                from_date = from_date;
                end_date = to_date;
                var url = '{{ route('expenses-history') }}';
                window.location.href = url + '?from_date=' + from_date + '&to_date=' + to_date + '&category_id=' +
                    category + '&project_id=' + project + '&user_id=' + user;
            }

        });
        $('#expense-export').click(function() {
            console.log('test');
            var user = $('#user_id').find(":selected").val();
            var project = $('#project_id').find(":selected").val();
            var category = $('#category_id').find(":selected").val();

            var from_date = $('#from_date').val();
            var end_date = $('#to_date').val();
            var url = '{{ route('expenses-export') }}';
            window.location.href = url + '?from_date=' + from_date + '&to_date=' + end_date + '&category_id=' +
                category + '&project_id=' + project + '&user_id=' + user;
        });
        $('#expense-pdf').click(function() {
            console.log('test1');
            var user = $('#user_id').find(":selected").val();
            var project = $('#project_id').find(":selected").val();
            var category = $('#category_id').find(":selected").val();

            var from_date = $('#from_date').val();
            var end_date = $('#to_date').val();
            var url = '{{ route('expenses-pdf') }}';
            window.location.href = url + '?from_date=' + from_date + '&to_date=' + end_date + '&category_id=' +
                category + '&project_id=' + project + '&user_id=' + user;
        });
    </script>
@endsection

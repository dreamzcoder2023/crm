@extends('layouts/contentNavbarLayout')
<link href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/css/bootstrap-select.min.css" />

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

@section('title', 'Create | HOUSE FIX - A DOCTOR FOR YOUR HOUSE')

@section('content')

@if (session()->has('expenses-popup'))
<script src="https://code.jquery.com/jquery-3.5.1.min.js"
    integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

<script>
      $(function() {
    toastr.success('{{ session('expenses-popup') }}', {
        timeOut: 1000,
        fadeOut: 1000,
    });
});
</script>
@endif
    <style>
        .bootstrap-select {
            max-width: 100%;
            color: #697a8d;
            border: solid 0.5px #697a8d !important;
        }
      
    </style>
    <!-- <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Forms/</span></h4> -->

    <!-- Basic Layout & Basic with Icons -->
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Add Labour Expenses
    </h4>
    <div class="row">
        <!-- Basic Layout -->
        <div class="col-xxl">
            <div class="card mb-4">


                <div class="card-body">
                    <form name="createExpenses" id="createExpenses" action="{{ route('labour-expenses.store') }}"
                        method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="user_id" id="user_id" value="{{Auth::user()->id}}">
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3 " id="here">
                                    <label class="form-label" for="basic-default-phone">Category Name</label>
                                   <input type="hidden" name="category_id" value="{{ $category?->id }}">
                                    <div> <select class="form-control " name="category_id" id="category_id"
                                            style="width:100%" disabled >
                                            <option value="">Select category </option>

                                                <option value="{{ $category?->id }}" {{ $category->name == 'salary' ? 'selected ' : '' }} readonly>{{ $category?->name }}</option>

                                        </select></div>
                                    <label id="showing_error_msg" class="error hide">Category name already exists.</label>
                                    <label id="showing_success_msg" class="success hide" style="color:green">Category added
                                        successfully.</label>
                                    <label id="category-error" class="error hide" for="basic-default-role">Category is
                                        required</label>
                                </div>
                                {{-- <div class="mb-3">
                                    <label class="form-label" for="basic-default-phone">Project Name</label>
                                    <select class="form-control selectpicker" name="project_id" data-live-search="true" id="project_id">
                                        <option value="">Select project </option>
                                        @foreach ($project as $project)
                                            <option value="{{ $project?->id }}">{{ $project?->name }}</option>
                                        @endforeach
                                    </select>
                                    <label id="project-error" class="error hide" for="basic-default-role">Project is
                                      required</label>
                                </div> --}}

                                <div class="mb-3">
                                    <label class="form-label" for="project_id">Project Name</label>
                                    <select class="form-control select2" name="project_id" id="project_id">
                                        <option value="">Select project</option>
                                        @foreach ($project as $project)
                                            <option value="{{ $project?->id }}">{{ $project?->name }}</option>
                                        @endforeach
                                    </select>
                                    <label id="project-error" class="error hide" for="basic-default-role">Project is required</label>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label" for="basic-default-phone">Labour Name</label>
                                    <select class="form-control select2" name="labour_id" id="labour_id">
                                        <option value="">Select labour </option>
                                        @foreach ($labours as $labour)
                                            <option value="{{ $labour?->id }}">{{ $labour?->name }}</option>
                                        @endforeach
                                    </select>
                                    <label id="labour-error" class="error hide" for="basic-default-role">Labour is
                                      required</label>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="basic-default-email">Salary</label>

                                    <input type="text" id="amount" name="amount" class="form-control"
                                        placeholder="Enter amount"
                                        onkeypress="allowNumbersOnly(event)" />
                                    <p class="advance_amt" style="color:blue"> </p>
                                    <label id="amount-error" class="error" for="basic-default-email">Amount is
                                        required</label>
                                    <input type="hidden" class="amount-check-error" value=""><br>

                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="basic-default-phone">Payment Mode</label>
                                    <select class="form-control" name="payment_mode" id="payment_mode">
                                        <option value="">Select payment </option>
                                        @foreach ($payment as $payment)
                                            <option value="{{ $payment?->id }}">{{ $payment?->name }}</option>
                                        @endforeach
                                    </select>
                                    <label id="payment-error" class="error" for="basic-default-role">Payment mode is
                                        required</label>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="basic-default-phone">Image</label>
                                    <input type="file" name="image" class="form-control" accept="application/pdf,image/*"
                                        placeholder="image">
                                </div>

                            </div>
                            <div class="col-6">


                                <div class="mb-3">
                                    <label class="form-label" for="basic-default-phone">Description</label>
                                    <textarea type="text" name="description" id="description" class="form-control phone-mask" style="height:28px"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="basic-default-message">Paid Amount</label><br>
                                    <input type="text" class="form-control" value="" id="paid_amt"  oninput="amountcheck(this.value)"
                                        name="paid_amt" onkeypress="allowNumbersOnly(event)">
                                    <label id="paid-error" class="error" for="basic-default-amt">Paid amount is
                                        required.</label>
                                        <label id="amount-check-error" class="error" for="basic-default-phone">Insufficient
                                          Balance</label>
                                    <!-- <label id="paid_amt-error" class="error" for="basic-default-amt">Paid amount must be less than amount.</label> -->
                                </div>
                                <!-- <div class="mb-3">
                    <label class="form-label" for="basic-default-message">Unpaid Amount</label><br>
                    <input  type="text" class="form-control" value="" id="unpaid_amt" name="unpaid_amt" readonly>
                  </div> -->

                                <div class="mb-3">
                                    <label class="form-label" for="datetimepicker1">Date</label><br>
                                    <input type="date" class="form-control" id="datetimepicker1" name="current_date"
                                        value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="appt">Time:</label><br>
                                    <input type="time" id="appt" class="form-control" name="time"
                                        value="{{ Carbon\Carbon::now()->format('h:i:s') }}">
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-sm-10 text-right" style="top:-40px">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Basic with Icons -->
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
  



<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/js/bootstrap-select.min.js"></script>

    <script>
       
       $('#project_id').select2({
        placeholder: "Select project",
        allowClear: true,
        width: '100%',
    });
    $('#labour_id').select2({
        placeholder: "Select labour",
        allowClear: true,
        width: '100%',
    });
       
   $(document).ready(function() {
            $('.error').addClass('hide');
            $('.success').addClass('hide');
            $('.addcategory').hide();
            $('.advance_amt').addClass('hide');
        });

        function allowNumbersOnly(e) {
            var key = e.key;
            if (isNaN(key) || key === ' ' || key === null) {
                e.preventDefault();
            }
        }
        $('#new_category_click').click(function() {
            $('.addcategory').css('display', 'block');
        });
        $('.savecategory').click(function() {
            var category = $('#new_category').val();
            var categoryname = false;
            if (category == "") {
                $('#category-error').removeClass('hide');
            } else {
                $('#category-error').addClass('hide');
                categoryname = true;
            }
            if (categoryname == true) {
                $.ajax({
                    url: "{{ route('new-category') }}",
                    data: {
                        'name': category
                    },
                    type: 'GET',
                    dataType: 'json',
                    success: function(result) {
                        console.log("result", result);
                        if (result == 'true' || result == true) {
                            $('.addcategory').attr("style", "display: none !important");
                            $('#showing_error_msg').addClass('hide');
                            $('#showing_success_msg').removeClass('hide');

                            $("#here").load(window.location.href + " #here");

                        }
                        if (result == 'false' || result == false) {
                            $('#showing_error_msg').removeClass('hide');
                        }
                    }
                });
            }
        });
        $('#labour_id').change(function() {
            var id = $(this).val();
            $.ajax({
                url: "{{ route('labour-salary') }}",
                data: {
                    'id': id
                },
                type: 'GET',
                dataType: 'json',
                success: function(result) {
                    console.log("result", result);
                    $('#amount').val(result.salary);
                    $('.advance_amt').text('Advance Amount :' + result.advance_amt);
                    $('.advance_amt').removeClass('hide');
                }
            });

        });
        $('#createExpenses').submit(function(e) {
            e.preventDefault();
            var category = $('#category_id').find(":selected").val();
            var project = $('#project_id').find(':selected').val();
            var labour = $('#labour_id').find(':selected').val();
            var payment = $('#payment_mode').find(":selected").val();
            var amount = $('#amount').val();
            var test = $('.amount-check-error').val();
            var paid = $('#paid_amt').val();
            // var unpaid = parseInt(amount) - parseInt(paid);
            // console.log('unpaid',unpaid);
            console.log('category_id', category);
            console.log('amount', amount);
            console.log('paid', paid);

            var clientname = false,
                amountname = false,
                pamtname = false,
                paymentmode = false,
                projectname = false,
                labourname = false,
                paidname = false;
            if (category.length < 1) {
                $('#category-error').removeClass('hide');
            } else {
                $('#category-error').addClass('hide');
                clientname = true;
            }
            if (project.length < 1) {
                $('#project-error').removeClass('hide');
            } else {
                $('#project-error').addClass('hide');
                projectname = true;
            }
            if (labour.length < 1) {
                $('#labour-error').removeClass('hide');
            } else {
                $('#labour-error').addClass('hide');
                labourname = true;
            }
            if (payment.length < 1) {
                $('#payment-error').removeClass('hide');
            } else {
                $('#payment-error').addClass('hide');
                paymentmode = true;
            }
            if (paid == "") {
                $('#paid-error').removeClass('hide');
            } else {
                $('#paid-error').addClass('hide');
                paidname = true;
            }
            if (amount == "") {
                $('#amount-error').removeClass('hide');
            }
            //  else if(parseInt(paid) < parseInt(amount)){
            //     console.log(amount,paid);
            //     $('#paid_amt-error').addClass('hide');
            //     pamtname = true;
            //     amountname = true;

            //   }
            //   else if(parseInt(paid) > parseInt(amount)){
            //     console.log(amount,paid);
            //       $('#paid_amt-error').removeClass('hide');
            //   }
            else {
                $('#amount-error').addClass('hide');
                $('#paid_amt-error').addClass('hide');
                amountname = true;
                pamtname = true;
            }

            if (clientname == true && amountname == true && projectname == true && labourname == true && (test == false || test == "false") && paymentmode ==
                true && paidname == true) {
                document.getElementById("createExpenses").submit();
            }
        });

        function amountcheck(amount) {
            console.log(amount, "amount check");
            var user_id = $('#user_id').val();
            $.ajax({
                url: "{{ route('amount-check') }}",
                data: {
                    'amount': amount,'user_id' : user_id
                },
                type: 'GET',
                dataType: 'json',
                success: function(result) {
                    console.log("result", result);
                    $('.amount-check-error').val(result);
          if(result == true)
             $('#amount-check-error').removeClass('hide');
          else
          $('#amount-check-error').addClass('hide');

                }
            });
        }
    </script>
@endsection

<!-- Banner -->



<!-- Dashboard -->
@extends('layouts/contentNavbarLayout')
{{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.4.2/css/all.min.css" integrity="sha512-NicFTMUg/LwBeG8C7VG+gC4YiiRtQACl98QdkmfsLy37RzXdkaUAuPyVMND0olPP4Jn8M/ctesGSB2pgUBDRIw==" crossorigin="anonymous" referrerpolicy="no-referrer" /> --}}
{{-- <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> --}}
<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

@section('title', 'Dashboard | HOUSE FIX - A DOCTOR FOR YOUR HOUSE')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}">
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>
@endsection
<!--- success pop
up -->

<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
@if (session()->has('popup'))
<script>
  $(function() {
    $('.success-msg').text('Wallet Details Created Successfully.')
    $('#walletsuccess').removeClass('fade');
    $('#walletsuccess').modal('show');
  });
</script>
@endif
@if (session()->has('popup1'))
<script>
  $(function() {
    $('.success-msg').text('Check-in Successfully.')
    $('#walletsuccess').removeClass('fade');
    $('#walletsuccess').modal('show');
  });
</script>
@endif
@if (session()->has('popup2'))
<script>
  $(function() {
    $('.success-msg').text('Check-out Successfully.')
    $('#walletsuccess').removeClass('fade');
    $('#walletsuccess').modal('show');
  });
</script>
@endif
<!-- success popup -->
@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type='text/css'>

<style>
  @media (max-width: 576px) {  /* Apply background image for screens wider than 576px */
   .inc{
    height:400px !important;
   }
   .incc{
    height:600px !important;
   }
   .inccc{
    height:800px !important;
   }
   .cards{
    margin-bottom:10px !important;
   }
  }
</style>
<style>
  .icon-shape {
    width: 50px;
    height: 50px;
  }

  /* .bi {
    margin-left: 13px;
    font-size: 19px;
    font-weight: 800;
  } */

  .card-container {
    overflow: hidden;
  }

  .cards {
    transition: transform 0.3s ease-in-out;
  }

  .cards:hover {
    transform: scale(1.05);
    /* You can adjust the scale factor as needed */
  }
  .bi{
    margin-left:12px;
    margin-top:15px !important;
    font-size: 20px;
  }
  .rounded-circle{
    display:flex;
    /* justify-content: start; */
    margin-top:-10px !important;
  }
</style>

<div class="row g-6 mb-6">
  @if ($checking == '')
  <div class="col-xl-3 col-sm-6 col-12">
    <div class="card cards shadow border-0" style="height: 95px;">
      <div class="card-body">
        <div class="row">
          <div class="col">
            <span class="h6 font-semibold text-muted text-sm d-block mb-2" style="font-weight: 800;color:black !important;">Check Out</span>
            <span class="h3 font-bold mb-0" style="font-size: 10px; font-weight:800;color:darkcyan;width:40px;">You Currently Check
              Out</span>
          </div>
          <div class="col-auto">


            <form action="{{ route('checking.store') }}" method="POST">
              @csrf
              <div class="icon icon-shape submit-icon text-white text-lg rounded-circle" style="background-color:darkcyan;border:2px solid white">
                <i class="bi bi-box-arrow-in-right" style="color:white" ></i>
              </div>
            </form>

          </div>
        </div>

      </div>
    </div>
  </div>
  @elseif ($checking->notes == 1)
  <div class="col-xl-3 col-sm-6 col-12">
    <div class="card cards shadow border-0" style="height: 95px;">
      <div class="card-body">
        <div class="row">
          <div class="col">
            <span class="h6 font-semibold text-muted text-sm d-block mb-2" style="font-weight: 800;color:black !important;">Check Out</span>
            <span class="h3 font-bold mb-0" style="font-size: 13px; font-weight:800;color:darkcyan;width:40px;">You
              Currently Check Out</span> <br>
              <p style="color:red; font-weight:800 !important; font-size:12px;margin-top:6px;font-style:bold ;"> <b style="color:red; font-weight:800 !important; font-size:12px;font-style:bold ;">Good Bye, {{Auth::user()->first_name}} {{Auth::user()->last_name}}</b></p>
          </div>
          <div class="col-auto">

            <form action="{{ route('checking.store') }}" method="POST">
              @csrf
              <div class="icon icon-shape submit-icon text-white text-lg rounded-circle" style="background-color:darkcyan;border:2px solid white">
                <i class="bi bi-box-arrow-in-right" style="color:white"></i>
              </div>
            </form>

          </div>
        </div>

      </div>
    </div>
  </div>
  @endif
  @if ($checking != '')
  @if ($checking->notes == 0)
  <div class="col-xl-3 col-sm-6 col-12">
    <div class="card cards shadow border-0" style="background-color: darkcyan;height:95px;">
      <div class="card-body">
        <div class="row">
          <div class="col">
            <span class="h6 font-semibold text-muted text-sm d-block mb-2" style=" color:rgb(215, 250, 246) !important;font-weight:800;">Check-in</span>
            <span class="h3 font-bold mb-0" style="font-size: 14px; font-weight:800;color:white;width:20px;">{{ $checking->created_at->format('d:m:Y h:i:s A') }}</span> <br>
            <p style="color:yellow; font-weight:800; font-size:12px;">Welcome, {{Auth::user()->first_name}} {{Auth::user()->last_name}}</p>
          </div>
          <div class="col-auto">

            <form action="{{ route('checking.update', $checking->id) }}" method="POST">
              @csrf
              @method('PUT')
              <div class="icon icon-shape submit-icon text-white text-lg rounded-circle" style="background-color:white;border:2px solid white">
                <i class="bi bi-box-arrow-in-left" style="color: darkcyan"></i>
              </div>

            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
  @endif
  @endif
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Get the submit icon element
      var submitIcon = document.querySelector('.submit-icon');

      // Add a click event listener to the submit icon
      submitIcon.addEventListener('click', function() {
        // Find the nearest form element and submit it
        var form = submitIcon.closest('form');
        form.submit();
      });
    });
  </script>
 <div class="col-xl-3 col-sm-6 col-12">
  <div class="card card1 cards shadow border-0">
      <div class="card-body">
          <div class="row">
              <div class="col text-center">
                  <!-- Initially centered text -->
                  <span class="h6 font-semibold text-muted text-sm d-block mb-2" style="color: black !important;margin-top:10px; font-weight:600 !important;text-align:center;">Team Members</span>
                  <!-- Hidden member information -->
                  <span class="h3 font-bold mb-0 member-info" style="display: none;font-size:22px; font-weight:900;color:darkcyan">{{ $member }}</span>
              </div>
              <div class="col-auto">
                <a class="dropdown-item" href="{{ route('user-index') }}">
                  <div class="icon icon-shape text-white text-lg rounded-circle" style="background-color:sandybrown;border:2px solid white;">
                    <i class="bi bi-people-fill" style="color: #ffffff;"></i>
                  </div>
                </a>
              </div>
          </div>
      </div>
  </div>
</div>


<script>
  $(document).ready(function () {
      // Toggle the display when the card is clicked
      $('.card1').click(function (event) {
          // Check if the clicked element is not the icon and not the col-auto element
          if (!$(event.target).is('.icon') && !$(event.target).closest('.col-auto').length) {
              $('.member-info').toggle();
          }
      });
  });
</script>


<div class="col-xl-3 col-sm-6 col-12">
  <div class="card card2 cards shadow border-0">
      <div class="card-body">
          <div class="row">
              <div class="col">
                  <span class="h6 font-semibold text-muted text-sm d-block mb-2" style="color: black !important; margin-top:10px; font-weight:600 !important;text-align:center;">Unpaid Amount</span>
                  <span class="h3 font-bold mb-0 member-infoo" style="display: none;font-size:22px; font-weight:900;color:darkcyan">{{ $unpaid_amt }}</span>
              </div>
              <div class="col-auto">
                  <a class="dropdown-item" href="{{ route('expenses-history') }}">
                      <div class="icon icon-shape  text-white text-lg rounded-circle" style="background-color:lightseagreen;border:2px solid white">
                        <i class="bi bi-credit-card-fill" style="color: #ffffff;"></i>
                      </div>
                  </a>
              </div>
          </div>
      </div>
  </div>
</div>

<script>
  $(document).ready(function () {
      // Toggle the display when the card is clicked
      $('.card2').click(function (event) {
          // Check if the clicked element is not the icon and not the col-auto element
          if (!$(event.target).is('.icon') && !$(event.target).closest('.col-auto').length) {
              $('.member-infoo').toggle();
          }
      });
  });
</script>


  <div class="col-xl-3 col-sm-6 col-12">
    <div class="card card3 cards shadow border-0">
      <div class="card-body">
        <div class="row">
          <div class="col">
            <span class="h6 font-semibold text-muted text-sm d-block mb-2 " style="color: black !important; margin-top:10px; font-weight:600 !important;text-align:center;">Wallet Balance</span>
            <span class="h3 font-bold mb-0 member-infooo"  style="display: none;font-size:22px; font-weight:900;color:darkcyan">{{ $wallet }}</span>
          </div>
          <div class="col-auto">
            <a class="dropdown-item" href="{{ route('user-index') }}">
            <div class="icon icon-shape bg-warning text-white text-lg rounded-circle" style="border:2px solid white;">
              <i class="bi bi-wallet2" style="color: #ffffff;"></i>
            </div>
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>
  <script>
    $(document).ready(function () {
        // Toggle the display when the card is clicked
        $('.card3').click(function (event) {
            // Check if the clicked element is not the icon and not the col-auto element
            if (!$(event.target).is('.icon') && !$(event.target).closest('.col-auto').length) {
                $('.member-infooo').toggle();
            }
        });
    });
  </script>
</div>


</div>
</div>

<!-- Bootstrap JS and Popper.js -->
<div class="row" style="margin:10px; padding:10px;">
  <!-- Order Statistics -->
  <div class="col-md-6 col-lg-4 col-xl-4 order-0 mb-4">
    <div class="card h-100 inc">
      <div class="card-header d-flex align-items-center justify-content-between pb-2">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2" style="color:black; margin-bottom:2px;font-size:18px !important;"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" class="feather feather-grid icon-16">
            <rect x="3" y="3" width="7" height="7"></rect>
            <rect x="14" y="3" width="7" height="7"></rect>
            <rect x="14" y="14" width="7" height="7"></rect>
            <rect x="3" y="14" width="7" height="7"></rect>
        </svg> &nbsp; Project Overview</h5>
          {{-- <small class="text-muted">42.82k Total Sales</small> --}}
        </div>
        <div class="dropdown">
          <button class="btn p-0" type="button" id="orederStatistics" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="bx bx-dots-vertical-rounded"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="orederStatistics">
            <a class="dropdown-item" href="{{ route('project-index') }}">View More</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <input type="hidden" id="total_value" value="{{ $project_open + $project_close }}">
          <input type="hidden" id="open_value" value="{{ $project_open  }}">
          <input type="hidden" id="close_value" value="{{$project_close }}">
          <div class="d-flex flex-column align-items-center gap-1">
            <h2 class="mb-2">{{ $project_open + $project_close }}</h2>

            <span>Total Projects</span>
          </div>
          <div id="orderStatisticsChart"></div>
        </div>
        <ul class="p-0 m-0">
          <li class="d-flex mb-4 pb-1">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-primary"><i class="fa fa-folder-open-o" aria-hidden="true" style="color:green"></i></span>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0" style="color:green">Open</h6>
                {{-- <small class="text-muted">Mobile, Earbuds, TV</small> --}}
              </div>
              <div class="user-progress">
                <small class="fw-semibold">{{ $project_open }}</small>
              </div>
            </div>
          </li>
          <li class="d-flex mb-4 pb-1">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-primary"><i class="fa fa-folder-o" aria-hidden="true" style="color:red"></i></span>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0" style="color:red">Close</h6>
                {{-- <small class="text-muted">Mobile, Earbuds, TV</small> --}}
              </div>
              <div class="user-progress">
                <small class="fw-semibold">{{ $project_close }}</small>
              </div>
            </div>
          </li>


        </ul>
      </div>
    </div>
  </div>
  <!--/ Order Statistics -->

  <!-- Expense Overview -->
  <div class="col-md-6 col-lg-4 order-1 mb-4">
    <div class="card h-100 incc">
        <div class="card-header">
            <ul class="nav nav-pills" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                        data-bs-target="#navs-tabs-line-card-income" aria-controls="navs-tabs-line-card-income"
                        aria-selected="true">Income</button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" role="tab"  data-bs-toggle="tab"
                    data-bs-target="#navs-tabs-line-card-expenses" aria-controls="navs-tabs-line-card-expenses"
                    aria-selected="true" style="color:black">Expenses</button>
                </li>

            </ul>

        </div>
        <div class="card-body px-0">
            <div class="tab-content p-0">
                <div class="tab-pane fade show active" id="navs-tabs-line-card-income" role="tabpanel">
                    <div class="d-flex p-4 pt-3">
                        <div class="avatar flex-shrink-0 me-3">
                            <img src="{{ asset('assets/img/icons/unicons/wallet.png') }}" alt="User">
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Balance</small>
                            <div class="d-flex align-items-center">
                                <div id="monthly_data" data-income="{{ json_encode($income) }}"></div>

                                <small class="text-success fw-semibold">
                                    <i class='bx bx-chevron-up'></i>

                                    {{count($incomeWithPercentage) == 0 ? 0 : $incomeWithPercentage[0]['percentage']}} %
                                </small>
                            </div>
                        </div>
                    </div>
                    <div id="incomeChart"></div>
                    <div class="d-flex justify-content-center pt-4 gap-2">
                      <div class="flex-shrink-0">
                          <div id="expensesOfWeek" data-income="{{$currentWeekPercentage}}"></div>
                      </div>
                      <div>
                          <p class="mb-n1 mt-1">Income This Week</p>
                          <small class="text-muted">{{$currentWeekPercentage}} %</small>
                      </div>
                  </div>
                </div>

                <div class="tab-pane fade" id="navs-tabs-line-card-expenses" role="tabpanel">
                    <div class="d-flex p-4 pt-3">
                        <div class="avatar flex-shrink-0 me-3">
                            <img src="{{ asset('assets/img/icons/unicons/wallet.png') }}" alt="User">
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Expenses</small>
                            <div class="d-flex align-items-center">
                                <div id="monthly_expense_data"
                                    data-expenses="{{ json_encode($expense) }}"></div>

                               <small class="text-danger fw-semibold">
                                    <i class='bx bx-chevron-down'></i>
                                    {{count($expenseWithPercentage) == 0 ? 0 : $expenseWithPercentage[0]['percentage']}} %
                                </small>
                            </div>
                        </div>
                    </div>
                    <div id="expenseChart"></div>
                    <div class="d-flex justify-content-center pt-4 gap-2">
                      <div class="flex-shrink-0">
                          <div id="expensesOfWeek1" data-income="{{$currentWeekExpensePercentage}}"></div>
                      </div>
                      <div>
                          <p class="mb-n1 mt-1">Expenses This Week</p>
                          <small class="text-muted">{{$currentWeekExpensePercentage}} %</small>
                      </div>
                  </div>
                </div>
            </div>
        </div>
    </div>
</div>
  <!--/ Expense Overview -->

  <!-- Transactions -->
  <div class="col-md-6 col-lg-4 order-2 mb-4">
    <div class="card h-100 inccc">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title m-0 me-2" style="color:black">Transactions</h5>
        <div class="dropdown">
          <button class="btn p-0" type="button" id="transactionID" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="bx bx-dots-vertical-rounded"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionID">
            <a class="dropdown-item" href="{{route('transfer-history')}}">View more</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <ul class="p-0 m-0">
          @foreach($transfer_history as $transfer)
          <li class="d-flex mb-4 pb-1">

            <div class="avatar flex-shrink-0 me-3">

              <img src="{{ asset('assets/img/icons/unicons/member.png') }}" alt="User" class="rounded">
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">

              <div class="me-2">
                <small class="text-muted d-block mb-1">{{$transfer->first_name}}</small>
                <h6 class="mb-0">{{$transfer->last_name}}</h6>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <h6 class="mb-0" style="font-weight: ">{{empty($transfer->total_amount) == 0 ? 0 :$transfer->total_amount}}</h6> <span class="text-muted">Rupees</span>
              </div>
            </div>

          </li>
          @endforeach
          {{-- <li class="d-flex mb-4 pb-1">
            <div class="avatar flex-shrink-0 me-3">
              <img src="{{ asset('assets/img/icons/unicons/wallet.png') }}" alt="User" class="rounded">
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <small class="text-muted d-block mb-1">Wallet</small>
                <h6 class="mb-0">Mac'D</h6>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <h6 class="mb-0">+270.69</h6> <span class="text-muted">USD</span>
              </div>
            </div>
          </li>
          <li class="d-flex mb-4 pb-1">
            <div class="avatar flex-shrink-0 me-3">
              <img src="{{ asset('assets/img/icons/unicons/chart.png') }}" alt="User" class="rounded">
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <small class="text-muted d-block mb-1">Transfer</small>
                <h6 class="mb-0">Refund</h6>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <h6 class="mb-0">+637.91</h6> <span class="text-muted">USD</span>
              </div>
            </div>
          </li>
          <li class="d-flex mb-4 pb-1">
            <div class="avatar flex-shrink-0 me-3">
              <img src="{{ asset('assets/img/icons/unicons/cc-success.png') }}" alt="User" class="rounded">
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <small class="text-muted d-block mb-1">Credit Card</small>
                <h6 class="mb-0">Ordered Food</h6>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <h6 class="mb-0">-838.71</h6> <span class="text-muted">USD</span>
              </div>
            </div>
          </li>
          <li class="d-flex mb-4 pb-1">
            <div class="avatar flex-shrink-0 me-3">
              <img src="{{ asset('assets/img/icons/unicons/wallet.png') }}" alt="User" class="rounded">
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <small class="text-muted d-block mb-1">Wallet</small>
                <h6 class="mb-0">Starbucks</h6>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <h6 class="mb-0">+203.33</h6> <span class="text-muted">USD</span>
              </div>
            </div>
          </li>
          <li class="d-flex">
            <div class="avatar flex-shrink-0 me-3">
              <img src="{{ asset('assets/img/icons/unicons/cc-warning.png') }}" alt="User" class="rounded">
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <small class="text-muted d-block mb-1">Mastercard</small>
                <h6 class="mb-0">Ordered Food</h6>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <h6 class="mb-0">-92.45</h6> <span class="text-muted">USD</span>
              </div>
            </div>
          </li> --}}
        </ul>
      </div>
    </div>
  </div>
<script>
   console.log(@json($income));
  </script>
  @endsection

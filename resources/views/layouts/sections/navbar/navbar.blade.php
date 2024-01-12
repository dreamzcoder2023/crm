<style>
  .dropbtn {

    color: white;
    padding: 16px;
    font-size: 16px;
    border: none;
  }

  .dropdown {
    position: relative;
    display: inline-block;
  }

  .dropdown-content {
    display: none;
    position: absolute;
    background-color: #f1f1f1;
    min-width: 200px;
    box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
    z-index: 1;
  }

  .dropdown-content a {
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    background-color: white;
  }

  .dropdown-content a:hover {
    background-color: #0081b8;
    color: white
  }

  .dropdown:hover .dropdown-content {
    display: block;
  }

  .preloader {
    width: 100%;
    height: 100vh;
    background-color: rgba(255, 255, 255, 0.2);
    /* 0.8 is the alpha channel for transparency */
    position: fixed;
    top: 0;
    left: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    backdrop-filter: blur(15px);
    /* Add a blur effect for the glassy look */
  }

  .loader {
    width: 15px;
    aspect-ratio: 1;
    border-radius: 50%;
    animation: l5 1s infinite linear alternate;
  }

  @keyframes l5 {
    0% {
      box-shadow: 20px 0 #000, -20px 0 #0002;
      background: #000
    }

    33% {
      box-shadow: 20px 0 #000, -20px 0 #0002;
      background: #0002
    }

    66% {
      box-shadow: 20px 0 #0002, -20px 0 #000;
      background: #0002
    }

    100% {
      box-shadow: 20px 0 #0002, -20px 0 #000;
      background: #000
    }
  }

  .dataTables_filter {
    text-align: center !important;
  }

  .pagination {
    justify-content: center !important;
    margin-left: -50px !important;
  }

  div.dataTables_wrapper div.dataTables_length select {
    width: 60px !important;
  }
</style>
<style>
  .wallet-container:hover .member-infoooo {
    display: inline-block;
  }

  .offcanvas {
    backdrop-filter: none;
    /* Remove the backdrop-filter property */
    /* Add any other necessary styling */
  }

  .offcanvas-header {
    background-color: #fff;
    /* Set a background color to avoid the blur effect */
    /* Add any other necessary styling */
  }
</style>
<style>
  body {
    overflow-x: hidden;
  }

  .navbar {
    padding: 15px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
  }

  #sidebar {
    position: fixed;
    top: 0;
    right: -250px;
    height: 100%;
    width: 250px;
    background-color: #343a40;
    transition: all 0.3s;
    z-index: 1;
    overflow-y: auto;
  }

  #sidebar a {
    padding: 15px;
    text-decoration: none;
    font-size: 18px;
    color: #818181;
    display: block;
    transition: color 0.3s;
  }

  #sidebar a:hover {
    color: #f8f9fa;
  }

  #content {
    transition: margin-right 0.3s;
    padding: 15px;
  }

  #menu-toggle {
    font-size: 24px;
    cursor: pointer;
    color: #007bff;
  }
</style>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<style>
  @media (min-width: 576px) {

    /* Apply background image for screens wider than 576px */
    #layout-navbar {
      background-image: url("{{ asset('assets/img/backgrounds/nav.jpg') }}");
      background-size: contain;
      background-repeat: no-repeat;
    }


  }

  @media (max-width: 576px) {

    .navvv {

      margin-top: -17px !important;
      margin-left: -97px !important;
    }
  }
</style>
<div class="preloader">
  <div class="loader"></div>
</div>
<!-- Bootstrap JS -->
<!-- Bootstrap 4 JS -->



<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.css" rel="stylesheet" type='text/css'>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script>
  toastr.options = {
    "closeButton": false,
    "debug": false,
    "newestOnTop": false,
    "progressBar": false,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
  };
</script>
@php
$containerNav = $containerNav ?? 'container-fluid';
$navbarDetached = ($navbarDetached ?? '');

@endphp
<!-- Navbar -->
@if(isset($navbarDetached) && $navbarDetached == 'navbar-detached')
<nav class="layout-navbar {{$containerNav}} navbar navbar-expand-xl {{$navbarDetached}} align-items-center bg-navbar-theme" id="layout-navbar">
  @endif
  @if(isset($navbarDetached) && $navbarDetached == '')
  <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="{{$containerNav}}">
      @endif

      <!--  Brand demo (display only for navbar-full and hide on below xl) -->
      @if(isset($navbarFull))
      <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
        <a href="{{url('/')}}" class="app-brand-link gap-2">
          <!-- <span class="app-brand-logo demo">
            @include('_partials.macros',["width"=>25,"withbg"=>'#0081b8'])
          </span>
          <span class="app-brand-text demo menu-text fw-bolder">{{config('variables.templateName')}}</span>
        </a> -->
      </div>
      @endif

      <!-- ! Not required for layout-without-menu -->
      @if(!isset($navbarHideToggle))
      <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
          <i class="bx bx-menu bx-sm"></i>
        </a>
      </div>
      @endif

      <div class="navbar-nav-right d-flex align-items-center navvv" id="navbar-collapse">
        <!-- Search -->
        <!-- <div class="navbar-nav align-items-center">
          <div class="nav-item d-flex align-items-center">
            <i class="bx bx-search fs-4 lh-0"></i>
            <input type="text" class="form-control border-0 shadow-none" placeholder="Search..." aria-label="Search...">
          </div>
        </div> -->
        <!-- /Search -->
        <ul class="navbar-nav flex-row align-items-center ms-auto">

          <!-- Place this tag where you want the button to render. -->
          <!-- <li class="nav-item lh-1 me-3">
            <a class="github-button" href="https://github.com/themeselection/sneat-html-laravel-admin-template-free" data-icon="octicon-star" data-size="large" data-show-count="true" aria-label="Star themeselection/sneat-html-laravel-admin-template-free on GitHub">Star</a>
          </li> -->
          @canany(['expenses-create','labour expenses-create','vendor expenses-create'])
          <li class="nav-item lh-1 me-3 dropdown">
            <img src="{{asset('assets/img/icons/expense.png')}}" class="dropbtn" alt="slack" class="me-3" height="70" width="70">
            <div class="dropdown-content">
              @can('expenses-create') <a href="{{ route('expenses-create') }}">Add Expenses</a>@endcan
              @can('labour expenses-create') <a href="{{ route('labour-expenses-create') }}">Add Labour Expenses</a> @endcan
              @can('vendor expenses-create') <a href="{{ route('vendor-expenses-create') }}">Add Vendor Expenses</a>@endcan
            </div>
          </li>
          @endcanany
          <!--- transfer started -->
          @can('transfer-create') <li class="nav-item lh-1 me-3">
            <div>
              <a href="javascript:void(0)" id="transfer-click">
                <img src="{{asset('assets/img/icons/transfer.png')}}" alt="slack" class="me-3" height="30"></a>
            </div>
            <!-- <button type="button"  id="transfer-click"  class="btn btn-primary" style="background-color: #0081b8;cursor:pointer;" ><span><i class="bi bi-currency-exchange fs-5 plh-0"></i></span>&nbsp;Transfer </button> -->


          </li>
          @endcan
          <!-- transfer ended -->

          <!-- wallet started --->


          <li class="nav-item lh-1 me-3">
            <div class="wallet-container">
              <button type="button" @can('wallet-create') id="wallet-click" @endcan class="btn btn-primary card4" style="background-color: #0081b8; cursor: pointer;" onmouseover="showWallet()" onmouseleave="closeWallet()">

                <span style="margin-left: -7px;">
                  <i class="bi bi-wallet fs-5 plh-0" style="color: white; font-weight:800" onmouseover="showWallet()" onmouseleave="closewallet()"></i>
                </span>&nbsp;
                <b id="wallet-info" class="member-infoooo" style="display: none; color:white; ">{{ Auth::user()->wallet }}</b>
              </button>
            </div>

            <script>
              function showWallet() {
                var walletInfo = document.getElementById('wallet-info');
                walletInfo.style.display = 'inline-block';
              }

              function closeWallet() {
                console.log('closewallet');
                var walletInfo = document.getElementById('wallet-info');
                walletInfo.style.display = 'none';
              }
            </script>
          </li>

          <!-- wallet ended -->
          {{-- <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">Toggle right offcanvas</button> --}}


          <!-- User -->
          <a href="#">
            <li class="nav-item navbar-dropdown dropdown-user dropdown" data-bs-toggle="offcanvas" data-bs-target="#demo">


              <div class="avatar avatar-online menu-click" data-bs-toggle="offcanvas" data-bs-target="#demo">
                @if(Auth::user()->image != '' || Auth::user()->image != null)
                <img class="rounded float-left" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight" src="public/images/{{ Auth::user()->image }}" width="30px" style="border-radius: 1.375rem !important;">
                @else
                <img id="navbarImage" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight" class=" w-px-40 h-auto rounded float-left" src="{{asset('assets/img/icons/gray-user-profile-icon.png')}}" width="30px" style="border-radius: 1.375rem !important;">
                @endif
              </div>




            </li>
          </a>



          <!-- Add the following HTML at the end of your existing code -->






          {{-- <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="javascript:void(0);">
                  <div class="d-flex">
                    <div class="flex-shrink-0 me-3">
                      <div class="avatar avatar-online">
                      @if(Auth::user()->image != '' || Auth::user()->image != null) <img class="rounded float-left" src="public/images/{{ Auth::user()->image }}" width="30px" style="border-radius: 1.375rem !important;"> @else <img class=" w-px-40 h-auto rounded float-left" src="{{asset('assets/img/icons/gray-user-profile-icon.png')}}" width="30px" style="border-radius: 1.375rem !important;"> @endif
      </div>
    </div>
    <div class="flex-grow-1">
      <span class="fw-semibold d-block"> {{ Auth::user()->first_name}} {{Auth::user()->last_name }}</span>
      <small class="text-muted">{{Auth::user()->getRoleNames()[0]}}</small>
    </div>
    </div>
    </a>
    </li>
    <li>
      <div class="dropdown-divider"></div>
    </li>

    <li>
      <div class="dropdown-divider"></div>
    </li>
    <li>
      <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <i class='bx bx-power-off me-2'></i>
        <span class="align-middle">Log Out</span>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
          @csrf
        </form>
      </a>
    </li>
    </ul> --}}

    <!--/ User -->
    </ul>
    </div>

    @if(!isset($navbarDetached))
    </div>
    @endif
  </nav>

  <!-- / Navbar -->
  <script>
    var preloader = document.querySelector(".preloader");
    window.onload = function() {
      preloader.style.display = "none";
    };
  </script>

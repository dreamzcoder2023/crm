@extends('layouts/contentNavbarLayout')

@section('title', 'Create | HOUSE FIX - A DOCTOR FOR YOUR HOUSE')

@section('content')
<!-- <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Forms/</span></h4> -->
<style>
    /* .error{
        position: relative;
    left: -170px;
    top: 30px;
    } */
</style>
<!-- Basic Layout & Basic with Icons -->
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Add Member
</h4>
        <div class="row" style="position:absolute; top:150px; right:50px;  ">
  <div class="col-md-12">
        <ul class="nav nav-pills flex-column flex-md-row mb-3">
      <li class="nav-item"><a class="nav-link active" href="{{route('user-index')}}"><i class="bx me-1"></i> Back </a></li>

    </ul>
  </div></div>
<!-- Tabs navs -->
<ul class="nav nav-tabs">
   <li class="active"><a href="#general-info" data-toggle="tab"  class="active">General Info</a></li>
   <li><a href="#job-info" data-toggle="tab">Job Info</a></li>
</ul>
<!-- Tabs navs -->

<div class="row">
<div class="tab-content">
   <div class="tab-pane active" id="general-info">
<form name="createMember" action="{{route('user.store')}}" id="createMember" method="post" enctype="multipart/form-data">
            @csrf
  <div class="col-xl">
    <div class="card mb-4" style="margin-top:30px;">
      <div class="card-header d-flex justify-content-between align-items-center">

      </div>

      <div class="card-body">
        <div class="row">
            <div class="col-6"><div class="mb-3">
            <label class="form-label" for="basic-default-fullname">First Name</label>
            <input type="text" id="first_name" name="first_name" class="form-control" id="basic-default-fullname" onkeydown="return /[a-z, ]/i.test(event.key)"
                 onblur="if (this.value == '') {this.value = '';}"
                onfocus="if (this.value == '') {this.value = '';}" placeholder="Enter First name" />
                <label id="first-error" class="error" for="basic-default-first_name">First Name is required</label>
          </div>
          <div class="mb-3">
            <label class="form-label" for="basic-default-company">Last Name</label>
            <input type="text" id="last_name" name="last_name" class="form-control" id="basic-default-company" onkeydown="return /[a-z, ]/i.test(event.key)"
            onblur="if (this.value == '') {this.value = '';}"
            onfocus="if (this.value == '') {this.value = '';}" placeholder="Enter Last name" />
            <label id="last-error" class="error" for="basic-default-last_name">last Name is required</label>
          </div>
          <div class="mb-3">
            <label class="form-label" for="basic-default-email">Email</label>

              <input  type="email" id="email" name="email" class="form-control" placeholder="Enter Email"  value=""/>
              <label id="email-error" class="error" for="basic-default-email">Email is required</label>
              <label id="email-invalid-error" class="error" for="basic-default-email">Email is invalid</label>

          </div>
          <div class="mb-3">
            <label class="form-label" for="basic-default-email">Password</label>

              <input  type="password" id="password" name="password" class="form-control" placeholder="Enter Password" value="" />
              <label id="password-error" class="error" for="basic-default-password">Password is required</label>
              <label id="password-invalid-error" class="error" for="basic-default-password">Password must be at least 8 characters long</label>

          </div>
          <div class="mb-3">
            <label class="form-label" for="basic-default-email">Confirm Password</label>

              <input  type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Enter Confirm Password" aria-label="john.doe" aria-describedby="basic-default-email2" />
              <label id="confirm-error" class="error" for="basic-default-confirm_password">Confirm Password is required</label>
              <label id="confirm-invalid-error" class="error" for="basic-default-password">Confirm Password must be at least 8 characters long</label>
              <label id="confirm-match-error" class="error" for="basic-default-password">Confirm password must match to Password</label>

          </div></div>
            <div class="col-6"><div class="mb-3">
            <label class="form-label" for="basic-default-phone">Phone Number</label>
            <input type="text" maxlength="10" name="phone" id="phone" class="form-control phone-mask"
            onkeypress="allowNumbersOnly(event)" placeholder="Enter Phone number" oninput="phoneunique(this.value)" />
    <label id="phone-error" class="error" for="basic-default-phone">Phone number is required</label>
    <input type="hidden" class="phone-unique-error" value="">
    <label id="phone-unique-error" class="error" for="basic-default-phone">Phone number already exists</label>
          </div>
          <div class="mb-3">
            <label class="form-label" for="basic-default-message">Gender</label><br>
            <input  type="radio" class="gender" value="1" id="male" name="gender">
            <label  class="form-label" for="male">Male</label> &nbsp;
            <input type="radio" class="gender" value="2" id="female" name="gender">
            <label class="form-label" for="female">Female</label> &nbsp;
            <input type="radio" value="3" class="gender" id="other" name="gender">
            <label class="form-label" for="male">Other</label><br/>
            <label id="gender-error" class="error" for="basic-default-gender">Gender  is required</label>
          </div>
          <div class="mb-3">
          <label class="form-label" for="basic-default-phone">Profile Photo</label>
          <input type="file" name="image" id="image" class="form-control" placeholder="image" accept="image/*">
            <label id="job_title-error" class="error" for="basic-default-job_title">Profile photo is required</label>
          </div>
          <div class="mb-3">
            <label class="form-label" for="basic-default-phone">Roles</label>
            <select class="form-control" name="roles" id="roles">
            <option value="">Select roles </option>
            @foreach($role as $role)
            <option value="{{$role->id}}">{{$role->name}}</option>
            @endforeach
            </select>
            <label id="roles-error" class="error" for="basic-default-role">Role is required</label>
          </div>
      <center>  <button type="submit" class="btn btn-primary" style="margin-top: 20px;">Submit</button>
      <button type="reset" id="resetform" class="btn btn-danger" style="background-color: red; margin-top:20px;">Reset</button>
    </center>
        </div>

        </div>



      </div>
    </div>
  </div>
</form>
</div>
  </div></div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('.error').addClass('hide');
    });
      function allowNumbersOnly(e) {
    var code = (e.which) ? e.which : e.keyCode;
    if (code > 31 && (code < 48 || code > 57)) {
        e.preventDefault();
    }
  }
    $('#createMember').submit(function(e) {
    e.preventDefault();
    var first_name = $('#first_name').val();
    var last_name = $('#last_name').val();
    var email = $('#email').val();
    var password = $('#password').val();
    var confirm_password = $('#confirm_password').val();
    var phone = $('#phone').val();
    var job_title = $('#image').val();
    var gender = $('.gender:checked').length;
    var roles = $('#roles').find(":selected").val();
  var test = $('.phone-unique-error').val();
 var fname=false,lname=false,emailname=false,passwordname=false,conname=false,phonename=false,jobname=false,gendername=false,rolesname=false;
    console.log('first',first_name);
    console.log('last',last_name);
    console.log('email',email);
    console.log('password',password);
    console.log('confirm',confirm_password);
    console.log('phone',phone);
    console.log('job_title',job_title);
    console.log('gender',gender);
    console.log('roles',roles);
    console.log('test',test);

    if (first_name =="") {
      $('#first-error').removeClass('hide');
    }
    else{
      $('#first-error').addClass('hide');
      fname = true;
    }
    if (last_name == "") {
      $('#last-error').removeClass('hide');
    }
    else{
      $('#last-error').addClass('hide');
      lname = true;
    }
    if (email == "") {
      console.log('email length',email);
      $('#email-error').removeClass('hide');
    } else  {
      var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        if(!regex.test(email)) {
        $('#email-error').addClass('hide');
        $('#email-invalid-error').removeClass('hide');
      }
      else{
        $('#email-error').addClass('hide');
        $('#email-invalid-error').addClass('hide');
        emailname = true;
      }
    }
    if(password.length < 1){
      $('#password-error').removeClass('hide');
    }
    else
    { if (password.length < 8) {
      $('#password-error').addClass('hide');
      $('#password-invalid-error').removeClass('hide');
    }
    else{
      $('#password-error').addClass('hide');
      $('#password-invalid-error').addClass('hide');
      passwordname = true;
    }
  }
  if(confirm_password.length < 1){
      $('#confirm-error').removeClass('hide');
    }
    else if (confirm_password.length < 8) {
      $('#confirm-error').addClass('hide');
      $('#confirm-invalid-error').removeClass('hide');
    }
    else{
      if(password != confirm_password){
        $('#confirm-error').addClass('hide');
      $('#confirm-invalid-error').addClass('hide');
        $('#confirm-match-error').removeClass('hide');
      }
      else{
        $('#confirm-error').addClass('hide');
      $('#confirm-invalid-error').addClass('hide');
        $('#confirm-match-error').addClass('hide');
        conname = true;
      }
    }
    if(phone.length < 1){
      $('#phone-error').removeClass('hide');
    }
    else{
      $('#phone-error').addClass('hide');
      phonename = true;
    }
    if(job_title == "" ){
      $('#job_title-error').removeClass('hide');
    }
    else{
      $('#job_title-error').addClass('hide');
      jobname = true;
    }
    if(gender < 1){
      $('#gender-error').removeClass('hide');
    }
    else{
      $('#gender-error').addClass('hide');
      gendername = true;
    }
    if(roles.length < 1){
      $('#roles-error').removeClass('hide');
    }
    else{
      $('#roles-error').addClass('hide');
      rolesname = true;
    }
    if(fname == true && lname == true && emailname == true && passwordname == true && conname == true && phonename == true && jobname == true && gendername == true && rolesname == true && (test == false || test == "false")){
      document.getElementById("createMember").submit();
    }
  });
  function phoneunique(phone){
      console.log(phone,"phone unique");
      if(phone.length == 10){
        $.ajax({
        url : "{{ route('phoneunique') }}",
        data : {'phone' : phone},
        type : 'GET',
        dataType : 'json',
        success : function(result){
          console.log("result",result);
          $('.phone-unique-error').val(result);
          if(result == true)
             $('#phone-unique-error').removeClass('hide');
          else
          $('#phone-unique-error').addClass('hide');
        }
    });
      }
    }
//     $(document).ready(function(){
//         $('#basic-default-role-error').hide();
//     });
// $('form[name="createMember"]').validate({
//   rules: {
//     first_name:{
//         required: true,
//     },
//     last_name:{
//         required: true,

//     },
//     email: {
//       required: true,
//       email: true,
//     },
//     password: {
//       required: true,
//       minlength: 8,
//     },
//     confirm_password: {
//       required: true,
//       minlength: 8,
//       equalTo: "#password"
//     },
//     phone:{
//         required:true,
//         maxLength:10,
//     },
//     gender:{
//         required:true,
//     },
//     job_title:{
//         required:true,
//     }
//     // roles:{
//     //     require: true
//     // }

//   },
//   messages: {
//     first_name:{
//         required: "First Name is required",
//     },
//     last_name:{
//         required: "Last Name is required",

//     },
//     email: {
//       required: "Email is required",
//       email: "Enter valid email",
//     },
//     password: {
//         required: 'Password is required',
//       minlength: 'Password must be at least 8 characters long'
//     },
//     confirm_password: {
//         required: 'Confirm password is required',
//       minlength: 'Password must be at least 8 characters long',
//       equalTo : 'Confirm password must match to Password'
//     },
//     phone:{
//         required:"Phone number is required",
//         maxLength:"Enter valid phone number",
//     },
//     gender:{
//         required:"Gender is required",
//     },
//     job_title:{
//         required:"Job title is required"
//     }
// //  roles:{
// //         require: "Roles is required"
// //     }
//   },
//   submitHandler: function(form) {
//     var e = document.getElementById("roles");
//     var mobileVal = e.options[e.selectedIndex].value;
//     if(mobileVal == "") {
//         console.log('mobile',mobileVal);
//     $('#basic-default-role-error').show();
//     }
//     else{
//     form.submit();
//     }
//   }
// });
</script>
@endsection

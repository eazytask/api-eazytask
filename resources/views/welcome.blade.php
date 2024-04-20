<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Eazytask API</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
</head>

<body class="text-center">

    <nav class="bg-black">
        <div class="nav nav-tabs p-1 justify-content-center" id="nav-tab" role="tablist">
            <button class="nav-link active" id="section-basic-tab" data-bs-toggle="tab" data-bs-target="#section-basic" type="button" role="tab" aria-controls="section-basic" aria-selected="true">Basic</button>
            <button class="nav-link" id="section1-tab" data-bs-toggle="tab" data-bs-target="#section1" type="button" role="tab" aria-controls="section1" aria-selected="false">Authentication</button>
            <button class="nav-link" id="superAdmin-tab" data-bs-toggle="tab" data-bs-target="#superAdmin" type="button" role="tab" aria-controls="superAdmin" aria-selected="false">Super-Admin</button>

            <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin" type="button" role="tab" aria-controls="admin" aria-selected="false">Admin</button>
            <button class="nav-link" id="user-tab" data-bs-toggle="tab" data-bs-target="#user" type="button" role="tab" aria-controls="user" aria-selected="false">Employee</button>
            <button class="nav-link" id="supervisor-tab" data-bs-toggle="tab" data-bs-target="#supervisor" type="button" role="tab" aria-controls="supervisor" aria-selected="false">Supervisor</button>
            <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Common</button>
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="section-basic" role="tabpanel" aria-labelledby="section-basic-tab" tabindex="0">
            <div id="section-basic" class="container-fluid bg-success text-white" style="padding:100px 20px;">

                <div class="row">
                    <div class="col-md-6">
                        <h2 class="">Base URL:</h2>
                        <strong>https://api.eazytask.au/api/v1/</strong>
                        <br>
                        <br>

                        <div class="card">
                            <div class="card-header">
                                <h2 class="text-dark">Status</h2>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped table-bordered table-responsive">
                                    <thead>
                                        <tr>
                                            <th scope="col">Name</th>
                                            <th scope="col">code</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Active</td>
                                            <td>1</td>
                                        </tr>
                                        <tr>
                                            <td>Inactive</td>
                                            <td>0</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2">
                        <div class="card">
                            <div class="card-header">
                                <h2 class="text-dark">User Role</h2>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped table-bordered table-responsive">
                                    <thead>
                                        <tr>
                                            <th scope="col">User</th>
                                            <th scope="col">Role</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Super-Admin</td>
                                            <td>1</td>
                                        </tr>
                                        <tr>
                                            <td>Admin</td>
                                            <td>2</td>
                                        </tr>
                                        <tr>
                                            <td>Employee</td>
                                            <td>3</td>
                                        </tr>
                                        <tr>
                                            <td>Supervisor</td>
                                            <td>4</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="card">
                            <div class="card-header bg-primary bg-opacity-25">
                                <h2 class="text-dark">Necessary Data</h2>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped table-bordered table-responsive">
                                    <thead>
                                        <tr>
                                            <th scope="col">Method</th>
                                            <th scope="col">URL</th>
                                            <th scope="col">Fields</th>
                                            <th scope="col">Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>Get</th>
                                            <td>/job/type</td>
                                            <td>
                                                {
                                                <!-- "token": "required" -->
                                                }
                                            </td>
                                            <td>fetch job-type</td>
                                        </tr>
                                        <tr>
                                            <th>Get</th>
                                            <td>/roster/status</td>
                                            <td>
                                                { }
                                            </td>
                                            <td>fetch roaster-status</td>
                                        </tr>
                                        <tr>
                                            <th>Get</th>
                                            <td>/projects/{all}</td>
                                            <td>
                                                { }
                                            </td>
                                            <td>fetch projects(by default only active). You can pass a parameter(/all) but it's optional</td>
                                        </tr>
                                        <tr>
                                            <th>Get</th>
                                            <td>/employees/{all}</td>
                                            <td>
                                                { }
                                            </td>
                                            <td>fetch employees(by default only active). You can pass a parameter(/all) but it's optional</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="section1" role="tabpanel" aria-labelledby="section1-tab" tabindex="0">

            <div id="section1" class="container-fluid bg-secondary text-white pt-5 pb-5">

                <div class="card">
                    <div class="card-header">
                        <h2 class="text-dark">Authentication API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Post</th>
                                    <td>/login</td>
                                    <td>
                                        {
                                        "email": "required",
                                        "password": "required"
                                        }
                                    </td>
                                    <td>login</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/logout</td>
                                    <td>
                                        { }
                                    </td>
                                    <td>logout authenticate user</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/forget/password</td>
                                    <td>
                                        {
                                        "email":"required|email"
                                        }
                                    </td>
                                    <td>send reset password link to email</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- all superAdmin api -->
        <div class="tab-pane fade" id="superAdmin" role="tabpanel" aria-labelledby="superAdmin-tab" tabindex="0">...</div>

        <!-- all admin api -->
        <div class="tab-pane fade" id="admin" role="tabpanel" aria-labelledby="admin-tab" tabindex="0">
            <div id="section1" class="container-fluid bg-success text-white pt-5 pb-5">

                <div class="card mt-5">
                    <div class="card-header">
                        <h2 class="text-dark">Employee API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Get</th>
                                    <td>/admin/employee</td>
                                    <td>
                                        {
                                        <!-- "token": "required" -->
                                        }
                                    </td>
                                    <td>fetch all employees</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/admin/employee</td>
                                    <td>
                                        {
                                        "fname":"required",
                                        "mname":"optional",
                                        "lname":"required",
                                        "email":"required",
                                        "password":"required",
                                        "contact_number":"required",
                                        "date_of_birth":"optional",
                                        "file":optional,
                                        "role":"required", // 3|4
                                        "status":"required", // 0|1
                                        "address":"required",
                                        "suburb":"required",
                                        "postal_code":"required",
                                        "state":"required",
                                        "has_compliance":"optional", // true|false
                                        "Compliance":[
                                        {
                                        "compliance":"required",
                                        "certificate_no":"required",
                                        "expire_date":"required",
                                        "comment":"optional"
                                        }
                                        ]
                                        }
                                    </td>
                                    <td>store employee. If has_compliance=true, then Compliance array is required</td>
                                </tr>
                                <tr>
                                    <th>Put</th>
                                    <td>/admin/employee</td>
                                    <td>
                                        {
                                        "id":"required",
                                        "fname":"required",
                                        "mname":"optional",
                                        "lname":"required",
                                        "contact_number":"required",
                                        "date_of_birth":"optional",
                                        "file":optional,
                                        "role":"required", // 3|4
                                        "status":"required", // 0|1
                                        "address":"required",
                                        "suburb":"required",
                                        "postal_code":"required",
                                        "state":"required",
                                        "has_compliance":"optional", // true|false
                                        "Compliance":[
                                        {
                                        "compliance":"required",
                                        "certificate_no":"required",
                                        "expire_date":"required",
                                        "comment":"optional"
                                        }
                                        ]
                                        }
                                    </td>
                                    <td>update employee. If has_compliance=true, then Compliance array is required</td>
                                </tr>
                                <tr>
                                    <th>Delete</th>
                                    <td>/admin/employee/{id}</td>
                                    <td>{ }</td>
                                    <td>delete employee</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/admin/employee/compliance</td>
                                    <td>
                                        {
                                        "email":"required",
                                        }
                                    </td>
                                    <td>fetch all compliances of a employee</td>
                                </tr>
                                <tr>
                                    <th>Get</th>
                                    <td>/admin/compliance</td>
                                    <td>
                                        {
                                        "email":"required",
                                        }
                                    </td>
                                    <td>fetch all compliances type</td>
                                </tr>
                                <tr>
                                    <th>Get</th>
                                    <td>/admin/employee/shift-details</td>
                                    <td>{ }</td>
                                    <td>Fetch All Shift Details with employee, client and project details</td>
                                </tr>
                                <tr>
                                    <th>Get</th>
                                    <td>employee/shift-details/{employee_id}</td>
                                    <td>{ }</td>
                                    <td>Fetch All Shift Details of single employee with client and project details</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header">
                        <h2 class="text-dark">Employee Inducted API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Get</th>
                                    <td>/admin/inducted/site</td>
                                    <td>
                                        {
                                        <!-- "token": "required" -->
                                        }
                                    </td>
                                    <td>fetch all inducted</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/admin/inducted/site</td>
                                    <td>
                                        {
                                        "employee_id": "required",
                                        "project_id": "required",
                                        "induction_date": "required",
                                        "remarks": "optional"
                                        }
                                    </td>
                                    <td>store inducted</td>
                                </tr>
                                <tr>
                                    <th>Put</th>
                                    <td>/admin/inducted/site</td>
                                    <td>
                                        {
                                        "id": required,
                                        "employee_id": "required",
                                        "project_id": "required",
                                        "induction_date": "required",
                                        "remarks": "optional"
                                        }
                                    </td>
                                    <td>update inducted</td>
                                </tr>
                                <tr>
                                    <th>Delete</th>
                                    <td>/admin/inducted/site/{id}</td>
                                    <td>{ }</td>
                                    <td>delete inducted</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header bg-primary bg-opacity-25">
                        <h2 class="text-dark">Employee Unavailability API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Get</th>
                                    <td>/admin/employee/unavailability</td>
                                    <td>
                                        {
                                        <!-- "token": "required" -->
                                        }
                                    </td>
                                    <td>fetch unavailabilities</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/admin/employee/unavailability</td>
                                    <td>
                                        {
                                        "employee_id":required,
                                        "start_date":"required",
                                        "end_date":"required"
                                        }
                                    </td>
                                    <td>store unavailability</td>
                                </tr>
                                <tr>
                                    <th>Put</th>
                                    <td>/admin/employee/unavailability</td>
                                    <td>
                                        {
                                        "id":required,
                                        "employee_id":required,
                                        "start_date":"required",
                                        "end_date":"required"
                                        }
                                    </td>
                                    <td>update unavailability</td>
                                </tr>
                                <tr>
                                    <th>Delete</th>
                                    <td>/admin/employee/unavailability/{id}</td>
                                    <td>{ }</td>
                                    <td>delete unavailability</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header">
                        <h2 class="text-dark">Clients API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Get</th>
                                    <td>/admin/client</td>
                                    <td>
                                        {
                                        <!-- "token": "required" -->
                                        }
                                    </td>
                                    <td>fetch clients</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/admin/client</td>
                                    <td>
                                        {
                                        "cname":"required",
                                        "cemail":"required",
                                        "cnumber":"required",
                                        "file":optional, //base64_image
                                        "cperson":"required",
                                        "status":"required",
                                        "caddress":"required",
                                        "suburb":"required",
                                        "cpostal_code":"required",
                                        "cstate":"required"
                                        }
                                    </td>
                                    <td>store client</td>
                                </tr>
                                <tr>
                                    <th>Put</th>
                                    <td>/admin/client</td>
                                    <td>
                                        {
                                        "id":"required",
                                        "cname":"required",
                                        "cemail":"required",
                                        "cnumber":"required",
                                        "file":optional, //base64_image
                                        "cperson":"required",
                                        "status":"required",
                                        "caddress":"required",
                                        "suburb":"required",
                                        "cpostal_code":"required",
                                        "cstate":"required"
                                        }
                                    </td>
                                    <td>update client</td>
                                </tr>
                                <tr>
                                    <th>Delete</th>
                                    <td>/admin/client/{id}</td>
                                    <td>{ }</td>
                                    <td>delete client</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header">
                        <h2 class="text-dark">Project API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Get</th>
                                    <td>/admin/project</td>
                                    <td>
                                        {
                                        <!-- "token": "required" -->
                                        }
                                    </td>
                                    <td>fetch projects</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/admin/project</td>
                                    <td>
                                        {
                                        "pName":"required",
                                        "cName":"required",
                                        "cNumber":"required",
                                        "clientName":"required",
                                        "Status":"required",
                                        "project_address":"required",
                                        "suburb":"required",
                                        "project_state":"required",
                                        "postal_code":"required",
                                        "lat":"required",
                                        "lon":"required"
                                        }
                                    </td>
                                    <td>store project</td>
                                </tr>
                                <tr>
                                    <th>Put</th>
                                    <td>/admin/project</td>
                                    <td>
                                        {
                                        "id":"required",
                                        "pName":"required",
                                        "cName":"required",
                                        "cNumber":"required",
                                        "clientName":"required",
                                        "Status":"required",
                                        "project_address":"required",
                                        "suburb":"required",
                                        "project_state":"required",
                                        "postal_code":"required",
                                        "lat":"required",
                                        "lon":"required"
                                        }
                                    </td>
                                    <td>update project</td>
                                </tr>
                                <tr>
                                    <th>Delete</th>
                                    <td>/admin/project/{id}</td>
                                    <td>{ }</td>
                                    <td>delete project</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header">
                        <h2 class="text-dark">Revenue API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Get</th>
                                    <td>/admin/revenue</td>
                                    <td>
                                        {
                                        <!-- "token": "required" -->
                                        }
                                    </td>
                                    <td>fetch revenues</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/admin/revenue</td>
                                    <td>
                                        {
                                        "project_name": "required",
                                        "roaster_date_from": "required",
                                        "roaster_date_to": "required",
                                        "hours": "required",
                                        "rate": "required",
                                        "amount": "required",
                                        "remarks": "optional",
                                        "payment_date": "required"
                                        }
                                    </td>
                                    <td>store revenue</td>
                                </tr>
                                <tr>
                                    <th>Put</th>
                                    <td>/admin/revenue</td>
                                    <td>
                                        {
                                        "id": required,
                                        "project_name": "required",
                                        "roaster_date_from": "required",
                                        "roaster_date_to": "required",
                                        "hours": "required",
                                        "rate": "required",
                                        "amount": "required",
                                        "remarks": "optional",
                                        "payment_date": "required"
                                        }
                                    </td>
                                    <td>update revenue</td>
                                </tr>
                                <tr>
                                    <th>Delete</th>
                                    <td>/admin/revenue/{id}</td>
                                    <td>{ }</td>
                                    <td>delete revenue</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/admin/revenue/search</td>
                                    <td>
                                        {
                                        "start_date": "required",
                                        "end_date": "required",
                                        "client_id": "optional",
                                        "project_id": "optional"
                                        }
                                    </td>
                                    <td>search revenue</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header">
                        <h2 class="text-dark">Timekeeper API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Get</th>
                                    <td>/admin/timekeeper</td>
                                    <td>
                                        {
                                        <!-- "token": "required" -->
                                        }
                                    </td>
                                    <td>fetch timekeeper</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/admin/timekeeper</td>
                                    <td>
                                        {
                                        "project_id": "required",
                                        "job_type_id": "required",
                                        "employee_id": "required",
                                        "roaster_date": "required",
                                        "shift_start": "required",
                                        "shift_end": "required",
                                        "duration": "required",
                                        "ratePerHour": "required",
                                        "amount": "required",
                                        "roaster_status_id": optional,
                                        "roaster_type": optional,
                                        "remarks": optional,
                                        "process": "optional|boolean"
                                        }
                                    </td>
                                    <td>store timekeeper</td>
                                </tr>
                                <tr>
                                    <th>Put</th>
                                    <td>/admin/timekeeper</td>
                                    <td>
                                        {
                                        "timekeeper_id": required,
                                        "project_id": "required",
                                        "job_type_id": "required",
                                        "employee_id": "required",
                                        "roaster_date": "required",
                                        "shift_start": "required",
                                        "shift_end": "required",
                                        "duration": "required",
                                        "ratePerHour": "required",
                                        "amount": "required",
                                        "roaster_status_id": optional,
                                        "roaster_type": optional,
                                        "remarks": optional,
                                        "process": "optional|boolean"
                                        }
                                    </td>
                                    <td>update timekeeper</td>
                                </tr>
                                <tr>
                                    <th>Delete</th>
                                    <td>/admin/timekeeper/{id}</td>
                                    <td>{ }</td>
                                    <td>delete timekeeper</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/admin/timekeeper/search</td>
                                    <td>
                                        {
                                        "start_date": "required",
                                        "end_date": "required",
                                        }
                                    </td>
                                    <td>search timekeeper</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header">
                        <h2 class="text-dark">Activity Log API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Get</th>
                                    <td>/admin/activity/log</td>
                                    <td>
                                        {
                                        <!-- "token": "required" -->
                                        }
                                    </td>
                                    <td>fetch logs</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/admin/activity/log/search</td>
                                    <td>
                                        {
                                        "start_date": "required",
                                        "end_date": "required",
                                        }
                                    </td>
                                    <td>search logs</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header bg-primary bg-opacity-25">
                        <h2 class="text-dark">Job-Type API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Get</th>
                                    <td>/admin/job/type</td>
                                    <td>
                                        {
                                        <!-- "token": "required" -->
                                        }
                                    </td>
                                    <td>fetch job-type</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/admin/job/type</td>
                                    <td>
                                        {
                                        "name":required,
                                        }
                                    </td>
                                    <td>store job-type</td>
                                </tr>
                                <tr>
                                    <th>Put</th>
                                    <td>/admin/job/type</td>
                                    <td>
                                        {
                                        "id":required,
                                        "name":required
                                        }
                                    </td>
                                    <td>update job-type</td>
                                </tr>
                                <tr>
                                    <th>Delete</th>
                                    <td>/admin/job/type/{id}</td>
                                    <td>{ }</td>
                                    <td>delete job-type</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card mt-5">
                    <div class="card-header bg-primary bg-opacity-25">
                        <h2 class="text-dark">Roster-Status API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Get</th>
                                    <td>/admin/roster/status</td>
                                    <td>
                                        {
                                        <!-- "token": "required" -->
                                        }
                                    </td>
                                    <td>fetch roster-status</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/admin/roster/status</td>
                                    <td>
                                        {
                                        "name":required,
                                        }
                                    </td>
                                    <td>store roster-status</td>
                                </tr>
                                <tr>
                                    <th>Put</th>
                                    <td>/admin/roster/status</td>
                                    <td>
                                        {
                                        "id":required,
                                        "name":required
                                        }
                                    </td>
                                    <td>update roster-status</td>
                                </tr>
                                <tr>
                                    <th>Delete</th>
                                    <td>/admin/roster/status/{id}</td>
                                    <td>{ }</td>
                                    <td>delete roster-status</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- all user api -->
        <div class="tab-pane fade" id="user" role="tabpanel" aria-labelledby="user-tab" tabindex="0">
            <div id="user" class="container-fluid bg-primary text-white pb-3">
                <h1 class="pb-4 pt-4">Employee</h1>

                <div class="card">
                    <div class="card-header">
                        <h2 class="text-dark">Employee,Supervisor,Admin Profile Update API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Post</th>
                                    <td>/user/change/pin</td>
                                    <td>
                                        {
                                        "old_password": "required|min:8",
                                        "new_pin": "required|min:4|max:4",
                                        "confirm_pin": "required|min:4|max:4",
                                        }
                                    </td>
                                    <td>change current user pin</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header bg-primary bg-opacity-25">
                        <h2 class="text-dark">Timesheet API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Get</th>
                                    <td>/user/timesheet</td>
                                    <td>
                                        {
                                        <!-- "token": "required" -->
                                        }
                                    </td>
                                    <td>fetch timesheets</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/user/timesheet</td>
                                    <td>
                                        {
                                        "roaster_date": "required" /* 12-09-2022 */,
                                        "shift_start": "required" /* 05:00 */,
                                        "shift_end": "required" /* 07:00 */,
                                        "duration": "required",
                                        "ratePerHour": "required",
                                        "amount": "required",
                                        "project_id": "required",
                                        "job_type_id": "required",
                                        "remarks": "optional"
                                        }
                                    </td>
                                    <td>store timesheet</td>
                                </tr>
                                <tr>
                                    <th>Put</th>
                                    <td>/user/timesheet</td>
                                    <td>
                                        {
                                        "timekeeper_id": "required",
                                        "roaster_date": "required" /* 12-09-2022 */,
                                        "shift_start": "required" /* 05:00 */,
                                        "shift_end": "required" /* 07:00 */,
                                        "duration": "required",
                                        "ratePerHour": "required",
                                        "amount": "required",
                                        "project_id": "required",
                                        "job_type_id": "required",
                                        "remarks": "optional"
                                        }
                                    </td>
                                    <td>update timesheet</td>
                                </tr>
                                <tr>
                                    <th>Delete</th>
                                    <td>/user/timesheet/{id}</td>
                                    <td>{ }</td>
                                    <td>delete timesheet</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/user/timesheet/search</td>
                                    <td>
                                        {
                                        "start_date": "required" /* 05-09-2022 */,
                                        "end_date": "required" /* 11-09-2022 */,
                                        "project_id": "optional"
                                        }
                                    </td>
                                    <td>search timesheet</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card mt-5">
                    <div class="card-header bg-success bg-opacity-25">
                        <h2 class="text-dark">Sign In Roster API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Get</th>
                                    <td>/user/sign/in</td>
                                    <td>
                                        {
                                        <!-- "token": "required" -->
                                        }
                                    </td>
                                    <td>fetch available rosters</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/user/sign/in/timekeeper</td>
                                    <td>
                                        {
                                        "timekeeper_id": "required",
                                        "lat": "optional",
                                        "lon": "optional",
                                        "image":"optional" //base64
                                        }
                                    </td>
                                    <td>sign in to roster</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/user/sign/out/timekeeper</td>
                                    <td>
                                        {
                                        "timekeeper_id": "required",
                                        "lat": "optional",
                                        "lon": "optional",
                                        "image":"optional" //base64
                                        }
                                    </td>
                                    <td>sign out from roster</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/user/start/unschedule</td>
                                    <td>
                                        {
                                        "lat": "optional",
                                        "lon": "optional",
                                        "image":"optional", //base64
                                        "project_id": "required",
                                        "ratePerHour": "required",
                                        "job_type_id": "required",
                                        "remarks": "optional"
                                        }
                                    </td>
                                    <td>start a new unscheduele roster</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header bg-warning bg-opacity-25">
                        <h2 class="text-dark">Upcoming Event API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Get</th>
                                    <td>/upcoming/event</td>
                                    <td>
                                        {
                                        <!-- "token": "required" -->
                                        }
                                    </td>
                                    <td>fetch upcoming events</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/upcoming/event/confirm</td>
                                    <td>
                                        {
                                        "event_id": "required"
                                        }
                                    </td>
                                    <td>showing interest on an event</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header bg-danger bg-opacity-25">
                        <h2 class="text-dark">Unconfirm Roster API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Get</th>
                                    <td>/user/unconfirm/shift</td>
                                    <td>
                                        {
                                        <!-- "token": "required" -->
                                        }
                                    </td>
                                    <td>fetch unconfirm roster</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/user/unconfirm/shift</td>
                                    <td>
                                        {
                                        "action": "required", //accept|reject
                                        "ids":"required|array"
                                        }
                                    </td>
                                    <td>accept/reject an/multiple unconfirm roster</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header bg-danger bg-opacity-25">
                        <h2 class="text-dark">All Shift API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Get</th>
                                    <td>/user/upcoming/shift</td>
                                    <td>
                                        {
                                        <!-- "token": "required" -->
                                        }
                                    </td>
                                    <td>fetch upcoming roster</td>
                                </tr>
                                <tr>
                                    <th>Get</th>
                                    <td>/user/past/shift</td>
                                    <td>
                                        {
                                        <!-- "token": "required" -->
                                        }
                                    </td>
                                    <td>fetch past roster</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header bg-danger bg-opacity-25">
                        <h2 class="text-dark">User Roster Report</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Get</th>
                                    <td>/user/roster/report</td>
                                    <td>
                                        {
                                        <!-- "token": "required" -->
                                        }
                                    </td>
                                    <td>fetch roster report</td>
                                </tr>
                                <tr>
                                    <th>post</th>
                                    <td>/user/roster/report</td>
                                    <td>
                                        {
                                        "start_date": "required",
                                        "end_date": "required",
                                        "payment_status": "optional", // 0|1
                                        "project_id": "optional",
                                        }
                                    </td>
                                    <td>search roster report</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header bg-danger bg-opacity-25">
                        <h2 class="text-dark">User Payment Report</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Post</th>
                                    <td>/user/payment/report</td>
                                    <td>
                                        {
                                        "start_date":"optional",
                                        "end_date":"optional"
                                        }
                                    </td>
                                    <td>fetch payment report(by default it will fetch current month all payment)</td>
                                </tr>
                                <tr>
                                    <th>get</th>
                                    <td>/user/payment/report/{payment_id}</td>
                                    <td>
                                        {

                                        }
                                    </td>
                                    <td>show specific one payment report with details</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header bg-primary bg-opacity-25">
                        <h2 class="text-dark">Employee Unavailability API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Get</th>
                                    <td>/user/unavailability</td>
                                    <td>
                                        {
                                        <!-- "token": "required" -->
                                        }
                                    </td>
                                    <td>fetch unavailabilities</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/user/unavailability</td>
                                    <td>
                                        {
                                        "start_date":"required",
                                        "end_date":"required"
                                        }
                                    </td>
                                    <td>store unavailability</td>
                                </tr>
                                <tr>
                                    <th>Put</th>
                                    <td>/user/unavailability</td>
                                    <td>
                                        {
                                        "id":required,
                                        "start_date":"required",
                                        "end_date":"required"
                                        }
                                    </td>
                                    <td>update unavailability</td>
                                </tr>
                                <tr>
                                    <th>Delete</th>
                                    <td>/user/unavailability/{id}</td>
                                    <td>{ }</td>
                                    <td>delete unavailability</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- all supervisor api -->
        <div class="tab-pane fade" id="supervisor" role="tabpanel" aria-labelledby="supervisor-tab" tabindex="0">supervisor</div>

        <!-- all common api -->
        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
            <div id="section1" class="container-fluid bg-info text-white pt-5 pb-5">
                <h1 class="pb-4 pt-4">Common</h1>

                <div class="card">
                    <div class="card-header">
                        <h2 class="text-dark">Notifications API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>get</th>
                                    <td>/notifications</td>
                                    <td>
                                        {

                                        }
                                    </td>
                                    <td>fetch all notifications</td>
                                </tr>
                                <tr>
                                    <th>get</th>
                                    <td>/notifications/mark/as/read</td>
                                    <td>
                                        {

                                        }
                                    </td>
                                    <td>mark as read all notifications</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header">
                        <h2 class="text-dark">Switch Company API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>get</th>
                                    <td>/companies</td>
                                    <td>
                                        {

                                        }
                                    </td>
                                    <td>fetch all companies</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/switch/company/{company_id}</td>
                                    <td>
                                        {

                                        }
                                    </td>
                                    <td>switch company</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header">
                        <h2 class="text-dark">Employee,Supervisor,Admin Profile Update API</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Post</th>
                                    <td>/change/password</td>
                                    <td>
                                        {
                                        "old_password": "required",
                                        "new_password": "required",
                                        "confirm_password": "required",
                                        }
                                    </td>
                                    <td>change current user password</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/image/update</td>
                                    <td>
                                        {
                                        "file": "required", //base64_image
                                        }
                                    </td>
                                    <td>change current user profile image</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/profile/update</td>
                                    <td>
                                        {
                                        "name": "required",
                                        "mname": "optional",
                                        "lname": "required",
                                        "company": "required",
                                        "company_contact": "required"
                                        }
                                    </td>
                                    <td>if has admin role</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/profile/update</td>
                                    <td>
                                        {
                                        "name": "required",
                                        "s_mname": "optional",
                                        "s_lname": "required",
                                        }
                                    </td>
                                    <td>if not admin but has supervisor role</td>
                                </tr>
                                <tr>
                                    <th>Post</th>
                                    <td>/profile/update</td>
                                    <td>
                                        {
                                        "name": "required",
                                        "e_mname": "optional",
                                        "e_lname": "required",
                                        }
                                    </td>
                                    <td>if only has employee role</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-u1OknCvxWvY5kfmNBILK2hRnQC3Pr17a+RTT6rIHI7NnikvbZlHgTPOOmMi466C8" crossorigin="anonymous"></script>
</body>

</html>
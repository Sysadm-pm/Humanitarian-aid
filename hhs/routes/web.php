<?php
// use App\Http\Controllers\PersonController;

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api/'], function ($app) {
    //Get collection fo Documets with search options
    $app->get('/', 'PersonController@search');
  ////Get operation counts
    $app->get('/counts', 'PersonController@getCounts');
    //Get protocol
    $app->get('/protocol', 'PersonController@getProtocol');
    //Get type of desision
    $app->get('/get_types_desision', 'PersonController@getTypeDesision');
    //Get Applicant Category
    $app->get('/get_applicant_category', 'PersonController@getApplicantCategory');
    //Get Districts
    $app->get('/get_districts', 'PersonController@getDistricts');
    //Get Building Type
    $app->get('/get_building_type', 'PersonController@getBuildingType');
    //Get Reason Of Submission
    $app->get('/get_reason_of_submission', 'PersonController@getReasonOfSubmission');
    //Get Applicant Type
    $app->get('/get_applicant_type', 'PersonController@getApplicantType');
    //Get Application Status
    $app->get('/get_application_status', 'PersonController@getApplicationStatus');
    //Get Submission Type
    $app->get('/get_submission_type', 'PersonController@getSubmissionType');
    //Get Org Unit
    $app->get('/get_org_unit', 'PersonController@getOrgUnit');
    //Get Org Employee
    $app->get('/get_org_employee', 'PersonController@getOrgEmployee');
  ////Create Person
    $app->put('/person/create', 'PersonController@createPerson');

    //Create request
    $app->post('/document/{id}/create_request/', 'PersonController@createRequest');
    //Update request
    $app->post('/document/update_request/{id}/', 'PersonController@updateRequest');

    //Create decision
    $app->put('/document/{id}/create_decision', 'PersonController@createDecision');
    //Update decision
    $app->post('/document/update_decision/{id}/', 'PersonController@updateDecision');

});


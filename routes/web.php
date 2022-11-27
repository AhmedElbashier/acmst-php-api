<?php

use Illuminate\Support\Facades\Hash;
/*
|--------------------------------------------------------------------------
| rout$routerlication Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an rout$routerlication.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return "Welcome";
});
$router->post('auth/login', ['uses' => 'AuthController@authenticate']);
$router->get('/gen', ['uses' => 'StudantController@genAccounts']);

 $router->group(['prefix' => 'api', 'middleware' => 'jwt.auth'], function () use ($router) {

    /**
     * Routes for Studant
     */
    $router->group(['prefix' => 'studant'], function () use ($router) {
        $router->post('/acceptance', ['uses' => 'StudantController@accaptance']);
        $router->post('/registration/{id}', ['uses' => 'StudantController@registration']);
        $router->post('/scolarship/{id}', ['uses' => 'StudantController@scolarship']);
        $router->post('/freeze', ['uses' => 'StudantController@freeze']);
        $router->post('/unfreeze', ['uses' => 'StudantController@unfreeze']);
        $router->post('/resign', ['uses' => 'StudantController@resign']);
        $router->post('/transfare', ['uses' => 'StudantController@transfare']);
        $router->post('/cardReplacement', ['uses' => 'StudantController@cardReplacement']);
        $router->delete('/scolarship/{id}', ['uses' => 'StudantController@removeScolarship']);
        $router->get('/scolarship', ['uses' => 'StudantController@getScolarship']);
        $router->get('/gen', ['uses' => 'StudantController@genAccounts']);
        $router->post('/pass', ['uses' => 'StudantController@passExams']);
        $router->post('/repeat', ['uses' => 'StudantController@repeatYear']);
        $router->post('/accepted', ['uses' => 'StudantController@accepted']);
        $router->get('/basic', ['uses' => 'StudantController@getAllBasic']);
        $router->get('/', ['uses' => 'StudantController@getAll']);
        $router->get('/{id}', ['uses' => 'StudantController@getStudant']);
        $router->post('/', ['uses' => 'StudantController@create']);
        $router->put('/{id}', ['uses' => 'StudantController@update']);
        $router->delete('/{id}', ['uses' => 'StudantController@delete']);
    });

    /**
     * Routes for role
     */
    $router->group(['prefix' => 'role'], function () use ($router) {
        $router->get('/', 'RolesController@all');
        $router->get('/{id}', 'RolesController@get');
        $router->post('/', 'RolesController@add');
        $router->put('/{id}', 'RolesController@put');
        $router->delete('/{id}', 'RolesController@remove');
    });

    /**
     * Routes for commity-log
     */
    $router->group(['prefix' => 'commity'], function () use ($router) {
        $router->get('/', 'CommityLogsController@all');
        $router->get('{id}', 'CommityLogsController@get');
        $router->post('/', 'CommityLogsController@add');
        $router->put('{id}', 'CommityLogsController@put');
        $router->delete('{id}', 'CommityLogsController@remove');
    });

    /**
     * Routes for extra
     */
    $router->group(['prefix' => 'extra'], function () use ($router) {
        $router->post('/add', 'ExtraTransactionsController@add');
    });

    /**
     * Routes for paymnet
     */
    $router->group(['prefix' => 'paymnet'], function () use ($router) {
        $router->get('/', 'PaymnetsController@all');
        $router->get('/{id}', 'PaymnetsController@get');
        $router->post('/', 'PaymnetsController@add');
        $router->put('/{id}', 'PaymnetsController@put');
        $router->delete('/{id}', 'PaymnetsController@remove');
        $router->post('/print/{id}', 'PaymnetsController@printInvoice');
        $router->get('/report/{id}', 'PaymnetsController@ReportInstallmentPage');
    });

    /**
     * Routes for studant-installment
     */
    $router->group(['prefix' => 'installment'], function () use ($router) {
        $router->get('/', 'StudantInstallmentsController@all');
        $router->get('/{id}', 'StudantInstallmentsController@get');
        $router->get('/{id}/years', 'StudantInstallmentsController@getYears');
        $router->post('/', 'StudantInstallmentsController@add');
        $router->put('/{id}', 'StudantInstallmentsController@put');
        $router->delete('/{id}', 'StudantInstallmentsController@remove');
    });

    /**
     * Routes for users
     */
    $router->group(['prefix' => 'users'], function () use ($router) {
        $router->get('/', 'UsersController@all');
        $router->get('/{id}', 'UsersController@get');
        $router->post('/', 'UsersController@add');
        $router->put('/{id}', 'UsersController@put');
        $router->delete('/{id}', 'UsersController@remove');
        $router->post('/{id}/role', 'UsersController@signRole');
    });
 /**
     * Routes for services
     */
    $router->group(['prefix' => 'services'], function () use ($router) {
        $router->get('/', 'ServicesController@all');
        $router->get('/{id}', 'ServicesController@get');
        $router->post('/', 'ServicesController@add');
        $router->put('/{id}', 'ServicesController@put');
        $router->delete('/{id}', 'ServicesController@remove');
    });
    /**
     * Routes for studant-account
     */
    $router->group(['prefix' => 'stdaccount'], function () use ($router) {
        $router->get('/', 'StudantAccountsController@all');
        $router->get('/{id}', 'StudantAccountsController@get');
        $router->get('/account/{id}', 'StudantAccountsController@getAccountInfo');
        $router->post('/', 'StudantAccountsController@add');
        $router->put('/{id}', 'StudantAccountsController@put');
        $router->delete('/{id}', 'StudantAccountsController@remove');
    });

    /**
     * Routes for studant-tolls
     */
    $router->group(['prefix' => 'tolls'], function () use ($router) {
        $router->get('/', 'StudantTollsController@all');
        $router->get('/{id}', 'StudantTollsController@get');
        $router->post('/', 'StudantTollsController@add');
        $router->put('/{id}', 'StudantTollsController@put');
        $router->delete('/{id}', 'StudantTollsController@remove');
    });

    /**
     * Routes for settings
     */
    $router->group(['prefix' => 'settings'], function () use ($router) {
        $router->get('/', 'SettingsController@all');
        $router->get('/{id}', 'SettingsController@get');
        $router->post('/', 'SettingsController@add');
        $router->post('/user', 'SettingsController@addUser');
        $router->post('/toll', 'SettingsController@addToll');
        $router->put('/{id}', 'SettingsController@put');
        $router->delete('/{id}', 'SettingsController@remove');
    });

    /**
     * Routes for cards
     */
    $router->group(['prefix' => 'cards'], function () use ($router) {
        $router->get('/', 'CardsController@all');
        $router->get('/{id}', 'CardsController@get');
        $router->post('/', 'CardsController@add');
        $router->put('/{id}', 'CardsController@put');
        $router->delete('/{id}', 'CardsController@remove');
        $router->post('/print', 'CardsController@printCard');
        $router->post('/testprint', 'CardsController@printReport');
    });

    /**
     * Routes for transactions
     */

    $router->group(['prefix' => 'transactions'], function () use ($router) {
        $router->get('/', 'TransactionsController@all');
        $router->get('/{id}', 'TransactionsController@get');
        $router->post('/', 'TransactionsController@add');
        $router->post('/report', 'TransactionsController@report');
        $router->put('/{id}', 'TransactionsController@put');
        $router->delete('/{id}', 'TransactionsController@remove');
    });
    /**
     * Routes for Reports
     */
    $router->group(['prefix' => 'reports'], function () use ($router) {
        $router->post('/print', 'ReportController@Print');
    });
});

$router->group(['prefix' => 'api'], function () use ($router) {
    /**
     * Routes for Reports
     */
    $router->group(['prefix' => 'reports'], function () use ($router) {
        $router->get('statment/{id}/{year}', 'ReportController@StdAccountReport');
        $router->get('invoice/{id}', 'ReportController@InvoiceReport');
        $router->get('card/{id}', 'ReportController@CardReport');
        $router->get('cards/}', 'ReportController@CardReport');
        $router->get('transaction/{id}', 'ReportController@TransactionReport');
        $router->get('/statment', 'ReportController@StdsAccountReport');
        $router->get('registration/{id}', 'ReportController@RegistrationReport');
        $router->get('extra/{id}', 'ReportController@ExtraTransactionReport');
    });
    $router->group(['prefix' => 'cards'], function () use ($router) {
        $router->get('/check/{id}', 'CardsController@Reader');
    });
    $router->get('password', function (){
        return Hash::Make("123");
    });

    $router->post('user/','UserController@add');


    

 });

/**
 * Routes for resource system-log
 */
// $app->get('system-log', 'SystemLogsController@all');
// $app->get('system-log/{id}', 'SystemLogsController@get');
// $app->post('system-log', 'SystemLogsController@add');
// $app->put('system-log/{id}', 'SystemLogsController@put');
// $app->delete('system-log/{id}', 'SystemLogsController@remove');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\adminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\Kelkoosearch;
use App\Http\Controllers\Website\WebsiteController;
use App\Models\Country;


Auth::routes(['login' => false]);

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);


//////////////////////////////////Website //////////////////////////////////////////

Route::get('/', [WebsiteController::class, 'index'])->name('home');

Route::get('/shop', function () {
    return view('website.index');
})->name('shop');


Route::get('/brands', [WebsiteController::class, 'brands'])->name('brands');
Route::get('/brands/offers/{name}', [WebsiteController::class, 'brandProducts'])->name('brands.offers');


Route::get('/offers/{name}', [WebsiteController::class, 'SingleBrandProduct'])->name('offers.product');
Route::get('/category/offers/{name}', [WebsiteController::class, 'categoryProducts'])->name('category.offers');

Route::get('/merchants', [WebsiteController::class, 'Merchants'])->name('merchants');
Route::get('/merchant/offers/{name}', [WebsiteController::class, 'merchantProducts'])->name('merchant.offers');

Route::get('/privacy-policy', function () {
    return view('website.privacy-policy');
})->name('privacy-policy');



//////////////////////////////////  Admin /////////////////////////////////////////////////


Route::middleware('auth')->prefix('admin')->group(function () {

    Route::get('/', [adminController::class, 'index'])->name('admin');
    Route::get('/api-key', [Kelkoosearch::class, 'Apikey'])->name('admin.api-key');
    Route::get('/merchant', [adminController::class, 'merchant'])->name('admin.merchant');
    Route::get('/merchant-product{id}', [adminController::class, 'merchantProduct'])->name('admin.merchant.product');
   
    Route::get('/country-list', function () {
        $countries = Country::all();
        return view('Admin/kalkoosearch/countrylist', ['countries' => $countries]);
    });

    Route::get('/kelkoo-search', function () {
        $countries = Country::all();
        return view('Admin/kalkoosearch/createcode', ['countries' => $countries]);
    });

    Route::post('/add-code', [Kelkoosearch::class, 'createShortcode'])->name('admin.add.code');
    Route::post('/add-country', [Kelkoosearch::class, 'addCountry'])->name('admin.add.country');
    Route::delete('/countries/{id}', [Kelkoosearch::class, 'destroy'])->name('admin.delete.country');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('admin.profile');
    Route::post('/profile-update', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::get('/feeds/link', [adminController::class, 'showFacebookFeedLink'])->name('admin.showFacebookFeedLink');
});

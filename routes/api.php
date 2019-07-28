<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Auth
Route::post('/register', 'Auth\AuthController@register')->name('user.register');
Route::post('/login', 'Auth\AuthController@login')->name('user.login');
Route::post('/refresh-token', 'Auth\AuthController@refresh')->name('user.refresh');
Route::post('/logout', 'Auth\AuthController@logout')->middleware('auth:api')->name('user.logout');



// Forgotten password
//TODO: replace log by email sending
Route::post('/password/create', 'Auth\PasswordResetController@create');
Route::get('/password/find/{token}', 'Auth\PasswordResetController@find');
Route::post('/password/reset', 'Auth\PasswordResetController@reset');



// Logged in admin routes
Route::group(['prefix' => 'admin', 'middleware' => ['auth:api', 'isadmin']], function () {
    // Allergens (index & show in public routes)
    Route::post('/allergens', 'AllergenController@store')->name('allergen.store');
    Route::put('/allergens/{slug}', 'AllergenController@update')->name('allergen.update');
    Route::delete('/allergens/{slug}', 'AllergenController@delete')->name('allergen.delete');
    Route::post('/allergens/{slug}/thumbnail', 'AllergenController@updateThumbnail')->name('allergen.update_thumbnail');

    // Ingredients (index & show in public routes)
    Route::post('/ingredients', 'IngredientController@store')->name('ingredient.store');
    Route::put('/ingredients/{slug}', 'IngredientController@update')->name('ingredient.update');
    Route::delete('/ingredients/{slug}', 'IngredientController@delete')->name('ingredient.delete');
    Route::post('/ingredients/{slug}/thumbnail', 'IngredientController@updateThumbnail')->name('ingredient.update_thumbnail');

    // Ingredient-categories (index & show in public routes)
    Route::post('/ingredient-categories', 'IngredientCategoryController@store')->name('ingredientCategory.store');
    Route::put('/ingredient-categories/{slug}', 'IngredientCategoryController@update')->name('ingredientCategory.update');
    Route::delete('/ingredient-categories/{slug}', 'IngredientCategoryController@delete')->name('ingredientCategory.delete');
    Route::post('/ingredient-categories/{slug}/thumbnail', 'IngredientCategoryController@updateThumbnail')->name('ingredientCategory.update_thumbnail');

    // Ingredient-sub-categories (index & show in public routes)
    Route::post('/ingredient-sub-categories', 'IngredientSubCatController@store')->name('ingredientSubCat.store');
    Route::put('/ingredient-sub-categories/{slug}', 'IngredientSubCatController@update')->name('ingredientSubCat.update');
    Route::delete('/ingredient-sub-categories/{slug}', 'IngredientSubCatController@delete')->name('ingredientSubCat.delete');
    Route::post('/ingredient-sub-categories/{slug}/thumbnail', 'IngredientSubCatController@updateThumbnail')->name('ingredientSubCat.update_thumbnail');

    // Seasons (index & show in public routes)
    Route::post('/seasons', 'SeasonController@store')->name('season.store');
    Route::put('/seasons/{slug}', 'SeasonController@update')->name('season.update');
    Route::delete('/seasons/{slug}', 'SeasonController@delete')->name('season.delete');

    // Recipes (index & show in public routes)
    Route::post('/recipes', 'RecipeController@store')->name('recipe.store');
    Route::put('/recipes/{slug}', 'RecipeController@update')->name('recipe.update');
    Route::delete('/recipes/{slug}', 'RecipeController@delete')->name('recipe.delete');
    Route::post('/recipes/{slug}/thumbnail', 'RecipeController@updateThumbnail')->name('recipe.update_thumbnail');
});



// Logged in routes
Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/user', 'UserController@showLoggedInUser')->name('user.showLoggedInUser');
    Route::put('/user', 'UserController@editLoggedInUser')->name('user.updateLoggedInUser');
    Route::post('/user/avatar', 'UserController@updatAvatar')->name('user.update_avatar');
    Route::delete('/user/avatar', 'UserController@deleteAvatar')->name('user.delete_avatar');
});



// Public routes
// Users
Route::get('/users', 'UserController@index')->name('user.index');
Route::get('/users/{slug}', 'UserController@showBySlug')->name('user.showBySlug');

// Allergens
Route::get('/allergens', 'AllergenController@index')->name('allergen.index');
Route::get('/allergens/{slug}', 'AllergenController@show')->name('allergen.show');

// Ingredients
Route::get('/ingredients', 'IngredientController@index')->name('ingredient.index');
Route::get('/ingredients/{slug}', 'IngredientController@show')->name('ingredient.show');

// Ingredient-categories
Route::get('/ingredient-categories', 'IngredientCategoryController@index')->name('ingredientCategory.index');
Route::get('/ingredient-categories/{slug}', 'IngredientCategoryController@show')->name('ingredientCategory.show');

// Ingredient-sub-categories
Route::get('/ingredient-sub-categories', 'IngredientSubCatController@index')->name('ingredientSubCat.index');
Route::get('/ingredient-sub-categories/{slug}', 'IngredientSubCatController@show')->name('ingredientSubCat.show');

// Seasons
Route::get('/seasons', 'SeasonController@index')->name('season.index');
Route::get('/seasons/{slug}', 'SeasonController@show')->name('season.show');

// Recipes
Route::get('/recipes', 'RecipeController@index')->name('recipe.index');
Route::get('/recipes/{slug}', 'RecipeController@show')->name('recipe.show');

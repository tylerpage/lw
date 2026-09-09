<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InsightsController;
use App\Http\Controllers\LlmsTxtController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\RssController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WorkController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/about', fn (PageController $controller) => $controller->show('about'))->name('about');
Route::get('/privacy', fn (PageController $controller) => $controller->show('privacy'))->name('privacy');

Route::get('/work', [WorkController::class, 'index'])->name('work.index');
Route::get('/work/{slug}', [WorkController::class, 'show'])->name('work.show');

Route::get('/insights', [InsightsController::class, 'index'])->name('insights.index');
Route::get('/insights/{slug}', [InsightsController::class, 'show'])->name('insights.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::get('/resume', ResumeController::class)->name('resume');
Route::get('/search', SearchController::class)->name('search');

Route::get('/preview/{type}/{id}', PreviewController::class)->name('preview');
Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/llms.txt', LlmsTxtController::class)->name('llms');
Route::get('/feed.xml', RssController::class)->name('rss');

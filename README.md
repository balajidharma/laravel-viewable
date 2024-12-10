<h1 align="center">Laravel Viewable</h1>
<h3 align="center">Track Page views for your Laravel projects.</h3>
<p align="center">
<a href="https://packagist.org/packages/balajidharma/laravel-viewable"><img src="https://poser.pugx.org/balajidharma/laravel-viewable/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/balajidharma/laravel-viewable"><img src="https://poser.pugx.org/balajidharma/laravel-viewable/v/stable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/balajidharma/laravel-viewable"><img src="https://poser.pugx.org/balajidharma/laravel-viewable/license" alt="License"></a>
</p>

## Table of Contents

- [Installation](#installation)
- [Demo](#demo)

## Installation
- Install the package via composer
```bash
composer require balajidharma/laravel-viewable
```

- Publish the migration with
```bash
php artisan vendor:publish --provider="BalajiDharma\LaravelViewable\ViewableServiceProvider" --tag="migrations"
```

- Run the migration
```bash
php artisan migrate
```

- To Publish the config/viewable.php config file with
```bash
php artisan vendor:publish --provider="BalajiDharma\LaravelViewable\ViewableServiceProvider" --tag="config"
```

- Preparing your model
To associate views with a model, the model must implement the HasViewable trait:
```php
<?php
namespace BalajiDharma\LaravelForum\Models;

use BalajiDharma\LaravelViewable\Traits\HasViewable;
use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    use HasViewable;
	
```

- Recording views
To make a view record, you can call the record method.
```php
public function show(Thread $thread)
{
    $thread->record();
    
```

## Demo
The "[Basic Laravel Admin Penel](https://github.com/balajidharma/basic-laravel-admin-panel)" starter kit come with Laravel Viewable

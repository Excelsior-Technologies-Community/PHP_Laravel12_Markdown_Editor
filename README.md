# PHP_Laravel12_Markdown_Editor

## Introduction

PHP_Laravel12_Markdown_Editor is a modern web application built using Laravel 12 and Filament Admin Panel, designed to provide a seamless Markdown editing and content management experience.

This project demonstrates how to integrate a powerful Markdown editor into an admin panel, allowing users to create, edit, and manage content efficiently while storing data in a clean and structured format.

The application converts Markdown content into HTML on the frontend, enabling users to view beautifully formatted blog-style posts.

It is ideal for developers who want to understand real-world implementation of admin panels, content management systems (CMS), and Markdown-based workflows in Laravel.

---

## Project Overview

This project provides a complete Markdown-based content management system with the following features:

- Filament Admin Panel Integration  
  A powerful admin dashboard for managing posts with minimal configuration.

- Markdown Editor Support  
  Integrated Markdown editor using Spatie plugin for writing structured content.

- Full CRUD Operations  
  Create, read, update, and delete posts easily through the admin interface.

- Database Storage  
  Stores content in Markdown format for flexibility and clean data management.

- Frontend Rendering  
  Converts Markdown into HTML dynamically using Laravel helpers.

- Modern UI Design  
  Responsive and clean frontend design built with Tailwind CSS.

- Scalable Architecture  
  Follows Laravel MVC structure, making it easy to extend and maintain.

---

## Features

- Laravel 12 based project
- Filament Admin Panel integration
- Markdown editor using Spatie plugin
- Full CRUD functionality
- Markdown to HTML rendering
- Clean and responsive UI

---

## Application Flow

Admin Panel (Filament)  
→ Create/Edit Markdown Content  
→ Store in Database  
→ Fetch via Route  
→ Render in Blade View  
→ Display as Styled HTML Blog

---

## Use Cases

- Personal Blog System  
- Documentation Platform  
- Knowledge Base  
- Content Management System (CMS)  



## Project Setup

## Step 1: Create Laravel 12 Project

```bash
composer create-project laravel/laravel PHP_Laravel12_Markdown_Editor "12.*"
cd PHP_Laravel12_Markdown_Editor
```

---

## Step 2: Setup Environment

Update database in .env:

```.env
DB_DATABASE=markdown_editor_db
DB_USERNAME=root
DB_PASSWORD=
```

Run migration:

```bash
php artisan migrate
```

---

## Install Filament Admin Panel

## Step 3: Install Filament

```bash
composer require filament/filament:"^3.0"
```

Run install command:

```bash
php artisan filament:install
```

Install Panels:

```bash
php artisan filament:install --panels
```

Create admin user:

```bash
php artisan make:filament-user
```

It will ask:

- Name
- Email
- Password

Example:

```
Name: Admin
Email: admin@gmail.com
Password: 12345678
```

---

## Install Markdown Editor Plugin

## Step 4: Install Spatie Markdown Editor

```bash
composer require spatie/filament-markdown-editor
```

---

## Step 5: Create Model & Migration

```bash
php artisan make:model Post -m
```

File: `database\migrations\2026_03_31_XXXXXX_create_posts_table.php`

Update migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content'); // Markdown content
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

Run migration:

```bash
php artisan migrate
```

---

## Create Filament Resource

## Step 6: Generate Resource

```bash
php artisan make:filament-resource Post
```

File: `app/Filament/Resources/PostResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\FilamentMarkdownEditor\MarkdownEditor;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    //  FORM (CREATE + EDIT)
    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('title')
                    ->label('Post Title')
                    ->required()
                    ->maxLength(255),

                MarkdownEditor::make('content')
                    ->label('Post Content')
                    ->required()
                    ->columnSpanFull(),

            ]);
    }

    //  TABLE (LIST VIEW)
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime(),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    //  PAGES
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
```

---

## Step 7: Model Setup

File: `app/Models/Post.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['title', 'content'];
}
```

---

## Step 8: Create Route

File: `routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Models\Post;

Route::get('/posts', function () {
    $posts = Post::latest()->get();
    return view('posts.index', compact('posts'));
});

Route::get('/', function () {
    return view('welcome');
});
```

---

## Step 9: Create View

File: `resources/views/posts/index.blade.php`

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Markdown Blog</title>

    <!-- Tailwind CSS + Typography Plugin -->
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 flex flex-col min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Markdown Blog</h1>
            <a href="/admin" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition">
                Admin Panel
            </a>
        </div>
    </nav>

    <!-- Header -->
    <header class="text-center py-12 bg-gradient-to-r from-blue-500 to-indigo-600 text-white">
        <h2 class="text-4xl font-bold">Latest Posts</h2>
        <p class="mt-2 text-lg">Read beautifully formatted markdown blogs</p>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">

        <div class="max-w-5xl mx-auto px-6 py-10">

            @forelse($posts as $post)

                <div class="bg-white rounded-2xl shadow-md p-6 mb-8 hover:shadow-lg transition duration-300">

                    <!-- Title -->
                    <h2 class="text-2xl font-semibold text-gray-800 mb-2">
                        {{ $post->title }}
                    </h2>

                    <!-- Date -->
                    <p class="text-sm text-gray-500 mb-4">
                        {{ $post->created_at->format('F d, Y') }}
                    </p>

                    <!-- Markdown Content -->
                    <div class="prose max-w-none text-gray-700">
                        {!! \Illuminate\Support\Str::markdown($post->content) !!}
                    </div>

                </div>

            @empty

                <div class="text-center text-gray-500 text-lg mt-10">
                    No posts available 😢
                </div>

            @endforelse

        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t text-center py-6 mt-auto">
        <p class="text-gray-500">© {{ date('Y') }} Markdown Blog. All rights reserved.</p>
    </footer>

</body>
</html>
```

---

## Step 10: Run Project

Run:

```bash
php artisan serve
```
Admin Panel:

```bash
http://127.0.0.1:8000/admin
```
Frontend:

```bash
http://127.0.0.1:8000/posts
```

---

## Output

<img src="screenshots/Screenshot 2026-03-31 122826.png" width="1000">

<img src="screenshots/Screenshot 2026-03-31 121957.png" width="1000">

<img src="screenshots/Screenshot 2026-03-31 120818.png" width="1000">

<img src="screenshots/Screenshot 2026-03-31 121615.png" width="1000">

---

## Project Structure

```
PHP_Laravel12_Markdown_Editor/
│
├── app/
│   ├── Models/
│   │   └── Post.php                 (Model for posts)
│   │
│   ├── Filament/
│   │   └── Resources/
│   │       ├── PostResource.php    (Main CRUD logic)
│   │       │
│   │       └── PostResource/
│   │           └── Pages/
│   │               ├── ListPosts.php    (List page)
│   │               ├── CreatePost.php   (Create page)
│   │               └── EditPost.php     (Edit page)
│   │
│   └── Providers/
│       └── Filament/
│           └── AdminPanelProvider.php  (Filament panel config)
│
├── bootstrap/
│   └── providers.php               (Registers Filament panel)
│
├── database/
│   ├── migrations/
│   │   └── xxxx_create_posts_table.php  (DB structure)
│   │
│   └── seeders/
│
├── public/
│   └── index.php
│
├── resources/
│   ├── views/
│   │   └── posts/
│   │       └── index.blade.php    (Frontend UI)
│   │
│   └── css/
│
├── routes/
│   └── web.php                   (Frontend routes)
│
├── vendor/                      (Composer packages)
│
├── .env                         (Database config)
├── artisan                      (Laravel CLI)
├── composer.json
└── README.md
```

---

Your PHP_Laravel12_Markdown_Editor Project is now ready!

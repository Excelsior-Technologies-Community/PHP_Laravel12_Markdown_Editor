<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        Post::create([
            'title' => 'Laravel Markdown Editor',
            'slug' => 'laravel-markdown-editor',
            'content' => '# Hello World

This is markdown content.',
            'is_published' => true,
        ]);

        Post::create([
            'title' => 'Welcome to Our Blog',
            'slug' => 'welcome-to-our-blog',
            'content' => '# Welcome

This is your first blog post. Start writing amazing content!

## Features
- **Markdown support**
- *Live preview*
- `Code blocks`

{{component:alert type=success title=Success message=Your blog is ready!}}',
            'is_published' => true,
        ]);

        Post::create([
            'title' => 'Getting Started with Markdown',
            'slug' => 'getting-started-with-markdown',
            'content' => '# Getting Started

Markdown is easy to learn!

## Basic Syntax

**Bold text** and *italic text*

{{component:card title=Quick Tips content=Use # for headers, ** for bold, * for italic}}

## Code Example

{{component:code-block lang=php code=echo "Hello World";}}',
            'is_published' => true,
        ]);

        Post::create([
            'title' => 'Advanced Components Demo',
            'slug' => 'advanced-components-demo',
            'content' => '# Custom Components

Try out our custom components!

{{component:button style=primary href=/about label=Learn More}}

{{component:badge color=blue text=New Feature}}

{{component:divider}}

## Table Example

{{component:table headers="Name,Role,Status"}}

{{component:alert type=warning title=Warning message=This is a draft post}}',
            'is_published' => false,
        ]);
    }
}
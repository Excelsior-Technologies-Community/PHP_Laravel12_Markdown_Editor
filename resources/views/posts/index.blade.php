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
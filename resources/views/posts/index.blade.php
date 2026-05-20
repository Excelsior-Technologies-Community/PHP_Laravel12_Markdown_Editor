<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Markdown Blog</title>

    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>

    <style>
        body {
            font-family: sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 transition duration-300">

    <!-- Navbar -->
    <nav class="bg-white dark:bg-gray-800 shadow">

        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Markdown Blog
            </h1>

            <div class="flex gap-4">

                <button
                    onclick="toggleTheme()"
                    class="bg-gray-800 text-white px-4 py-2 rounded-lg"
                >
                    Toggle Theme
                </button>

                <a
                    href="/admin"
                    class="bg-blue-500 text-white px-4 py-2 rounded-lg"
                >
                    Admin
                </a>

            </div>

        </div>

    </nav>

    <!-- Hero -->
    <section class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-16">

        <div class="max-w-5xl mx-auto px-6 text-center">

            <h2 class="text-5xl font-bold mb-4">
                Laravel Markdown CMS
            </h2>

            <p class="text-lg">
                Beautiful markdown blogging system with Filament Admin Panel
            </p>

        </div>

    </section>

    <!-- Posts -->
    <div class="max-w-5xl mx-auto px-6 py-10">

        @forelse($posts as $post)

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg mb-10 overflow-hidden">

                @if($post->featured_image)

                    <img
                        src="{{ asset('storage/' . $post->featured_image) }}"
                        class="w-full h-72 object-cover"
                    >

                @endif

                <div class="p-8">

                    <h2 class="text-3xl font-bold text-gray-800 dark:text-white mb-2">
                        {{ $post->title }}
                    </h2>

                    <p class="text-sm text-gray-500 mb-6">
                        {{ $post->created_at->format('F d, Y') }}
                    </p>

                    <div class="prose dark:prose-invert max-w-none">
                        {!! \Illuminate\Support\Str::markdown($post->content) !!}
                    </div>

                </div>

            </div>

        @empty

            <div class="text-center text-gray-500 text-xl">
                No Posts Found
            </div>

        @endforelse

    </div>

    <script>
        function toggleTheme()
        {
            document.documentElement.classList.toggle('dark');
        }
    </script>

</body>
</html>
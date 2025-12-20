<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workspace Booking - Modern Dark</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* Hiệu ứng nền chấm bi mờ ảo */
        .bg-grid-pattern {
            background-image: radial-gradient(#6366f1 1px, transparent 1px);
            background-size: 40px 40px;
            mask-image: radial-gradient(ellipse at center, black 40%, transparent 80%);
            -webkit-mask-image: radial-gradient(ellipse at center, black 40%, transparent 80%);
        }

        /* Animation nhẹ cho badge */
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
    </style>
</head>
<body class="bg-[#0B1120] text-white min-h-screen relative overflow-hidden flex flex-col">

    <div class="absolute inset-0 z-0 pointer-events-none">
        <div class="absolute inset-0 bg-grid-pattern opacity-[0.15]"></div>

        <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-600 rounded-full mix-blend-multiply filter blur-[128px] opacity-40 animate-blob"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-600 rounded-full mix-blend-multiply filter blur-[128px] opacity-40 animate-blob animation-delay-2000"></div>
    </div>

    <nav class="relative z-50 px-6 py-6 flex justify-between items-center max-w-7xl mx-auto w-full">
        <div class="flex items-center gap-2 cursor-pointer">
            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                <span class="font-bold text-xl text-white">W</span>
            </div>
            <span class="font-bold text-xl tracking-tight text-white">WorkSpace</span>
        </div>

        <div class="flex gap-4 items-center">
            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-300 hover:text-white transition px-4 py-2">Log in</a>
            <a href="{{ route('login') }}" class="text-sm font-bold bg-white text-[#0B1120] hover:bg-indigo-50 px-5 py-2.5 rounded-full transition shadow-lg shadow-white/10">Get Started</a>
        </div>
    </nav>

    <main class="relative z-10 flex-grow flex flex-col items-center justify-center px-4 text-center -mt-20">

        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-sm font-medium mb-8 backdrop-blur-md">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
            </span>
            Booking System v2.0 Live
        </div>

        <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight mb-6 leading-[1.15]">
            Find your perfect <br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400">
                Creative Flow State
            </span>
        </h1>

        <p class="text-lg md:text-xl text-slate-400 max-w-2xl mb-12 leading-relaxed">
            Discover flexible workspaces, meeting rooms, and private offices.
            <br class="hidden md:block">Seamless booking management for modern teams.
        </p>

        <div class="w-full max-w-5xl bg-slate-800/40 backdrop-blur-xl border border-white/10 p-3 rounded-2xl shadow-2xl shadow-black/50 ring-1 ring-white/5">
            <form action="{{ route('login') }}" method="GET" class="flex flex-col md:flex-row gap-3">

                <div class="flex-[1.5] relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400 group-focus-within:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <input type="text" class="w-full bg-slate-900/60 border border-transparent focus:border-indigo-500/50 text-white placeholder-slate-500 rounded-xl py-4 pl-12 pr-4 focus:ring-0 focus:bg-slate-900 transition outline-none" placeholder="Search by location (e.g. Hanoi, Da Nang)">
                </div>

                <div class="flex-1 relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400 group-focus-within:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <input type="text" onfocus="(this.type='date')" onblur="(this.type='text')" class="w-full bg-slate-900/60 border border-transparent focus:border-indigo-500/50 text-white placeholder-slate-500 rounded-xl py-4 pl-12 pr-4 focus:ring-0 focus:bg-slate-900 transition outline-none" placeholder="Check-in Date">
                </div>

                 <div class="flex-1 relative group hidden md:block">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400 group-focus-within:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <select class="w-full bg-slate-900/60 border border-transparent focus:border-indigo-500/50 text-slate-300 rounded-xl py-4 pl-12 pr-4 focus:ring-0 focus:bg-slate-900 transition outline-none appearance-none cursor-pointer">
                        <option value="" disabled selected>Workspace Type</option>
                        <option value="desk">Hot Desk</option>
                        <option value="office">Private Office</option>
                        <option value="meeting">Meeting Room</option>
                    </select>
                </div>

                <button type="submit" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold py-4 px-8 rounded-xl transition-all shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] flex items-center justify-center gap-2 whitespace-nowrap">
                    <span>Search</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>
            </form>
        </div>

    </main>

    <footer class="relative z-10 w-full text-center py-6 text-slate-600 text-sm">
        <p>&copy; {{ date('Y') }} Workspace Booking System. Powered by Laravel.</p>
    </footer>

</body>
</html>

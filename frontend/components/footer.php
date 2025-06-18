<footer class="bg-gradient-to-r from-slate-900 to-slate-800 text-gray-300 px-4 py-8 border-t border-slate-800 shadow-inner">
    <div class="max-w-4xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="bg-blue-600 text-white rounded-full p-2 shadow-lg">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 17.25h16.5M4.5 6.75h15v10.5a2.25 2.25 0 0 1-2.25 2.25h-10.5A2.25 2.25 0 0 1 4.5 17.25V6.75zm3 3.75h9"/></svg>
            </span>
            <span class="text-lg font-bold text-white tracking-tight select-none">
                <?= $appName; ?>
            </span>
        </div>
        <div class="text-center md:text-left">
            <p class="text-sm text-gray-400">
                Version <span class="font-semibold text-white"><?= $version; ?></span>
            </p>
            <div class="mt-1 text-xs text-gray-500 flex items-center justify-center md:justify-start gap-2 flex-wrap">
                <span>&copy; <?= date('Y') ?> <?= $appName; ?></span>
                <a href="https://github.com/bugraozdmr" target="_blank" class="inline-flex items-center gap-1 text-gray-400 hover:text-white transition underline underline-offset-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.484 2 12.021c0 4.428 2.865 8.184 6.839 9.504.5.092.682-.217.682-.482 0-.237-.009-.868-.014-1.703-2.782.605-3.369-1.342-3.369-1.342-.454-1.156-1.11-1.464-1.11-1.464-.908-.62.069-.608.069-.608 1.004.07 1.532 1.032 1.532 1.032.892 1.53 2.341 1.088 2.91.832.091-.647.35-1.088.636-1.339-2.221-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.987 1.029-2.687-.103-.254-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.025A9.564 9.564 0 0 1 12 6.844c.85.004 1.705.115 2.504.337 1.909-1.295 2.748-1.025 2.748-1.025.546 1.378.202 2.396.1 2.65.64.7 1.028 1.594 1.028 2.687 0 3.847-2.337 4.695-4.566 4.944.359.309.678.919.678 1.852 0 1.336-.012 2.417-.012 2.747 0 .267.18.578.688.48A10.025 10.025 0 0 0 22 12.021C22 6.484 17.523 2 12 2z"/></svg>
                    Github
                </a>
            </div>
        </div>
    </div>
</footer>
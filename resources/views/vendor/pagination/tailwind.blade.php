@if ($paginator->total() > 0)
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 py-1">
        
        <!-- Left: Per-Page Selector & Summary Info -->
        <div class="flex flex-wrap items-center gap-3">
            <!-- Dropdown Pilihan Jumlah Data -->
            <div class="flex items-center gap-1.5">
                <label class="text-xs text-gray-500 font-medium">Tampilkan:</label>
                @php
                    $currentPerPage = (int) $paginator->perPage();
                    $perPageOptions = [10, 15, 25, 50, 100];
                    if (!in_array($currentPerPage, $perPageOptions)) {
                        $perPageOptions[] = $currentPerPage;
                        sort($perPageOptions);
                    }
                @endphp
                <select onchange="window.location.href = this.value" 
                        class="py-1 px-2.5 pe-7 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 shadow-2xs hover:border-blue-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 cursor-pointer transition-colors"
                        title="Pilih jumlah data yang ditampilkan per halaman">
                    @foreach($perPageOptions as $option)
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => $option, 'page' => 1]) }}" {{ $currentPerPage === $option ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
                <span class="text-xs text-gray-500">per hal</span>
            </div>

            <span class="text-gray-300 hidden sm:inline select-none">|</span>

            <!-- Total Data & Range -->
            <p class="text-xs text-gray-500">
                Menampilkan 
                <span class="font-bold text-gray-800">{{ $paginator->firstItem() ?? 1 }}</span>
                - 
                <span class="font-bold text-gray-800">{{ $paginator->lastItem() ?? $paginator->total() }}</span> 
                dari 
                <span class="font-bold text-gray-800">{{ $paginator->total() }}</span> 
                data
            </p>
        </div>

        <!-- Right: Page Navigation -->
        @if ($paginator->hasPages())
            <div class="flex items-center gap-1 self-center sm:self-auto">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span class="inline-flex items-center justify-center size-8 rounded-lg border border-gray-200 bg-gray-50/70 text-gray-400 cursor-not-allowed text-xs select-none" aria-disabled="true" title="Halaman Sebelumnya">
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" 
                       class="inline-flex items-center justify-center size-8 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-300 shadow-2xs transition-colors text-xs" 
                       title="Halaman Sebelumnya">
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="inline-flex items-center justify-center size-8 text-xs text-gray-400 select-none">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="inline-flex items-center justify-center size-8 rounded-lg bg-blue-600 text-white font-bold text-xs shadow-sm shadow-blue-500/20 select-none" aria-current="page">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" 
                                   class="inline-flex items-center justify-center size-8 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-300 shadow-2xs transition-colors text-xs font-medium">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" 
                       class="inline-flex items-center justify-center size-8 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-300 shadow-2xs transition-colors text-xs" 
                       title="Halaman Selanjutnya">
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                @else
                    <span class="inline-flex items-center justify-center size-8 rounded-lg border border-gray-200 bg-gray-50/70 text-gray-400 cursor-not-allowed text-xs select-none" aria-disabled="true" title="Halaman Selanjutnya">
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </span>
                @endif
            </div>
        @endif
    </nav>
@endif

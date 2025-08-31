document.addEventListener('DOMContentLoaded', () => {
    // --- STATE MANAGEMENT ---
    let currentSlide = 0;
    let slideInterval;
    let currentPage = 1;
    let lastPage = 1;
    let currentFilters = {};

    // --- HELPER FUNCTIONS ---
    const formatCurrency = (amount) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);

    // --- THEME LOGIC ---
    const themeToggle = document.getElementById('theme-toggle');
    const sunIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>`;
    const moonIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path></svg>`;

    const applyTheme = (theme) => {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
            if (themeToggle) themeToggle.innerHTML = sunIcon;
        } else {
            document.documentElement.classList.remove('dark');
            if (themeToggle) themeToggle.innerHTML = moonIcon;
        }
    };

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const newTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
            localStorage.setItem('theme', newTheme);
            applyTheme(newTheme);
        });
    }

    // --- RENDER FUNCTIONS ---
    async function fetchAndRenderMotors(page = 1, filters = {}) {
        const container = document.getElementById('motorcycle-list-container');
        if (!container) return;

        const queryParams = new URLSearchParams({ page, ...filters }).toString();
        container.innerHTML = `<div class="text-center py-16 text-gray-500">Memuat...</div>`;

        try {
            const response = await fetch(`/api/vehicles?${queryParams}`);
            if (!response.ok) throw new Error('Network response was not ok.');
            const result = await response.json();

            renderMotorcycleList(result.data);
            renderPagination(result.links, result.meta);
            currentPage = result.meta.current_page;
            lastPage = result.meta.last_page;
            currentFilters = filters;

        } catch (error) {
            console.error('Fetch error:', error);
            container.innerHTML = `<div class="text-center py-16 text-red-500">Gagal memuat data. Silakan coba lagi.</div>`;
        }
    }

    function renderMotorcycleList(motors) {
        const container = document.getElementById('motorcycle-list-container');
        if (!container) return;

        let content = `<div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-8 text-center">Pilihan Motor Untuk Anda</h2>`;

        if (motors.length > 0) {
            content += `<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                ${motors.map(motor => `
                    <a href="${motor.show_url}" class="motor-card block bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 group">
                        <div class="relative overflow-hidden">
                            <img src="${motor.imageUrl}" alt="${motor.name}" class="w-full h-48 object-cover group-hover:scale-110 transition-transform duration-500" onError="this.onerror=null;this.src='https://placehold.co/600x400/cccccc/ffffff?text=Gagal+Muat';">
                            <div class="absolute top-0 right-0 bg-red-600 text-white px-3 py-1 text-sm font-bold rounded-bl-lg">${motor.year}</div>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-1 truncate">${motor.name}</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mb-3">${motor.brand} - ${motor.model}</p>
                            <p class="text-xl md:text-2xl font-extrabold text-red-500">${motor.formatted_price}</p>
                        </div>
                    </a>
                `).join('')}
            </div>`;
        } else {
            content += `<div class="text-center text-gray-500 dark:text-gray-400 py-16"><p class="text-xl">Motor tidak ditemukan.</p></div>`;
        }
        content += `<div id="pagination-container" class="mt-12"></div></div>`;
        container.innerHTML = content;
    }

    function renderPagination(links, meta) {
        const container = document.getElementById('pagination-container');
        if (!container) return;

        let linksHtml = meta.links.map(link => {
            const pageNumber = new URLSearchParams(link.url?.split('?')[1]).get('page');
            const isDisabled = !link.url;
            const isActive = link.active;

            if (isDisabled) {
                return `<span class="px-4 py-2 mx-1 text-gray-500 dark:text-gray-600">${link.label.replace('&laquo;','').replace('&raquo;','')}</span>`;
            }
            if (isActive) {
                return `<span class="px-4 py-2 mx-1 text-white bg-red-600 rounded-md">${link.label}</span>`;
            }
            return `<button data-page="${pageNumber}" class="pagination-link px-4 py-2 mx-1 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 rounded-md hover:bg-red-500 hover:text-white dark:hover:bg-red-500">${link.label.replace('&laquo;','').replace('&raquo;','')}</button>`;
        }).join('');

        container.innerHTML = `<div class="flex justify-center items-center">${linksHtml}</div>`;

        document.querySelectorAll('.pagination-link').forEach(button => {
            button.addEventListener('click', (e) => {
                const page = e.target.dataset.page;
                fetchAndRenderMotors(page, currentFilters);
            });
        });
    }

    // --- EVENT LISTENERS ---
    function setupHomePageEventListeners() {
        // Slider
        const slider = document.getElementById('hero-slider');
        if (slider) {
            const slides = slider.querySelectorAll('.hero-slide');
            const nextBtn = document.getElementById('next-slide');
            const prevBtn = document.getElementById('prev-slide');

            const showSlide = (index) => {
                slides.forEach((slide, i) => {
                    slide.classList.toggle('opacity-100', i === index);
                    slide.classList.toggle('opacity-0', i !== index);
                });
            };

            const next = () => {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            };

            nextBtn.addEventListener('click', () => {
                next();
                resetSlideInterval();
            });

            prevBtn.addEventListener('click', () => {
                currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                showSlide(currentSlide);
                resetSlideInterval();
            });

            const resetSlideInterval = () => {
                clearInterval(slideInterval);
                slideInterval = setInterval(next, 5000);
            };
            resetSlideInterval();
        }

        // Filter
        const filterForm = document.getElementById('filter-form');
        if (filterForm) {
            filterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);
                const filters = Object.fromEntries(formData.entries());
                fetchAndRenderMotors(1, filters);
            });
        }
    }

    // --- INITIALIZATION ---
    document.getElementById('footer-year').textContent = new Date().getFullYear();
    const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    applyTheme(savedTheme);

    // Initial render
    if (document.getElementById('motorcycle-list-container')) {
        fetchAndRenderMotors();
        setupHomePageEventListeners();
    }
});

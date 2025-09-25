import './bootstrap'; // tetap ada
// 1. Import Swiper core
import Swiper from 'swiper';

// 2. Import module yang dibutuhkan dari 'swiper/modules'
import { Navigation, Pagination } from 'swiper/modules';

// 3. Import CSS untuk core, navigation, dan pagination
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination'; // <-- Jangan lupa tambahkan CSS untuk pagination 

import Alpine from 'alpinejs'

window.Alpine = Alpine

Alpine.start()


const swiper = new Swiper('.swiper', { // Ganti '.swiper' dengan selector Anda
  // Daftarkan module yang sudah di-import
  modules: [Navigation, Pagination],

  // Opsi Swiper Anda yang lain
  loop: true,

  // Konfigurasi untuk Pagination
  pagination: {
    el: '.swiper-pagination',
    clickable: true,
  },

  // Konfigurasi untuk Navigation
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },
});

document.addEventListener('DOMContentLoaded', () => {
    const swiper = new Swiper('.blog-swiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            640: { slidesPerView: 1 },
            768: { slidesPerView: 2 },
            1024: { slidesPerView: 3 },
        },
    });
});

import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();

// JS untuk menyimpan posisi scroll sidebar agar tetap sama saat user kembali ke halaman sebelumnya
document.addEventListener("DOMContentLoaded", () => {
    const sidebarNav = document.querySelector("aside nav");
    if (!sidebarNav) return;

    // Kembalikan posisi scroll dari kunjungan sebelumnya
    const savedScroll = sessionStorage.getItem("sidebar-scroll-position");
    if (savedScroll !== null) {
        sidebarNav.scrollTop = parseInt(savedScroll, 10);
    }

    // Simpan posisi scroll terkini tiap kali user scroll sidebar
    sidebarNav.addEventListener("scroll", () => {
        sessionStorage.setItem("sidebar-scroll-position", sidebarNav.scrollTop);
    });
});

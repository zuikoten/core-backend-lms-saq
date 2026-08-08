document.addEventListener('alpine:init', () => {
    Alpine.data('bulkInvoiceGeneration', () => ({
        academicYearId: '',
        periodMonth: new Date().getMonth() + 1,
        periodYear: new Date().getFullYear(),
        students: [],
        selectedIds: [],
        loading: false,

        async fetchStudents() {
            this.selectedIds = [];
            this.students = [];

            if (!this.academicYearId || !this.periodMonth || !this.periodYear) return;

            this.loading = true;

            const params = new URLSearchParams({
                academic_year_id: this.academicYearId,
                period_month: this.periodMonth,
                period_year: this.periodYear,
            });

            try {
                const response = await fetch(`${window.financeRoutes.eligibleInvoiceStudents}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' },
                });

                if (!response.ok) throw new Error('Gagal memuat daftar siswa');

                const data = await response.json();
                this.students = data.students;
                this.selectedIds = this.students.map(student => student.id);
            } catch (error) {
                alert('Gagal memuat daftar siswa. Coba lagi.');
            } finally {
                this.loading = false;
            }
        },

        toggleAll(checked) {
            this.selectedIds = checked ? this.students.map(student => student.id) : [];
        },
    }));
});
document.addEventListener('alpine:init', () => {
    Alpine.data('bulkTariffMapping', () => ({
        billingTariffId: '',
        filterType: 'all',
        classGroupId: '',
        students: [],
        selectedIds: [],
        loading: false,

        async fetchStudents() {
            this.selectedIds = [];
            this.students = [];

            if (!this.billingTariffId) return;
            if (this.filterType === 'class_group' && !this.classGroupId) return;

            this.loading = true;

            const params = new URLSearchParams({
                billing_tariff_id: this.billingTariffId,
                filter_type: this.filterType,
            });

            if (this.filterType === 'class_group') {
                params.set('class_group_id', this.classGroupId);
            }

            try {
                const response = await fetch(`${window.financeRoutes.eligibleStudentTariffs}?${params.toString()}`, {
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
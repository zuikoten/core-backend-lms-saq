document.addEventListener('alpine:init', () => {
    Alpine.data('classGroupPlotting', ({ students, classGroups }) => ({
        students,
        classGroups,
        activeTab: 'unassigned',
        search: '',
        selectedIds: [],
        targetClassGroupId: '',

        avatarPalette: [
            'bg-indigo-100 text-indigo-600',
            'bg-emerald-100 text-emerald-600',
            'bg-amber-100 text-amber-600',
            'bg-rose-100 text-rose-600',
            'bg-sky-100 text-sky-600',
            'bg-violet-100 text-violet-600',
        ],

        init() {
            // Default ke tab kerja utama; kalau semua sudah beres, tampilkan semua siswa.
            this.activeTab = this.unassignedCount > 0 ? 'unassigned' : 'all';
        },

        get unassignedCount() {
            return this.students.filter(s => !s.class_group_id).length;
        },

        get placedCount() {
            return this.students.length - this.unassignedCount;
        },

        get progress() {
            return this.students.length === 0
                ? 0
                : Math.round((this.placedCount / this.students.length) * 100);
        },

        get tabs() {
            return [
                { key: 'unassigned', label: 'Belum Ditempatkan', count: this.unassignedCount },
                ...this.classGroups.map(group => ({
                    key: group.id,
                    label: group.name,
                    count: this.students.filter(s => s.class_group_id === group.id).length,
                })),
                { key: 'all', label: 'Semua', count: this.students.length },
            ];
        },

        get filteredStudents() {
            const keyword = this.search.trim().toLowerCase();

            return this.students.filter(student => {
                if (this.activeTab === 'unassigned' && student.class_group_id) return false;
                if (typeof this.activeTab === 'number' && student.class_group_id !== this.activeTab) return false;
                if (keyword && !student.name.toLowerCase().includes(keyword)) return false;

                return true;
            });
        },

        get targetOptions() {
            // Di tab rombel, rombel itu sendiri tidak relevan sebagai tujuan.
            return this.classGroups.filter(group => group.id !== this.activeTab);
        },

        get actionLabel() {
            if (this.activeTab === 'unassigned') return 'Tempatkan';
            if (this.activeTab === 'all') return 'Terapkan';

            return 'Pindahkan';
        },

        get allVisibleSelected() {
            return this.filteredStudents.length > 0
                && this.filteredStudents.every(s => this.selectedIds.includes(s.id));
        },

        setTab(key) {
            this.activeTab = key;
            this.search = '';
            this.selectedIds = [];
            this.targetClassGroupId = '';
        },

        isSelected(id) {
            return this.selectedIds.includes(id);
        },

        toggle(id) {
            this.selectedIds = this.isSelected(id)
                ? this.selectedIds.filter(selectedId => selectedId !== id)
                : [...this.selectedIds, id];
        },

        toggleAllVisible(checked) {
            const visibleIds = this.filteredStudents.map(s => s.id);

            this.selectedIds = checked
                ? [...new Set([...this.selectedIds, ...visibleIds])]
                : this.selectedIds.filter(id => !visibleIds.includes(id));
        },

        avatarClass(id) {
            return this.avatarPalette[id % this.avatarPalette.length];
        },
    }));
});

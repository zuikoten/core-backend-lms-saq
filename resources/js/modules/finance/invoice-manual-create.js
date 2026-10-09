const blankItem = () => ({
    billing_tariff_id: '',
    billing_type_id: '',
    item_name: '',
    amount: '',
    adjustment_note: '',
});

document.addEventListener('alpine:init', () => {
    // initialItems = old('items') dari server, supaya isian tidak hilang saat validasi gagal.
    Alpine.data('manualInvoiceForm', (billingTariffs, billingTypes = [], initialItems = []) => ({
        billingTariffs: billingTariffs,
        billingTypes: billingTypes,
        items: initialItems.length
            ? initialItems.map(item => ({ ...blankItem(), ...item }))
            : [blankItem()],

        addItem() {
            this.items.push(blankItem());
        },
        removeItem(index) {
            if (this.items.length > 1) this.items.splice(index, 1);
        },
        tariffFor(index) {
            return this.billingTariffs.find(t => t.id == this.items[index].billing_tariff_id);
        },
        fillFromTariff(index) {
            const tariff = this.tariffFor(index);
            if (tariff) {
                this.items[index].billing_type_id = tariff.billing_type_id;
                this.items[index].item_name = tariff.label;
                this.items[index].amount = tariff.amount;
                this.items[index].adjustment_note = '';
            }
        },
        // Nominal diubah dari nominal tarif → form meminta alasan (wajib, dicek juga di server).
        isAdjusted(index) {
            const tariff = this.tariffFor(index);
            if (!tariff) return false;
            const amount = Math.round((parseFloat(this.items[index].amount) || 0) * 100);
            return amount !== Math.round(parseFloat(tariff.amount) * 100);
        },
        tariffAmountLabel(index) {
            const tariff = this.tariffFor(index);
            return tariff ? 'Rp' + parseFloat(tariff.amount).toLocaleString('id-ID') : '';
        },
        get total() {
            return this.items.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
        },
    }));
});

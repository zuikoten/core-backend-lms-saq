document.addEventListener('alpine:init', () => {
    Alpine.data('manualInvoiceForm', (billingTariffs) => ({
        billingTariffs: billingTariffs,
        items: [{ billing_tariff_id: '', billing_type_id: '', item_name: '', amount: '' }],

        addItem() {
            this.items.push({ billing_tariff_id: '', billing_type_id: '', item_name: '', amount: '' });
        },
        removeItem(index) {
            if (this.items.length > 1) this.items.splice(index, 1);
        },
        fillFromTariff(index) {
            const tariff = this.billingTariffs.find(t => t.id == this.items[index].billing_tariff_id);
            if (tariff) {
                this.items[index].billing_type_id = tariff.billing_type_id;
                this.items[index].item_name = tariff.label;
                this.items[index].amount = tariff.amount;
            }
        },
        get total() {
            return this.items.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
        },
    }));
});
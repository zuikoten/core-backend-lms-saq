document.addEventListener('alpine:init', () => {
    Alpine.data('otpInput', () => ({
        digits: ['', '', '', '', '', ''],

        onInput(index, event) {
            const value = event.target.value.replace(/\D/g, '').slice(-1);
            this.digits[index] = value;

            if (value && index < 5) {
                this.$nextTick(() => this.$refs['digit' + (index + 1)]?.focus());
            }
        },

        onBackspace(index) {
            if (!this.digits[index] && index > 0) {
                this.$refs['digit' + (index - 1)]?.focus();
            }
        },

        handlePaste(event) {
            const text = (event.clipboardData || window.clipboardData)
                .getData('text')
                .replace(/\D/g, '')
                .slice(0, 6);

            text.split('').forEach((char, i) => {
                this.digits[i] = char;
            });

            this.$nextTick(() => {
                const lastIndex = Math.min(text.length, 6) - 1;
                if (lastIndex >= 0) {
                    this.$refs['digit' + lastIndex]?.focus();
                }
            });
        },
    }));
});
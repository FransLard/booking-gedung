document.addEventListener('alpine:init', () => {
    Alpine.data('bookingForm', (hargaGedung, bookedDatesRaw, hargaPerJam, minimumJam) => ({

        hargaGedung,
        hargaPerJam,
        minimumJam: minimumJam || 2,
        paymentType: '',
        addonQtys: {},
        addonPrices: [],

        bookedDates: bookedDatesRaw || [],
        currentMonth: new Date().getMonth(),
        currentYear: new Date().getFullYear(),
        selectedDate: null,
        selectedDateStr: '',
        calendarOpen: true,
        duration: 1,
        selesaiDateStr: '',

        jamMulai: '',
        jamSelesai: '',
        timeSlots: [],

        init() {
            this.generateTimeSlots();

            const oldTanggal = document.querySelector('input[name="tanggal"]')?.value;
            if (oldTanggal) {
                this.selectedDateStr = oldTanggal;
                this.selectedDate = new Date(oldTanggal + 'T00:00:00');
            }
            const oldMulai = document.querySelector('input[name="jam_mulai"]')?.value;
            if (oldMulai) this.jamMulai = oldMulai;
            const oldSelesai = document.querySelector('input[name="jam_selesai"]')?.value;
            if (oldSelesai) this.jamSelesai = oldSelesai;

            document.querySelectorAll('input[name^="add_ons["]').forEach(el => {
                const match = el.name.match(/add_ons\[(\d+)\]/);
                if (match) {
                    const id = parseInt(match[1]);
                    const val = parseInt(el.value);
                    if (val > 0) {
                        this.addonQtys[id] = val;
                    }
                }
            });
        },

        get monthNames() {
            return ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        },

        get dayNames() {
            return ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
        },

        get firstDayOfMonth() {
            return new Date(this.currentYear, this.currentMonth, 1).getDay();
        },

        get daysInMonth() {
            return new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
        },

        get calendarDays() {
            const days = [];

            for (let i = 0; i < this.firstDayOfMonth; i++) {
                days.push(null);
            }

            for (let d = 1; d <= this.daysInMonth; d++) {
                days.push(d);
            }
            return days;
        },

        prevMonth() {
            if (this.currentMonth === 0) {
                this.currentMonth = 11;
                this.currentYear--;
            } else {
                this.currentMonth--;
            }
        },

        nextMonth() {
            if (this.currentMonth === 11) {
                this.currentMonth = 0;
                this.currentYear++;
            } else {
                this.currentMonth++;
            }
        },

        isToday(day) {
            const today = new Date();
            return day === today.getDate() &&
                   this.currentMonth === today.getMonth() &&
                   this.currentYear === today.getFullYear();
        },

        isPast(day) {
            const date = new Date(this.currentYear, this.currentMonth, day);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return date < today;
        },

        isBooked(day) {
            const dateStr = this.formatDateStr(day);
            return this.bookedDates.includes(dateStr);
        },

        isSelected(day) {
            if (!this.selectedDate) return false;
            return day === this.selectedDate.getDate() &&
                   this.currentMonth === this.selectedDate.getMonth() &&
                   this.currentYear === this.selectedDate.getFullYear();
        },

        selectDate(day) {
            if (this.isPast(day) || this.isBooked(day)) return;
            this.selectedDate = new Date(this.currentYear, this.currentMonth, day);
            this.selectedDateStr = this.formatDateStr(day);
            this.duration = 1;
            this.updateSelesaiDate();

            document.querySelector('input[name="tanggal"]').value = this.selectedDateStr;
        },

        formatDateStr(day) {
            const m = String(this.currentMonth + 1).padStart(2, '0');
            const d = String(day).padStart(2, '0');
            return `${this.currentYear}-${m}-${d}`;
        },

        selectDuration(days) {
            this.duration = days;
            this.updateSelesaiDate();
        },

        updateSelesaiDate() {
            if (!this.selectedDate) return;
            const end = new Date(this.selectedDate);
            end.setDate(end.getDate() + this.duration - 1);
            const y = end.getFullYear();
            const m = String(end.getMonth() + 1).padStart(2, '0');
            const d = String(end.getDate()).padStart(2, '0');
            this.selesaiDateStr = `${y}-${m}-${d}`;
            document.querySelector('input[name="tanggal_selesai"]').value = this.selesaiDateStr;
        },

        dayClass(day) {
            if (!day) return 'invisible';
            if (this.isPast(day)) return 'text-gray-300 cursor-not-allowed';
            if (this.isBooked(day)) return 'text-red-600 bg-red-50 cursor-not-allowed rounded-full font-medium';
            if (this.isSelected(day)) return 'text-white bg-gradient-to-r from-mirage-600 to-blue-500 rounded-full shadow-md font-bold';
            if (this.isToday(day)) return 'text-mirage-600 font-bold hover:bg-mirage-50 rounded-full cursor-pointer border-2 border-mirage-300';
            return 'text-gray-700 hover:bg-mirage-50 rounded-full cursor-pointer';
        },

        generateTimeSlots() {
            this.timeSlots = [];
            for (let h = 8; h <= 21; h++) {
                const val = String(h).padStart(2, '0') + ':00';
                const label = `${String(h).padStart(2, '0')}:00`;
                this.timeSlots.push({ value: val, label });
                if (h < 21) {
                    const val30 = String(h).padStart(2, '0') + ':30';
                    const label30 = `${String(h).padStart(2, '0')}:30`;
                    this.timeSlots.push({ value: val30, label: label30 });
                }
            }
        },

        get availableEndSlots() {
            if (!this.jamMulai) return [];
            const startIdx = this.timeSlots.findIndex(s => s.value === this.jamMulai);
            return this.timeSlots.slice(startIdx + 1);
        },

        selectMulai(val) {
            this.jamMulai = val;
            this.jamSelesai = '';
            document.querySelector('input[name="jam_mulai"]').value = val;
        },

        selectSelesai(val) {
            this.jamSelesai = val;
            document.querySelector('input[name="jam_selesai"]').value = val;
        },

        isSlotBooked(timeSlot) {

            return false;
        },

        get jamPakai() {
            if (!this.jamMulai || !this.jamSelesai) return 0;
            const [mh, mm] = this.jamMulai.split(':').map(Number);
            const [sh, sm] = this.jamSelesai.split(':').map(Number);
            const menit = (sh * 60 + sm) - (mh * 60 + mm);
            return Math.max(Math.ceil(menit / 60), this.minimumJam);
        },

        get jumlahHargaGedung() {
            if (this.duration === 1 && this.hargaPerJam) {
                return Math.min(this.hargaPerJam * this.jamPakai, this.hargaGedung);
            }
            return this.hargaGedung * this.duration;
        },

        get totalHarga() {
            let total = this.jumlahHargaGedung;
            for (const id in this.addonQtys) {
                total += (this.addonQtys[id] || 0) * (this.addonPrices[id] || 0);
            }
            return total;
        },

        updateAddonQty(id, price, delta) {
            const current = this.addonQtys[id] || 0;
            const next = Math.max(0, current + delta);
            this.addonQtys[id] = next;
            this.addonPrices[id] = price;
            const input = document.querySelector(`input[name="add_ons[${id}]"]`);
            if (input) input.value = next;
        },

        toggleFlatAddon(id, price) {
            const current = this.addonQtys[id] || 0;
            const next = current > 0 ? 0 : 1;
            this.addonQtys[id] = next;
            this.addonPrices[id] = price;
            const input = document.querySelector(`input[name="add_ons[${id}]"]`);
            if (input) input.value = next;
        },

        formatRupiah(value) {
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },
    }));
});

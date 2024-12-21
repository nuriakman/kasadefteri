<template>
  <q-page padding>
    <div class="row q-col-gutter-md">
      <!-- Gün Sonu Formu -->
      <div class="col-12 col-md-4">
        <q-card>
          <q-card-section>
            <div class="text-h6">Gün Sonu İşlemi</div>
          </q-card-section>

          <q-card-section>
            <q-form @submit="handleDayEnd" class="q-gutter-md">
              <q-input
                v-model="selectedDate"
                type="date"
                label="Tarih"
                :rules="[val => !!val || 'Tarih seçiniz']"
              />

              <q-input
                v-model.number="shortage"
                type="number"
                label="Kasa Eksiği"
                prefix="₺"
                :rules="[val => val >= 0 || 'Geçersiz tutar']"
              />

              <q-input
                v-model.number="excess"
                type="number"
                label="Kasa Fazlası"
                prefix="₺"
                :rules="[val => val >= 0 || 'Geçersiz tutar']"
              />

              <q-input
                v-model="notes"
                type="textarea"
                label="Notlar"
              />

              <q-btn
                label="Gün Sonu Al"
                type="submit"
                color="primary"
                :loading="loading"
                class="full-width"
              />
            </q-form>
          </q-card-section>
        </q-card>
      </div>

      <!-- Gün Özeti -->
      <div class="col-12 col-md-8">
        <q-card>
          <q-card-section>
            <div class="text-h6">Gün Özeti</div>
          </q-card-section>

          <q-card-section>
            <div class="row q-col-gutter-md">
              <!-- Gelir Özeti -->
              <div class="col-12 col-md-6">
                <q-card class="bg-positive text-white">
                  <q-card-section>
                    <div class="text-h6">Toplam Gelir</div>
                    <div class="text-h4">{{ formatCurrency(summary.totalIncome) }}</div>
                  </q-card-section>
                </q-card>
              </div>

              <!-- Gider Özeti -->
              <div class="col-12 col-md-6">
                <q-card class="bg-negative text-white">
                  <q-card-section>
                    <div class="text-h6">Toplam Gider</div>
                    <div class="text-h4">{{ formatCurrency(summary.totalExpense) }}</div>
                  </q-card-section>
                </q-card>
              </div>

              <!-- İşlem Sayısı -->
              <div class="col-12 col-md-6">
                <q-card class="bg-info text-white">
                  <q-card-section>
                    <div class="text-h6">İşlem Sayısı</div>
                    <div class="text-h4">{{ summary.transactionCount }}</div>
                  </q-card-section>
                </q-card>
              </div>

              <!-- Net Bakiye -->
              <div class="col-12 col-md-6">
                <q-card :class="summary.balance >= 0 ? 'bg-primary' : 'bg-warning'" class="text-white">
                  <q-card-section>
                    <div class="text-h6">Net Bakiye</div>
                    <div class="text-h4">{{ formatCurrency(summary.balance) }}</div>
                  </q-card-section>
                </q-card>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useQuasar } from 'quasar';
import { date } from 'quasar';
import { api } from 'boot/axios';

const $q = useQuasar();

const selectedDate = ref(date.formatDate(Date.now(), 'YYYY-MM-DD'));
const shortage = ref(0);
const excess = ref(0);
const notes = ref('');
const loading = ref(false);

const summary = reactive({
  totalIncome: 0,
  totalExpense: 0,
  transactionCount: 0,
  balance: 0
});

// Para formatı
const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY'
  }).format(value);
};

// Gün özeti yükle
const loadSummary = async () => {
  try {
    const response = await api.get('/transactions/summary', {
      params: { date: selectedDate.value }
    });
    Object.assign(summary, response.data);
  } catch (error) {
    console.error('Özet yüklenirken hata:', error);
    $q.notify({
      type: 'negative',
      message: 'Gün özeti yüklenirken hata oluştu'
    });
  }
};

// Gün sonu işlemi
const handleDayEnd = async () => {
  loading.value = true;
  try {
    await api.post('/transactions/day-end', {
      date: selectedDate.value,
      shortage: shortage.value,
      excess: excess.value,
      notes: notes.value
    });

    $q.notify({
      type: 'positive',
      message: 'Gün sonu işlemi başarıyla tamamlandı'
    });

    // Formu temizle
    shortage.value = 0;
    excess.value = 0;
    notes.value = '';
    
    // Özeti güncelle
    await loadSummary();
  } catch (error) {
    console.error('Gün sonu işlemi hatası:', error);
    $q.notify({
      type: 'negative',
      message: 'Gün sonu işlemi sırasında hata oluştu'
    });
  } finally {
    loading.value = false;
  }
};

// Sayfa yüklendiğinde özeti getir
loadSummary();
</script>

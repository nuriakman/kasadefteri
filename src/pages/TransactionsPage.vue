<template>
  <q-page padding>
    <div class="row q-col-gutter-md">
      <!-- İşlem Formu -->
      <div class="col-12 col-md-4">
        <q-card>
          <q-card-section>
            <div class="text-h6">Yeni İşlem</div>
          </q-card-section>

          <q-card-section>
            <TransactionForm @saved="loadTransactions" />
          </q-card-section>
        </q-card>
      </div>

      <!-- İşlem Listesi -->
      <div class="col-12 col-md-8">
        <q-card>
          <q-card-section class="row items-center">
            <div class="text-h6">Günlük İşlemler</div>
            <q-space />
            <q-input
              v-model="selectedDate"
              type="date"
              dense
              outlined
            />
          </q-card-section>

          <q-card-section>
            <q-table
              :rows="transactions"
              :columns="columns"
              row-key="id"
              :loading="loading"
              :pagination="{ rowsPerPage: 10 }"
            >
              <!-- Tutar sütunu özel gösterimi -->
              <template v-slot:body-cell-amount="props">
                <q-td :props="props">
                  <span :class="props.row.type === 'income' ? 'text-positive' : 'text-negative'">
                    {{ formatCurrency(props.row.amount) }}
                  </span>
                </q-td>
              </template>

              <!-- İşlem tipi sütunu özel gösterimi -->
              <template v-slot:body-cell-type="props">
                <q-td :props="props">
                  <q-chip
                    :color="props.row.type === 'income' ? 'positive' : 'negative'"
                    text-color="white"
                    dense
                  >
                    {{ props.row.type === 'income' ? 'Gelir' : 'Gider' }}
                  </q-chip>
                </q-td>
              </template>
            </q-table>

            <!-- Toplam Özeti -->
            <div class="row q-mt-md q-gutter-sm justify-end">
              <q-chip color="positive" text-color="white">
                Toplam Gelir: {{ formatCurrency(totalIncome) }}
              </q-chip>
              <q-chip color="negative" text-color="white">
                Toplam Gider: {{ formatCurrency(totalExpense) }}
              </q-chip>
              <q-chip
                :color="balance >= 0 ? 'primary' : 'warning'"
                text-color="white"
              >
                Bakiye: {{ formatCurrency(balance) }}
              </q-chip>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { date } from 'quasar';
import { api } from 'boot/axios';
import TransactionForm from 'components/TransactionForm.vue';

interface Transaction {
  id: number;
  amount: number;
  type: 'income' | 'expense';
  description: string;
  categoryName: string;
  registerName: string;
  transactionDate: string;
}

const loading = ref(false);
const transactions = ref<Transaction[]>([]);
const selectedDate = ref(date.formatDate(Date.now(), 'YYYY-MM-DD'));

// Tablo sütunları
const columns = [
  {
    name: 'transactionDate',
    required: true,
    label: 'Tarih',
    align: 'left',
    field: (row: Transaction) => date.formatDate(row.transactionDate, 'DD.MM.YYYY HH:mm'),
    sortable: true
  },
  {
    name: 'type',
    required: true,
    label: 'Tür',
    align: 'left',
    field: 'type',
    sortable: true
  },
  {
    name: 'amount',
    required: true,
    label: 'Tutar',
    align: 'right',
    field: 'amount',
    sortable: true
  },
  {
    name: 'categoryName',
    required: true,
    label: 'Kategori',
    align: 'left',
    field: 'categoryName',
    sortable: true
  },
  {
    name: 'registerName',
    required: true,
    label: 'Kasa',
    align: 'left',
    field: 'registerName',
    sortable: true
  },
  {
    name: 'description',
    required: true,
    label: 'Açıklama',
    align: 'left',
    field: 'description'
  }
];

// Hesaplamalar
const totalIncome = computed(() => 
  transactions.value
    .filter(t => t.type === 'income')
    .reduce((sum, t) => sum + t.amount, 0)
);

const totalExpense = computed(() => 
  transactions.value
    .filter(t => t.type === 'expense')
    .reduce((sum, t) => sum + t.amount, 0)
);

const balance = computed(() => totalIncome.value - totalExpense.value);

// Para formatı
const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY'
  }).format(value);
};

// İşlemleri yükle
const loadTransactions = async () => {
  loading.value = true;
  try {
    const response = await api.get('/transactions', {
      params: { date: selectedDate.value }
    });
    transactions.value = response.data.transactions;
  } catch (error) {
    console.error('İşlemler yüklenirken hata:', error);
  } finally {
    loading.value = false;
  }
};

// Tarih değiştiğinde işlemleri yeniden yükle
watch(selectedDate, () => {
  loadTransactions();
});

// Sayfa yüklendiğinde işlemleri getir
onMounted(() => {
  loadTransactions();
});
</script>

<template>
  <q-form @submit="onSubmit" class="q-gutter-md">
    <!-- İşlem Tipi -->
    <q-select
      v-model="type"
      :options="typeOptions"
      label="İşlem Tipi"
      :rules="[val => !!val || 'İşlem tipi seçiniz']"
    />

    <!-- Tutar -->
    <q-input
      v-model.number="amount"
      type="number"
      label="Tutar"
      :rules="[
        val => !!val || 'Tutar giriniz',
        val => val > 0 || 'Tutar 0\'dan büyük olmalıdır'
      ]"
    />

    <!-- Kategori -->
    <q-select
      v-model="category"
      :options="categories"
      label="Kategori"
      option-label="name"
      option-value="id"
      :rules="[val => !!val || 'Kategori seçiniz']"
    />

    <!-- Kasa Türü -->
    <q-select
      v-model="registerType"
      :options="registerTypes"
      label="Kasa Türü"
      option-label="name"
      option-value="id"
      :rules="[val => !!val || 'Kasa türü seçiniz']"
    />

    <!-- Açıklama -->
    <q-input
      v-model="description"
      type="textarea"
      label="Açıklama"
      :rules="[val => !!val || 'Açıklama giriniz']"
    />

    <!-- Tarih -->
    <q-input
      v-model="transactionDate"
      type="date"
      label="Tarih"
      :rules="[val => !!val || 'Tarih seçiniz']"
    />

    <!-- Kaydet Butonu -->
    <q-btn
      label="Kaydet"
      type="submit"
      color="primary"
      :loading="loading"
    />
  </q-form>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useQuasar } from 'quasar';
import { api } from 'boot/axios';

const $q = useQuasar();

// Form alanları
const type = ref(null);
const amount = ref(0);
const category = ref(null);
const registerType = ref(null);
const description = ref('');
const transactionDate = ref('');
const loading = ref(false);

// Seçenekler
const typeOptions = [
  { label: 'Gelir', value: 'income' },
  { label: 'Gider', value: 'expense' }
];

// Kategoriler ve kasa türleri API'den gelecek
const categories = ref([]);
const registerTypes = ref([]);

// Kategori ve kasa türlerini yükle
const loadOptions = async () => {
  try {
    const [categoriesRes, registerTypesRes] = await Promise.all([
      api.get('/categories'),
      api.get('/register-types')
    ]);
    categories.value = categoriesRes.data;
    registerTypes.value = registerTypesRes.data;
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'Veriler yüklenirken hata oluştu'
    });
  }
};

// Form gönderimi
const onSubmit = async () => {
  loading.value = true;
  try {
    await api.post('/transactions', {
      type: type.value,
      amount: amount.value,
      categoryId: category.value.id,
      registerType: registerType.value.id,
      description: description.value,
      transactionDate: transactionDate.value
    });

    $q.notify({
      color: 'positive',
      message: 'İşlem başarıyla kaydedildi'
    });

    // Form alanlarını temizle
    type.value = null;
    amount.value = 0;
    category.value = null;
    registerType.value = null;
    description.value = '';
    transactionDate.value = '';

  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'İşlem kaydedilirken hata oluştu'
    });
  } finally {
    loading.value = false;
  }
};

// Komponent yüklendiğinde seçenekleri yükle
loadOptions();
</script>
